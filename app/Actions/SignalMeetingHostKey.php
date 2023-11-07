<?php

namespace App\Actions;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class SignalMeetingHostKey implements \App\Contracts\SignalsMeetingHostKey
{
    /**
     * {@inheritDoc}
     */
    public static function signalMeetingHostKey(
        string $token, string $deviceId, string $callId, string $dtmfString, Pool $pool = null
    ): PromiseInterface|Response {
        $webexApiUrl = config('services.webex.api_url');
        $arguments = ['CallId' => $callId, 'DTMFString' => $dtmfString, 'Feedback' => 'Silent'];
        $xapiUrl = $webexApiUrl.'/xapi/command/Call.DTMFSend';
        $body = compact('deviceId', 'arguments');

        return $pool?->withToken($token)->post($xapiUrl, $body) ?? Http::withToken($token)->post($xapiUrl, $body);
    }

    public static function handle(
        string $token, string $deviceId, string $callId, string $dtmfString, Pool $pool = null
    ): PromiseInterface|Response {
        return self::signalMeetingHostKey($token, $deviceId, $callId, $dtmfString, $pool);
    }
}
