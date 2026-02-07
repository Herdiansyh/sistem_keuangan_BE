<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTransactionRequest extends FormRequest
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
            'transaction_date' => ['nullable', 'date', 'date_format:Y-m-d'],
            'description' => ['nullable', 'string', 'max:500'],
            'account_id' => ['nullable', 'integer', 'exists:accounts,id'],
            'debit' => ['nullable', 'numeric', 'min:0', 'regex:/^\d+(\.\d{1,2})?$/'],
            'credit' => ['nullable', 'numeric', 'min:0', 'regex:/^\d+(\.\d{1,2})?$/'],
            'notes' => ['nullable', 'string', 'max:1000'],
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
            'transaction_date.date' => 'Transaction date must be a valid date',
            'transaction_date.date_format' => 'Transaction date must be in Y-m-d format',
            'description.max' => 'Description may not be greater than 500 characters',
            'account_id.exists' => 'Selected account does not exist',
            'debit.numeric' => 'Debit amount must be a number',
            'debit.min' => 'Debit amount must be at least 0',
            'debit.regex' => 'Debit amount must have maximum 2 decimal places',
            'credit.numeric' => 'Credit amount must be a number',
            'credit.min' => 'Credit amount must be at least 0',
            'credit.regex' => 'Credit amount must have maximum 2 decimal places',
            'notes.max' => 'Notes may not be greater than 1000 characters',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $this->validateTransactionAmount($validator);
            $this->validateActiveAccount($validator);
        });
    }

    /**
     * Validate that either debit or credit is filled, not both
     */
    private function validateTransactionAmount($validator)
    {
        $transaction = $this->route('transaction');
        $currentDebit = $transaction->debit ?? 0;
        $currentCredit = $transaction->credit ?? 0;
        
        $debit = $this->input('debit', $currentDebit);
        $credit = $this->input('credit', $currentCredit);

        // Both cannot be filled
        if ($debit > 0 && $credit > 0) {
            $validator->errors()->add('amount', 'Cannot fill both debit and credit fields');
        }

        // Both cannot be empty
        if ($debit == 0 && $credit == 0) {
            $validator->errors()->add('amount', 'Either debit or credit must be filled');
        }
    }

    /**
     * Validate that selected account is active
     */
    private function validateActiveAccount($validator)
    {
        $accountId = $this->input('account_id');
        
        if ($accountId) {
            $account = \App\Models\Account::find($accountId);
            
            if ($account && !$account->is_active) {
                $validator->errors()->add('account_id', 'Selected account is not active');
            }
        }
    }
}
