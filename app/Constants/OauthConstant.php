<?php

namespace App\Constants;

final class OauthConstant
{
    public const ACCESS_TOKEN = 'access_token';

    public const CLIENT_ID = 'client_id';

    public const CLIENT_SECRET = 'client_secret';

    public const EXPIRES_IN = 'expires_in';

    public const EXPIRES_AT = 'expires_at';

    public const GRANT_TYPE = 'grant_type';

    public const REFRESH_TOKEN = 'refresh_token';

    public const REFRESH_TOKEN_EXPIRES_IN = 'refresh_token_expires_in';

    public const SCOPE = 'scope';

    public const TOKEN_TYPE = 'token_type';

    public const SECRETS = [self::CLIENT_SECRET, self::ACCESS_TOKEN, self::REFRESH_TOKEN];
}
