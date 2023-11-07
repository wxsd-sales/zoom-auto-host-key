<?php

namespace App\Library\Contracts\Webex\WorkspaceIntegration\Actions;

use App\Library\Contracts\Webex\WorkspaceIntegration\DTOs\UpdateJwtPayload;
use ArrayAccess;

interface HandlesUpdateAction
{
    /**
     * Handle update user action for Workspace Integration.
     */
    public function handleUpdateAction(UpdateJwtPayload|ArrayAccess|array $payload);
}
