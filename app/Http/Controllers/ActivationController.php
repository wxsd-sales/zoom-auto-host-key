<?php

namespace App\Http\Controllers;

use App\Constants\ActivationConstant;
use App\Http\Requests\StoreActivationRequest;
use App\Http\Requests\UpdateActivationRequest;
use App\Models\Activation;
use App\Models\WbxWiAction;
use App\Models\WbxWiOauth;
use App\Models\ZmS2sOauth;
use App\Services\WebexService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class ActivationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $columns = [
            ...['id', 'created_at', 'updated_at', 'deleted_at'],
            Str::snake(ActivationConstant::WBX_WI_DISPLAY_NAME),
            Str::snake(ActivationConstant::WBX_WI_ORG_ID),
        ];

        return inertia('Dashboard', [
            'activations' => Activation::withTrashed()->get($columns)->jsonSerialize(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $id = $request->old('id') ?? Str::orderedUuid()->toString();
        $signature = $request->old('signature');
        $actionUrl = $request->old('id') !== null
            ? URL::route('activations.store', compact('id', 'signature'))
            : URL::signedRoute('activations.store', compact('id'));

        ${ActivationConstant::WBX_WI_CONFIG} = collect(array_merge(
            compact('id'), config('services.webex.workspace_integration'))
        )->camelCaseKeys(0);
        ${ActivationConstant::ZM_S2S_CONFIG} = collect(config('services.zoom.server_to_server'))->camelCaseKeys(0);
        ${ActivationConstant::OPERATION_MODE} = 'automatic';

        return inertia('Activations/Create', compact(
            'actionUrl',
            ActivationConstant::WBX_WI_CONFIG,
            ActivationConstant::ZM_S2S_CONFIG,
            ActivationConstant::OPERATION_MODE
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreActivationRequest $request)
    {
        $activationAttributes = collect([
            'id' => $request['id'],
            ...$request->validated(),
            ActivationConstant::HMAC_SECRET => Str::password(),
            ActivationConstant::WBX_WI_ORG_ID => basename(base64_decode($request->wbxWiActionJwtPayload['sub'])),
            ActivationConstant::WBX_WI_MANIFEST_ID => $request->wbxWiManifest['id'],
            ActivationConstant::WBX_WI_MANIFEST_VERSION => $request->wbxWiManifest['manifestVersion'],
            ActivationConstant::WBX_WI_DISPLAY_NAME => $request->wbxWiManifest['displayName'],
            ActivationConstant::WBX_WI_MANIFEST_URL => $request->wbxWiActionJwtPayload['manifestUrl'],
            ActivationConstant::WBX_WI_APP_URL => $request->wbxWiActionJwtPayload['appUrl'],
        ]);
        $wbxWiActionAttributes = collect([
            'activationId' => $activationAttributes['id'],
            'jwtPayload' => $request->wbxWiActionJwtPayload,
            'jwt' => $request->validated(ActivationConstant::WBX_WI_ACTION_JWT),
            ...$request->wbxWiActionJwtPayload,
        ]);
        $wbxWiOauthAttributes = collect([
            'activationId' => $activationAttributes['id'],
            'accountId' => '',
            ...$request->wbxWiOauth,
        ]);
        $zmS2sOauthAttributes = collect([
            'activationId' => $activationAttributes['id'],
            'accountId' => $request[ActivationConstant::ZM_S2S_ACCOUNT_ID],
            ...$request->zmS2sOauth,
        ]);

        [$activation, $wbxWiOauth, $zmS2sOauth, $wbxWiAction] = [
            Activation::make($activationAttributes->snakeCaseKeys(0)->toArray()),
            WbxWiOauth::make($wbxWiOauthAttributes->snakeCaseKeys(0)->toArray()),
            ZmS2sOauth::make($zmS2sOauthAttributes->snakeCaseKeys(0)->toArray()),
            WbxWiAction::make($wbxWiActionAttributes->snakeCaseKeys(0)->toArray()),
        ];

        DB::transaction(function () use ($activation, $wbxWiOauth, $zmS2sOauth, $wbxWiAction) {
            array_map(
                fn (Model $model) => $model->save(), [$activation, $wbxWiOauth, $zmS2sOauth, $wbxWiAction]
            );

            $activation->wbx_wi_oauth_id = $wbxWiOauth->id;
            $activation->zm_s2s_oauth_id = $zmS2sOauth->id;
            $activation->save();

            $actionsUrl = config('app.url').route('activations.actions', $activation, false);
            $webhookUrl = config('app.url').route('activations.webhook', $activation, false);
            WebexService::activateWorkspaceIntegration($activation, $actionsUrl, $webhookUrl)->throw();
        });

        return redirect()->action([ActivationController::class, 'index']);
    }

    /**
     * Display the specified resource.
     */
    public function show(Activation $activation)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Activation $activation)
    {
        $id = $activation->id;

        ${ActivationConstant::WBX_WI_CONFIG} = collect(array_merge(
            compact('id'), config('services.webex.workspace_integration'))
        )->camelCaseKeys(0);
        ${ActivationConstant::ZM_S2S_CONFIG} = collect(config('services.zoom.server_to_server'))->camelCaseKeys(0);

        return inertia('Activations/Create', array_merge(
            compact(ActivationConstant::WBX_WI_CONFIG, ActivationConstant::ZM_S2S_CONFIG),
            $activation->jsonSerialize())
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateActivationRequest $request, Activation $activation)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Activation $activation)
    {
        $activation->delete();

        return redirect()->action([ActivationController::class, 'index']);
    }
}
