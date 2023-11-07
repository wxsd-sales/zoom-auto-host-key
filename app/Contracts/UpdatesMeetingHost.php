<?php

namespace App\Contracts;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Response;

interface UpdatesMeetingHost
{
    /**
     * Updates the meeting's host to a new user or machine/room account
     *
     * @return PromiseInterface|Response
     */
    public static function updateMeetingHost(string $token, string $meetingId, string $newHost, array $oldHosts);
}
