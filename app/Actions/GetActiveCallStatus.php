<?php

namespace App\Actions;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class GetActiveCallStatus implements \App\Contracts\GetsActiveCallStatus
{
    /**
     * {@inheritDoc}
     */
    public static function getActiveCallDetails(string $token, string $deviceId, Pool $pool = null): PromiseInterface|Response
    {
        $webexApiUrl = config('services.webex.api_url');
        $name = 'Call[*].*';
        $body = compact('deviceId', 'name');

        return $pool?->withToken($token)->get($webexApiUrl.'/xapi/status', $body) ??
            Http::withToken($token)->get($webexApiUrl.'/xapi/status', $body);
    }

    public static function handle(string $token, string $deviceId, Pool $pool = null): PromiseInterface|Response
    {
        return self::getActiveCallDetails($token, $deviceId, $pool);
    }
}
