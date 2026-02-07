<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'transaction_date',
        'description',
        'account_id',
        'debit',
        'credit',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'transaction_date' => 'date',
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
    ];

    /**
     * Get the account that owns the transaction.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Scope to filter by date range
     */
    public function scopeDateRange($query, $startDate = null, $endDate = null)
    {
        if ($startDate) {
            $query->whereDate('transaction_date', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->whereDate('transaction_date', '<=', $endDate);
        }
        
        return $query;
    }

    /**
     * Scope to filter by account name
     */
    public function scopeByAccountName($query, $accountName = null)
    {
        if ($accountName) {
            $query->whereHas('account', function ($q) use ($accountName) {
                $q->where('name', 'like', "%{$accountName}%");
            });
        }
        
        return $query;
    }

    /**
     * Scope to get transactions for active accounts only
     */
    public function scopeActiveAccount($query)
    {
        return $query->whereHas('account', function ($q) {
            $q->where('is_active', true);
        });
    }

    /**
     * Get formatted amount attribute
     */
    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 2, ',', '.');
    }

    /**
     * Get calculated amount attribute (debit - credit)
     */
    public function getAmountAttribute(): float
    {
        return (float) ($this->debit - $this->credit);
    }

    /**
     * Get transaction type based on amount
     */
    public function getTransactionTypeAttribute(): string
    {
        if ($this->debit > 0) {
            return 'debit';
        } elseif ($this->credit > 0) {
            return 'credit';
        }
        return 'neutral';
    }

    /**
     * Check if transaction is valid (debit or credit must be filled)
     */
    public function isValidTransaction(): bool
    {
        return ($this->debit > 0 && $this->credit == 0) || 
               ($this->credit > 0 && $this->debit == 0);
    }
}
