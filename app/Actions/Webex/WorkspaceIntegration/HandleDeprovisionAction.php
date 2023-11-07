<?php

namespace App\Actions\Webex\WorkspaceIntegration;

use App\Models\Activation;
use App\Models\WbxWiAction;

class HandleDeprovisionAction
{
    public static function handleDeprovisionAction(Activation $payload, WbxWiAction $wbxWiAction)
    {
        $payload->wbxWiActions()->save($wbxWiAction);
        $payload->wbxWiActions()->delete();
        $payload->wbxWiOauth->forceDelete();
        $payload->zmS2sOauth->forceDelete();
        $payload->delete();
    }
}
