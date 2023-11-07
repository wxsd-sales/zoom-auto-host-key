<?php

namespace App\Http\Requests\Abstract;

use App\Constants\ActivationConstant;
use App\Constants\ErrorMessageConstant;
use App\Library\Constants\OauthConstant;
use App\Services\OauthService;
use App\Services\WebexService;
use App\Services\ZoomService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Client\HttpClientException;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Validator;
use PHPUnit\Exception;
use UnexpectedValueException;

abstract class ActivationRequest extends FormRequest
{
    protected const JWT = ActivationConstant::WBX_WI_ACTION_JWT;

    protected const JWT_PAYLOAD = ActivationConstant::WBX_WI_ACTION_JWT_PAYLOAD;

    protected const JWT_ERRORED = ActivationConstant::WBX_WI_ACTION_JWT_ERRORED;

    protected const WBX_WI_OAUTH = ActivationConstant::WBX_WI_OAUTH;

    protected const WBX_WI_CLIENT_ID = ActivationConstant::WBX_WI_CLIENT_ID;

    protected const WBX_WI_CLIENT_SECRET = ActivationConstant::WBX_WI_CLIENT_SECRET;

    protected const WBX_WI_OAUTH_ERRORED = ActivationConstant::WBX_WI_OAUTH_ERRORED;

    protected const ZM_S2S_OAUTH = ActivationConstant::ZM_S2S_OAUTH;

    protected const ZM_S2S_ACCOUNT_ID = ActivationConstant::ZM_S2S_ACCOUNT_ID;

    protected const ZM_S2S_CLIENT_ID = ActivationConstant::ZM_S2S_CLIENT_ID;

    protected const ZM_S2S_CLIENT_SECRET = ActivationConstant::ZM_S2S_CLIENT_SECRET;

    protected const ZM_S2S_OAUTH_ERRORED = ActivationConstant::ZM_S2S_OAUTH_ERRORED;

    protected const MANIFEST = ActivationConstant::WBX_WI_MANIFEST;

    protected const MANIFEST_ERRORED = ActivationConstant::WBX_WI_MANIFEST_ERRORED;

    protected $stopOnFirstFailure = true;

    public readonly ?array $wbxWiActionJwtPayload;

    public readonly ?array $wbxWiOauth;

    public readonly ?array $zmS2sOauth;

    public mixed $wbxWiManifest;

    private static function getWbxWiManifestKeys(): array
    {
        return [static::JWT_PAYLOAD.'.'.'manifestUrl', static::WBX_WI_OAUTH.'.'.OauthConstant::ACCESS_TOKEN];
    }

    private static function getWbxWiOauthKeys(): array
    {
        return [static::JWT_PAYLOAD.'.'.'refreshToken', static::WBX_WI_CLIENT_ID, static::WBX_WI_CLIENT_SECRET];
    }

    private static function getZmS2sOauthKeys(): array
    {
        return [static::ZM_S2S_ACCOUNT_ID, static::ZM_S2S_CLIENT_ID, static::ZM_S2S_CLIENT_SECRET];
    }

    private static function getDecodePayload(Response|array $payload, callable $callback = null): array
    {
        [$value, $error] = [null, null];

        try {
            $value = gettype($payload) === 'array'
                ? ($callback != null ? $callback($payload) : $payload)
                : ($callback != null ? $callback($payload->throw()->json()) : $payload->throw()->json());
        } catch (UnexpectedValueException|HttpClientException $e) {
            Log::debug($e);
            $error = $e;
        } catch (Exception $e) {
            Log::error($e);
            $error = ErrorMessageConstant::UNEXPECTED;
        }

        return [$value, $error];
    }

    /**
     * Adds decoded JWT Payload for the Webex Workspace Integration application.
     */
    protected static function addJwtPayloadData(array $data): array
    {
        [${static::JWT_PAYLOAD}, ${static::JWT_ERRORED}] = isset($data[static::JWT])
            ? static::getDecodePayload($data, WebexService::getWorkspaceIntegrationJwtPayload(...))
            : [null, ErrorMessageConstant::INVALID_JWT];

        return array_merge($data, compact(static::JWT_PAYLOAD, static::JWT_ERRORED));
    }

    /**
     * Adds OAuth token for the Webex Workspace Integration application.
     */
    protected static function addOauthTokenData(array $data): array
    {
        [$hasWbxWiOauthKeys, $hasZmS2sOauthKeys] = [
            Arr::has($data, static::getWbxWiOauthKeys()), Arr::has($data, static::getZmS2sOauthKeys()),
        ];
        $responses = Http::pool(fn (Pool $pool) => [
            $hasWbxWiOauthKeys ? WebexService::getWorkspaceIntegrationOauth($data, $pool) : null,
            $hasZmS2sOauthKeys ? ZoomService::getServerToServerOauth($data, $pool) : null,
        ]);
        [${static::WBX_WI_OAUTH},  ${static::WBX_WI_OAUTH_ERRORED}] = $hasWbxWiOauthKeys && isset($responses[0])
            ? static::getDecodePayload($responses[0], OauthService::addExpiresAt(...))
            : ($hasZmS2sOauthKeys ? [null, ErrorMessageConstant::INVALID_OAUTH] : [null, null]);
        [${static::ZM_S2S_OAUTH},  ${static::ZM_S2S_OAUTH_ERRORED}] = $hasZmS2sOauthKeys && isset($responses[1])
            ? static::getDecodePayload($responses[1], OauthService::addExpiresAt(...))
            : ($hasZmS2sOauthKeys ? [null, ErrorMessageConstant::INVALID_OAUTH] : [null, null]);

        return array_merge($data,
            compact(static::WBX_WI_OAUTH, static::WBX_WI_OAUTH_ERRORED),
            compact(static::ZM_S2S_OAUTH, static::ZM_S2S_OAUTH_ERRORED)
        );
    }

