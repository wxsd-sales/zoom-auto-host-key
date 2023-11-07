<?php

namespace App\Actions\Webex\WorkspaceIntegration;

use App\Library\Enums\Webex\WorkspaceIntegration\WebhookPayloadTypeEnum;

class HandleHealthCheckWebhook
{
    public function handleHealthCheckWebhook(): array
    {
        return [
            'message' => 'ok',
            WebhookPayloadTypeEnum::HEALTH_CHECK->value => true,
        ];
    }
}
