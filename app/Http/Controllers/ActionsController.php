<?php

namespace App\Http\Controllers;

use App\Constants\ActionsConstant;
use App\Http\Requests\ActionsRequest;
use App\Models\Activation;
use App\Models\WbxWiAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;

class ActionsController extends Controller
{
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

    public function store(ActionsRequest $request, Activation $activation)
    {
        $wbxWiAction = WbxWiAction::make(collect([
            ActionsConstant::JWT => $request->validated(ActionsConstant::JWT),
            ActionsConstant::JWT_PAYLOAD => $request->jwtPayload,
            ...$request->jwtPayload,
        ])->snakeCaseKeys(0)->toArray());

        return match ($request->jwtPayload['action']) {
            ActionsConstant::DEPROVISION => $this->handleDeprovision($activation, $wbxWiAction),
            ActionsConstant::HEALTH_CHECK => $this->handleHealthCheck($activation, $wbxWiAction),
            ActionsConstant::UPDATE_APPROVED => $this->handleUpdateApproved($activation, $wbxWiAction),
            default => response()->noContent(400),
        };
    }
}
