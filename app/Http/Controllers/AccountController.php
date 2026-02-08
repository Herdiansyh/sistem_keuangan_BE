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
            'message' => 'Akun berhasil diambil',
            'data' => [
                'accounts' => AccountResource::collection($accounts),
                'pagination' => [
                    'current_page' => $accounts->currentPage(),
                    'last_page' => $accounts->lastPage(),
                    'per_page' => $accounts->perPage(),
                    'total' => $accounts->total(),
                ],
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
                'message' => 'Struktur akun berhasil diambil',
                'data' => $tree
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil struktur akun: ' . $e->getMessage(),
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
                'message' => 'Akun berhasil dibuat',
                'data' => new AccountResource($account)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat akun: ' . $e->getMessage(),
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
            // Check if account has transactions and trying to change parent
            if ($account->transactions()->exists() && $request->parent_id !== $account->parent_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat mengubah parent akun yang memiliki transaksi',
                    'data' => null
                ], 422);
            }

            // Check if account has children and trying to change type
            if ($account->children()->exists() && $request->type && $request->type !== $account->type) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat mengubah tipe akun yang memiliki akun anak',
                    'data' => null
                ], 422);
            }

            // NEW: Check if account has transactions and trying to change type
            if ($account->transactions()->exists() && $request->type && $request->type !== $account->type) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat mengubah tipe akun yang memiliki transaksi. Kode akun sudah fixed.',
                    'data' => null
                ], 422);
            }

            // NEW: For accounts with existing code, prevent type change to maintain consistency
            if ($account->code && $request->type && $request->type !== $account->type) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat mengubah tipe akun yang sudah memiliki kode. Buat akun baru dengan tipe yang diinginkan.',
                    'data' => null
                ], 422);
            }

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
                'message' => 'Akun berhasil diperbarui',
                'data' => new AccountResource($account)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui akun: ' . $e->getMessage(),
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
                    'message' => 'Tidak dapat menghapus akun yang memiliki akun anak',
                    'data' => null
                ], 422);
            }

            // Check if account has transactions
            if ($account->transactions()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat menghapus akun yang memiliki transaksi',
                    'data' => null
                ], 422);
            }

            $account->delete();

            return response()->json([
                'success' => true,
                'message' => 'Akun berhasil dihapus',
                'data' => null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus akun: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }
}
