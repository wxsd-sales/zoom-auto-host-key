<?php

namespace App\Library\Contracts\Webex\WorkspaceIntegration\Actions;

use App\Library\Contracts\Webex\WorkspaceIntegration\DTOs\HealthCheckWebhookPayload;
use ArrayAccess;

interface HandlesHealthCheckWebhook
{
    /**
     * Handle health check webhook for Workspace Integration.
     */
    public function handleHealthCheckWebhook(HealthCheckWebhookPayload|ArrayAccess|array $payload);
}
