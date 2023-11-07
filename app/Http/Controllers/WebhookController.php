<?php

namespace App\Http\Controllers;

use App\Actions\DisplayInputPromptForActiveCall;
use App\Actions\GetActiveCallStatus;
use App\Actions\SendHostOtp;
use App\Actions\SignalMeetingHostKey;
use App\Actions\UpdateMeetingHost;
use App\Actions\UpdateUserHostKey;
use App\Http\Requests\WebhookRequest;
use App\Library\Constants\OauthConstant;
use App\Library\Enums\Webex\WorkspaceIntegration\WebhookPayloadTypeEnum;
use App\Models\Activation;
use App\Services\OauthService;
use App\Services\WebexService;
use App\Services\ZoomService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class WebhookController extends Controller
{
    /*
     * Destructors the dial string of a call if it's and outgoing Zoom CRC meeting without any host key.
     */
    protected function shouldDestructureCallbackNumber(array $call)
    {
        $zoomCrcSuffix = '@zoomcrc.com';
        $callDirection = $call['Direction'] ?? '';
        $callbackNumber = str_replace(['spark:', 'sip:'], '', $call['CallbackNumber'] ?? '');
        $filteredNumber = preg_filter("/$zoomCrcSuffix$/", '', strtolower($callbackNumber));
        $explodedNumber = empty($filteredNumber) ? [] : explode('.', $filteredNumber);

        return $callDirection === 'Outgoing' && ! empty($explodedNumber[1]) && empty($explodedNumber[3])
            ? $explodedNumber
            : [];
    }

    protected function createDtmfString(array $numbers, ?string $hostKey): string
    {
        return $numbers[2] === '504'
            ? '717'.$hostKey.'#*7'
            : $hostKey.'#';
    }

    protected function getMeetingDetails($token, $meetingId)
    {
        $zoomApiUrl = config('services.zoom.api_url');
        $meetingsUrl = $zoomApiUrl.'/meetings/'.$meetingId;

        return Http::withToken($token)->get($meetingsUrl);
    }

    public function store(WebhookRequest $request, Activation $activation): JsonResponse
    {
        $data = $request->validated();
        $wbxWiToken = $activation->wbxWiOauth->access_token;
        $zmS2sToken = $activation->zmS2sOauth->access_token;
        $deviceId = $data['deviceId'] ?? null;

        return match (WebhookPayloadTypeEnum::tryFrom($data['type'])) {
            WebhookPayloadTypeEnum::HEALTH_CHECK => response()->json([
                'message' => 'ok',
                WebhookPayloadTypeEnum::HEALTH_CHECK->value => true,
            ]),
            WebhookPayloadTypeEnum::STATUS => $this->handleStatusChange(
                $data, $wbxWiToken, $deviceId, $zmS2sToken, $activation
            ),
            WebhookPayloadTypeEnum::EVENTS => $this->handleEventsUpdate(
                $data[WebhookPayloadTypeEnum::EVENTS->value], $activation, $zmS2sToken, $wbxWiToken, $deviceId
            ),
            default => response()->json(['message' => 'ok']),
        };
    }

    /**
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function handleStatusChange(
        mixed $data, mixed $wbxWiToken, mixed $deviceId, mixed $zmS2sToken, Activation $activation
    ): JsonResponse {
        $isFullSync = $data['isFullSync'] ?? null;
        $isOnActiveCall = ($data['changes']['updated']['SystemUnit.State.NumberOfActiveCalls'] ?? null) > 0;
        $activeCallStatus = $isOnActiveCall
            ? GetActiveCallStatus::handle($wbxWiToken, $deviceId)->throw()->json('result.Call.0')
            : [];
        $destructuredCallbackNumber = $this->shouldDestructureCallbackNumber($activeCallStatus);

        if ($isFullSync) {
            $this->shouldUpdateWbxWiOauth($activation);
            $this->shouldUpdateZmS2sOauth($activation);
        }

        if ($isFullSync === false && empty($destructuredCallbackNumber) !== true) {
            $meetingId = $destructuredCallbackNumber[0];
            $meetingDetails = $this->getMeetingDetails($zmS2sToken, $meetingId)->json();
            $meetingStatus = Arr::get($meetingDetails, 'status');

            if ($meetingStatus === 'waiting'
                && $activation->operation_mode === 'automatic'
                && empty($activation->zm_host_accounts) === true
            ) {
                $toEmail = Arr::get($meetingDetails, 'host_email');
                $hostKey = Str::password(6, false, true, false);
                $dtmfString = $this->createDtmfString($destructuredCallbackNumber, $hostKey);
                UpdateUserHostKey::handle($zmS2sToken, $toEmail, $hostKey)->throw();
                SignalMeetingHostKey::handle($wbxWiToken, $deviceId, $activeCallStatus['id'], $dtmfString)->throw();

                return response()->json(['message' => 'ok']);
            }

            if ($meetingStatus === 'waiting'
                && $activation->operation_mode === 'automatic'
                && empty($activation->zm_host_accounts) !== true
            ) {
                $currentHost = Arr::get($meetingDetails, 'host_email');
                $machineAccount = Arr::first(
                    $activation->zm_host_accounts,
                    fn ($v) => strcasecmp(Arr::get($v, 'email'), $currentHost) === 0,
                    Arr::random($activation->zm_host_accounts)
                );
                $toEmail = $machineAccount['email'];
                $hostKey = $machineAccount['key'];
                $dtmfString = $this->createDtmfString($destructuredCallbackNumber, $hostKey);
                if ($currentHost !== $machineAccount['email']) {
                    UpdateMeetingHost::handle($zmS2sToken, $meetingId, $toEmail, $currentHost)->throw();
                }
                SignalMeetingHostKey::handle($wbxWiToken, $deviceId, $activeCallStatus['id'], $dtmfString)->throw();

                return response()->json(['message' => 'ok']);
            }

            if ($meetingStatus === 'waiting' && $activation->operation_mode === 'manual') {
                $toEmail = Arr::get($meetingDetails, 'host_email');
                $hostKey = Str::password(6, false, true, false);
                $randomMachineAccountEmail = empty($activation->zm_host_accounts) === false
                    ? Arr::random($activation->zm_host_accounts)['email']
                    : '';
                $payload = [
                    'callId' => $activeCallStatus['id'],
                    'numbers' => $destructuredCallbackNumber,
                    'hostKey' => $hostKey,
                    'toEmail' => $toEmail,
                ];
                SendHostOtp::handle($wbxWiToken, $toEmail, $hostKey);
                DisplayInputPromptForActiveCall::handle($wbxWiToken, $deviceId, $payload, $randomMachineAccountEmail)
                    ->throw();

                return response()->json(['message' => 'ok']);
            }
        }

        return response()->json(['message' => 'ok']);
    }

    /**
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function handleEventsUpdate(
        $data, Activation $activation, mixed $zmS2sToken, mixed $wbxWiToken, mixed $deviceId
    ): JsonResponse {
        $input = Arr::first(
            $data,
            fn ($v) => Arr::get($v, 'key') === 'UserInterface.Message.TextInput.Response'
        );
        $payload = json_decode(Arr::get($input, 'value.FeedbackId', '{}'), true);
        $destructuredCallbackNumber = $payload['numbers'];
        $meetingId = $destructuredCallbackNumber[0];
        $text = trim(Arr::get($input, 'value.Text', ''));
        $callId = $payload['callId'] ?? null;
        $isHostKeyInput = preg_match('/^[0-9]{6,10}$/', $text);
        $machineAccount = Arr::first(
            $activation->zm_host_accounts,
            fn ($v) => strcasecmp(Arr::get($v, 'email'), $text) === 0
        );

        // use a machine account as indicated by the user
        if (! empty($input) && $machineAccount != null) {
            $meetingDetails = $this->getMeetingDetails($zmS2sToken, $meetingId)->throw()->json();
            $currentHost = Arr::get($meetingDetails, 'host_email');
            $toEmail = $machineAccount['email'];
            $hostKey = $machineAccount['key'];
            $dtmfString = $this->createDtmfString($destructuredCallbackNumber, $hostKey);
            if ($currentHost !== $machineAccount['email']) {
                UpdateMeetingHost::handle($zmS2sToken, $meetingId, $toEmail, $currentHost)->throw();
            }
            SignalMeetingHostKey::handle($wbxWiToken, $deviceId, $callId, $dtmfString)->throw();

            return response()->json(['message' => 'ok']);
        }

        // use the host-key provide by the user (they know their host key)
        if (! empty($input) && $isHostKeyInput && $text !== $payload['hostKey']) {
            $hostKey = $text;
            $dtmfString = $this->createDtmfString($destructuredCallbackNumber, $hostKey);
            SignalMeetingHostKey::handle($wbxWiToken, $deviceId, $callId, $dtmfString)->throw()->json();

            return response()->json(['message' => 'ok']);
        }

        // use the new host-key provided over webex by the user
        if (! empty($input) && $isHostKeyInput && $text === $payload['hostKey']) {
            $hostKey = $payload['hostKey'];
            $dtmfString = $this->createDtmfString($payload, $hostKey);
            $oldHost = $payload['toEmail'];
            UpdateUserHostKey::handle($zmS2sToken, $oldHost, $hostKey)->throw()->json();
            SignalMeetingHostKey::handle($wbxWiToken, $deviceId, $callId, $dtmfString)->throw()->json();

            return response()->json(['message' => 'ok']);
        }

        return response()->json(['message' => 'ok']);
    }

    protected function shouldUpdateWbxWiOauth(Activation $activation)
    {
        return now()->timestamp + 6000 >= $activation->wbxWiOauth->expires_at->timestamp &&
            $activation->wbxWiOauth->update(OauthService::addExpiresAt(WebexService::getWorkspaceIntegrationOauth([
                OauthConstant::CLIENT_ID => $activation->wbx_wi_client_id,
                OauthConstant::CLIENT_SECRET => $activation->wbx_wi_client_secret,
                OauthConstant::REFRESH_TOKEN => $activation->wbxWiOauth->refresh_token,
            ])->throw()->json()));
    }

    protected function shouldUpdateZmS2sOauth(Activation $activation)
    {
        return now()->timestamp + 2400 >= $activation->zmS2sOauth->expires_at->timestamp &&
            $activation->zmS2sOauth->update(OauthService::addExpiresAt(ZoomService::getServerToServerOauth([
                OauthConstant::ACCOUNT_ID => $activation->zm_s2s_account_id,
                OauthConstant::CLIENT_ID => $activation->zm_s2s_client_id,
                OauthConstant::CLIENT_SECRET => $activation->zm_s2s_client_secret,
            ])->throw()->json()));
    }
}
