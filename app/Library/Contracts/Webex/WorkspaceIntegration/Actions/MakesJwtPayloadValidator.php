<?php

namespace App\Library\Contracts\Webex\WorkspaceIntegration\Actions;

use App\Library\Enums\Webex\WorkspaceIntegration\JwtPayloadActionEnum as ActionEnum;
use ArrayAccess;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;

interface MakesJwtPayloadValidator
{
    /**
     * Creates validator for a Workspace Integration's decoded JWT Payload.
     */
    public static function makeJwtPayloadValidator(
        ArrayAccess|array $data, ?int $manifestVersion, ?ActionEnum $action, ?string $appId, ?string $accountId,
    ): ValidatorContract;
}
