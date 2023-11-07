<?php

namespace App\Contracts;

use App\Models\Activation;
use App\Models\WbxWiOauth;
use DateTime;

interface UpsertsWbxWiOauth
{
    public static function upsertWbxWiOauth(Activation $activation, DateTime $time): WbxWiOauth;
}
