<?php

namespace App\Http\Controllers\Payments\Mpesa;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

use App\Http\Controllers\Traits\MpesaTrait;
use App\Http\Controllers\Traits\PaymentTrait;

use App\Models\MpesaIpAddress;
use App\Models\StkPayment;
use App\Models\Payment;

use App\Enums\StkPaymentStatus;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Facades\Http;

class StkController extends Controller
{
    use MpesaTrait, OrderTrait;

    #function to retrun STK push UI/view
    public function stk(){
        return view('mpesa.stk');
    }

     #donate with mpesa Sim TooKit(STK)
     public function donate(Request $request){
        try{
            $validated=$request->validate([
                'phone_number'  =>  'integer | required | min:9',
                'email' =>  'email | required',
                'amount'    =>  'integer | required'
            ]);

            $stk=$this->initiateStk($$validated['phone_number'],$validated['amount']);
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
                StkPayment::create([
                    'responseDescription'=>$responseDescription,
                    'responseCode'=>$responseCode,
                    'customerMessage'=>$customerMessage,
                    "merchantRequestID"=>$merchantRequestID, 
                    "checkoutRequestID"=>$checkoutRequestID,
                    "amount"=>$validated['amount'], 
                    "status"=>StkPaymentStatus::Requested,
                    "phoneNumber"=>$validated['phone_number']
                ]);
           
                DB::commit();

                return redirect()->route('processing',['checkoutRequestID'=>$checkoutRequestID]);
                
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
            //return redirect()->back()->withErrors(['error' => 'failed to make transacrion']);
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
            "CallBackURL"=>env('MPESA_TEST_URL').'/process-stk-callback',
            "AccountReference"=> "CompanyXLTD",
            "TransactionDesc"=> "Payment of X" 
          );

        $url = '/stkpush/v1/processrequest';

       return $response = $this->makeHttp($url, $curl_post_data);


    }

    /**
     * Use this function to process the STK push request callback
     * @return string
     */

    public function processStkCallback(){
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
        $mpesaIpAddress=MpesaIpAddress::where('ip_address',$clientIP);


        if(!empty($mpesaIpAddress) && $resultCode==0){
            #Process Mpesa Transaction 
            Storage::disk('local')->put('stk.txt',   $mpesaResponse);
            $this->insertSuccessfulStkPayment($jsonMpesaResponse);
        } 

        if(!empty($mpesaIpAddress) && $resultCode!=0){
            #insert failed stk request 
            Storage::disk('local')->put('stk.txt',$mpesaResponse);
            $this->insertFailedStkPayment($jsonMpesaResponse);
        } 
        
        if(empty($mpesaIpAddress)){
            Storage::disk('local')->put('fake-stk.txt',$mpesaResponse);
        }
        
    }

    #retrun processing page
    public function processing($checkoutRequestID){
        return view('processing',[['checkoutRequestID'=>$checkoutRequestID]]);
    }

    //public function verifyPayment(Request $request){
    public function confirmPayment(Request $request){
        try{
            $validated=$request->validate(['checkoutRequestID'=>'required']);
            
            $stkPayment=StkPayment::where('checkoutRequestID',$validated['checkoutRequestID'])->first();

            if($stkPayment){

                DB::beginTransaction();
                #store in database
                    $payment=Payment::where('tracking_id',$validated['checkoutRequestID'])->first();
                    $this->updatePaymentStatus($payment);
                DB::commit();

                return array('checkoutRequestID'=>$validated['checkoutRequestID'],'resultCode'=>$stkPayment->resultCode);
            }

        }catch(\Exception $e){
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }

    }

    #load success page
    public function success(){
        return view('success');
    }

    #load failure page
    public function failure(){
        return view('failure');
    }

    #store successful stk transaction into database
    private function insertSuccessfulStkPayment($jsonMpesaResponse){
        $resultCode=$jsonMpesaResponse->Body->stkCallback->ResultCode;
        $resultDesc=$jsonMpesaResponse->Body->stkCallback->ResultDesc;
        $checkoutRequestID=$jsonMpesaResponse->Body->stkCallback->CheckoutRequestID;
        $amount=$jsonMpesaResponse->Body->stkCallbackCallbackMetadata->Item[0]->Value;
        $mpesaReceiptNumber=$jsonMpesaResponse->Body->stkCallbackCallbackMetadata->Item[1]->Value;
        $balance=$jsonMpesaResponse->Body->stkCallbackCallbackMetadata->Item[2]->Value;
        $transactionDate=$jsonMpesaResponse->Body->stkCallbackCallbackMetadata->Item[3]->Value;

        #store in database
        $stkPayment=StkPayment::where('checkoutRequestID',$checkoutRequestID)->first();

        #check if transaction exist in database
        if($StkPayment){
            $payment=["resultDesc"=>$resultDesc,"resultCode"=>$resultCode,'status'=>StkPaymentStatus::Paid,"mpesaReceiptNumber"=>$mpesaReceiptNumber, "balance"=>$balance, "transactionDate"=>$transactionDate];
            $stkPayment->update($payment);
        }
        
    }

    private function insertFailedStkPayment($jsonMpesaResponse){
        $checkoutRequestID=$jsonMpesaResponse->Body->stkCallback->checkoutRequestID;
        #store in database
        $stkPayment=StkPayment::where('CheckoutRequestID',$checkoutRequestID)->first();

        if($StkPayment){
            $payment=["resultDesc"=>$resultDesc,"resultCode"=>$resultCode,'status'=>StkPaymentStatus::Failed];
            $stkPayment->update($payment);
        }
        
    }       

}