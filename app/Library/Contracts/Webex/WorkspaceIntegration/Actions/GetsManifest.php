<?php

namespace App\Library\Contracts\Webex\WorkspaceIntegration\Actions;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Response;

interface GetsManifest
{
    /**
     * Retrieves Manifest response for a Workspace Integration using provided data.
     */
    public static function getManifest(string $manifestUrl, string $accessToken): PromiseInterface|Response;
}
