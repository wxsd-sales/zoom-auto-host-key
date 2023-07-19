<?php

namespace App\Services;

use App\Constants\ActivationConstant;
use App\Constants\OauthConstant;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;
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
        $oauthUrl = config('services.zoom.oauth_url');
        $oauthBody = [OauthConstant::GRANT_TYPE => 'account_credentials', 'account_id' => $accountId];

        return $pool?->withBasicAuth($clientId, $clientSecret)->asForm()->post($oauthUrl, $oauthBody) ??
            Http::withBasicAuth($clientId, $clientSecret)->asForm()->post($oauthUrl, $oauthBody);
    }

    /**
     * Creates OAuth token validator for a Server to Server application.
     */
    public static function getServerToServerOauthValidator($data): Validator
    {
        $requiredScopes = array_map(
            fn ($value) => $value['id'], config('services.zoom.server_to_server.scopes')
        );

        $rules = [
            OauthConstant::ACCESS_TOKEN => ['required', 'string'],
            OauthConstant::TOKEN_TYPE => ['required', 'string', Rule::in(['Bearer', 'bearer'])],
            OauthConstant::EXPIRES_IN => ['required', 'numeric', 'min:600'],
            OauthConstant::SCOPE => ['required', 'string', Rule::has($requiredScopes)],
        ];

        return \Validator::make($data, $rules);
    }
}
