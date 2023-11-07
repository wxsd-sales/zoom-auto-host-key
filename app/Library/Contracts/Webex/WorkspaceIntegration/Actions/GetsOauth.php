<?php

namespace App\Library\Contracts\Webex\WorkspaceIntegration\Actions;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Response;

interface GetsOauth
{
    /**
     * Retrieves OAuth response token for a Workspace Integration using provided data.
     */
    public static function getOauth(
        string $oauthUrl, string $clientId, string $clientSecret, string $refreshToken
    ): PromiseInterface|Response;
}
