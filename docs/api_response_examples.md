# API Response Examples - After Parameter-Driven Implementation

## GET /api/accounts (Flat List - Default)

### Basic Request
```
GET /api/accounts
```

### Response
```json
{
  "success": true,
  "message": "Accounts retrieved successfully",
  "data": {
    "accounts": [
      {
        "id": 2,
        "code": "100001",
        "name": "Kas Kecil",
        "full_name": "100001 - Kas Kecil",
        "type": "asset",
        "type_label": "Asset",
        "is_active": true,
        "parent_id": 1,
        "opening_balance": 500000.00,
        "description": "Kas kecil operasional",
        "can_be_used_in_transactions": true,
        "is_leaf_account": true,
        "is_parent_account": false,
        "created_at": "2026-02-07T12:01:00.000000Z",
        "updated_at": "2026-02-07T12:01:00.000000Z",
        "parent": null,
        "children": null,
        "children_count": 0
      }
    ],
    "pagination": {
      "current_page": 1,
      "last_page": 1,
      "per_page": 15,
      "total": 1
    }
  }
}
```

## GET /api/accounts?include=parent (Flat List - With Parent)

### Request
```
GET /api/accounts?include=parent
```

### Response
```json
{
  "success": true,
  "message": "Accounts retrieved successfully",
  "data": {
    "accounts": [
      {
        "id": 2,
        "code": "100001",
        "name": "Kas Kecil",
        "full_name": "100001 - Kas Kecil",
        "type": "asset",
        "type_label": "Asset",
        "is_active": true,
        "parent_id": 1,
        "opening_balance": 500000.00,
        "description": "Kas kecil operasional",
        "can_be_used_in_transactions": true,
        "is_leaf_account": true,
        "is_parent_account": false,
        "created_at": "2026-02-07T12:01:00.000000Z",
        "updated_at": "2026-02-07T12:01:00.000000Z",
        "parent": {
          "id": 1,
          "code": "1000",
          "name": "Kas",
          "full_name": "1000 - Kas",
          "type": "asset",
          "type_label": "Asset",
          "is_active": true,
          "parent_id": null,
          "can_be_used_in_transactions": false,
          "is_leaf_account": false,
          "is_parent_account": true
        },
        "children": null,
        "children_count": 0
      }
    ],
    "pagination": {
      "current_page": 1,
      "last_page": 1,
      "per_page": 15,
      "total": 1
    }
  }
}
```

### With Filters + Include
```
GET /api/accounts?parent_id=null&is_active=true&type=asset&include=parent
```

### Response
```json
{
  "success": true,
  "message": "Accounts retrieved successfully",
  "data": {
    "accounts": [
      {
        "id": 1,
        "code": "1000",
        "name": "Kas",
        "parent_id": null,
        "parent": null,
        "children": null,
        "children_count": 2,
        "can_be_used_in_transactions": false,
        "is_leaf_account": false,
        "is_parent_account": true
      }
    ],
    "pagination": {
      "current_page": 1,
      "last_page": 1,
      "per_page": 15,
      "total": 1
    }
  }
}
```

## GET /api/accounts/tree (Unchanged)

### Request
```
GET /api/accounts/tree
```

### Response
```json
{
  "success": true,
  "message": "Account tree retrieved successfully",
  "data": [
    {
      "id": 1,
      "code": "1000",
      "name": "Kas",
      "full_name": "1000 - Kas",
      "type": "asset",
      "type_label": "Asset",
      "is_active": true,
      "parent_id": null,
      "opening_balance": 1000000.00,
      "description": "Akun kas utama",
      "can_be_used_in_transactions": false,
      "is_leaf_account": false,
      "is_parent_account": true,
      "level": 0,
      "created_at": "2026-02-07T12:00:00.000000Z",
      "updated_at": "2026-02-07T12:00:00.000000Z",
      "children": [
        {
          "id": 2,
          "code": "100001",
          "name": "Kas Kecil",
          "full_name": "100001 - Kas Kecil",
          "type": "asset",
          "type_label": "Asset",
          "is_active": true,
          "parent_id": 1,
          "opening_balance": 500000.00,
          "description": "Kas kecil operasional",
          "can_be_used_in_transactions": true,
          "is_leaf_account": true,
          "is_parent_account": false,
          "level": 1,
          "created_at": "2026-02-07T12:01:00.000000Z",
          "updated_at": "2026-02-07T12:01:00.000000Z",
          "children": [],
          "children_count": 0,
          "has_children": false
        }
      ],
      "children_count": 2,
      "has_children": true
    }
  ]
}
```

## GET /api/accounts/{id} (Unchanged)

### Request
```
GET /api/accounts/1
```

### Response
```json
{
  "success": true,
  "message": "Account retrieved successfully",
  "data": {
    "id": 1,
    "code": "1000",
    "name": "Kas",
    "full_name": "1000 - Kas",
    "type": "asset",
    "type_label": "Asset",
    "is_active": true,
    "parent_id": null,
    "opening_balance": 1000000.00,
    "description": "Akun kas utama",
    "can_be_used_in_transactions": false,
    "is_leaf_account": false,
    "is_parent_account": true,
    "created_at": "2026-02-07T12:00:00.000000Z",
    "updated_at": "2026-02-07T12:00:00.000000Z",
    "parent": null,
    "children": [
      {
        "id": 2,
        "code": "100001",
        "name": "Kas Kecil",
        "full_name": "100001 - Kas Kecil",
        "type": "asset",
        "type_label": "Asset",
        "is_active": true,
        "parent_id": 1,
        "opening_balance": 500000.00,
        "description": "Kas kecil operasional",
        "can_be_used_in_transactions": true,
        "is_leaf_account": true,
        "is_parent_account": false,
        "created_at": "2026-02-07T12:01:00.000000Z",
        "updated_at": "2026-02-07T12:01:00.000000Z",
        "parent": {
          "id": 1,
          "code": "1000",
          "name": "Kas",
          "full_name": "1000 - Kas"
        },
        "children": null,
        "children_count": 0
      }
    ]
  }
}
```

## Key Differences After Parameter-Driven Implementation

### GET /api/accounts (Default)
- ✅ `parent: null` (no parent object)
- ✅ Minimal response size
- ✅ Optimized for performance

### GET /api/accounts?include=parent
- ✅ `parent: {...}` (parent object included)
- ✅ Additional data when needed
- ✅ Flexible for frontend requirements

### GET /api/accounts/tree
- ✅ Unchanged (always includes children)
- ✅ Full hierarchical structure

### GET /api/accounts/{id}
- ✅ Unchanged (always includes relationships)
- ✅ Complete account detail

## Frontend Usage Examples

### Simple Dropdown (Performance Optimized)
```javascript
const accounts = await api.get('/api/accounts');
// Returns flat list without parent objects
```

### Dropdown with Parent Display
```javascript
const accountsWithParent = await api.get('/api/accounts?include=parent');
// Returns flat list with parent objects
```

### Table with Parent Column
```javascript
const accounts = await api.get('/api/accounts?include=parent');
const tableData = accounts.map(acc => ({
  code: acc.code,
  name: acc.name,
  parentName: acc.parent?.name || '-'
}));
```

### Tree View
```javascript
const treeData = await api.get('/api/accounts/tree');
// Returns full hierarchical structure
```

### Multiple Includes (Future Extensibility)
```javascript
const accountsWithMultiple = await api.get('/api/accounts?include=parent,children');
// Can be extended for multiple includes
```
