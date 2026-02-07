# Transaction API Examples

## POST /api/transactions
```json
{
  "success": true,
  "message": "Transaction created successfully",
  "data": {
    "id": 1,
    "transaction_date": "2026-02-07",
    "description": "Pembelian ATK",
    "account_id": 2,
    "debit": 500000.00,
    "credit": 0,
    "amount": 500000.00,
    "transaction_type": "debit",
    "account": {
      "id": 2,
      "code": "100001",
      "name": "Kas Kecil",
      "type": "asset"
    }
  }
}
```

## GET /api/transactions
```json
{
  "success": true,
  "message": "Transactions retrieved successfully",
  "data": {
    "transactions": [...],
    "pagination": {...},
    "statistics": {
      "total_debit": 1500000,
      "total_credit": 500000,
      "total_amount": 1000000,
      "total_transactions": 3
    }
  }
}
```

## Design Notes:
- Single entry: debit OR credit (not both)
- Account must be active
- Soft delete enabled
- Pagination & filtering supported
