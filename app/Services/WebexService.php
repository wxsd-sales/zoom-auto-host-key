<?php

namespace App\Services;

use App\Constants\ActivationConstant;
use App\Constants\OauthConstant;
use App\Models\Account;
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

    const WORKSPACE_INTEGRATION_OAUTH_KEYS = [
        ActivationConstant::WBX_WI_JWT_PAYLOAD.'.'.'refreshToken',
        ActivationConstant::WBX_WI_CLIENT_ID,
        ActivationConstant::WBX_WI_CLIENT_SECRET,
    ];

    const WORKSPACE_INTEGRATION_MANIFEST_KEYS = [
        ActivationConstant::WBX_WI_JWT_PAYLOAD.'.'.'manifestUrl',
        ActivationConstant::WBX_WI_OAUTH.'.'.OauthConstant::ACCESS_TOKEN,
    ];

    /**
     * Decodes a Workspace Integration's JWT string into an array using provided data.
     *
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public static function getWorkspaceIntegrationJwtPayload(array|string $data): array
    {
        $jwt = gettype($data) === 'string' ? $data : $data[ActivationConstant::WBX_WI_JWT];
        $jwks = config('services.webex.jwk');

        return (array) self::decodeJwt($jwt, array_is_list($jwks) ? $jwks : array_values($jwks));
    }

    /**
     * Validates a Workspace Integration's decoded JWT Payload.
     */
    public static function getWorkspaceIntegrationJwtPayloadValidator($data, Request $request = null): Validator
    {
        $defaultAppIdRule = ['required', 'uuid', 'unique:activations,id', 'unique:activations,wbx_wi_manifest_id'];
        $defaultUserIdRule = ['required', 'string'];
        $appId = $request?->query('id');
        $userId = Account::whereId($request?->session()->get('account.webex'))->first()?->provider_account_id;
        $requiredScopes = array_map(
            fn ($value) => $value['scope'], config('services.webex.workspace_integration.api_access')
        );
        $appIdRule = $appId !== null ? [...$defaultAppIdRule, Rule::in($appId)] : $defaultAppIdRule;
        $userIdRule = $userId !== null ? [...$defaultUserIdRule, Rule::hydraId($userId)] : $defaultUserIdRule;

        $rules = [
            'sub' => ['required', 'string', 'max:255'],
            'iat' => ['required', 'numeric', 'gte:'.time() - 86400],
            'jti' => ['required', 'string'],
            'appId' => $appIdRule,
            'action' => ['required', 'string', Rule::in('provision')],
            'oauthUrl' => ['required', 'url'],
            'orgName' => ['required', 'string'],
            'appUrl' => ['required', 'url'],
            'userId' => $userIdRule,
            'manifestUrl' => ['required', 'url'],
            'expiryTime' => ['required', 'date', 'after:+10 minutes'],
            'webexapisBaseUrl' => ['required', 'url'],
            'scopes' => ['required', Rule::has($requiredScopes, ',')],
            'region' => ['required', 'string'],
            'refreshToken' => ['required', 'string', 'unique:wbx_wi_oauths,refresh_token'],
            'xapiAccess' => ['required', 'json'],
        ];

        return \Validator::make($data, $rules);
    }

    /**
     * Retrieves OAuth token for a Workspace Integration using provided data.
     */
    public static function getWorkspaceIntegrationOauth(array $data, Pool $pool = null): PromiseInterface|Response
    {
        $jwtPayload = $data[ActivationConstant::WBX_WI_JWT_PAYLOAD] ?? null;
        $clientId = $data[ActivationConstant::WBX_WI_CLIENT_ID];
        $clientSecret = $data[ActivationConstant::WBX_WI_CLIENT_SECRET];
        $refreshToken = $data[ActivationConstant::WBX_WI_REFRESH_TOKEN] ?? $jwtPayload['refreshToken'];
        $oauthUrl = $jwtPayload['oauthUrl'] ?? config('services.webex.oauth_url');
        $oauthBody = [
            OauthConstant::GRANT_TYPE => OauthConstant::REFRESH_TOKEN,
            OauthConstant::CLIENT_ID => $clientId,
            OauthConstant::CLIENT_SECRET => $clientSecret,
            OauthConstant::REFRESH_TOKEN => $refreshToken,
        ];

        return $pool?->asForm()->post($oauthUrl, $oauthBody) ?? Http::asForm()->post($oauthUrl, $oauthBody);
    }

    /**
     * Validates OAuth token for a Workspace Integration.
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
     * Retrieves Manifest for a Workspace Integration using provided data.
     */
    public static function getWorkspaceIntegrationManifest(array $data, Pool $pool = null): PromiseInterface|Response
    {
        $manifestUrl = $data[ActivationConstant::WBX_WI_JWT_PAYLOAD]['manifestUrl'];
        $accessToken = $data[ActivationConstant::WBX_WI_OAUTH][OauthConstant::ACCESS_TOKEN];

        return $pool?->withToken($accessToken)->get($manifestUrl) ?? Http::withToken($accessToken)->get($manifestUrl);
    }

    /**
     * Validates Manifest for a Workspace Integration.
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
