<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAccountRequest extends FormRequest
{
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:asset,liability,equity,revenue,expense'],
            'is_active' => ['boolean'],
            'parent_id' => ['nullable', 'integer', 'exists:accounts,id'],
            'opening_balance' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Account name is required',
            'name.max' => 'Account name may not be greater than 255 characters',
            'type.required' => 'Account type is required',
            'type.in' => 'Account type must be one of: asset, liability, equity, revenue, expense',
            'parent_id.exists' => 'Selected parent account does not exist',
            'opening_balance.required' => 'Opening balance is required',
            'opening_balance.numeric' => 'Opening balance must be a number',
            'opening_balance.min' => 'Opening balance must be at least 0',
            'description.max' => 'Description may not be greater than 1000 characters',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->parent_id) {
                $this->validateParentAccountType($validator);
            }
        });
    }

    /**
     * Validate that parent and child have same account type
     */
    private function validateParentAccountType($validator)
    {
        $parent = \App\Models\Account::find($this->parent_id);
        
        if ($parent && $parent->type !== $this->type) {
            $validator->errors()->add('parent_id', 'Parent account type must match child account type');
        }
    }
}
