<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Support\Collection;

class AccountSummaryService
{
    /**
     * Get account summary with balances and children
     */
    public function getAccountSummary(array $filters = []): Collection
    {
        // Get all active accounts with children and transactions
        $query = Account::with(['children', 'transactions'])
            ->where('is_active', true);

        // Apply filters if any
        if (!empty($filters['account_type'])) {
            $query->where('type', $filters['account_type']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('code', 'like', "%{$filters['search']}%");
            });
        }

        $accounts = $query->orderBy('code')->get();

        // Calculate balances and build summary
        return $accounts->map(function ($account) {
            return $this->buildAccountSummary($account);
        });
    }

    /**
     * Build account summary with balance and children
     */
    private function buildAccountSummary(Account $account): array
    {
        // Get transactions for this account
        $transactions = $account->transactions;
        $totalDebit = $transactions->where('debit', '>', 0)->sum('debit') ?? 0;
        $totalCredit = $transactions->where('credit', '>', 0)->sum('credit') ?? 0;
        
        // Calculate balance: Opening Balance + Debit - Credit
        $balance = $account->opening_balance + $totalDebit - $totalCredit;

        // Build children summary recursively
        $childrenSummary = [];
        if ($account->children && $account->children->isNotEmpty()) {
            $childrenSummary = $account->children->map(function ($child) {
                return $this->buildAccountSummary($child);
            })->toArray();
        }

        // Calculate total balance including children
        $childrenTotalBalance = collect($childrenSummary)->sum('total_balance');
        $totalBalance = $balance + $childrenTotalBalance;

        return [
            'id' => $account->id,
            'code' => $account->code,
            'name' => $account->name,
            'full_name' => $account->full_name,
            'type' => $account->type,
            'type_label' => $account->type_label,
            'is_active' => $account->is_active,
            'parent_id' => $account->parent_id,
            'opening_balance' => (float) $account->opening_balance,
            'total_debit' => (float) $totalDebit,
            'total_credit' => (float) $totalCredit,
            'balance' => (float) $balance,
            'total_balance' => (float) $totalBalance,
            'formatted_balance' => number_format($balance, 2, ',', '.'),
            'formatted_total_balance' => number_format($totalBalance, 2, ',', '.'),
            'has_children' => $account->children->isNotEmpty(),
            'children_count' => $account->children->count(),
            'children' => $childrenSummary,
            'transaction_count' => $transactions->count(),
        ];
    }

    /**
     * Get account summary by ID
     */
    public function getAccountSummaryById(int $accountId): ?array
    {
        $account = Account::with(['children', 'transactions'])
            ->where('is_active', true)
            ->find($accountId);

        if (!$account) {
            return null;
        }

        return $this->buildAccountSummary($account);
    }

    /**
     * Get financial summary statistics
     */
    public function getFinancialSummary(array $filters = []): array
    {
        $query = Transaction::whereHas('account', function ($q) {
            $q->where('is_active', true);
        });

        // Apply date filters
        if (!empty($filters['start_date'])) {
            $query->whereDate('transaction_date', '>=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $query->whereDate('transaction_date', '<=', $filters['end_date']);
        }

        $totalDebit = $query->sum('debit');
        $totalCredit = $query->sum('credit');
        $totalTransactions = $query->count();

        return [
            'total_debit' => (float) $totalDebit,
            'total_credit' => (float) $totalCredit,
            'net_amount' => (float) ($totalDebit - $totalCredit),
            'total_transactions' => $totalTransactions,
            'period' => [
                'start_date' => $filters['start_date'] ?? null,
                'end_date' => $filters['end_date'] ?? null,
            ],
        ];
    }

    /**
     * Get account balance by ID
     */
    public function getAccountBalance(int $accountId): ?float
    {
        $account = Account::with(['transactions'])
                ->where('is_active', true)
                ->find($accountId);

        if (!$account) {
            return null;
        }

        // Get transactions and calculate balance
        $transactions = $account->transactions;
        $totalDebit = $transactions->where('debit', '>', 0)->sum('debit') ?? 0;
        $totalCredit = $transactions->where('credit', '>', 0)->sum('credit') ?? 0;

        // Balance = Opening Balance + Debit - Credit
        return (float) ($account->opening_balance + $totalDebit - $totalCredit);
    }

    /**
     * Get top accounts by balance
     */
    public function getTopAccountsByBalance(int $limit = 10): Collection
    {
        return Account::with(['transactions'])
                ->where('is_active', true)
                ->get()
                ->map(function ($account) {
                    // Get transactions and calculate balance
                    $transactions = $account->transactions;
                    $totalDebit = $transactions->where('debit', '>', 0)->sum('debit') ?? 0;
                    $totalCredit = $transactions->where('credit', '>', 0)->sum('credit') ?? 0;
                    $balance = $account->opening_balance + $totalDebit - $totalCredit;

                    return [
                        'id' => $account->id,
                        'code' => $account->code,
                        'name' => $account->name,
                        'type' => $account->type,
                        'balance' => (float) $balance,
                        'formatted_balance' => number_format($balance, 2, ',', '.'),
                    ];
                })
                ->sortByDesc('balance')
                ->take($limit)
                ->values();
    }
}
