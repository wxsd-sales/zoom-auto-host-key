<?php

namespace App\Library\Contracts\Zoom\ServerToServer\Actions;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Response;

interface GetsOauth
{
    /**
     * Retrieves OAuth token for a Server to Server application using provided data.
     */
    public static function getOauth(
        string $accountId, string $clientId, string $clientSecret
    ): PromiseInterface|Response;
}
