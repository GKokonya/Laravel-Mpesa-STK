<?php

namespace App\Enums;

enum STKRequestStatus:string {
    case Requested='requested';
    case  Paid='paid';
    case Failed='failed';

}