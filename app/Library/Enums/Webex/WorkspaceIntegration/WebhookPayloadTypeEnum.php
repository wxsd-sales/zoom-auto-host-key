<?php

namespace App\Library\Enums\Webex\WorkspaceIntegration;

enum WebhookPayloadTypeEnum: string
{
    case STATUS = 'status';

    case EVENTS = 'events';

    case HEALTH_CHECK = 'healthCheck';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
