<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    protected TransactionService $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * Display a listing of transactions.
     */
    public function index(Request $request): JsonResponse
    {
        // Build filters from request
        $filters = [
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'account_name' => $request->input('account_name'),
        ];

        // Apply filters
        $query = $this->transactionService->getTransactions($filters);

        // Paginate results
        $transactions = $query->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'message' => 'Transactions retrieved successfully',
            'data' => [
                'transactions' => TransactionResource::collection($transactions),
                'pagination' => [
                    'current_page' => $transactions->currentPage(),
                    'last_page' => $transactions->lastPage(),
                    'per_page' => $transactions->perPage(),
                    'total' => $transactions->total(),
                ],
                'filters' => $filters,
                'statistics' => $this->transactionService->getTransactionStats($filters),
            ]
        ]);
    }

    /**
     * Store a newly created transaction.
     */
    public function store(StoreTransactionRequest $request): JsonResponse
    {
        try {
            $transaction = $this->transactionService->createTransaction($request->validated());

            // Load account relationship for response
            $transaction->load('account');

            return response()->json([
                'success' => true,
                'message' => 'Transaction created successfully',
                'data' => new TransactionResource($transaction)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create transaction: ' . $e->getMessage(),
                'data' => null
            ], 400);
        }
    }

    /**
     * Display the specified transaction.
     */
    public function show(Transaction $transaction): JsonResponse
    {
        // Load account relationship for detail view
        $transaction->load('account');

        return response()->json([
            'success' => true,
            'message' => 'Transaction retrieved successfully',
            'data' => new TransactionResource($transaction)
        ]);
    }

    /**
     * Update the specified transaction.
     */
    public function update(UpdateTransactionRequest $request, Transaction $transaction): JsonResponse
    {
        try {
            $updatedTransaction = $this->transactionService->updateTransaction(
                $transaction, 
                $request->validated()
            );

            // Load account relationship for response
            $updatedTransaction->load('account');

            return response()->json([
                'success' => true,
                'message' => 'Transaction updated successfully',
                'data' => new TransactionResource($updatedTransaction)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update transaction: ' . $e->getMessage(),
                'data' => null
            ], 400);
        }
    }

    /**
     * Remove the specified transaction.
     */
    public function destroy(Transaction $transaction): JsonResponse
    {
        try {
            $deleted = $this->transactionService->deleteTransaction($transaction);

            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'Transaction deleted successfully',
                    'data' => null
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete transaction',
                    'data' => null
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete transaction: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * Get transaction statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        // Build filters from request
        $filters = [
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'account_name' => $request->input('account_name'),
        ];

        $stats = $this->transactionService->getTransactionStats($filters);

        return response()->json([
            'success' => true,
            'message' => 'Transaction statistics retrieved successfully',
            'data' => [
                'statistics' => $stats,
                'filters' => $filters,
            ]
        ]);
    }

    /**
     * Generate transaction report
     */
    public function report(Request $request): JsonResponse
    {
        // Build filters from request
        $filters = [
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'account_name' => $request->input('account_name'),
        ];

        $report = $this->transactionService->generateReport($filters);

        return response()->json([
            'success' => true,
            'message' => 'Transaction report generated successfully',
            'data' => $report
        ]);
    }
}
