<?php

namespace App\Contracts;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Response;

interface SendsHostOpt
{
    /**
     * Sends a 6-10 digit OPT to the user.
     *
     * @return PromiseInterface|Response
     */
    public static function sendHostOtp(string $token, string $toEmail, string $key);
}
