<?php

namespace App\Services;

use App\Constants\ActivationConstant;
use App\Library\Constants\OauthConstant;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Validation\Validator;

class ZoomService
{
    /**
     * Retrieves OAuth token for a Server to Server application using provided data.
     */
    public static function getServerToServerOauth(array $data, Pool $pool = null): PromiseInterface|Response
    {
        $accountId = $data[OauthConstant::ACCOUNT_ID] ?? $data[ActivationConstant::ZM_S2S_ACCOUNT_ID];
        $clientId = $data[OauthConstant::CLIENT_ID] ?? $data[ActivationConstant::ZM_S2S_CLIENT_ID];
        $clientSecret = $data[OauthConstant::CLIENT_SECRET] ?? $data[ActivationConstant::ZM_S2S_CLIENT_SECRET];

        return \App\Actions\Zoom\ServerToServer\GetOauth::handle($accountId, $clientId, $clientSecret, $pool);
    }

    /**
     * Creates OAuth token validator for a Server to Server application.
     */
    public static function getServerToServerOauthValidator($data): Validator
    {
        return \App\Actions\Zoom\ServerToServer\MakeOauthValidator::handle($data);
    }
}
