<?php

namespace App\Actions;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class DisplayInputPromptForActiveCall implements \App\Contracts\DisplaysInputPromptForActiveCall
{
    /**
     * {@inheritDoc}
     */
    public static function displayInputPromptForActiveCall(
        string $token, string $deviceId, array $payload, ?string $email, Pool $pool = null
    ): PromiseInterface|Response {
        $webexApiUrl = config('services.webex.api_url');
        $arguments = [
            'FeedbackId' => json_encode($payload),
            'InputText' => $email ?? '',
            'InputType' => 'SingleLine',
            'KeyboardState' => 'Open',
            'Placeholder' => "Meeting Host's Email or Host Key",
            'SubmitText' => 'Submit',
            'Text' => 'Join as meeting host or as the account below.',
            'Title' => 'Zoom Auto Host Key',
        ];
        $xapiUrl = $webexApiUrl.'/xapi/command/UserInterface.Message.TextInput.Display';
        $body = compact('deviceId', 'arguments');

        return $pool?->withToken($token)->post($xapiUrl, $body) ?? Http::withToken($token)->post($xapiUrl, $body);
    }

    public static function handle(
        string $token, string $deviceId, array $payload, ?string $email, Pool $pool = null
    ): PromiseInterface|Response {
        return self::displayInputPromptForActiveCall($token, $deviceId, $payload, $email, $pool);
    }
}
