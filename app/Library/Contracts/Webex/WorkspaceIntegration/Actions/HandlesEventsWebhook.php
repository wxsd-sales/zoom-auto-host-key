<?php

namespace App\Library\Contracts\Webex\WorkspaceIntegration\Actions;

use App\Library\Contracts\Webex\WorkspaceIntegration\DTOs\EventsWebhookPayload;
use ArrayAccess;

interface HandlesEventsWebhook
{
    /**
     * Handle health check webhook for Workspace Integration.
     */
    public function handleEventsWebhook(EventsWebhookPayload|ArrayAccess|array $payload);
}
