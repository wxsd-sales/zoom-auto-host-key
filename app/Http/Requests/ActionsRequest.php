<?php

namespace App\Http\Requests;

use App\Constants\ActionsConstant;
use App\Http\Requests\Abstract\ActivationRequest;

class ActionsRequest extends ActivationRequest
{
    protected const JWT = ActionsConstant::JWT;

    protected const JWT_PAYLOAD = ActionsConstant::JWT_PAYLOAD;

    protected const JWT_ERRORED = ActionsConstant::JWT_ERRORED;

    public readonly ?array $jwtPayload;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [self::JWT => ['required', 'string']];
    }

    /** {@inheritdoc} */
    public function after(): array
    {
        $data = self::addJwtPayloadData($this->all());

        $setJwtPayload = function ($data) {
            $this->jwtPayload = $data[self::JWT_PAYLOAD] ?? null;
        };

        return [fn ($validator) => $this->performJwtPayloadValidation($validator, $data, $setJwtPayload)];
    }
}
