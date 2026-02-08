<?php

namespace App\Http\Controllers;

use App\Http\Resources\AccountSummaryResource;
use App\Services\AccountSummaryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountSummaryController extends Controller
{
    protected AccountSummaryService $accountSummaryService;

    public function __construct(AccountSummaryService $accountSummaryService)
    {
        $this->accountSummaryService = $accountSummaryService;
    }

    /**
     * Get account summary with balances and children
     */
    public function index(Request $request): JsonResponse
    {
        // Build filters from request
        $filters = [
            'account_type' => $request->input('account_type'),
            'search' => $request->input('search'),
        ];

        // Get account summary
        $accountSummary = $this->accountSummaryService->getAccountSummary($filters);

        // Apply pagination if requested
        $perPage = $request->input('per_page', 15);
        $page = $request->input('page', 1);
        
        $paginatedData = $this->paginateCollection($accountSummary, $perPage, $page);

        return response()->json([
            'success' => true,
            'message' => 'Ringkasan akun berhasil diambil',
            'data' => [
                'accounts' => $paginatedData['items'],
                'pagination' => $paginatedData['pagination'],
                'filters' => $filters,
                'summary' => [
                    'total_accounts' => $accountSummary->count(),
                    'total_balance' => $accountSummary->sum('total_balance'),
                    'formatted_total_balance' => number_format($accountSummary->sum('total_balance'), 2, ',', '.'),
                ],
            ],
        ]);
    }

    /**
     * Get specific account summary by ID
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $accountSummary = $this->accountSummaryService->getAccountSummaryById($id);

        if (!$accountSummary) {
            return response()->json([
                'success' => false,
                'message' => 'Account not found',
                'data' => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Account summary retrieved successfully',
            'data' => $accountSummary
        ]);
    }

    /**
     * Get financial summary statistics
     */
    public function financial(Request $request): JsonResponse
    {
        // Build filters from request
        $filters = [
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
        ];
        
        $financialSummary = $this->accountSummaryService->getFinancialSummary($filters);

        Log::info('Financial summary request', [
            'filters' => $filters,
            'summary_data' => $financialSummary
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Ringkasan keuangan berhasil diambil',
            'data' => $financialSummary
        ]);
    }

    /**
     * Get top accounts by balance
     */
    public function topAccounts(Request $request): JsonResponse
    {
        $limit = min($request->input('limit', 10), 50); // Max 50 for performance
        
        $topAccounts = $this->accountSummaryService->getTopAccountsByBalance($limit);

        return response()->json([
            'success' => true,
            'message' => 'Top accounts retrieved successfully',
            'data' => [
                'accounts' => $topAccounts,
                'limit' => $limit,
            ]
        ]);
    }

    /**
     * Get account balance by ID
     */
    public function balance(Request $request, int $id): JsonResponse
    {
        $balance = $this->accountSummaryService->getAccountBalance($id);

        if ($balance === null) {
            return response()->json([
                'success' => false,
                'message' => 'Account not found',
                'data' => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Account balance retrieved successfully',
            'data' => [
                'account_id' => $id,
                'balance' => $balance,
                'formatted_balance' => number_format($balance, 2, ',', '.'),
            ]
        ]);
    }

    /**
     * Paginate collection manually
     */
    private function paginateCollection($collection, $perPage, $page): array
    {
        $total = $collection->count();
        $items = $collection->forPage($page, $perPage);

        return [
            'items' => $items,
            'pagination' => [
                'current_page' => $page,
                'last_page' => ceil($total / $perPage),
                'per_page' => $perPage,
                'total' => $total,
            ],
        ];
    }
}