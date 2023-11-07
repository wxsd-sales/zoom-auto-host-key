<?php

namespace App\Library\Contracts\Zoom\ServerToServer\Actions;

use Illuminate\Contracts\Validation\Validator as ValidatorContract;

interface MakesOauthValidator
{
    /**
     * Creates validator for a Server to Server application's OAuth token.
     */
    public static function makeOauthValidator($data): ValidatorContract;
}
