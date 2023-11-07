<?php

namespace App\Actions\Webex\WorkspaceIntegration;

use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Validation\Rule;

class MakeManifestValidator
{
    public static function makeManifestValidator(array $data, ?string $id): ValidatorContract
    {
        $keys = ['xapiAccess.events', 'xapiAccess.status', 'xapiAccess.commands'];
        $workspaceIntegration = config('services.webex.workspace_integration');
        $apiAccessComparer = fn (array $required, array $data) => strcmp(
            implode('', [$required['scope'], $required['access'], $required['role'] ?? '']),
            implode('', [$data['scope'], $data['access'], $required['role'] ?? ''])
        );
        $xapiAccessComparer = fn (array $required, array $data) => strcmp(
            implode('', [$required['path'], $required['access']]),
            implode('', [$data['path'], $data['access']])
        );
        $defaultIdRule = ['required', 'uuid', 'unique:activations,id', 'unique:activations,wbx_wi_manifest_id'];
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

    public static function handle(array $data, ?string $id): ValidatorContract
    {
        return self::makeManifestValidator($data, $id);
    }
}
