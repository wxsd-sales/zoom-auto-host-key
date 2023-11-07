<?php

namespace App\Library\Contracts\Webex\WorkspaceIntegration\DTOs\Base;

interface WebhookPayload
{
    public function getAppId();

    public function getTimestamp();

    public function getType();
}
