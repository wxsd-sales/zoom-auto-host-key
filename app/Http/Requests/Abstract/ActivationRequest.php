<?php

namespace App\Http\Requests\Abstract;

use App\Constants\ActivationConstant;
use App\Constants\OauthConstant;
use App\Services\OauthService;
use App\Services\WebexService;
use App\Services\ZoomService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Validator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
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

    const WBX_WI_MANIFEST_KEYS = [
        self::JWT_PAYLOAD.'.'.'manifestUrl',
        self::WBX_WI_OAUTH.'.'.OauthConstant::ACCESS_TOKEN,
    ];

    const WBX_WI_OAUTH_KEYS = [
        self::JWT_PAYLOAD.'.'.'refreshToken',
        self::WBX_WI_CLIENT_ID,
        self::WBX_WI_CLIENT_SECRET,
    ];

    const ZM_S2S_OAUTH_KEYS = [
        self::ZM_S2S_ACCOUNT_ID,
        self::ZM_S2S_CLIENT_ID,
        self::ZM_S2S_CLIENT_SECRET,
    ];

    protected $stopOnFirstFailure = true;

    public readonly ?array $wbxWiActionJwtPayload;

    public readonly ?array $wbxWiOauth;

    public readonly ?array $zmS2sOauth;

    public mixed $wbxWiManifest;

    protected static function addJwtPayloadData(array $data): array
    {
        [${static::JWT_PAYLOAD}, ${static::JWT_ERRORED}] = [null, null];

        if (isset($data[static::JWT])) {
            try {
                ${static::JWT_PAYLOAD} = WebexService::getWorkspaceIntegrationJwtPayload($data);
            } catch (UnexpectedValueException $e) {
                Log::debug($e);
                ${static::JWT_ERRORED} = $e;
            } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
                Log::debug($e);
                ${static::JWT_ERRORED} = 'Unexpected server error while decoding JWT.';
            }
        } else {
            ${static::JWT_ERRORED} = 'Unable to decode JWT due to missing on invalid data.';
        }

        return array_merge($data, compact(static::JWT_PAYLOAD, static::JWT_ERRORED));
    }

    protected static function addOauthTokenData(array $data): array
    {
        [${self::WBX_WI_OAUTH}, ${self::WBX_WI_OAUTH_ERRORED}] = [null, null];
        [${self::ZM_S2S_OAUTH}, ${self::ZM_S2S_OAUTH_ERRORED}] = [null, null];
        $hasWbxWiOauthKeys = Arr::has($data, self::WBX_WI_OAUTH_KEYS);
        $hasZmS2sOauthKeys = Arr::has($data, self::ZM_S2S_OAUTH_KEYS);

        $responses = Http::pool(fn (Pool $pool) => [
            $hasWbxWiOauthKeys ? WebexService::getWorkspaceIntegrationOauth($data, $pool) : null,
            $hasZmS2sOauthKeys ? ZoomService::getServerToServerOauth($data, $pool) : null,
        ]);

        if ($hasWbxWiOauthKeys && isset($responses[0])) {
            try {
                ${self::WBX_WI_OAUTH} = OauthService::addExpiresAt($responses[0]->throw()->json());
            } catch (RequestException $e) {
                Log::debug($e);
                ${self::WBX_WI_OAUTH_ERRORED} = $e->response->json();
            } catch (ConnectionException) {
                Log::debug($e);
                ${self::WBX_WI_OAUTH_ERRORED} = 'Connection timeout while making request.';
            }
        } else {
            $message = 'Unable to get Oauth token due to missing on invalid data.';
            ${self::WBX_WI_OAUTH_ERRORED} = $hasZmS2sOauthKeys ? $message : null;
        }

        if ($hasZmS2sOauthKeys && isset($responses[1])) {
            try {
                ${self::ZM_S2S_OAUTH} = OauthService::addExpiresAt($responses[1]->throw()->json());
            } catch (RequestException $e) {
                Log::debug($e);
                ${self::ZM_S2S_OAUTH_ERRORED} = $e->response->json();
            } catch (ConnectionException) {
                Log::debug($e);
                ${self::ZM_S2S_OAUTH_ERRORED} = 'Connection timeout while making request.';
            }
        } else {
            $message = 'Unable to get Oauth token due to missing on invalid data.';
            ${self::ZM_S2S_OAUTH_ERRORED} = $hasWbxWiOauthKeys ? $message : null;
        }

        return array_merge($data,
            compact(self::WBX_WI_OAUTH, self::WBX_WI_OAUTH_ERRORED),
            compact(self::ZM_S2S_OAUTH, self::ZM_S2S_OAUTH_ERRORED)
        );
    }

    protected static function addManifestData(array $data): array
    {
        [${self::MANIFEST}, ${self::MANIFEST_ERRORED}] = [null, null];

        if (Arr::has($data, self::WBX_WI_MANIFEST_KEYS)) {
            try {
                ${self::MANIFEST} = WebexService::getWorkspaceIntegrationManifest($data)->throw()->json();
            } catch (RequestException $e) {
                Log::debug($e);
                ${self::MANIFEST_ERRORED} = $e->response->json();
            } catch (ConnectionException) {
                Log::debug($e);
                ${self::MANIFEST_ERRORED} = 'Connection timeout while making request.';
            }
        } else {
            ${self::MANIFEST_ERRORED} = 'Unable to get Manifest due to missing on invalid data.';
        }

        return array_merge($data, compact(self::MANIFEST, self::MANIFEST_ERRORED));
    }

    protected function performJwtPayloadValidation(Validator $validator, array $data, $callback = null): Validator
    {
        $jwtPayloadValidator = WebexService::getWorkspaceIntegrationJwtPayloadValidator(
            $data[static::JWT_PAYLOAD] ?? [], $this
        );

        if ($callback !== null) {
            $jwtPayloadValidator->after(fn () => $callback($data));
        }

        if ($validator->errors()->isEmpty() && $jwtPayloadValidator->fails()) {
            Log::debug($jwtPayloadValidator->messages());
            $message = $data[static::JWT_PAYLOAD] === null
                ? $data[static::JWT_ERRORED] ?? 'Could not validate JWT.'
                : 'Duplicate, invalid or expired JWT.';
            $validator->errors()->add(static::JWT, $message);
        }

        return $jwtPayloadValidator;
    }

    protected function performManifestValidation(Validator $validator, array $data, $callback = null): Validator
    {
        $manifestValidator = WebexService::getWorkspaceIntegrationManifestValidator(
            $data[self::MANIFEST] ?? [], $this
        );

        if ($callback !== null) {
            $manifestValidator->after(fn () => $callback($data));
        }

        if ($validator->errors()->isEmpty() && $manifestValidator->fails()) {
            $message = $data[self::MANIFEST_ERRORED] ?? 'Unexpected Manifest uploaded.';
            $validator->errors()->add(self::MANIFEST, $message);
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
        );

        if ($callback !== null) {
            $oauthValidator->after(fn () => $callback($data));
        }

        if ($validator->errors()->isEmpty() && $oauthValidator->fails()) {
            $message = $data[static::WBX_WI_OAUTH_ERRORED] ?? 'Could not retrieve valid token.';
            $validator->errors()->add(static::WBX_WI_CLIENT_SECRET, $message);
        }

        return $oauthValidator;
    }

    /**
     * Validates OAuth token for the Zoom Server to Server application.
     */
    protected function performZoomOauthValidation(Validator $validator, array $data, $callback = null): Validator
    {
        $oauthValidator = ZoomService::getServerToServerOauthValidator($data[self::ZM_S2S_OAUTH] ?? []);

        if ($callback !== null) {
            $oauthValidator->after(fn () => $callback($data));
        }

        if ($validator->errors()->isEmpty() && $oauthValidator->fails()) {
            $message = $data[self::ZM_S2S_OAUTH_ERRORED] ?? 'Could not retrieve valid token.';
            $validator->errors()->add(self::ZM_S2S_CLIENT_SECRET, $message);
        }

        return $oauthValidator;
    }

    public function after(): array
    {
        $data = $this->all();
        $data = self::addJwtPayloadData($data);
        $data = self::addOauthTokenData($data);
        $data = self::addManifestData($data);

        $setWorkspaceIntegrationActionJwtPayload = function ($data) {
            $this->wbxWiActionJwtPayload = $data[self::JWT_PAYLOAD] ?? null;
        };

        $setWorkspaceIntegrationManifest = function ($data) {
            $this->wbxWiManifest = $data[self::MANIFEST] ?? null;
        };

        $setWorkspaceIntegrationOauth = function ($data) {
            $this->wbxWiOauth = $data[self::WBX_WI_OAUTH] ?? null;
        };

        $setServer2serverOauth = function ($data) {
            $this->zmS2sOauth = $data[self::ZM_S2S_OAUTH] ?? null;
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
