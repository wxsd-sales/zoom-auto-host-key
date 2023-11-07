<?php

namespace App\Actions\Webex\WorkspaceIntegration;

use App\Library\Contracts\Webex\WorkspaceIntegration\Actions\GetsManifest;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class GetManifest implements GetsManifest
{
    /**
     * {@inheritDoc}
     */
    public static function getManifest(
        string $manifestUrl, string $accessToken, Pool $pool = null
    ): PromiseInterface|Response {
        return $pool?->withToken($accessToken)->get($manifestUrl) ?? Http::withToken($accessToken)->get($manifestUrl);
    }

    public static function handle(
        string $manifestUrl, string $accessToken, Pool $pool = null
    ): PromiseInterface|Response {
        return self::getManifest($manifestUrl, $accessToken, $pool);
    }
}
