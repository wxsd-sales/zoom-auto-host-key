<?php

namespace App\Actions\Webex\WorkspaceIntegration;

use App\Models\Activation;
use App\Models\WbxWiAction;

class HandleUpdateApprovedAction
{
    public function handleUpdateApprovedAction(Activation $payload, WbxWiAction $wbxWiAction)
    {
        $payload->wbx_wi_manifest_version = intval($wbxWiAction->jwt_payload['manifestVersion']);
        $payload->wbxWiActions()->save($wbxWiAction);
        $payload->save();
    }
}
