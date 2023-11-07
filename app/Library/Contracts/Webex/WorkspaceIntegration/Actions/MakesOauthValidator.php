<?php

namespace App\Library\Contracts\Webex\WorkspaceIntegration\Actions;

use ArrayAccess;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;

interface MakesOauthValidator
{
    /**
     * Creates validator for a Workspace Integration's OAuth token.
     */
    public static function makeOauthValidator(ArrayAccess|array $data): ValidatorContract;
}
