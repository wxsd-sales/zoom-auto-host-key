<?php

namespace App\Http\Requests;

use App\Constants\ActivationConstant;
use App\Http\Requests\Abstract\ActivationRequest;
use Illuminate\Contracts\Validation\ValidationRule;

class StoreActivationRequest extends ActivationRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->hasValidSignature();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            ActivationConstant::WBX_WI_CLIENT_ID => [
                'required', 'string', 'unique:activations,wbx_wi_client_id', 'max:255',
            ],
            ActivationConstant::WBX_WI_CLIENT_SECRET => [
                'required', 'string', 'max:255',
            ],
            ActivationConstant::WBX_WI_ACTION_JWT => [
                'required', 'string', 'unique:wbx_wi_actions,jwt',
            ],
            ActivationConstant::ZM_S2S_ACCOUNT_ID => [
                'required', 'string', 'max:255',
            ],
            ActivationConstant::ZM_S2S_CLIENT_ID => [
                'required', 'string', 'unique:activations,zm_s2s_client_id', 'max:255',
            ],
            ActivationConstant::ZM_S2S_CLIENT_SECRET => [
                'required', 'string', 'max:255',
            ],
            ActivationConstant::ZM_HOST_ACCOUNTS => [
                'sometimes', 'array', 'min:1', 'max:25',
            ],
            ActivationConstant::ZM_HOST_ACCOUNTS.'.*.email' => [
                'required', 'email', 'distinct:ignore_case',
            ],
            ActivationConstant::ZM_HOST_ACCOUNTS.'.*.key' => [
                'required', 'string', 'digits:6',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            ActivationConstant::WBX_WI_CLIENT_ID => 'Webex Workspace Integration OAuth Client ID',
            ActivationConstant::WBX_WI_CLIENT_SECRET => 'Webex Workspace Integration OAuth Client Secret',
            ActivationConstant::WBX_WI_ACTION_JWT => 'Webex Workspace Integration JWT (Activation/Update code)',
            ActivationConstant::ZM_S2S_ACCOUNT_ID => 'Zoom Server to Server OAuth app Account ID',
            ActivationConstant::ZM_S2S_CLIENT_ID => 'Zoom Server to Server OAuth app Client ID',
            ActivationConstant::ZM_S2S_CLIENT_SECRET => 'Zoom Server to Server OAuth app Client Secret',
            ActivationConstant::ZM_HOST_ACCOUNTS => 'Zoom Host Account',
            ActivationConstant::ZM_HOST_ACCOUNTS.'.*.email' => 'Zoom Host Account Email',
            ActivationConstant::ZM_HOST_ACCOUNTS.'.*.key' => 'Zoom Host Account Key',
        ];
    }
}
