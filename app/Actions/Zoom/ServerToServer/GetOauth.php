<?php

namespace App\Actions\Zoom\ServerToServer;

use App\Library\Constants\OauthConstant;
use App\Library\Contracts\Zoom\ServerToServer\Actions\GetsOauth;
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
        string $accountId, string $clientId, string $clientSecret, Pool $pool = null
    ): PromiseInterface|Response {
        $oauthUrl = config('services.zoom.oauth_url');
        $oauthBody = [OauthConstant::GRANT_TYPE => 'account_credentials', 'account_id' => $accountId];

        return $pool?->withBasicAuth($clientId, $clientSecret)->asForm()->post($oauthUrl, $oauthBody) ??
            Http::withBasicAuth($clientId, $clientSecret)->asForm()->post($oauthUrl, $oauthBody);
    }

    public static function handle(
        string $accountId, string $clientId, string $clientSecret, Pool $pool = null
    ): PromiseInterface|Response {
        return self::getOauth($accountId, $clientId, $clientSecret, $pool);
    }
}
