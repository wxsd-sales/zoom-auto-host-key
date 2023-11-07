<?php

namespace App\Library\Contracts\Webex\WorkspaceIntegration\DTOs\Base;

interface JwtPayload
{
    public function getSub();

    public function getIat();

    public function getJti();

    public function getAppId();

    public function getAction();
}
