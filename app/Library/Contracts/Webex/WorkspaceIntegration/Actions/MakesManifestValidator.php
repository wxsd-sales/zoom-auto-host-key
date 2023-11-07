<?php

namespace App\Library\Contracts\Webex\WorkspaceIntegration\Actions;

use ArrayAccess;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;

interface MakesManifestValidator
{
    /**
     * Creates validator for a Workspace Integration's manifest.
     */
    public static function makeManifestValidator(ArrayAccess|array $data, ?string $id): ValidatorContract;
}
