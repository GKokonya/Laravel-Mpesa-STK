<?php
namespace App\Traits;
use App\Enums\PaymentStatus;

use App\Models\Payment;

trait PaymentTrait {
    
    #enter order in database
    public function initiatePayment($amount, $type, $tracking_id, $email=''){
        #create payment
        $payment_data=['amount' => $amount, 'status' => PaymentStatus::Pending, 'type' => $type, 'tracking_id' => $tracking_id];
        $payment=Payment::create($payment_data);
    }

    #make payment status and order status as paid
    public function updatePaymentStatus($tracking_id){
        $payment = Payment::where('tracking_id',$tracking_id)->first();
        if($payment){
            #update payment status to paid
            $payment->status =PaymentStatus::Paid;
            $payment->update();
        }
    }
}