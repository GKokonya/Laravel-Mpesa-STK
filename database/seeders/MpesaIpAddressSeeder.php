<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MpesaIpAddress;

class MpesaIpAddressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        MpesaIpAddress::create(['ip_address'=>'196.201.212.74']);
        MpesaIpAddress::create(['ip_address'=>'196.201.212.127']);
        MpesaIpAddress::create(['ip_address'=>'196.201.212.128']);
        MpesaIpAddress::create(['ip_address'=>'196.201.212.129']);
        MpesaIpAddress::create(['ip_address'=>'196.201.212.132']);
        MpesaIpAddress::create(['ip_address'=>'196.201.212.136']);
        MpesaIpAddress::create(['ip_address'=>'196.201.212.138']);
        MpesaIpAddress::create(['ip_address'=>'196.201.213.44']);
        MpesaIpAddress::create(['ip_address'=>'196.201.213.114']);
        MpesaIpAddress::create(['ip_address'=>'196.201.214.200']);
        MpesaIpAddress::create(['ip_address'=>'196.201.214.201']);
        MpesaIpAddress::create(['ip_address'=>'196.201.214.206']);
        MpesaIpAddress::create(['ip_address'=>'196.201.214.207']);
        MpesaIpAddress::create(['ip_address'=>'196.201.214.208']);
        MpesaIpAddress::create(['ip_address'=>'196.201.214.209']);
    }
}
