<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Traits\MpesaTrait;
use App\Traits\PaymentTrait;

use App\Models\MpesaIpAddress;
use App\Models\STKRequest;
use App\Models\Payment;

use App\Enums\STKRequestStatus;





use Illuminate\Support\Facades\Http;
class STKRequestController extends Controller
{
    //
    
    use MpesaTrait, paymentTrait;

    #function to retrun STK push UI/view
    public function stk(){
        return view('stk-requests.stk');
    }

    public function fake(){
        $url = env('MPESA_TEST_URL').'/stk-requests/verify';
        $data='
        {
            "Body": {
            
                "stkCallback": {
                
                    "MerchantRequestID": "29115-34620561-11",
                    
                    "CheckoutRequestID": "ws_CO_1912201910203639725",
                    
                    "ResultCode": 0,
                    
                    "ResultDesc": "The service request is processed successfully.",
                
                    "CallbackMetadata": {
                    
                        "Item": [
                            {"Name": "Amount","Value": 2000.00},
                            {"Name": "MpesaReceiptNumber","Value": "ZNLJ7RTD1SK"},
                            {"Name": "TransactionDate","Value": 20191219102115},
                            {"Name": "PhoneNumber","Value": 254700000000}
                        ]
                    }
                
                }
            }
        }';

        $curl = curl_init();
        curl_setopt_array(
            $curl,
            array(
                    CURLOPT_URL => $url,
                    CURLOPT_HTTPHEADER => array('Content-Type:application/json'),
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => $data
                )
            );
        $curl_response = curl_exec($curl);
        curl_close($curl);
        return print_r($curl_response);


    }

