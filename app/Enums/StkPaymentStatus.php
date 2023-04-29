<?php

namespace App\Enums;

enum StkPaymentStatus:string {
    case Requested='requested';
    case  Paid='paid';
    case Failed='failed';

}