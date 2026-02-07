<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'type',
        'is_active',
        'parent_id',
        'opening_balance',
        'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'opening_balance' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    
    public function parent()
    {
        return $this->belongsTo(Account::class, 'parent_id');
    }

    
    public function children()
    {
        return $this->hasMany(Account::class, 'parent_id');
    }

    
    public function descendants()
    {
        return $this->children()->with('descendants');
    }

   
    public function ancestors()
    {
        return $this->parent()->with('ancestors');
    }

 
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

   
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

   
    public function scopeParents($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Check if account can be used in transactions
     * Only leaf accounts (no children) can be used for transactions
     */
    public function canBeUsedInTransactions(): bool
    {
        return $this->is_active && !$this->children()->exists();
    }

    /**
     * Check if account is a leaf account (no children)
     */
    public function isLeafAccount(): bool
    {
        return !$this->children()->exists();
    }

    /**
     * Check if account is a parent account (has children)
     */
    public function isParentAccount(): bool
    {
        return $this->children()->exists();
    }

    
    public function getTypeLabelAttribute(): string
    {
        return ucfirst($this->type);
    }

   
    public function getFullNameAttribute(): string
    {
        return $this->code . ' - ' . $this->name;
    }
}
