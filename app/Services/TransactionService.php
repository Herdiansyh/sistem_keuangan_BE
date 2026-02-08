<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TransactionService
{
    /**
     * Create a new transaction with validation
     */
    public function createTransaction(array $data): Transaction
    {
        // Validate account is active
        $account = Account::findOrFail($data['account_id']);
        if (!$account->is_active) {
            throw new \InvalidArgumentException('Selected account is not active');
        }

        // Validate transaction amount (debit or credit must be filled)
        $this->validateTransactionAmount($data);

        return DB::transaction(function () use ($data) {
            $transaction = Transaction::create($data);
            
            // Update account balance (if needed in future)
            // $this->updateAccountBalance($transaction);
            
            return $transaction;
        });
    }

    /**
     * Update existing transaction
     */
    public function updateTransaction(Transaction $transaction, array $data): Transaction
    {
        // Validate account is active if account_id is being updated
        if (isset($data['account_id'])) {
            $account = Account::findOrFail($data['account_id']);
            if (!$account->is_active) {
                throw new \InvalidArgumentException('Selected account is not active');
            }
        }

        // Validate transaction amount if debit/credit is being updated
        if (isset($data['debit']) || isset($data['credit'])) {
            $updateData = array_merge($transaction->toArray(), $data);
            $this->validateTransactionAmount($updateData);
        }

        return DB::transaction(function () use ($transaction, $data) {
            $transaction->update($data);
            
            // Update account balance (if needed in future)
            // $this->updateAccountBalance($transaction);
            
            return $transaction->fresh();
        });
    }

    /**
     * Delete transaction
     */
    public function deleteTransaction(Transaction $transaction): bool
    {
        return DB::transaction(function () use ($transaction) {
            $deleted = $transaction->delete();
            
            // Update account balance (if needed in future)
            // $this->updateAccountBalance($transaction);
            
            return $deleted;
        });
    }

    /**
     * Get transactions with filters
     */
    public function getTransactions(array $filters = [])
    {
        $query = Transaction::with(['account'])
            ->activeAccount()
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc');

        // Apply date range filter
        if (isset($filters['start_date']) || isset($filters['end_date'])) {
            $query->dateRange(
                $filters['start_date'] ?? null,
                $filters['end_date'] ?? null
            );
        }

        // Apply account filter by ID
        if (!empty($filters['account_id'])) {
            $query->where('account_id', $filters['account_id']);
        }

        // Apply account name filter (for backward compatibility)
        if (!empty($filters['account_name'])) {
            $query->byAccountName($filters['account_name']);
        }

        // Debug: Log filters for troubleshooting
        if (!empty($filters['account_id'])) {
            \Log::info('Filtering by account_id: ' . $filters['account_id']);
        }

        // Apply search filter (description and notes)
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('description', 'like', "%{$filters['search']}%")
                  ->orWhere('notes', 'like', "%{$filters['search']}%");
            });
        }

        // Apply transaction type filter
        if (!empty($filters['transaction_type'])) {
            if ($filters['transaction_type'] === 'debit') {
                $query->where('debit', '>', 0);
            } elseif ($filters['transaction_type'] === 'credit') {
                $query->where('credit', '>', 0);
            }
        }

        return $query;
    }

    /**
     * Get transaction statistics
     */
    public function getTransactionStats(array $filters = []): array
    {
        $query = $this->getTransactions($filters);

        $totalDebit = $query->sum('debit');
        $totalCredit = $query->sum('credit');
        $totalTransactions = $query->count();

        return [
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'total_amount' => $totalDebit - $totalCredit,
            'total_transactions' => $totalTransactions,
        ];
    }

    /**
     * Validate transaction amount (debit or credit must be filled)
     */
    private function validateTransactionAmount(array $data): void
    {
        $debit = $data['debit'] ?? 0;
        $credit = $data['credit'] ?? 0;

        // Both cannot be filled
        if ($debit > 0 && $credit > 0) {
            throw new \InvalidArgumentException('Cannot fill both debit and credit fields');
        }

        // Both cannot be empty
        if ($debit == 0 && $credit == 0) {
            throw new \InvalidArgumentException('Either debit or credit must be filled');
        }

        // Amount must be positive
        if ($debit < 0 || $credit < 0) {
            throw new \InvalidArgumentException('Debit and credit amounts must be positive');
        }
    }

    /**
     * Update account balance (placeholder for future implementation)
     */
    private function updateAccountBalance(Transaction $transaction): void
    {
        // This can be implemented when we need to track account balances
        // For now, we'll keep it as a placeholder
    }

    /**
     * Generate transaction report
     */
    public function generateReport(array $filters = []): array
    {
        $transactions = $this->getTransactions($filters)->get();
        $stats = $this->getTransactionStats($filters);

        return [
            'transactions' => $transactions,
            'statistics' => $stats,
            'filters' => $filters,
            'generated_at' => Carbon::now()->toISOString(),
        ];
    }
}
