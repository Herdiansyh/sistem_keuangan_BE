# Account Summary API Examples

## GET /api/account-summary
```json
{
  "success": true,
  "message": "Account summary retrieved successfully",
  "data": {
    "accounts": [
      {
        "id": 1,
        "code": "1000",
        "name": "Kas",
        "full_name": "1000 - Kas",
        "type": "asset",
        "type_label": "Asset",
        "is_active": true,
        "parent_id": null,
        "opening_balance": 10000000,
        "total_debit": 5000000,
        "total_credit": 2000000,
        "balance": 3000000,
        "total_balance": 3500000,
        "formatted_balance": "3.000.000,00",
        "formatted_total_balance": "3.500.000,00",
        "has_children": true,
        "children_count": 2,
        "transaction_count": 1,
        "children": [
          {
            "id": 4,
            "code": "1001",
            "name": "Kas Kecil",
            "balance": 2000000,
            "total_balance": 2000000,
            "formatted_balance": "2.000.000,00",
            "children": []
          }
        ]
      }
    ],
    "pagination": {...},
    "summary": {
      "total_accounts": 10,
      "total_balance": 15000000,
      "formatted_total_balance": "15.000.000,00"
    }
  }
}
```

## GET /api/account-summary/{id}
```json
{
  "success": true,
  "message": "Account summary retrieved successfully",
  "data": {
    "id": 1,
    "code": "1000",
    "name": "Kas",
    "type": "asset",
    "balance": 3000000,
    "total_balance": 3500000,
    "children": [...]
  }
}
```

## Design Notes:
- Real-time balance calculation (no database storage)
- Recursive children aggregation
- Pagination support
- Filter by account type and search
