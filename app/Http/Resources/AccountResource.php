<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountResource extends JsonResource
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
            'description' => $this->description,
            'can_be_used_in_transactions' => $this->resource->canBeUsedInTransactions(),
            'is_leaf_account' => $this->resource->isLeafAccount(),
            'is_parent_account' => $this->resource->isParentAccount(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Include parent when loaded (for flat list)
            'parent' => $this->whenLoaded('parent', function () {
                return new self($this->parent);
            }),
            
            // Include children only when explicitly loaded (for detail views)
            'children' => $this->whenLoaded('children', function () {
                return self::collection($this->children);
            }),
            
            // Metadata for performance
            'children_count' => $this->when(isset($this->children_count), $this->children_count),
        ];
    }
}
