<?php

namespace App\Services;

use App\Constants\OauthConstant;

class OauthService
{
    public static function addExpiresAt(array $oauth)
    {
        ${OauthConstant::EXPIRES_IN} = $oauth[OauthConstant::EXPIRES_IN] ?? null;
        ${OauthConstant::EXPIRES_AT} = ${OauthConstant::EXPIRES_IN} !== null ?
            now()->timestamp + ${OauthConstant::EXPIRES_IN} : null;

        return array_merge(compact(OauthConstant::EXPIRES_AT), $oauth);
    }
}
