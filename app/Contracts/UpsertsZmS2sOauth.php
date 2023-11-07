<?php

namespace App\Contracts;

use App\Models\Activation;
use App\Models\ZmS2sOauth;
use DateTime;

interface UpsertsZmS2sOauth
{
    public static function upsertZmS2sOauth(Activation $activation, DateTime $time): ZmS2sOauth;
}
