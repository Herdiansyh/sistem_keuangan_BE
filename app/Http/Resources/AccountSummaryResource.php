<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountSummaryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'full_name' => $this->full_name,
            'type' => $this->type,
            'type_label' => $this->type_label,
            'is_active' => $this->is_active,
            'parent_id' => $this->parent_id,
            'opening_balance' => (float) $this->opening_balance,
            'total_debit' => (float) $this->total_debit,
            'total_credit' => (float) $this->total_credit,
            'balance' => (float) $this->balance,
            'total_balance' => (float) $this->total_balance,
            'formatted_balance' => $this->formatted_balance,
            'formatted_total_balance' => $this->formatted_total_balance,
            'has_children' => $this->has_children,
            'children_count' => $this->children_count,
            'transaction_count' => $this->transaction_count,
            
            // Include children when available (nested structure)
            'children' => $this->when(isset($this->children) && $this->children !== null, function () {
                return AccountSummaryResource::collection($this->children);
            }),
        ];
    }
}
