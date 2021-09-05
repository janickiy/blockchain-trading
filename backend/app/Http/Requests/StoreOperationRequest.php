<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOperationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'from_account_id' => 'exists:App\Models\Account,id',
            'action' => 'required|integer',
            'target_username' => 'required|string',
            'amount' => 'required|numeric',
            'condition' => 'required|integer',
            'condition_payload' => 'array',
            'is_cloner' => 'required|boolean',
        ];
    }
}
