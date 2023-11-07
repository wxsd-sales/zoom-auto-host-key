<?php

namespace App\Actions;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class SendHostOtp implements \App\Contracts\SendsHostOpt
{
    /**
     * {@inheritDoc}
     */
    public static function sendHostOtp(
        string $token, string $toEmail, string $key, Pool $pool = null
    ): PromiseInterface|Response {
        $webexApiUrl = config('services.webex.api_url');
        $introduction = [
            'You are receiving this message since someone tried to join your Zoom Meeting on a RoomOS device.'.
            'If this wasn\'t you, please ignore this message.',
            'You may also mute this bot to ignore any future interruptions.',
        ];
        $body = [
            'toPersonEmail' => $toEmail,
            'markdown' => implode("\n", [
                implode("\n", $introduction),
                "\n",
                "### OTP: $key",
                'Use the above OTP if you would like to start the Zoom Meeting as the host, but do not '.
                'remember your Zoom Host Key. This OTP will be your new Zoom Host Key moving forward.',
                'Your Zoom Host Key is changed only if you use the one above on the target input prompt.',
            ]),
        ];

        return $pool?->withToken($token)->post($webexApiUrl.'/messages', $body) ??
            Http::withToken($token)->post($webexApiUrl.'/messages', $body);
    }

    public static function handle(
        string $token, string $toEmail, string $key, Pool $pool = null
    ): PromiseInterface|Response {
        return self::sendHostOtp($token, $toEmail, $key, $pool);
    }
}
