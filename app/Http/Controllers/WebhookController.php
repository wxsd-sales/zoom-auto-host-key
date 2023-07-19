<?php

namespace App\Http\Controllers;

use App\Constants\OauthConstant;
use App\Constants\WebhookConstant;
use App\Http\Requests\WebhookRequest;
use App\Models\Activation;
use App\Services\WebexService;
use App\Services\ZoomService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class WebhookController extends Controller
{
    protected function getActiveCallStatus($token, $deviceId)
    {
        $webexApiUrl = config('services.webex.api_url');
        $name = 'Call[*].*';

        return Http::withToken($token)
            ->get($webexApiUrl.'/xapi/status', compact('deviceId', 'name'))
            ->json('result.Call.0');
    }

    protected function sendNewHostKeyOvermessaging($token, $toEmail, $key)
    {
        $webexApiUrl = config('services.webex.api_url');
        $introduction = [
            'You are receiving this message since someone tried to join your Zoom Meeting on a RoomOS device.'.
            'If this wasn\'t you, please ignore this message.',
            'You may also mute this bot to ignore future interruptions.',
        ];

        return Http::withToken($token)->post($webexApiUrl.'/messages', [
            'toPersonEmail' => $toEmail,
            'markdown' => implode("\n", [
                implode("\n", $introduction),
                "\n",
                "### OTP: $key",
                'Use the above OTP if you would like to join the ongoing Zoom Meeting, but do not '.
                'remember your Zoom Host Key. This will be your new Zoom Host Key moving forward.',
                'Your Zoom Host Key is changed only if you use the one above on the target input prompt.',
            ]),
        ]);
    }

    protected function displayInputPromptForActiveCall($token, $deviceId, $payload, ?string $email)
    {
        $webexApiUrl = config('services.webex.api_url');
        $arguments = [
            'FeedbackId' => json_encode($payload),
            'InputText' => $email,
            'InputType' => 'SingleLine',
            'KeyboardState' => 'Open',
            'Placeholder' => "Meeting Host's Email or Host Key",
            'SubmitText' => 'Submit',
            'Text' => 'Join as meeting host or as the account below.',
            'Title' => 'Zoom Auto Host Key',
        ];
        $xapiUrl = $webexApiUrl.'/xapi/command/UserInterface.Message.TextInput.Display';
        $body = compact('deviceId', 'arguments');

        return Http::withToken($token)->post($xapiUrl, $body);
    }

    /*
     * Destructors the dial string of a call if it's and outgoing Zoom CRC meeting without any host key.
     */
    protected function shouldDestructureCallbackNumber(array $call)
    {
        $zoomCrcSuffix = '@zoomcrc.com';
        $callbackNumber = str_replace(['spark:', 'sip:'], '', $call['CallbackNumber']);
        $filteredNumber = preg_filter("/$zoomCrcSuffix$/", '', strtolower($callbackNumber));
        $explodedNumber = empty($filteredNumber) ? [] : explode('.', $filteredNumber);

        return $call['Direction'] === 'Outgoing' && empty($explodedNumber[3])
            ? $explodedNumber
            : [];
    }

    protected function signalHostKey($token, $deviceId, $callId, $dtmfString)
    {
        $webexApiUrl = config('services.webex.api_url');
        $arguments = ['CallId' => $callId, 'DTMFString' => $dtmfString, 'Feedback' => 'Silent'];
        $xapiUrl = $webexApiUrl.'/xapi/command/Call.DTMFSend';
        $body = compact('deviceId', 'arguments');

        return Http::withToken($token)->post($xapiUrl, $body);
    }

    protected function createDtmfString(array $feedback, ?string $hostKey)
    {
        return Arr::get($feedback, 'numbers.3') === ''
            ? $hostKey.'#'
            : $hostKey.'#';
    }

    protected function getMeetingDetails($token, $meetingId)
    {
        $zoomApiUrl = config('services.zoom.api_url');
        $meetingsUrl = $zoomApiUrl.'/meetings/'.$meetingId;

        return Http::withToken($token)->get($meetingsUrl);
    }

    protected function updateMeetingHost($token, $meetingId, $newHost, $oldHosts)
    {
        $zoomApiUrl = config('services.zoom.api_url');
        $meetingsUrl = $zoomApiUrl.'/meetings/'.$meetingId;

        \Log::debug(json_encode($oldHosts));
        return Http::withToken($token)->patch($meetingsUrl, [
            'schedule_for' => $newHost,
            'settings' => ['alternative_hosts' => implode(';', array_unique($oldHosts))],
        ]);
    }

    protected function updateHostKey($token, $userId, $key)
    {
        $zoomApiUrl = config('services.zoom.api_url');
        $usersUrl = $zoomApiUrl.'/users/'.$userId;

        return Http::withToken($token)->patch($usersUrl, ['host_key' => $key]);
    }

    public function store(WebhookRequest $request, Activation $activation)
    {
        $data = $request->validated();
        $wbxWiToken = $activation->wbxWiOauth->access_token;
        $zmS2sToken = $activation->zmS2sOauth->access_token;
        $deviceId = $data['deviceId'] ?? null;

        return match ($data['type']) {
            WebhookConstant::STATUS => $this->handleStatusChange(
                $data, $wbxWiToken, $deviceId, $zmS2sToken, $activation
            ),
            WebhookConstant::EVENTS => $this->handleEventsUpdate(
                $data[WebhookConstant::EVENTS], $activation, $zmS2sToken, $wbxWiToken, $deviceId
            ),
            default => response()->json(['message' => 'ok']),
        };
    }

    /**
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function handleStatusChange(
        mixed $data, mixed $wbxWiToken, mixed $deviceId, mixed $zmS2sToken, Activation $activation
    ): \Illuminate\Http\JsonResponse {
        $isFullSync = $data['isFullSync'] ?? null;
        $isOnActiveCall = ($data['changes']['updated']['SystemUnit.State.NumberOfActiveCalls'] ?? null) > 0;
        $activeCallStatus = $isOnActiveCall ? $this->getActiveCallStatus($wbxWiToken, $deviceId) : [];
        $destructuredCallbackNumber = $isOnActiveCall
            ? $this->shouldDestructureCallbackNumber($activeCallStatus)
            : [];

        if ($isFullSync) {
            $this->shouldUpdateWbxWiOauth($activation);
            $this->shouldUpdateZmS2sOauth($activation);
        }

        if ($isFullSync === false && ! empty($destructuredCallbackNumber)) {
            $hostKey = Str::password(6, false, true, false);

            $meetingId = $destructuredCallbackNumber[0];
            $meetingDetails = $this->getMeetingDetails($zmS2sToken, $meetingId)->json();
            $toEmail = Arr::get($meetingDetails, 'host_email', '');
            $payload = [
                'callId' => $activeCallStatus['id'],
                'numbers' => $destructuredCallbackNumber,
                'hostKey' => $hostKey,
                'toEmail' => $toEmail,
            ];

            $this->sendNewHostKeyOvermessaging($wbxWiToken, $toEmail, $hostKey);
            $this->displayInputPromptForActiveCall(
                $wbxWiToken,
                $deviceId,
                $payload,
                Arr::random($activation->zm_host_accounts ?? [])['email'] ?? null
            )->throw()->json();
        }

        return response()->json(['message' => 'ok']);
    }

    /**
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function handleEventsUpdate(
        $data, Activation $activation, mixed $zmS2sToken, mixed $wbxWiToken, mixed $deviceId
    ): \Illuminate\Http\JsonResponse {
        $input = Arr::first(
            $data,
            fn ($v) => Arr::get($v, 'key') === 'UserInterface.Message.TextInput.Response'
        );
        $payload = json_decode(Arr::get($input, 'value.FeedbackId', '{}'), true);
        $text = trim(Arr::get($input, 'value.Text', ''));
        $callId = $payload['callId'] ?? null;
        $isHostKeyInput = preg_match('/^[0-9]{6,10}$/', $text);
        $machineAccount = Arr::first(
            $activation->zm_host_accounts,
            function ($v) use ($text) {
                return strcasecmp(Arr::get($v, 'email'), $text) === 0;
            }
        );

        if (! empty($input) && $machineAccount != null) {
            $hostKey = $machineAccount['key'];
            $dtmfString = $this->createDtmfString($payload, $hostKey);

            $newHost = $machineAccount['email'];

            $meetingId = $payload['numbers'][0];
            $meetingDetails = $this->getMeetingDetails($zmS2sToken, $meetingId)->throw()->json();
            $oldHost = Arr::get($meetingDetails, 'host_email');
            $oldAssistant = Arr::get($meetingDetails, 'assistant_id');
            $alternativeHosts = array_merge(
                //explode(';', Arr::get($meetingDetails, 'settings.alternative_hosts')),
                //! empty($oldAssistant) ? [$oldAssistant] : [],
                ! empty($oldHost) ? [$oldHost] : []
            );

            $this->updateMeetingHost($zmS2sToken, $meetingId, $newHost, $alternativeHosts)->throw()->json();
            $this->signalHostKey($wbxWiToken, $deviceId, $callId, $dtmfString)->throw()->json();
        } elseif (! empty($input) && $isHostKeyInput && $text !== $payload['hostKey']) {
            $hostKey = $text;
            $dtmfString = $this->createDtmfString($payload, $hostKey);

            $this->signalHostKey($wbxWiToken, $deviceId, $callId, $dtmfString)->throw()->json();
        } elseif (! empty($input) && $isHostKeyInput && $text === $payload['hostKey']) {
            $hostKey = $payload['hostKey'];
            $dtmfString = $this->createDtmfString($payload, $hostKey);

            $oldHost = $payload['toEmail'];

            $this->updateHostKey($zmS2sToken, $oldHost, $hostKey)->throw()->json();
            $this->signalHostKey($wbxWiToken, $deviceId, $callId, $dtmfString)->throw()->json();
        }

        return response()->json(['message' => 'ok']);
    }

    public function shouldUpdateWbxWiOauth(Activation $activation)
    {
        return now()->timestamp + 1200 >= $activation->wbxWiOauth->expires_at->timestamp &&
            $activation->wbxWiOauth->update(WebexService::getWorkspaceIntegrationOauth([
                OauthConstant::CLIENT_ID => $activation->wbx_wi_client_id,
                OauthConstant::CLIENT_SECRET => $activation->wbx_wi_client_secret,
                OauthConstant::REFRESH_TOKEN => $activation->wbxWiOauth->refresh_token,
            ])->throw()->json());
    }

    public function shouldUpdateZmS2sOauth(Activation $activation)
    {
        return now()->timestamp + 1200 >= $activation->zmS2sOauth->expires_at->timestamp &&
            $activation->zmS2sOauth->update(ZoomService::getServerToServerOauth([
                OauthConstant::ACCOUNT_ID => $activation->zm_s2s_account_id,
                OauthConstant::CLIENT_ID => $activation->zm_s2s_client_id,
                OauthConstant::CLIENT_SECRET => $activation->zm_s2s_client_secret,
            ])->throw()->json());
    }
}
