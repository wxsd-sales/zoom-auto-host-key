<?php

namespace App\Library\Contracts\Webex\WorkspaceIntegration\Actions;

interface GetsJwtPayload
{
    /**
     * Decodes a Workspace Integration's JWT to obtain its payload as an array.
     */
    public static function getJwtPayload(string $jwt): array;
}
