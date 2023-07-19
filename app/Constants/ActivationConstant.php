<?php

namespace App\Constants;

final class ActivationConstant
{
    public const WBX_WI_CLIENT_ID = 'wbxWiClientId';

    public const WBX_WI_CLIENT_SECRET = 'wbxWiClientSecret';

    public const WBX_WI_CONFIG = 'wbxWiConfig';

    public const WBX_WI_ACTION_JWT = 'wbxWiActionJwt';

    public const WBX_WI_ACTION_JWT_ERRORED = 'wbxWiActionJwtErrored';

    public const WBX_WI_ACTION_JWT_PAYLOAD = 'wbxWiActionJwtPayload';

    public const WBX_WI_MANIFEST = 'wbxWiManifest';

    public const WBX_WI_MANIFEST_ID = 'wbxWiManifestId';

    public const WBX_WI_MANIFEST_VERSION = 'wbxWiManifestVersion';

    public const WBX_WI_MANIFEST_URL = 'wbxWiManifestUrl';

    public const WBX_WI_APP_URL = 'wbxWiAppUrl';

    public const WBX_WI_ORG_ID = 'wbxWiOrgId';

    public const WBX_WI_DISPLAY_NAME = 'wbxWiDisplayName';

    public const WBX_WI_MANIFEST_ERRORED = 'wbxWiManifestErrored';

    public const WBX_WI_OAUTH = 'wbxWiOauth';

    public const WBX_WI_OAUTH_ERRORED = 'wbxWiOauthErrored';

    public const WBX_WI_REFRESH_TOKEN = 'wbxWiRefreshToken';

    public const ZM_HOST_ACCOUNTS = 'zmHostAccounts';

    public const ZM_S2S_ACCOUNT_ID = 'zmS2sAccountId';

    public const ZM_S2S_CLIENT_ID = 'zmS2sClientId';

    public const ZM_S2S_CLIENT_SECRET = 'zmS2sClientSecret';

    public const ZM_S2S_CONFIG = 'zmS2sConfig';

    public const ZM_S2S_OAUTH = 'zmS2sOauth';

    public const ZM_S2S_OAUTH_ERRORED = 'zmS2sOauthErrored';

    public const HMAC_SECRET = 'hmacSecret';

    public const SECRETS = [
        self::HMAC_SECRET,
        self::WBX_WI_ACTION_JWT,
        self::WBX_WI_CLIENT_SECRET,
        self::ZM_S2S_CLIENT_SECRET,
    ];
}
