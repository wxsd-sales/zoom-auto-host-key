<?php

namespace App\Actions\Webex\WorkspaceIntegration;

use App\Library\Constants\OauthConstant;
use App\Library\Contracts\Webex\WorkspaceIntegration\Actions\GetsOauth;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class GetOauth implements GetsOauth
{
    /**
     * {@inheritDoc}
     */
    public static function getOauth(
        string $oauthUrl, string $clientId, string $clientSecret, string $refreshToken, Pool $pool = null
    ): PromiseInterface|Response {
        $oauthBody = [
            OauthConstant::GRANT_TYPE => OauthConstant::REFRESH_TOKEN,
            OauthConstant::CLIENT_ID => $clientId,
            OauthConstant::CLIENT_SECRET => $clientSecret,
            OauthConstant::REFRESH_TOKEN => $refreshToken,
        ];

        return $pool?->asForm()->post($oauthUrl, $oauthBody) ?? Http::asForm()->post($oauthUrl, $oauthBody);
    }

    public static function handle(
        string $oauthUrl, string $clientId, string $clientSecret, string $refreshToken, Pool $pool = null
    ): PromiseInterface|Response {
        return self::getOauth($oauthUrl, $clientId, $clientSecret, $refreshToken, $pool);
    }
}
