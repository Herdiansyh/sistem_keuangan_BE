<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAccountRequest extends FormRequest
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
        $accountId = $this->route('account');

        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'in:asset,liability,equity,revenue,expense'],
            'is_active' => ['boolean'],
            'parent_id' => ['nullable', 'integer', 'exists:accounts,id', 
                           Rule::notIn([$accountId]), // Cannot set self as parent
                          ],
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
            'parent_id.not_in' => 'Account cannot be its own parent',
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
            $this->validateParentAccountType($validator);
            $this->validateCircularReference($validator);
        });
    }

    /**
     * Validate that parent and child have same account type
     */
    private function validateParentAccountType($validator)
    {
        if ($this->parent_id && $this->type) {
            $parent = \App\Models\Account::find($this->parent_id);
            
            if ($parent && $parent->type !== $this->type) {
                $validator->errors()->add('parent_id', 'Parent account type must match child account type');
            }
        }
    }

    /**
     * Validate circular reference in parent-child relationship
     */
    private function validateCircularReference($validator)
    {
        if ($this->parent_id) {
            $accountId = $this->route('account');
            $parent = \App\Models\Account::find($this->parent_id);
            
            // Check if the selected parent is a descendant of current account
            if ($parent && $this->isDescendant($parent, $accountId)) {
                $validator->errors()->add('parent_id', 'Cannot set a descendant account as parent');
            }
        }
    }

    /**
     * Check if account is descendant of another account
     */
    private function isDescendant($account, $ancestorId): bool
    {
        if ($account->id === $ancestorId) {
            return true;
        }

        if ($account->parent_id) {
            $parent = \App\Models\Account::find($account->parent_id);
            return $this->isDescendant($parent, $ancestorId);
        }

        return false;
    }
}
