# Transaction API Testing Guide

## 📋 Prerequisites

### 1. Login untuk mendapatkan token
```http
POST /api/auth/login
Content-Type: application/json

{
  "email": "admin@example.com",
  "password": "password"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "token": "1|abc123...",
    "user": {...}
  }
}
```

### 2. Copy token untuk digunakan di request berikutnya
```
Authorization: Bearer 1|abc123...
```

## 🏦 Available Accounts (dari seeder)

### Asset Accounts
- **ID 1**: 1000 - Kas (parent)
- **ID 2**: 1100 - Bank (parent) 
- **ID 3**: 2000 - Piutang Usaha (parent)
- **ID 4**: 1001 - Kas Kecil (child of Kas)
- **ID 5**: 1002 - Kas Besar (child of Kas)

### Liability Accounts  
- **ID 6**: 3000 - Utang Usaha (parent)
- **ID 7**: 3100 - Utang Bank (parent)

### Equity Accounts
- **ID 8**: 4000 - Modal Saham (parent)

### Revenue Accounts
- **ID 9**: 5000 - Pendapatan Usaha (parent)

### Expense Accounts
- **ID 10**: 6000 - Beban Operasional (parent)

## 🧪 Testing Scenarios

### 1. Create Transaction (Debit)
```http
POST /api/transactions
Authorization: Bearer {token}
Content-Type: application/json

{
  "transaction_date": "2026-02-07",
  "description": "Pembelian ATK kantor",
  "account_id": 4,
  "debit": 500000,
  "credit": 0,
  "notes": "Pembelian kertas, pulpen, dan alat tulis lainnya"
}
```

### 2. Create Transaction (Credit)
```http
POST /api/transactions
Authorization: Bearer {token}
Content-Type: application/json

{
  "transaction_date": "2026-02-07",
  "description": "Penjualan produk",
  "account_id": 9,
  "debit": 0,
  "credit": 2500000,
  "notes": "Penjualan kepada customer ABC"
}
```

### 3. Get All Transactions
```http
GET /api/transactions
Authorization: Bearer {token}
```

### 4. Get Transactions with Filters
```http
GET /api/transactions?start_date=2026-02-01&end_date=2026-02-07&account_name=Kas
Authorization: Bearer {token}
```

### 5. Get Transaction Detail
```http
GET /api/transactions/1
Authorization: Bearer {token}
```

### 6. Update Transaction
```http
PUT /api/transactions/1
Authorization: Bearer {token}
Content-Type: application/json

{
  "description": "Pembelian ATK kantor (updated)",
  "debit": 550000,
  "notes": "Updated notes"
}
```

### 7. Delete Transaction (Soft Delete)
```http
DELETE /api/transactions/1
Authorization: Bearer {token}
```

### 8. Get Transaction Statistics
```http
GET /api/transactions/statistics
Authorization: Bearer {token}
```

### 9. Generate Transaction Report
```http
GET /api/transactions/report?start_date=2026-02-01&end_date=2026-02-07
Authorization: Bearer {token}
```

## ⚠️ Error Scenarios to Test

### 1. Invalid Account (Inactive/Not Found)
```http
POST /api/transactions
{
  "account_id": 999, // Not exists
  "debit": 100000
}
```
**Expected:** 400 - "Selected account does not exist"

### 2. Both Debit and Credit Filled
```http
POST /api/transactions
{
  "debit": 100000,
  "credit": 50000
}
```
**Expected:** 400 - "Cannot fill both debit and credit fields"

### 3. Both Debit and Credit Empty
```http
POST /api/transactions
{
  "debit": 0,
  "credit": 0
}
```
**Expected:** 400 - "Either debit or credit must be filled"

### 4. Negative Amount
```http
POST /api/transactions
{
  "debit": -100000
}
```
**Expected:** 400 - "Debit amount must be at least 0"

### 5. Missing Required Fields
```http
POST /api/transactions
{
  "debit": 100000
}
```
**Expected:** 400 - "Transaction date is required", "Description is required", etc.

### 6. Invalid Date Format
```http
POST /api/transactions
{
  "transaction_date": "07-02-2026", // Wrong format
  "description": "Test",
  "account_id": 1,
  "debit": 100000
}
```
**Expected:** 400 - "Transaction date must be in Y-m-d format"

## 📊 Expected Response Examples

### Create Transaction Success
```json
{
  "success": true,
  "message": "Transaction created successfully",
  "data": {
    "id": 1,
    "transaction_date": "2026-02-07",
    "description": "Pembelian ATK kantor",
    "account_id": 4,
    "debit": 500000.00,
    "credit": 0,
    "amount": 500000.00,
    "transaction_type": "debit",
    "formatted_amount": "500.000,00",
    "account": {
      "id": 4,
      "code": "1001",
      "name": "Kas Kecil",
      "type": "asset"
    }
  }
}
```

### Get Transactions Success
```json
{
  "success": true,
  "message": "Transactions retrieved successfully",
  "data": {
    "transactions": [...],
    "pagination": {
      "current_page": 1,
      "last_page": 1,
      "per_page": 15,
      "total": 5
    },
    "statistics": {
      "total_debit": 1500000,
      "total_credit": 2500000,
      "total_amount": -1000000,
      "total_transactions": 5
    }
  }
}
```

## 🎯 Testing Checklist

- [ ] Login dan dapat token
- [ ] Create transaction (debit)
- [ ] Create transaction (credit) 
- [ ] Get all transactions
- [ ] Get transactions with filters
- [ ] Get transaction detail
- [ ] Update transaction
- [ ] Delete transaction
- [ ] Get statistics
- [ ] Generate report
- [ ] Test error scenarios
- [ ] Test pagination

## 🚀 Quick Start

1. **Login:** `POST /api/auth/login` dengan `admin@example.com` / `password`
2. **Copy token** dari response
3. **Create transaction:** Gunakan account_id 4 (Kas Kecil) untuk debit
4. **Verify:** `GET /api/transactions` untuk melihat hasil

**Selamat testing!** 🎉
