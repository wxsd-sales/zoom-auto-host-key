<?php

namespace App\Contracts;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Response;

interface SignalsMeetingHostKey
{
    /**
     * Signals the meeting's host key to the device.
     *
     * @return PromiseInterface|Response
     */
    public static function signalMeetingHostKey(string $token, string $deviceId, string $callId, string $dtmfString);
}
