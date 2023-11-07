<?php

namespace App\Contracts;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Response;

interface GetsActiveCallStatus
{
    /**
     * Gets the details for an active call on a device.
     *
     * @return PromiseInterface|Response
     */
    public static function getActiveCallDetails(string $token, string $deviceId);
}
