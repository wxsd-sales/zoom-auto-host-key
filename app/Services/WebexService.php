<?php

namespace App\Services;

use App\Constants\ActivationConstant;
use App\Constants\OauthConstant;
use App\Models\Account;
use App\Models\Activation;
use App\Traits\DecodeJwt;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class WebexService
{
    use DecodeJwt;

    /**
     * Activates a Workspace Integration by sending provisioning payload to Webex.
     */
    public static function activateWorkspaceIntegration(Activation $activation): PromiseInterface|Response
    {
        $actionsUrl = config('app.url').route('activations.actions', $activation, false);
        $webhookUrl = config('app.url').route('activations.webhook', $activation, false);

        return Http::withToken($activation->wbxWiOauth->access_token)->patch($activation->wbx_wi_app_url, [
            'provisioningState' => 'completed',
            'actionsUrl' => $actionsUrl,
            'webhook' => [
                'targetUrl' => $webhookUrl,
                'type' => 'hmac_signature',
                'secret' => $activation->hmac_secret,
            ],
            'customer' => ['id' => $activation->wbx_wi_org_id],
        ]);
    }

    /**
     * Decodes a Workspace Integration's JWT string into an array using provided data.
     *
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public static function getWorkspaceIntegrationJwtPayload(array|string $data): array
    {
        $jwt = gettype($data) === 'string' ? $data : $data['jwt'] ?? $data[ActivationConstant::WBX_WI_ACTION_JWT];
        $jwks = config('services.webex.jwk');

        return (array) self::decodeJwt($jwt, array_is_list($jwks) ? $jwks : array_values($jwks));
    }

    /**
     * Creates validator for Workspace Integration's decoded JWT Payload.
     */
    public static function getWorkspaceIntegrationJwtPayloadValidator($data, Request $request = null): Validator
    {
        $actions = ['provision', 'deprovision', 'updateApproved', 'update', 'healthCheck'];
        $appIdRule = ['required_if:action,provision', 'uuid'];
        $userIdRule = ['required_if:action,provision', 'string'];
        $apiAccess = config('services.webex.workspace_integration.api_access');
        $xapiAccess = config('services.webex.workspace_integration.xapi_access');
        $manifestVersion = $request?->activation?->wbx_wi_manifest_version !== null
            ? (string) $request?->activation?->wbx_wi_manifest_version
            : (string) config('services.webex.workspace_integration.manifest_version');
        $requiredScopes = array_map(
            fn ($value) => $value['scope'], array_filter($apiAccess, fn ($value) => $value['access'] === 'required')
        );
        $requiredXapiAccess = array_merge(...array_map(fn (string $key, array $value) => [
            $key !== 'status' ? $key : 'statuses' => array_map(
                fn ($value) => $value['path'], array_filter($value, fn ($value) => $value['access'] === 'required')),
        ], array_keys($xapiAccess), array_values($xapiAccess)));
        $xapiAccessComparer = fn (array $required, array $data) => array_diff($required, $data);

        if ($request?->route('activations.store')) {
            $userId = Account::whereId($request->session()->get('account.webex'))->first()->provider_account_id;
            $uniqueIds = ['unique:activations,id', 'unique:activations,wbx_wi_manifest_id'];
            $appIdRule = [...$appIdRule, ...$uniqueIds, Rule::in($request->query('id'))];
            $userIdRule = [...$userIdRule, Rule::hydraId($userId)];
            $actions = ['provision'];
        } elseif ($request?->routeIs('activations.update')) {
            $userId = Account::whereId($request->session()->get('account.webex'))->first()->provider_account_id;
            $appIdRule = [...$appIdRule, Rule::unique('activations')->ignore($request->activation)];
            $userIdRule = [...$userIdRule, Rule::hydraId($userId)];
            $actions = ['update'];
        } elseif ($request?->routeIs('activations.actions')) {
            $actions = ['updateApproved', 'deprovision', 'healthCheck'];
        }

        $rules = [
            'sub' => ['required', 'string', 'max:255'],
            'iat' => ['required', 'numeric', 'gte:'.time() - 86400],
            'jti' => ['required', 'string', 'unique:wbx_wi_actions'],
            'appId' => $appIdRule,
            'action' => ['required', 'string', Rule::in($actions)],
            'oauthUrl' => ['required_if:action,provision', 'url'],
            'orgName' => ['required_if:action,provision', 'string'],
            'appUrl' => ['required_if:action,provision,update', 'url'],
            'userId' => $userIdRule,
            'manifestUrl' => ['required_if:action,provision', 'url'],
            'expiryTime' => ['required_if:action,provision', 'date', 'after:+10 minutes'],
            'webexapisBaseUrl' => ['required_if:action,provision', 'url'],
            'manifestVersion' => ['required_if:action,updateApproved', 'integer',  Rule::in($manifestVersion)],
            'scopes' => ['required_if:action,provision,updateApproved', Rule::has($requiredScopes, ',')],
            'region' => ['required_if:action,provision,update', 'string'],
            'refreshToken' => ['required_if:action,provision,update', 'string', 'unique:wbx_wi_oauths,refresh_token'],
            'xapiAccess' => [
                'required_if:action,provision,updateApproved',
                'json',
                Rule::has($requiredXapiAccess, null, $xapiAccessComparer),
            ],
        ];

        return \Validator::make($data, $rules);
    }

    /**
     * Retrieves OAuth token for a Workspace Integration using provided data.
     */
    public static function getWorkspaceIntegrationOauth(array $data, Pool $pool = null): PromiseInterface|Response
    {
        $jwtPayload = $data['jwtPayload'] ?? $data[ActivationConstant::WBX_WI_ACTION_JWT_PAYLOAD] ?? null;
        $clientId = $data[OauthConstant::CLIENT_ID] ?? $data[ActivationConstant::WBX_WI_CLIENT_ID];
        $clientSecret = $data[OauthConstant::CLIENT_SECRET] ?? $data[ActivationConstant::WBX_WI_CLIENT_SECRET];
        $refreshToken = $data[OauthConstant::REFRESH_TOKEN] ?? $jwtPayload['refreshToken'];
        $oauthUrl = config('services.webex.oauth_url');
        $oauthBody = [
            OauthConstant::GRANT_TYPE => OauthConstant::REFRESH_TOKEN,
            OauthConstant::CLIENT_ID => $clientId,
            OauthConstant::CLIENT_SECRET => $clientSecret,
            OauthConstant::REFRESH_TOKEN => $refreshToken,
        ];

        return $pool?->asForm()->post($oauthUrl, $oauthBody) ?? Http::asForm()->post($oauthUrl, $oauthBody);
    }

    /**
     * Creates OAuth token validator for a Workspace Integration.
     */
    public static function getWorkspaceIntegrationOauthValidator($data): Validator
    {
        $requiredScopes = array_map(
            fn ($value) => $value['scope'], config('services.webex.workspace_integration.api_access')
        );

        $rules = [
            OauthConstant::ACCESS_TOKEN => ['required', 'string'],
            OauthConstant::EXPIRES_IN => ['required', 'numeric', 'min:600'],
            OauthConstant::REFRESH_TOKEN => ['required', 'string'],
            OauthConstant::REFRESH_TOKEN_EXPIRES_IN => ['required', 'numeric', 'min:600'],
            OauthConstant::TOKEN_TYPE => ['required', 'string', Rule::in(['Bearer', 'bearer'])],
            OauthConstant::SCOPE => ['required', 'string', Rule::has($requiredScopes)],
        ];

        return \Validator::make($data, $rules);
    }

    /**
     * Retrieves manifest for a Workspace Integration using provided data.
     */
    public static function getWorkspaceIntegrationManifest(array $data, Pool $pool = null): PromiseInterface|Response
    {
        $manifestUrl = $data['manifestUrl'] ?? $data[ActivationConstant::WBX_WI_ACTION_JWT_PAYLOAD]['manifestUrl'];
        $accessToken = $data[OauthConstant::ACCESS_TOKEN] ??
            $data[ActivationConstant::WBX_WI_OAUTH][OauthConstant::ACCESS_TOKEN];

        return $pool?->withToken($accessToken)->get($manifestUrl) ?? Http::withToken($accessToken)->get($manifestUrl);
    }

    /**
     * Creates manifest validator for a Workspace Integration.
     */
    public static function getWorkspaceIntegrationManifestValidator($data, Request $request = null): Validator
    {
        $defaultIdRule = ['required', 'uuid', 'unique:activations,id', 'unique:activations,wbx_wi_manifest_id'];
        $id = $request?->query('id');
        $workspaceIntegration = config('services.webex.workspace_integration');
        $apiAccessComparer = function (array $required, array $data) {
            return strcmp(
                implode('', [$required['scope'], $required['access'], $required['role'] ?? '']),
                implode('', [$data['scope'], $data['access'], $required['role'] ?? ''])
            );
        };
        $xapiAccessComparer = function (array $required, array $data) {
            return strcmp(
                implode('', [$required['path'], $required['access']]),
                implode('', [$data['path'], $data['access']])
            );
        };
        $keys = ['xapiAccess.events', 'xapiAccess.status', 'xapiAccess.commands'];
        $idRule = $id !== null ? [...$defaultIdRule, Rule::in($id)] : $defaultIdRule;

        $rules = [
            'id' => $idRule,
            'displayName' => [
                'required',
                'string',
                "regex:/^{$workspaceIntegration['display_name']}( — .*)?$/",
            ],
            'description' => [
                'required',
                'string',
                Rule::in($workspaceIntegration['description']),
            ],
            'vendor' => [
                'required',
                'string',
                Rule::in($workspaceIntegration['vendor']),
            ],
            'email' => [
                'required',
                'email',
                Rule::in($workspaceIntegration['email']),
            ],
            'apiAccess' => [
                'required',
                'array',
                Rule::has($workspaceIntegration['api_access'], null, $apiAccessComparer),
            ],
            'manifestVersion' => [
                'required',
                'numeric',
                "min:{$workspaceIntegration['manifest_version']}",
                "max:{$workspaceIntegration['manifest_version']}",
            ],
            'availability' => [
                'required',
                'string',
                Rule::in($workspaceIntegration['availability']),
            ],
            'xapiAccess' => [
                'required',
                'array',
            ],
            'xapiAccess.events' => [
                'required',
                'array',
                Rule::has($workspaceIntegration['xapi_access']['events'], null, $xapiAccessComparer),
            ],
            'xapiAccess.status' => [
                'required',
                'array',
                Rule::has($workspaceIntegration['xapi_access']['status'], null, $xapiAccessComparer),
            ],
            'xapiAccess.commands' => [
                'required',
                'array',
                Rule::has($workspaceIntegration['xapi_access']['commands'], null, $xapiAccessComparer),
            ],
        ];
        $attributes = array_combine($keys, $keys);

        return \Validator::make($data, $rules, [], $attributes);
    }
}
