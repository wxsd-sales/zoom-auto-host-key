<?php

namespace App\Http\Requests\Abstract;

use App\Constants\ActivationConstant;
use App\Constants\OauthConstant;
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
    protected $stopOnFirstFailure = true;

    public readonly array|null $wbxWiJwtPayload;

    public readonly array|null $wbxWiOauth;

    public readonly array|null $zmS2sOauth;

    public mixed $wbxWiManifest;

    protected static function addJwtPayloadData(array $data): array
    {
        Log::debug(__METHOD__);
        [${ActivationConstant::WBX_WI_JWT_PAYLOAD}, ${ActivationConstant::WBX_WI_JWT_ERRORED}] = [null, null];

        if (isset($data[ActivationConstant::WBX_WI_JWT])) {
            try {
                ${ActivationConstant::WBX_WI_JWT_PAYLOAD} = WebexService::getWorkspaceIntegrationJwtPayload($data);
            } catch (UnexpectedValueException $e) {
                Log::debug($e);
                ${ActivationConstant::WBX_WI_JWT_ERRORED} = $e;
            } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
                Log::debug($e);
                ${ActivationConstant::WBX_WI_JWT_ERRORED} = 'Unexpected server error while decoding JWT.';
            }
        } else {
            ${ActivationConstant::WBX_WI_JWT_ERRORED} = 'Unable to decode JWT due to missing on invalid data.';
        }

        return array_merge($data, compact(
            ActivationConstant::WBX_WI_JWT_PAYLOAD, ActivationConstant::WBX_WI_JWT_ERRORED
        ));
    }

    protected static function addOauthTokenData(array $data): array
    {
        Log::debug(__METHOD__);
        [${ActivationConstant::WBX_WI_OAUTH}, ${ActivationConstant::WBX_WI_OAUTH_ERRORED}] = [null, null];
        [${ActivationConstant::ZM_S2S_OAUTH}, ${ActivationConstant::ZM_S2S_OAUTH_ERRORED}] = [null, null];
        $hasWbxWiOauthKeys = Arr::has($data, WebexService::WORKSPACE_INTEGRATION_OAUTH_KEYS);
        $hasZmS2sOauthKeys = Arr::has($data, ZoomService::SERVER_TO_SERVER_OAUTH_KEYS);
        $now = now()->timestamp;

        $responses = Http::pool(fn (Pool $pool) => [
            $hasWbxWiOauthKeys ? WebexService::getWorkspaceIntegrationOauth($data, $pool) : null,
            $hasZmS2sOauthKeys ? ZoomService::getServerToServerOauth($data, $pool) : null,
        ]);

        if ($hasWbxWiOauthKeys && isset($responses[0])) {
            try {
                $json = $responses[0]->throw()->json();
                ${OauthConstant::EXPIRES_IN} = $json[OauthConstant::EXPIRES_IN];
                ${OauthConstant::EXPIRES_AT} = ${OauthConstant::EXPIRES_IN} !== null ?
                    $now + ${OauthConstant::EXPIRES_IN} : null;
                ${ActivationConstant::WBX_WI_OAUTH} = array_merge(compact(OauthConstant::EXPIRES_AT), $json);
            } catch (RequestException $e) {
                Log::debug($e);
                ${ActivationConstant::WBX_WI_OAUTH_ERRORED} = $e->response->json();
            } catch (ConnectionException) {
                Log::debug($e);
                ${ActivationConstant::WBX_WI_OAUTH_ERRORED} = 'Connection timeout while making request.';
            }
        } else {
            $message = 'Unable to get Oauth token due to missing on invalid data.';
            ${ActivationConstant::WBX_WI_OAUTH_ERRORED} = $hasZmS2sOauthKeys ? $message : null;
        }

        if ($hasZmS2sOauthKeys && isset($responses[1])) {
            try {
                $json = $responses[1]->throw()->json();
                ${OauthConstant::EXPIRES_IN} = $json[OauthConstant::EXPIRES_IN];
                ${OauthConstant::EXPIRES_AT} = ${OauthConstant::EXPIRES_IN} !== null ?
                    $now + ${OauthConstant::EXPIRES_IN} : null;
                ${ActivationConstant::ZM_S2S_OAUTH} = array_merge(compact(OauthConstant::EXPIRES_AT), $json);
            } catch (RequestException $e) {
                Log::debug($e);
                ${ActivationConstant::ZM_S2S_OAUTH_ERRORED} = $e->response->json();
            } catch (ConnectionException) {
                Log::debug($e);
                ${ActivationConstant::ZM_S2S_OAUTH_ERRORED} = 'Connection timeout while making request.';
            }
        } else {
            $message = 'Unable to get Oauth token due to missing on invalid data.';
            ${ActivationConstant::ZM_S2S_OAUTH_ERRORED} = $hasWbxWiOauthKeys ? $message : null;
        }

        return array_merge($data,
            compact(ActivationConstant::WBX_WI_OAUTH, ActivationConstant::WBX_WI_OAUTH_ERRORED),
            compact(ActivationConstant::ZM_S2S_OAUTH, ActivationConstant::ZM_S2S_OAUTH_ERRORED)
        );
    }

    protected static function addManifestData(array $data): array
    {
        Log::debug(__METHOD__);
        [${ActivationConstant::WBX_WI_MANIFEST}, ${ActivationConstant::WBX_WI_MANIFEST_ERRORED}] = [null, null];

        if (Arr::has($data, WebexService::WORKSPACE_INTEGRATION_MANIFEST_KEYS)) {
            try {
                ${ActivationConstant::WBX_WI_MANIFEST} = WebexService::getWorkspaceIntegrationManifest($data)
                    ->throw()
                    ->json();
            } catch (RequestException $e) {
                Log::debug($e);
                ${ActivationConstant::WBX_WI_MANIFEST_ERRORED} = $e->response->json();
            } catch (ConnectionException) {
                Log::debug($e);
                ${ActivationConstant::WBX_WI_MANIFEST_ERRORED} = 'Connection timeout while making request.';
            }
        } else {
            ${ActivationConstant::WBX_WI_MANIFEST_ERRORED} = 'Unable to get Manifest due to missing on invalid data.';
        }

        return array_merge($data, compact(
            ActivationConstant::WBX_WI_MANIFEST, ActivationConstant::WBX_WI_MANIFEST_ERRORED)
        );
    }

    protected function performJwtPayloadValidation(Validator $validator, array $data, $callback = null): Validator
    {
        Log::debug(__METHOD__);
        $jwtPayloadValidator = WebexService::getWorkspaceIntegrationJwtPayloadValidator(
            $data[ActivationConstant::WBX_WI_JWT_PAYLOAD] ?? [], $this
        );

        if ($callback !== null) {
            $jwtPayloadValidator->after(fn () => $callback($data));
        }

        if ($validator->errors()->isEmpty() && $jwtPayloadValidator->fails()) {
            $message = $data[ActivationConstant::WBX_WI_JWT_PAYLOAD] === null
                ? $data[ActivationConstant::WBX_WI_JWT_ERRORED] ?? 'Could not validate JWT.'
                : 'Duplicate, invalid or expired JWT.';
            $validator->errors()->add(ActivationConstant::WBX_WI_JWT, $message);
        }

        return $jwtPayloadValidator;
    }

    protected function performManifestValidation(Validator $validator, array $data, $callback = null): Validator
    {
        Log::debug(__METHOD__);
        $manifestValidator = WebexService::getWorkspaceIntegrationManifestValidator(
            $data[ActivationConstant::WBX_WI_MANIFEST] ?? [], $this
        );

        if ($callback !== null) {
            $manifestValidator->after(fn () => $callback($data));
        }

        if ($validator->errors()->isEmpty() && $manifestValidator->fails()) {
            $message = $data[ActivationConstant::WBX_WI_MANIFEST_ERRORED] ?? 'Unexpected Manifest uploaded.';
            $validator->errors()->add(ActivationConstant::WBX_WI_MANIFEST, $message);
        }

        return $manifestValidator;
    }

    protected function performWebexOauthValidation(Validator $validator, array $data, $callback = null): Validator
    {
        Log::debug(__METHOD__);
        $oauthValidator = WebexService::getWorkspaceIntegrationOauthValidator(
            $data[ActivationConstant::WBX_WI_OAUTH] ?? []
        );

        if ($callback !== null) {
            $oauthValidator->after(fn () => $callback($data));
        }

        if ($validator->errors()->isEmpty() && $oauthValidator->fails()) {
            $message = $data[ActivationConstant::WBX_WI_OAUTH_ERRORED] ?? 'Could not retrieve valid token.';
            $validator->errors()->add(ActivationConstant::WBX_WI_CLIENT_SECRET, $message);
        }

        return $oauthValidator;
    }

    protected function performZoomOauthValidation(Validator $validator, array $data, $callback = null): Validator
    {
        Log::debug(__METHOD__);
        $oauthValidator = ZoomService::getServerToServerOauthValidator(
            $data[ActivationConstant::ZM_S2S_OAUTH] ?? []
        );

        if ($callback !== null) {
            $oauthValidator->after(fn () => $callback($data));
        }

        if ($validator->errors()->isEmpty() && $oauthValidator->fails()) {
            $message = $data[ActivationConstant::ZM_S2S_OAUTH_ERRORED] ?? 'Could not retrieve valid token.';
            $validator->errors()->add(ActivationConstant::ZM_S2S_CLIENT_SECRET, $message);
        }

        return $oauthValidator;
    }

    public function after(): array
    {
        $data = $this->all();
        $data = self::addJwtPayloadData($data);
        $data = self::addOauthTokenData($data);
        $data = self::addManifestData($data);

        $setWorkspaceIntegrationJwtPayload = function ($data) {
            $this->wbxWiJwtPayload = $data[ActivationConstant::WBX_WI_JWT_PAYLOAD] ?? null;
        };

        $setWorkspaceIntegrationManifest = function ($data) {
            $this->wbxWiManifest = $data[ActivationConstant::WBX_WI_MANIFEST] ?? null;
        };

        $setWorkspaceIntegrationOauth = function ($data) {
            $this->wbxWiOauth = $data[ActivationConstant::WBX_WI_OAUTH] ?? null;
        };

        $setServer2serverOauth = function ($data) {
            $this->zmS2sOauth = $data[ActivationConstant::ZM_S2S_OAUTH] ?? null;
        };

        return [
            fn ($validator) => $this->performJwtPayloadValidation(
                $validator, $data, $setWorkspaceIntegrationJwtPayload
            ),
            fn ($validator) => $this->performWebexOauthValidation($validator, $data, $setWorkspaceIntegrationOauth),
            fn ($validator) => $this->performManifestValidation($validator, $data, $setWorkspaceIntegrationManifest),
            fn ($validator) => $this->performZoomOauthValidation($validator, $data, $setServer2serverOauth),
        ];
    }
}
