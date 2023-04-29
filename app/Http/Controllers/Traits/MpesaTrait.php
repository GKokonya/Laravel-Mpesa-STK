<?php
namespace App\Http\Controllers\Traits;
trait MpesaTrait {
 #get access token from mpesa
 public function getAccessToken()
 {
    try{
        
        $url = env('MPESA_ENV') == 'sandbox'
        ? 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials'
        : 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
   
        $curl = curl_init($url);
        curl_setopt_array(
            $curl,
            array(
                CURLOPT_HTTPHEADER => ['Content-Type: application/json; charset=utf8'],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER => false,
                CURLOPT_USERPWD => env('MPESA_CONSUMER_KEY') . ':' . env('MPESA_CONSUMER_SECRET')
            )
        );
        $response = json_decode(curl_exec($curl));
        curl_close($curl);

        if(empty($response)){
            throw new \Exception("Empty access token");
        }else{
            // return $response;
            return $response->access_token;
        }
            
    } catch (\Exception $e) {
        //display custom message
     $e->getMessage();
    }
    catch (\Exception $e) {
        //display custom message
         $e->getMessage();
    }

 }

 #make http request to mpesa
 public function makeHttp($url, $body)
 {
     $url = 'https://sandbox.safaricom.co.ke/mpesa/' . $url;
     $curl = curl_init();
     curl_setopt_array(
         $curl,
         array(
                 CURLOPT_URL => $url,
                 CURLOPT_HTTPHEADER => array('Content-Type:application/json','Authorization:Bearer '. $this->getAccessToken()),
                 CURLOPT_RETURNTRANSFER => true,
                 CURLOPT_POST => true,
                 CURLOPT_POSTFIELDS => json_encode($body)
             )
     );
     $curl_response = curl_exec($curl);
     curl_close($curl);
     return $curl_response;
 }

}