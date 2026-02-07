<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAccountRequest;
use App\Http\Requests\UpdateAccountRequest;
use App\Http\Resources\AccountResource;
use App\Http\Resources\AccountTreeResource;
use App\Models\Account;
use App\Services\AccountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    protected AccountService $accountService;

    public function __construct(AccountService $accountService)
    {
        $this->accountService = $accountService;
    }

    /**
     * Display a listing of accounts.
     */
    public function index(Request $request): JsonResponse
    {
        // Build query without relationships by default
        $query = Account::query();

        // Apply filters
        $this->applyFilters($query, $request);

        // Apply includes based on query parameters
        $this->applyIncludes($query, $request);

        $accounts = $query->orderBy('code')->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'message' => 'Accounts retrieved successfully',
            'data' => [
                'accounts' => AccountResource::collection($accounts),
                'pagination' => [
                    'current_page' => $accounts->currentPage(),
                    'last_page' => $accounts->lastPage(),
                    'per_page' => $accounts->perPage(),
                    'total' => $accounts->total(),
                ]
            ]
        ]);
    }

    /**
     * Apply includes based on query parameters
     */
    private function applyIncludes($query, Request $request): void
    {
        if ($request->has('include')) {
            $includes = array_map('trim', explode(',', $request->input('include')));
            
            if (in_array('parent', $includes)) {
                $query->with('parent');
            }
        }
    }

    /**
     * Apply filters to account query
     */
    private function applyFilters($query, Request $request): void
    {
        // Filter by type
        $query->when($request->has('type'), function ($q) use ($request) {
            $q->ofType($request->type);
        });

        // Filter by active status
        $query->when($request->has('is_active'), function ($q) use ($request) {
            $q->where('is_active', $request->boolean('is_active'));
        });

        // Filter by parent
        $query->when($request->has('parent_id'), function ($q) use ($request) {
            $this->applyParentFilter($q, $request->input('parent_id'));
        });

        // Search by name or code
        $query->when($request->has('search'), function ($q) use ($request) {
            $search = $request->search;
            $q->where(function ($subQuery) use ($search) {
                $subQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
            });
        });
    }

    /**
     * Apply parent filter with proper null handling
     */
    private function applyParentFilter($query, $parentId): void
    {
        if ($parentId === 'null' || $parentId === null || $parentId === '') {
            // Filter root accounts only
            $query->whereNull('parent_id');
        } elseif (is_numeric($parentId) && $parentId > 0) {
            // Filter by specific parent ID
            $query->where('parent_id', $parentId);
        }
        // Ignore invalid parent_id values (non-numeric, negative, etc.)
    }

    /**
     * Get accounts in tree structure.
     */
    public function tree(): JsonResponse
    {
        try {
            $tree = $this->accountService->getAccountTree();

            return response()->json([
                'success' => true,
                'message' => 'Account tree retrieved successfully',
                'data' => $tree
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve account tree: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * Store a newly created account.
     */
    public function store(StoreAccountRequest $request): JsonResponse
    {
        try {
            // Generate account code
            $code = $this->accountService->generateAccountCode(
                $request->type,
                $request->parent_id
            );

            $account = Account::create([
                'code' => $code,
                'name' => $request->name,
                'type' => $request->type,
                'is_active' => $request->boolean('is_active', true),
                'parent_id' => $request->parent_id,
                'opening_balance' => $request->opening_balance,
                'description' => $request->description,
            ]);

            // Load relationships for response
            $account->load(['parent', 'children']);

            return response()->json([
                'success' => true,
                'message' => 'Account created successfully',
                'data' => new AccountResource($account)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create account: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * Display the specified account.
     */
    public function show(Account $account): JsonResponse
    {
        // Load parent and children for detail view
        $account->load(['parent', 'children']);

        return response()->json([
            'success' => true,
            'message' => 'Account retrieved successfully',
            'data' => new AccountResource($account)
        ]);
    }

    /**
     * Update the specified account.
     */
    public function update(UpdateAccountRequest $request, Account $account): JsonResponse
    {
        try {
            $account->update([
                'name' => $request->name,
                'type' => $request->type ?? $account->type, // Use existing type if not provided
                'is_active' => $request->boolean('is_active', $account->is_active),
                'parent_id' => $request->parent_id,
                'opening_balance' => $request->opening_balance,
                'description' => $request->description,
            ]);

            // Load relationships for response
            $account->load(['parent', 'children']);

            return response()->json([
                'success' => true,
                'message' => 'Account updated successfully',
                'data' => new AccountResource($account)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update account: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * Remove the specified account (soft delete).
     */
    public function destroy(Account $account): JsonResponse
    {
        try {
            // Check if account has children
            if ($account->children()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete account with child accounts',
                    'data' => null
                ], 422);
            }

            // Check if account has transactions (when transaction module is implemented)
            // For now, we'll just check if it can be deleted
            if (!$account->canBeUsedInTransactions()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete inactive account',
                    'data' => null
                ], 422);
            }

            $account->delete();

            return response()->json([
                'success' => true,
                'message' => 'Account deleted successfully',
                'data' => null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete account: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }
}
