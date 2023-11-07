<?php

namespace App\Actions\Webex\WorkspaceIntegration;

use App\Models\Activation;
use App\Models\WbxWiAction;
use Illuminate\Support\Facades\Http;

class HandleHealthCheckAction
{
    public function handleHealthCheckAction(Activation $payload, WbxWiAction $wbxWiAction): array
    {
        $payload->wbxWiActions()->save($wbxWiAction);
        $appUrlResponse = Http::withToken($payload->wbxWiOauth->access_token)->get($payload->wbx_wi_app_url);
        $operationalState = 'operational';
        $tokensState = $appUrlResponse->failed()
            ? ($appUrlResponse->unauthorized() ? 'invalid' : 'unknown')
            : 'valid';

        return compact('operationalState', 'tokensState');
    }
}
