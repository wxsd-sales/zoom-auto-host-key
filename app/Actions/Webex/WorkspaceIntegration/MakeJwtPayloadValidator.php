<?php

namespace App\Actions\Webex\WorkspaceIntegration;

use App\Library\Enums\Webex\WorkspaceIntegration\JwtPayloadActionEnum as ActionEnum;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Validation\Rule;

class MakeJwtPayloadValidator
{
    public static function makeJwtPayloadValidator(
        array $data, ?int $manifestVersion, ?ActionEnum $action, ?string $appId, ?string $accountId,
    ): ValidatorContract {
        [$actions, $provision, $update, $updateApproved, $deprovision, $healthCheck] = [
            ActionEnum::values(),
            ActionEnum::PROVISION->value,
            ActionEnum::UPDATE->value,
            ActionEnum::UPDATE_APPROVED->value,
            ActionEnum::DEPROVISION->value,
            ActionEnum::HEALTH_CHECK->value,
        ];
        $appIdRule = ['required', 'uuid'];
        $userIdRule = ['required_if:action,'.$provision, 'string'];
        $apiAccess = config('services.webex.workspace_integration.api_access');
        $xapiAccess = config('services.webex.workspace_integration.xapi_access');
        $manifestVersion = $manifestVersion != null
            ? (string) $manifestVersion
            : (string) config('services.webex.workspace_integration.manifest_version');
        $requiredScopes = array_map(
            fn ($value) => $value['scope'], array_filter($apiAccess, fn ($value) => $value['access'] === 'required')
        );
        $requiredXapiAccess = array_merge(...array_map(fn (string $key, array $value) => [
            $key !== 'status' ? $key : 'statuses' => array_map(
                fn ($value) => $value['path'], array_filter($value, fn ($value) => $value['access'] === 'required')),
        ], array_keys($xapiAccess), array_values($xapiAccess)));
        $xapiAccessComparer = fn (array $required, array $data) => array_diff($required, $data);

        if ($action?->value === $provision) {
            $userId = $accountId;
            $uniqueIds = ['unique:activations,id', 'unique:activations,wbx_wi_manifest_id'];
            $appIdRule = [...$appIdRule, ...$uniqueIds, Rule::in($appId)];
            $userIdRule = [...$userIdRule, Rule::hydraId($userId)];
            $actions = [$provision];
        } elseif ($action?->value === $update) {
            $userId = $accountId;
            $appIdRule = [...$appIdRule, Rule::unique('activations')->ignore($appId)];
            $userIdRule = [...$userIdRule, Rule::hydraId($userId)];
            $actions = [$update];
        } elseif (in_array($action?->value, [$update, $updateApproved, $deprovision, $healthCheck])) {
            $actions = array_diff(ActionEnum::values(), [$provision]);
        }

        $rules = [
            'sub' => ['required', 'string', 'max:255'],
            'iat' => ['required', 'numeric', 'gte:'.time() - 86400],
            'jti' => ['required', 'string', 'unique:wbx_wi_actions'],
            'appId' => $appIdRule,
            'action' => ['required', 'string', Rule::in($actions)],
            'oauthUrl' => ['required_if:action,'.$provision, 'url'],
            'orgName' => ['required_if:action,'.$provision, 'string'],
            'appUrl' => ['required_if:action,'.$provision, 'url'],
            'userId' => $userIdRule,
            'manifestUrl' => ['required_if:action,'.$provision, 'url'],
            'expiryTime' => ['required_if:action,'.$provision, 'date', 'after:+10 minutes'],
            'webexapisBaseUrl' => ['required_if:action,'.$provision, 'url'],
            'manifestVersion' => ['required_if:action,'.$updateApproved, 'integer',  Rule::in($manifestVersion)],
            'scopes' => ['required_if:action,'.$provision.','.$updateApproved, Rule::has($requiredScopes, ',')],
            'region' => ['required_if:action,'.$provision, 'string'],
            'refreshToken' => [
                'required_if:action,'.$provision.','.$update,
                'string',
                'unique:wbx_wi_oauths,refresh_token',
            ],
            'xapiAccess' => [
                'required_if:action,'.$provision.','.$updateApproved,
                'json',
                Rule::has($requiredXapiAccess, null, $xapiAccessComparer),
            ],
        ];

        return \Validator::make($data, $rules);
    }

    public static function handle(
        array $data, ?int $manifestVersion, ?ActionEnum $action, ?string $appId, ?string $accountId,
    ): ValidatorContract {
        return self::makeJwtPayloadValidator($data, $manifestVersion, $action, $appId, $accountId);
    }
}
