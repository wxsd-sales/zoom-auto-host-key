<?php

namespace App\Actions;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class AddUserAssistant
{
    public static function addUserAssistant(
        string $token, string $email, string $assistant, Pool $pool = null
    ): PromiseInterface|Response {
        $zoomApiUrl = config('services.zoom.api_url');
        $meetingsUrl = $zoomApiUrl.'/users/'.$email.'/assistants';
        $body = ['assistants' => [['email' => $assistant]]];

        return $pool?->withToken($token)->post($meetingsUrl, $body) ??
            Http::withToken($token)->post($meetingsUrl, $body);
    }

    public static function handle(
        string $token, string $email, string $assistant, Pool $pool = null
    ): PromiseInterface|Response {
        return self::addUserAssistant($token, $email, $assistant, $pool);
    }
}
