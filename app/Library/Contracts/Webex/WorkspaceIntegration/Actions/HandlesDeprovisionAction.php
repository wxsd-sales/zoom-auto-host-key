<?php

namespace App\Library\Contracts\Webex\WorkspaceIntegration\Actions;

use App\Library\Contracts\Webex\WorkspaceIntegration\DTOs\DeprovisionJwtPayload;
use ArrayAccess;

interface HandlesDeprovisionAction
{
    /**
     * Handle deprovision user action for Workspace Integration.
     */
    public static function handleDeprovisionAction(DeprovisionJwtPayload|ArrayAccess|array $payload);
}
