<?php

namespace App\Library\Enums\Webex\WorkspaceIntegration;

enum JwtPayloadActionEnum: string
{
    case DEPROVISION = 'deprovision';

    case HEALTH_CHECK = 'healthCheck';

    case PROVISION = 'provision';

    case UPDATE = 'update';

    case UPDATE_APPROVED = 'updateApproved';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
