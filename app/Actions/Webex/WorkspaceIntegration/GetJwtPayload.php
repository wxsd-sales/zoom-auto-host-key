<?php

namespace App\Actions\Webex\WorkspaceIntegration;

use App\Library\Contracts\Webex\WorkspaceIntegration\Actions\GetsJwtPayload;
use App\Traits\DecodeJwt;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class GetJwtPayload implements GetsJwtPayload
{
    use DecodeJwt;

    /**
     * {@inheritDoc}
     *
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public static function getJwtPayload(string $jwt): array
    {
        $jwks = config('services.webex.jwk');

        return (array) self::decodeJwt($jwt, array_is_list($jwks) ? $jwks : array_values($jwks));
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public static function handle(string $jwt): array
    {
        return self::getJwtPayload($jwt);
    }
}
