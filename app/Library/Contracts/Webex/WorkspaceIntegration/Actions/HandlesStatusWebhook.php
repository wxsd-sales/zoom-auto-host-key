<?php

namespace App\Library\Contracts\Webex\WorkspaceIntegration\Actions;

use App\Library\Contracts\Webex\WorkspaceIntegration\DTOs\StatusWebhookPayload;
use ArrayAccess;

interface HandlesStatusWebhook
{
    /**
     * Handle status webhook for Workspace Integration.
     */
    public function handleStatusWebhook(StatusWebhookPayload|ArrayAccess|array $payload);
}
