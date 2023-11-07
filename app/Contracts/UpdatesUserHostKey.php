<?php

namespace App\Contracts;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Response;

interface UpdatesUserHostKey
{
    /**
     * Updates the user's host key.
     *
     * @return PromiseInterface|Response
     */
    public static function updateUserHostKey(string $token, string $userId, string $key);
}
