<?php

namespace App\Traits;

use Firebase\JWT\CachedKeySet;
use Firebase\JWT\JWT;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use LogicException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use UnexpectedValueException;

/**
 * Decodes a JWT string into an object.
 */
trait DecodeJwt
{
    /**
     * Decodes a JWT string into an object.
     *
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws UnexpectedValueException
     */
    public static function decodeJwt(
        string $jwt, string|array $jwks, int $expiresAfter = null, bool $rateLimit = true
    ): object {
        $jwksUri = gettype($jwks) === 'string' ? [$jwks] : $jwks;
        $httpClient = new Client();
        $httpFactory = new HttpFactory();
        $cache = app()->get('cache.psr6');
        $payload = null;
        $errored = null;

        foreach ($jwksUri as $jwk) {
            $args = [$jwk, $httpClient, $httpFactory, $cache, $expiresAfter, $rateLimit];
            $cacheKeySet = new CachedKeySet(...$args);
            try {
                $payload = JWT::decode($jwt, $cacheKeySet);
            } catch (UnexpectedValueException|LogicException $e) {
                $errored = $e;
            }

            if ($payload != null) {
                break;
            }
        }

        if ($payload === null) {
            throw $errored ?? new UnexpectedValueException();
        }

        return $payload;
    }
}
