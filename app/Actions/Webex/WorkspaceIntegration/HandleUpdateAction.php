<?php

namespace App\Actions\Webex\WorkspaceIntegration;

use App\Library\Constants\OauthConstant;
use App\Models\Activation;
use App\Models\WbxWiAction;
use App\Services\OauthService;
use App\Services\WebexService;

class HandleUpdateAction
{
    public function handleUpdateAction(Activation $payload, WbxWiAction $wbxWiAction)
    {
        $payload->wbxWiActions()->save($wbxWiAction);
        $payload->wbx_wi_org_id = basename(base64_decode($wbxWiAction->jwt_payload['sub']));
        if ($wbxWiAction->jwt_payload['appUrl'] ?? null) {
            $payload->wbx_wi_app_url = $wbxWiAction->jwt_payload['appUrl'];
        }
        if ($wbxWiAction->jwt_payload['manifestUrl'] ?? null) {
            $payload->wbx_wi_manifest_url = $wbxWiAction->jwt_payload['manifestUrl'];
        }
        $payload->wbxWiOauth->update(OauthService::addExpiresAt(WebexService::getWorkspaceIntegrationOauth([
            OauthConstant::REFRESH_TOKEN => $wbxWiAction->jwt_payload['refreshToken'],
            OauthConstant::CLIENT_ID => $payload->wbx_wi_client_id,
            OauthConstant::CLIENT_SECRET => $payload->wbx_wi_client_secret,
        ])->json()));
        $payload->save();
    }
}
