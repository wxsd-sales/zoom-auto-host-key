<?php

namespace App\Http\Controllers;

use App\Constants\ActionsConstant;
use App\Http\Requests\ActionsRequest;
use App\Library\Constants\OauthConstant;
use App\Library\Enums\Webex\WorkspaceIntegration\JwtPayloadActionEnum;
use App\Models\Activation;
use App\Models\WbxWiAction;
use App\Services\OauthService;
use App\Services\WebexService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;

class ActionsController extends Controller
{
    /**
     * Handle deprovision user action for Workspace Integration.
     */
    protected function handleDeprovision(Activation $activation, WbxWiAction $wbxWiAction): Response
    {
        $activation->wbxWiActions()->save($wbxWiAction);
        $activation->wbxWiActions()->delete();
        $activation->wbxWiOauth->forceDelete();
        $activation->zmS2sOauth->forceDelete();
        $activation->delete();

        return response()->noContent();
    }

    /**
     * Handle health check user action for Workspace Integration.
     */
    protected function handleHealthCheck(Activation $activation, WbxWiAction $wbxWiAction): JsonResponse
    {
        $activation->wbxWiActions()->save($wbxWiAction);
        $appUrlResponse = Http::withToken($activation->wbxWiOauth->access_token)->get($activation->wbx_wi_app_url);
        $operationalState = 'operational';
        $tokensState = $appUrlResponse->failed()
            ? ($appUrlResponse->unauthorized() ? 'invalid' : 'unknown')
            : 'valid';

        return response()->json(compact('operationalState', 'tokensState'));
    }

    /**
     * Handle update user action for Workspace Integration.
     */
    protected function handleUpdate(Activation $activation, WbxWiAction $wbxWiAction): Response
    {
        $activation->wbxWiActions()->save($wbxWiAction);
        $activation->wbx_wi_org_id = basename(base64_decode($wbxWiAction->jwt_payload['sub']));
        if ($wbxWiAction->jwt_payload['appUrl'] ?? null) {
            $activation->wbx_wi_app_url = $wbxWiAction->jwt_payload['appUrl'];
        }
        if ($wbxWiAction->jwt_payload['manifestUrl'] ?? null) {
            $activation->wbx_wi_manifest_url = $wbxWiAction->jwt_payload['manifestUrl'];
        }
        $activation->wbxWiOauth->update(OauthService::addExpiresAt(WebexService::getWorkspaceIntegrationOauth([
            OauthConstant::REFRESH_TOKEN => $wbxWiAction->jwt_payload['refreshToken'],
            OauthConstant::CLIENT_ID => $activation->wbx_wi_client_id,
            OauthConstant::CLIENT_SECRET => $activation->wbx_wi_client_secret,
        ])->json()));
        $activation->save();

        return response()->noContent();
    }

    /**
     * Handle update approved user action for Workspace Integration.
     */
    protected function handleUpdateApproved(Activation $activation, WbxWiAction $wbxWiAction): Response
    {
        $activation->wbx_wi_manifest_version = intval($wbxWiAction->jwt_payload['manifestVersion']);
        $activation->wbxWiActions()->save($wbxWiAction);
        $activation->save();

        return response()->noContent();
    }

    public function store(ActionsRequest $request, Activation $activation)
    {
        $wbxWiAction = WbxWiAction::make(collect([
            ActionsConstant::JWT => $request->validated(ActionsConstant::JWT),
            ActionsConstant::JWT_PAYLOAD => $request->jwtPayload,
            ...$request->jwtPayload,
        ])->snakeCaseKeys(0)->toArray());

        return match (JwtPayloadActionEnum::tryFrom($request->jwtPayload['action'])) {
            JwtPayloadActionEnum::DEPROVISION => $this->handleDeprovision($activation, $wbxWiAction),
            JwtPayloadActionEnum::HEALTH_CHECK => $this->handleHealthCheck($activation, $wbxWiAction),
            JwtPayloadActionEnum::UPDATE => $this->handleUpdate($activation, $wbxWiAction),
            JwtPayloadActionEnum::UPDATE_APPROVED => $this->handleUpdateApproved($activation, $wbxWiAction),
            default => response()->noContent(400),
        };
    }
}