     #donate with mpesa Sim TooKit(STK)
     public function donate(Request $request){
        try{
            $validated=$request->validate([
                'phone_number'  =>  'integer | required ',
                'email' =>  'email | required',
                'amount'    =>  'integer | required',
            ]);

            $stk=$this->initiateStk($validated['phone_number'],$validated['amount']);
            $lnmo_response=json_decode($stk);
            
            $checkoutRequestID=$lnmo_response->CheckoutRequestID;
            $merchantRequestID=$lnmo_response->MerchantRequestID;
            $responseCode=$lnmo_response->ResponseCode;
            $responseDescription=$lnmo_response->ResponseDescription;
            $customerMessage=$lnmo_response->CustomerMessage;

            if($responseCode==0){

                DB::beginTransaction();
                $this->initiatePayment($validated['amount'], 'mpesa',  $checkoutRequestID);
                #store in database
                STKRequest::create([
                    'responseDescription'=>$responseDescription,
                    'responseCode'=>$responseCode,
                    'customerMessage'=>$customerMessage,
                    "merchantRequestID"=>$merchantRequestID, 
                    "checkoutRequestID"=>$checkoutRequestID,
                    "amount"=>$validated['amount'], 
                    "status"=>STKRequestStatus::Requested,
                    "phoneNumber"=>$validated['phone_number']
                ]);
           
                DB::commit();

                return redirect()->route('stk-requests.processing',['checkoutRequestID'=>$checkoutRequestID]);
                
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with(['stk_error' => $e->getMessage()]);
            //return redirect()->back()->withErrors(['stk_error' => 'failed to make transacrion']);
        }
        
    }

    #function to initiate stk push
    private function initiateStk($phone_number,$amount)
    {        
        $curl_post_data = array(
            "BusinessShortCode"=> env('MPESA_STK_SHORTCODE'),
            "Password"=> base64_encode(env('MPESA_STK_SHORTCODE').env('MPESA_PASSKEY').date('YmdHis')),
            "Timestamp"=> date('YmdHis'),
            "TransactionType"=> "CustomerPayBillOnline",
            "Amount"=> $amount,
            "PartyA"=> $phone_number,
            "PartyB"=>  env('MPESA_STK_SHORTCODE'),
            "PhoneNumber"=> $phone_number,
            "CallBackURL"=>env('MPESA_TEST_URL').'/stk_requests/verify',
            "AccountReference"=> "CompanyXLTD",
            "TransactionDesc"=> "Payment of X" 
          );

        $url = '/stkpush/v1/processrequest';

       return $response = $this->makeHttp($url, $curl_post_data);


    }

    /**
     * Use this method to verfiy the STK push request callback is coming from safaricom
     * @return string
     */

    public function verifyStkCallback(){
        /**Set timezone To Kenyan timezone */
        date_default_timezone_set('Africa/Nairobi');
        
        /**Get raw Response */
        $mpesaResponse = file_get_contents('php://input');

        /**Json decode raw Mpesa callback response */
        $jsonMpesaResponse=json_decode($mpesaResponse);

        $resultCode=$jsonMpesaResponse->Body->stkCallback->ResultCode;
        $checkoutRequestID=$jsonMpesaResponse->Body->stkCallback->CheckoutRequestID;


        //[HTTP_CF_CONNECTING_IP]=>196.201.214.208
        $clientIP=$_SERVER['REMOTE_ADDR'];

        $mpesaIpAddress=MpesaIpAddress::where('ip_address',$clientIP)->first();


        if(!empty($mpesaIpAddress) && $resultCode==0){
            #Process Mpesa Transaction 
            Log::channel('stk')->info(['mpesaResponse'=>$mpesaResponse]);
            $this->insertSuccessfulStkPayment($jsonMpesaResponse);
        } 

        if(!empty($mpesaIpAddress) && $resultCode!=0){
            #insert failed stk request 
            Log::channel('stk')->info(['mpesaResponse'=>$mpesaResponse]);
            $this->insertFailedStkPayment($jsonMpesaResponse);
        } 
        
        if(empty($mpesaIpAddress)){
            Log::channel('fake-stk')->info(['mpesaResponse'=>$mpesaResponse,['mpesaIP'=>$_SERVER['REMOTE_ADDR']]]);
        }
        //return ['data'=>'success'];
        
    }

    #retrun processing page
    public function processing($checkoutRequestID){
        session()->put('checkoutRequestID', $checkoutRequestID);
        return view('stk-requests.processing',['checkoutRequestID'=>$checkoutRequestID]);
    }

    // function verifyPayment(
    public function confirmPayment(Request $request){
        try{
            $validated=$request->validate(['checkoutRequestID'=>'required']);
            
            $STKRequest=STKRequest::where('checkoutRequestID',$validated['checkoutRequestID'])->first();

            if($STKRequest->resultCode=='0'){

                DB::beginTransaction();
                #store in database
                    $payment=Payment::where('tracking_id',$validated['checkoutRequestID'])->first();
                    $this->updatePaymentStatus($payment->tracking_id);
                DB::commit();

                return array('checkoutRequestID'=>$validated['checkoutRequestID'],'resultCode'=>$STKRequest->resultCode);
            }else{
                return array('checkoutRequestID'=>$validated['checkoutRequestID'],'resultCode'=>$STKRequest->resultCode);
            }

        }catch(\Exception $e){
            DB::rollBack();
            return redirect()->back()->with(['stk_error' => $e->getMessage()]);
        }

    }

    #load success page
    public function success(){
        return view('stk-requests.success');
    }

    #load failure page
    public function failure(){
        return view('stk-requests.failure');
    }

    #store successful stk transaction into database 
    private function insertSuccessfulStkPayment($jsonMpesaResponse){
        $resultCode=$jsonMpesaResponse->Body->stkCallback->ResultCode;
        $resultDesc=$jsonMpesaResponse->Body->stkCallback->ResultDesc;
        $checkoutRequestID=$jsonMpesaResponse->Body->stkCallback->CheckoutRequestID;
        $amount=$jsonMpesaResponse->Body->stkCallback->CallbackMetadata->Item[0]->Value;
        $mpesaReceiptNumber=$jsonMpesaResponse->Body->stkCallback->CallbackMetadata->Item[1]->Value;
        $balance=$jsonMpesaResponse->Body->stkCallback->CallbackMetadata->Item[2]->Value;
        $transactionDate=$jsonMpesaResponse->Body->stkCallback->CallbackMetadata->Item[3]->Value;

        #store in database
        $STKRequest=STKRequest::where('checkoutRequestID',$checkoutRequestID)->first();

        #check if transaction exist in database
        if($STKRequest){
            $payment=["resultDesc"=>$resultDesc,"resultCode"=>$resultCode,'status'=>STKRequestStatus::Paid,"mpesaReceiptNumber"=>$mpesaReceiptNumber, "balance"=>$balance, "transactionDate"=>$transactionDate];
            $STKRequest->update($payment);
        }
        
    }

    #store failed stk transaction into database 
    private function insertFailedSTKRequest($jsonMpesaResponse){
        $checkoutRequestID=$jsonMpesaResponse->Body->stkCallback->checkoutRequestID;
        #store in database
        $STKRequest=STKRequest::where('CheckoutRequestID',$checkoutRequestID)->first();

        if($STKRequest){
            $payment=["resultDesc"=>$resultDesc,"resultCode"=>$resultCode,'status'=>STKRequestStatus::Failed];
            $STKRequest->update($payment);
        }
        
    }  

        /**
     * Display a listing of the resource.
     */
    public function index()
    {
        
        return view( 'stk-requests.index' , [ 'stkRequests' => STKRequest::paginate(10) ]) ;
    }



    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $checkoutRequestID)
    {
        //
        $stkRequest = STKRequest::where('checkoutRequestID', $checkoutRequestID)->first();
        if($stkRequest){
            return view( 'stk-requests.edit' , [ 'stkRequest' => $stkRequest ]) ;
        }

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $checkoutRequestID)
    {


        try{
            $validated=$request->validate(['mpesaReceiptNumber' =>  'required']);
            $STKRequest = STKRequest::where('checkoutRequestID',$checkoutRequestID)->first();
            if($STKRequest){
                DB::beginTransaction();
                $this->updatePaymentStatus($checkoutRequestID);
                $payment=["resultDesc"=>'The service request is processed successfully',"resultCode"=>0,'status'=>STKRequestStatus::Paid,"mpesaReceiptNumber"=>$validated['mpesaReceiptNumber'], "transactionDate"=>date("Y-m-d")];
            
                $result=$STKRequest->update($payment);
                DB::commit();
                return redirect()->route('payments.index');
            }else{
               return redirect()->back()->with(['stk_error' => 'checkoutRequestID does not exist']);
            }
        }catch(\Exception $e){
            DB::rollBack();
            return redirect()->back()->with(['stk_error' => $e->getMessage()]);
        }
    }
}
