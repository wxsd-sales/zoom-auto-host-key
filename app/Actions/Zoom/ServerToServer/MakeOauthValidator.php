<?php

namespace App\Actions\Zoom\ServerToServer;

use App\Library\Constants\OauthConstant;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Validation\Rule;

class MakeOauthValidator
{
    public static function makeOauthValidator(array $data): ValidatorContract
    {
        $requiredScopes = array_map(
            fn ($value) => $value['id'], config('services.zoom.server_to_server.scopes')
        );

        $rules = [
            OauthConstant::ACCESS_TOKEN => ['required', 'string'],
            OauthConstant::TOKEN_TYPE => ['required', 'string', Rule::in(['Bearer', 'bearer'])],
            OauthConstant::EXPIRES_IN => ['required', 'numeric'],
            OauthConstant::SCOPE => ['required', 'string', Rule::has($requiredScopes)],
        ];

        return \Validator::make($data, $rules);
    }

    public static function handle(array $data): ValidatorContract
    {
        return self::makeOauthValidator($data);
    }
}
