<?php

namespace App\Actions\Webex\WorkspaceIntegration;

use App\Models\Activation;
use Illuminate\Support\Arr;

class HandleEventsWebhook
{
    public function handleEventsWebhook(array $payload, Activation $activation): array
    {
        $wbxWiToken = $activation->wbxWiOauth->access_token;
        $zmS2sToken = $activation->zmS2sOauth->access_token;
        $deviceId = $payload['deviceId'] ?? null;

        $input = Arr::first(
            $payload,
            fn ($v) => Arr::get($v, 'key') === 'UserInterface.Message.TextInput.Response'
        );
        $feedback_payload = json_decode(Arr::get($input, 'value.FeedbackId', '{}'), true);
        $text = trim(Arr::get($input, 'value.Text', ''));
        $callId = $feedback_payload['callId'] ?? null;
        $isHostKeyInput = preg_match('/^[0-9]{6,10}$/', $text);
        $machineAccount = Arr::first(
            $activation->zm_host_accounts,
            fn ($v) => strcasecmp(Arr::get($v, 'email'), $text) === 0
        );

        if (! empty($input) && $machineAccount != null) {
            $hostKey = $machineAccount['key'];
            $dtmfString = $this->createDtmfString($feedback_payload, $hostKey);
            $newHost = $machineAccount['email'];
            $meetingId = $feedback_payload['numbers'][0];
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
        } elseif (! empty($input) && $isHostKeyInput && $text !== $feedback_payload['hostKey']) {
            $hostKey = $text;
            $dtmfString = $this->createDtmfString($feedback_payload, $hostKey);
            $this->signalHostKey($wbxWiToken, $deviceId, $callId, $dtmfString)->throw()->json();
        } elseif (! empty($input) && $isHostKeyInput && $text === $feedback_payload['hostKey']) {
            $hostKey = $feedback_payload['hostKey'];
            $dtmfString = $this->createDtmfString($feedback_payload, $hostKey);
            $oldHost = $feedback_payload['toEmail'];
            $this->updateHostKey($zmS2sToken, $oldHost, $hostKey)->throw()->json();
            $this->signalHostKey($wbxWiToken, $deviceId, $callId, $dtmfString)->throw()->json();
        }

        return ['message' => 'ok'];
    }
}
