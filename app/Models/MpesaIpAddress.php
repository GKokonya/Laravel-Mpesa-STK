<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MpesaIpAddress extends Model
{
    use HasFactory;

    protected $table='mpesa_ip_addresses';
    protected $fillable = ['ip_address'];
}
