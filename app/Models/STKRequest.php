<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class STKRequest extends Model
{
    use HasFactory;
    /**
 * The attributes that are mass assignable.
 *
 * @var array<int, string>
 */
protected $fillable = [
    'merchantRequestID',
    'checkoutRequestID',
    'responseDescription',
    'responseCode',
    'customerMessage',
    'status', //requested , paid , failed
    'resultCode',
    'resultDesc',
    'amount',
    'mpesaReceiptNumber',
    'balance',
    'transactionDate',
    'phoneNumber'
];

protected $table='stk_requests';

public function payment(){
    return $this->belongsTo(Payment::class,'CheckoutRequestID','tracking_id');
}
}
