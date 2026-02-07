<?php

namespace App\Services;

use App\Models\Account;
use Illuminate\Support\Facades\DB;

class AccountService
{
    /**
     * Account type prefixes
     */
    const TYPE_PREFIXES = [
        'asset' => '1',
        'liability' => '2',
        'equity' => '3',
        'revenue' => '4',
        'expense' => '5',
    ];

   
    public function generateAccountCode(string $type, ?int $parentId = null): string
    {
        if ($parentId) {
            return $this->generateChildCode($parentId);
        }

        return $this->generateParentCode($type);
    }

    
    private function generateParentCode(string $type): string
    {
        $prefix = self::TYPE_PREFIXES[$type];
        
       $lastAccount = Account::where('type', $type)
            ->whereNull('parent_id')
            ->orderBy('code', 'desc')
            ->first();

        if (!$lastAccount) {
           return $prefix . '000';
        }

        $lastCode = $lastAccount->code;
        $numericPart = (int) substr($lastCode, 1);
        $newNumeric = $numericPart + 1;

        return $prefix . str_pad($newNumeric, 3, '0', STR_PAD_LEFT);
    }

    
    private function generateChildCode(int $parentId): string
    {
        $parent = Account::findOrFail($parentId);
        
       $lastChild = Account::where('parent_id', $parentId)
            ->orderBy('code', 'desc')
            ->first();

        if (!$lastChild) {
           return $parent->code . '01';
        }

        $lastCode = $lastChild->code;
        $lastTwoDigits = (int) substr($lastCode, -2);
        $newDigits = $lastTwoDigits + 1;

       if ($newDigits > 99) {
            throw new \Exception('Maximum child accounts reached for parent: ' . $parent->code);
        }

        return substr($parent->code, 0, -2) . str_pad($newDigits, 2, '0', STR_PAD_LEFT);
    }

    
    public function validateAccountType(int $parentId, string $childType): bool
    {
        $parent = Account::findOrFail($parentId);
        return $parent->type === $childType;
    }

    /**
     * Get account tree structure
     */
    public function getAccountTree()
    {
        $accounts = Account::whereNull('parent_id')
            ->with(['children' => function ($query) {
                $query->with(['children' => function ($query) {
                    $query->with('children');
                }]);
            }])
            ->orderBy('code')
            ->get();

        if ($accounts->isEmpty()) {
            return [];
        }

        return $this->buildTree($accounts);
    }

    
    /**
     * Build tree structure from accounts
     */
    private function buildTree($accounts, $level = 0)
    {
        $tree = [];
        
        foreach ($accounts as $account) {
            $account->level = $level; // Add level property
            
            $node = [
                'id' => $account->id,
                'code' => $account->code,
                'name' => $account->name,
                'type' => $account->type,
                'type_label' => ucfirst($account->type),
                'is_active' => $account->is_active,
                'parent_id' => $account->parent_id,
                'opening_balance' => (float) $account->opening_balance,
                'description' => $account->description,
                'can_be_used_in_transactions' => $account->is_active && $account->children->isEmpty(),
                'is_leaf_account' => $account->children->isEmpty(),
                'is_parent_account' => $account->children->isNotEmpty(),
                'level' => $level,
                'created_at' => $account->created_at,
                'updated_at' => $account->updated_at,
                'children' => []
            ];

            if ($account->children && $account->children->isNotEmpty()) {
                $node['children'] = $this->buildTree($account->children, $level + 1);
                $node['children_count'] = $account->children->count();
                $node['has_children'] = true;
            } else {
                $node['children_count'] = 0;
                $node['has_children'] = false;
            }

            $tree[] = $node;
        }

        return $tree;
    }
}
