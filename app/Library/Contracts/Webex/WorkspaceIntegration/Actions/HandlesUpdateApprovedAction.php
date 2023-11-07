<?php

namespace App\Library\Contracts\Webex\WorkspaceIntegration\Actions;

use App\Library\Contracts\Webex\WorkspaceIntegration\DTOs\UpdateApprovedJwtPayload;
use ArrayAccess;

interface HandlesUpdateApprovedAction
{
    /**
     * Handle update approved user action for Workspace Integration.
     */
    public function handleUpdateApprovedAction(UpdateApprovedJwtPayload|ArrayAccess|array $payload);
}
