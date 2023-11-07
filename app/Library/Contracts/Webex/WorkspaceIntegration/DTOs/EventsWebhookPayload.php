<?php

namespace App\Library\Contracts\Webex\WorkspaceIntegration\DTOs;

use App\Library\Contracts\Webex\WorkspaceIntegration\DTOs\Base\WebhookPayload;

interface EventsWebhookPayload extends WebhookPayload
{
    public function getEvents();
}