    /**
     * Adds Manifest data for the Webex Workspace Integration application.
     */
    protected static function addManifestData(array $data): array
    {
        [${static::MANIFEST}, ${static::MANIFEST_ERRORED}] = Arr::has($data, static::getWbxWiManifestKeys())
            ? static::getDecodePayload(WebexService::getWorkspaceIntegrationManifest($data))
            : [null, ErrorMessageConstant::COULD_NOT_GET_MANIFEST];

        return array_merge($data, compact(static::MANIFEST, static::MANIFEST_ERRORED));
    }

    /**
     * Validates decoded JWT Payload for the Webex Workspace Integration application.
     */
    protected function performJwtPayloadValidation(Validator $validator, array $data, $callback = null): Validator
    {
        $jwtPayloadValidator = WebexService::getWorkspaceIntegrationJwtPayloadValidator(
            $data[static::JWT_PAYLOAD] ?? [], $this
        )->after(fn () => $callback !== null ? $callback($data) : null);

        if ($validator->errors()->isEmpty() && $jwtPayloadValidator->fails()) {
            $message = $data[static::JWT_PAYLOAD] === null
                ? $data[static::JWT_ERRORED] ?? ErrorMessageConstant::COULD_NOT_VALIDATE_JWT
                : ErrorMessageConstant::INVALID_JWT;
            $validator->errors()->add(static::JWT, $message);
        }

        return $jwtPayloadValidator;
    }

    /**
     * Validates Manifest for the Webex Workspace Integration application.
     */
    protected function performManifestValidation(Validator $validator, array $data, $callback = null): Validator
    {
        $manifestValidator = WebexService::getWorkspaceIntegrationManifestValidator(
            $data[static::MANIFEST] ?? [], $this
        )->after(fn () => $callback !== null ? $callback($data) : null);

        if ($validator->errors()->isEmpty() && $manifestValidator->fails()) {
            $message = $data[static::MANIFEST_ERRORED] ?? ErrorMessageConstant::INVALID_MANIFEST;
            $validator->errors()->add(static::MANIFEST, $message);
        }

        return $manifestValidator;
    }

    /**
     * Validates OAuth token for the Webex Workspace Integration application.
     */
    protected function performWebexOauthValidation(Validator $validator, array $data, $callback = null): Validator
    {
        $oauthValidator = WebexService::getWorkspaceIntegrationOauthValidator(
            $data[static::WBX_WI_OAUTH] ?? []
        )->after(fn () => $callback !== null ? $callback($data) : null);

        if ($validator->errors()->isEmpty() && $oauthValidator->fails()) {
            $message = $data[static::WBX_WI_OAUTH_ERRORED] ?? ErrorMessageConstant::COULD_NOT_GET_OAUTH;
            $validator->errors()->add(static::WBX_WI_CLIENT_SECRET, $message);
        }

        return $oauthValidator;
    }

    /**
     * Validates OAuth token for the Zoom Server to Server application.
     */
    protected function performZoomOauthValidation(Validator $validator, array $data, $callback = null): Validator
    {
        $oauthValidator = ZoomService::getServerToServerOauthValidator(
            $data[static::ZM_S2S_OAUTH] ?? []
        )->after(fn () => $callback !== null ? $callback($data) : null);

        if ($validator->errors()->isEmpty() && $oauthValidator->fails()) {
            $message = $data[static::ZM_S2S_OAUTH_ERRORED] ?? ErrorMessageConstant::COULD_NOT_GET_OAUTH;
            $validator->errors()->add(static::ZM_S2S_CLIENT_SECRET, $message);
        }

        return $oauthValidator;
    }

    /**
     * Get the "after" validation callables for the request.
     */
    public function after(): array
    {
        $data = $this->all();
        $data = static::addJwtPayloadData($data);
        $data = static::addOauthTokenData($data);
        $data = static::addManifestData($data);

        $setWorkspaceIntegrationActionJwtPayload = function ($data) {
            $this->wbxWiActionJwtPayload = $data[static::JWT_PAYLOAD] ?? null;
        };

        $setWorkspaceIntegrationManifest = function ($data) {
            $this->wbxWiManifest = $data[static::MANIFEST] ?? null;
        };

        $setWorkspaceIntegrationOauth = function ($data) {
            $this->wbxWiOauth = $data[static::WBX_WI_OAUTH] ?? null;
        };

        $setServer2serverOauth = function ($data) {
            $this->zmS2sOauth = $data[static::ZM_S2S_OAUTH] ?? null;
        };

        return [
            fn ($validator) => $this->performJwtPayloadValidation(
                $validator, $data, $setWorkspaceIntegrationActionJwtPayload
            ),
            fn ($validator) => $this->performWebexOauthValidation($validator, $data, $setWorkspaceIntegrationOauth),
            fn ($validator) => $this->performManifestValidation($validator, $data, $setWorkspaceIntegrationManifest),
            fn ($validator) => $this->performZoomOauthValidation($validator, $data, $setServer2serverOauth),
        ];
    }
}
