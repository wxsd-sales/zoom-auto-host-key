<?php

namespace App\Contracts;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Response;

interface DisplaysInputPromptForActiveCall
{
    /**
     * Displays a text input prompt for an active call.
     */
    public static function displayInputPromptForActiveCall(
        string $token, string $deviceId, array $payload, ?string $email
    ): PromiseInterface|Response;
}
