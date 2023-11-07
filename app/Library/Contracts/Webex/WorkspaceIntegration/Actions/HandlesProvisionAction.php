<?php

namespace App\Library\Contracts\Webex\WorkspaceIntegration\Actions;

use App\Library\Contracts\Webex\WorkspaceIntegration\DTOs\ProvisionJwtPayload;
use ArrayAccess;

interface HandlesProvisionAction
{
    /**
     * Handle provision user action for Workspace Integration.
     */
    public function handleProvisionAction(ProvisionJwtPayload|ArrayAccess|array $payload);
}
