<?php

namespace App\Constants;

final class ErrorMessageConstant
{
    const COULD_NOT_VALIDATE_JWT = 'Could not validate JWT due to missing on invalid data.';

    const INVALID_JWT = 'Duplicate, invalid or expired JWT.';

    const COULD_NOT_GET_MANIFEST = 'Could not retrieve Manifest due to missing on invalid data.';

    const INVALID_MANIFEST = 'Unexpected Manifest uploaded.';

    const COULD_NOT_GET_OAUTH = 'Could not retrieve OAuth token due to missing on invalid data.';

    const INVALID_OAUTH = 'Unable to get Oauth due to invalid client id or client secret.';

    const UNEXPECTED = 'Unexpected server error while processing input.';
}
