<?php

namespace App\Http\Requests;

use App\Constants\WebhookConstant;
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

        if ($signature === null) {
            return false;
        }

        $computedSignature = hash_hmac('sha1', $this->getContent(), $this->activation->hmac_secret);

        return hash_equals($signature, $computedSignature);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            // status only
            'changes' => ['required_if:type,'.WebhookConstant::STATUS],
            'isFullSync' => ['required_if:type,'.WebhookConstant::STATUS, Rule::in([true, false])],
            // event only
            'events' => ['required_if:type,'.WebhookConstant::EVENTS],
            // event or status
            'deviceId' => ['required_if:type,'.WebhookConstant::STATUS.','.WebhookConstant::EVENTS, 'string'],
            'workspaceId' => ['required_if:type,'.WebhookConstant::STATUS.','.WebhookConstant::EVENTS, 'string'],
            // event, status or healthcheck
            'orgId' => ['required', 'string', Rule::hydraId('ORGANIZATION/'.$this->activation->wbx_wi_org_id)],
            'appId' => ['required', 'uuid', Rule::in($this->activation->wbx_wi_manifest_id)],
            'timestamp' => ['required', 'date'],
            'type' => [
                'required', Rule::in([WebhookConstant::STATUS, WebhookConstant::EVENTS, WebhookConstant::HEALTH_CHECK]),
            ],
        ];
    }
}
