<?php

namespace App\Http\Requests;

use App\Constants\WebhookConstant;
use App\Library\Enums\Webex\WorkspaceIntegration\WebhookPayloadTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WebhookRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $signature = $this->header(WebhookConstant::X_SPARK_SIGNATURE);

        return $signature !== null &&
            hash_equals($signature, hash_hmac('sha1', $this->getContent(), $this->activation->hmac_secret));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        [$status, $events, $healthCheck] = [
            WebhookPayloadTypeEnum::STATUS->value,
            WebhookPayloadTypeEnum::EVENTS->value,
            WebhookPayloadTypeEnum::HEALTH_CHECK->value,
        ];

        return [
            // status only
            'changes' => ['required_if:type,'.$status],
            'isFullSync' => ['required_if:type,'.$status, Rule::in([true, false])],
            // event only
            'events' => ['required_if:type,'.$events],
            // event or status
            'deviceId' => ['required_if:type,'.$status.','.$events, 'string'],
            'workspaceId' => ['required_if:type,'.$status.','.$events, 'string'],
            // event, status or healthcheck
            'orgId' => ['required', 'string', Rule::hydraId('ORGANIZATION/'.$this->activation->wbx_wi_org_id)],
            'appId' => ['required', 'uuid', Rule::in($this->activation->wbx_wi_manifest_id)],
            'timestamp' => ['required', 'date'],
            'type' => ['required', Rule::in([$status, $events, $healthCheck])],
        ];
    }
}
