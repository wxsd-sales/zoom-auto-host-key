<?php

namespace App\Library\Contracts\Webex\WorkspaceIntegration\DTOs;

use App\Library\Contracts\Webex\WorkspaceIntegration\DTOs\Base\WebhookPayload;

interface StatusWebhookPayload extends WebhookPayload
{
    public function getChanges();

    public function getIsFullSync();
}
