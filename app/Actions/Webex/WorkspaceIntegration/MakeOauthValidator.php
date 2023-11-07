<?php

namespace App\Actions\Webex\WorkspaceIntegration;

use App\Library\Constants\OauthConstant;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Validation\Rule;

class MakeOauthValidator
{
    public static function makeOauthValidator(array $data): ValidatorContract
    {
        $requiredScopes = array_map(
            fn ($value) => $value['scope'], config('services.webex.workspace_integration.api_access')
        );

        $rules = [
            OauthConstant::ACCESS_TOKEN => ['required', 'string'],
            OauthConstant::EXPIRES_IN => ['required', 'numeric'],
            OauthConstant::REFRESH_TOKEN => ['required', 'string'],
            OauthConstant::REFRESH_TOKEN_EXPIRES_IN => ['required', 'numeric'],
            OauthConstant::TOKEN_TYPE => ['required', 'string', Rule::in(['Bearer', 'bearer'])],
            OauthConstant::SCOPE => ['required', 'string', Rule::has($requiredScopes)],
        ];

        return \Validator::make($data, $rules);
    }

    public static function handle(array $data): ValidatorContract
    {
        return self::makeOauthValidator($data);
    }
}
