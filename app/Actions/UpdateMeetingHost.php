<?php

namespace App\Actions;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class UpdateMeetingHost implements \App\Contracts\UpdatesMeetingHost
{
    /**
     * {@inheritDoc}
     */
    public static function updateMeetingHost(
        string $token, string $meetingId, string $newHost, array $oldHosts, Pool $pool = null
    ): PromiseInterface|Response {
        $zoomApiUrl = config('services.zoom.api_url');
        $meetingsUrl = $zoomApiUrl.'/meetings/'.$meetingId;
        $body = [
            'schedule_for' => $newHost,
            'settings' => ['alternative_hosts' => implode(';', array_unique($oldHosts))],
        ];

        return $pool?->withToken($token)->patch($meetingsUrl, $body) ??
            Http::withToken($token)->patch($meetingsUrl, $body);
    }

    public static function handle(
        string $token, string $meetingId, string $newHost, array $oldHosts, Pool $pool = null
    ): PromiseInterface|Response {
        return self::updateMeetingHost($token, $meetingId, $newHost, $oldHosts, $pool);
    }
}
