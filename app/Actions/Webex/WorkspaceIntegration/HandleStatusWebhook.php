<?php

namespace App\Actions\Webex\WorkspaceIntegration;

use App\Models\Activation;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class HandleStatusWebhook
{
    public function handleStatusWebhook(array $payload, Activation $activation): array
    {
        $wbxWiToken = $activation->wbxWiOauth->access_token;
        $zmS2sToken = $activation->zmS2sOauth->access_token;
        $deviceId = $payload['deviceId'] ?? null;

        $isFullSync = $payload['isFullSync'] ?? null;
        $isOnActiveCall = ($payload['changes']['updated']['SystemUnit.State.NumberOfActiveCalls'] ?? null) > 0;
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
            $json_payload = [
                'callId' => $activeCallStatus['id'],
                'numbers' => $destructuredCallbackNumber,
                'hostKey' => $hostKey,
                'toEmail' => $toEmail,
            ];

            $this->sendNewHostKeyOverMessaging($wbxWiToken, $toEmail, $hostKey);
            $this->displayInputPromptForActiveCall(
                $wbxWiToken,
                $deviceId,
                $json_payload,
                Arr::random($activation->zm_host_accounts ?? [])['email'] ?? null
            )->throw()->json();
        }

        return ['message' => 'ok'];
    }
}
