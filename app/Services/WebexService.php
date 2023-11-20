<?php

namespace App\Services;

use App\Constants\ActivationConstant;
use App\Library\Constants\OauthConstant;
use App\Models\Account;
use App\Models\Activation;
use App\Traits\DecodeJwt;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Validator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class WebexService
{
    use DecodeJwt;

    /**
     * Activates a Workspace Integration by sending provisioning payload to Webex.
     */
    public static function activateWorkspaceIntegration(
        Activation $activation, string $actionsUrl, string $webhookUrl
    ): PromiseInterface|Response {
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
     * Decodes a Workspace Integration's JWT to obtain its payload as an array.
     *
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public static function getWorkspaceIntegrationJwtPayload(array|string $data): array
    {
        $jwt = gettype($data) === 'string' ? $data : $data['jwt'] ?? $data[ActivationConstant::WBX_WI_ACTION_JWT];

        return \App\Actions\Webex\WorkspaceIntegration\GetJwtPayload::handle($jwt);
    }

    /**
     * Creates validator for Workspace Integration's decoded JWT Payload.
     */
    public static function getWorkspaceIntegrationJwtPayloadValidator(array $data, Request $request = null): Validator
    {
        $appId = $request?->query('id');
        $accountId = $request->hasSession()
            ? Account::whereId($request->session()->get('account.webex'))->first()->provider_account_id
            : null;
        $manifestVersion = $request?->activation?->wbx_wi_manifest_version !== null
            ? (string) $request?->activation?->wbx_wi_manifest_version
            : (string) config('services.webex.workspace_integration.manifest_version');

        return \App\Actions\Webex\WorkspaceIntegration\MakeJwtPayloadValidator::handle(
            $data, $manifestVersion, null, $appId, $accountId
        );
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

        return \App\Actions\Webex\WorkspaceIntegration\GetOauth::handle(
            $oauthUrl, $clientId, $clientSecret, $refreshToken, $pool
        );
    }

    /**
     * Creates OAuth token validator for a Workspace Integration.
     */
    public static function getWorkspaceIntegrationOauthValidator(array $data): Validator
    {
        return \App\Actions\Webex\WorkspaceIntegration\MakeOauthValidator::handle($data);
    }

    /**
     * Retrieves manifest for a Workspace Integration using provided data.
     */
    public static function getWorkspaceIntegrationManifest(array $data, Pool $pool = null): PromiseInterface|Response
    {
        $manifestUrl = $data['manifestUrl'] ?? $data[ActivationConstant::WBX_WI_ACTION_JWT_PAYLOAD]['manifestUrl'];
        $accessToken = $data[OauthConstant::ACCESS_TOKEN] ??
            $data[ActivationConstant::WBX_WI_OAUTH][OauthConstant::ACCESS_TOKEN];

        return \App\Actions\Webex\WorkspaceIntegration\GetManifest::handle($manifestUrl, $accessToken, $pool);
    }

    /**
     * Creates manifest validator for a Workspace Integration.
     */
    public static function getWorkspaceIntegrationManifestValidator(array $data, Request $request = null): Validator
    {
        return \App\Actions\Webex\WorkspaceIntegration\MakeManifestValidator::handle($data, $request?->query('id'));
    }
}
