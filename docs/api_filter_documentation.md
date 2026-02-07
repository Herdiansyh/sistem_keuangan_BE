# Account Filter API Documentation

## Endpoint: GET /api/accounts

### Query Parameters

| Parameter | Type | Description | Example |
|-----------|------|-------------|---------|
| `type` | string | Filter by account type | `?type=asset` |
| `is_active` | boolean | Filter by active status | `?is_active=true` |
| `parent_id` | mixed | Filter by parent ID | `?parent_id=null` |
| `search` | string | Search by name or code | `?search=kas` |
| `per_page` | integer | Items per page | `?per_page=10` |

### Parent ID Filter Behavior

#### 1. Get Root Accounts Only
```
GET /api/accounts?parent_id=null
```
**Result**: Accounts with `parent_id IS NULL`

#### 2. Get Children of Specific Parent
```
GET /api/accounts?parent_id=5
```
**Result**: Accounts where `parent_id = 5`

#### 3. No Parent Filter
```
GET /api/accounts
```
**Result**: All accounts (no parent filtering)

### Combined Filters

#### Root + Type Filter
```
GET /api/accounts?parent_id=null&type=asset
```
**Result**: Root asset accounts only

#### Root + Search Filter
```
GET /api/accounts?parent_id=null&search=kas
```
**Result**: Root accounts matching "kas"

#### Parent + Active Filter
```
GET /api/accounts?parent_id=2&is_active=true
```
**Result**: Active children of account 2

### Edge Cases Handled

1. **String "null"**: Converted to SQL `NULL`
2. **Empty string**: Treated as null
3. **Invalid numeric**: Ignored (no filter applied)
4. **Negative numbers**: Ignored
5. **Zero**: Ignored

### Implementation Details

```php
private function applyParentFilter($query, $parentId): void
{
    if ($parentId === 'null' || $parentId === null || $parentId === '') {
        $query->whereNull('parent_id');
    } elseif (is_numeric($parentId) && $parentId > 0) {
        $query->where('parent_id', $parentId);
    }
    // Invalid values are ignored
}
```

### Testing Scenarios

✅ `GET /api/accounts?parent_id=null` → Root accounts only
✅ `GET /api/accounts?parent_id=2` → Children of account 2
✅ `GET /api/accounts` → All accounts
✅ `GET /api/accounts?parent_id=null&search=kas` → Root accounts matching "kas"
✅ `GET /api/accounts?parent_id=abc` → All accounts (invalid ignored)
✅ `GET /api/accounts?parent_id=0` → All accounts (zero ignored)
