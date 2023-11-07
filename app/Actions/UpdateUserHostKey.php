<?php

namespace App\Actions;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class UpdateUserHostKey implements \App\Contracts\UpdatesUserHostKey
{
    /**
     * {@inheritDoc}
     */
    public static function updateUserHostKey(
        string $token, string $userId, string $key, Pool $pool = null
    ): PromiseInterface|Response {
        $zoomApiUrl = config('services.zoom.api_url');
        $usersUrl = $zoomApiUrl.'/users/'.$userId;
        $body = ['host_key' => $key];

        return $pool?->withToken($token)->patch($usersUrl, $body) ??
            Http::withToken($token)->patch($usersUrl, $body);
    }

    public static function handle(
        string $token, string $userId, string $key, Pool $pool = null
    ): PromiseInterface|Response {
        return self::updateUserHostKey($token, $userId, $key, $pool);
    }
}
