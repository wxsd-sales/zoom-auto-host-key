<?php

namespace App\Library\Contracts\Webex\WorkspaceIntegration\Actions;

use App\Library\Contracts\Webex\WorkspaceIntegration\DTOs\HealthCheckJwtPayload;
use ArrayAccess;

interface HandlesHealthCheckAction
{
    /**
     * Handle health check user action for Workspace Integration.
     */
    public function handleHealthCheckAction(HealthCheckJwtPayload|ArrayAccess|array $payload);
}
