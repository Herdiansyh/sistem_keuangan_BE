# Sistem Keuangan - Laravel REST API

Sistem keuangan berbasis web dengan arsitektur Backend Laravel API + Frontend React SPA untuk manajemen akun, transaksi, dan ringkasan keuangan real-time.

## 🏗️ Arsitektur Sistem

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Frontend    │    │    Backend     │    │    Database     │
│   React SPA    │◄──►│   Laravel API   │◄──►│    MySQL/PG     │
│   :3000        │    │    :8000        │    │    :3306        │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

### 📋 Fitur Utama

- **🏦 Manajemen Akun**: Hierarchical parent-child accounts
- **💰 Transaksi**: Single-entry system dengan validasi debit/kredit
- **📊 Ringkasan Keuangan**: Real-time balance calculation
- **🔐 Authentication**: Laravel Sanctum untuk API security
- **📱 Responsive**: API RESTful untuk multi-platform

## 🚀 Quick Start dengan Docker

### Prasyarat

- Docker & Docker Compose
- Git
- Terminal/Command Prompt

### 1. Clone Repository

```bash
git clone <repository-url>
cd sistem_keuangan_BE
```

### 2. Konfigurasi Environment

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 3. Build & Run Containers

```bash
# Build dan jalankan semua services
docker-compose up --build -d

# Lihat logs (opsional)
docker-compose logs -f
```

### 4. Setup Database

```bash
# Jalankan migration
docker-compose exec app php artisan migrate

# Seed data awal
docker-compose exec app php artisan db:seed
```

### 5. Akses Aplikasi

- **Backend API**: http://localhost:8000
- **Frontend SPA**: http://localhost:3000
- **Database Admin**: http://localhost:8080 (PHPMyAdmin)
- **Redis**: localhost:6379

## 📁 Struktur Project

```
sistem_keuangan_BE/
├── app/
│   ├── Http/
│   │   ├── Controllers/          # API Controllers
│   │   ├── Requests/            # Form Request Validation
│   │   └── Resources/           # API Resources
│   ├── Models/                  # Eloquent Models
│   ├── Services/                # Business Logic Layer
│   └── Providers/              # Service Providers
├── database/
│   ├── migrations/             # Database Schema
│   ├── seeders/               # Initial Data
│   └── factories/             # Model Factories
├── routes/
│   └── api.php                # API Routes
├── docker/                    # Docker Configuration
├── docs/                      # API Documentation
└── tests/                     # Unit & Feature Tests
```

## 🔌 API Endpoints

### Authentication

| Method | Endpoint | Description |
|---------|-----------|-------------|
| POST | `/api/auth/register` | User registration |
| POST | `/api/auth/login` | User login |
| POST | `/api/auth/logout` | User logout |
| GET | `/api/auth/profile` | User profile |

### Accounts

| Method | Endpoint | Description |
|---------|-----------|-------------|
| GET | `/api/accounts` | List all accounts |
| POST | `/api/accounts` | Create new account |
| GET | `/api/accounts/{id}` | Get account detail |
| PUT | `/api/accounts/{id}` | Update account |
| DELETE | `/api/accounts/{id}` | Delete account |
| GET | `/api/accounts/tree` | Hierarchical account tree |

### Transactions

| Method | Endpoint | Description |
|---------|-----------|-------------|
| GET | `/api/transactions` | List transactions |
| POST | `/api/transactions` | Create transaction |
| GET | `/api/transactions/{id}` | Get transaction detail |
| PUT | `/api/transactions/{id}` | Update transaction |
| DELETE | `/api/transactions/{id}` | Delete transaction |
| GET | `/api/transactions/statistics` | Transaction statistics |
| GET | `/api/transactions/report` | Transaction report |

### Account Summary

| Method | Endpoint | Description |
|---------|-----------|-------------|
| GET | `/api/account-summary` | Account balances |
| GET | `/api/account-summary/{id}` | Account detail with children |
| GET | `/api/account-summary/financial` | Financial summary |
| GET | `/api/account-summary/top` | Top accounts by balance |

## 📝 Contoh API Request & Response

### Login

```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@example.com",
    "password": "password"
  }'
```

**Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "token": "1|abc123...",
    "user": {
      "id": 1,
      "name": "Admin User",
      "email": "admin@example.com"
    }
  }
}
```

### Create Transaction

```bash
curl -X POST http://localhost:8000/api/transactions \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {token}" \
  -d '{
    "transaction_date": "2026-02-07",
    "description": "Pembelian ATK",
    "account_id": 4,
    "debit": 500000,
    "credit": 0
  }'
```

**Response:**
```json
{
  "success": true,
  "message": "Transaction created successfully",
  "data": {
    "id": 1,
    "transaction_date": "2026-02-07",
    "description": "Pembelian ATK",
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

### Account Summary

```bash
curl -X GET http://localhost:8000/api/account-summary \
  -H "Authorization: Bearer {token}"
```

**Response:**
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
        "type": "asset",
        "balance": 3000000,
        "total_balance": 3500000,
        "formatted_balance": "3.000.000,00",
        "has_children": true,
        "children": [
          {
            "id": 4,
            "code": "1001",
            "name": "Kas Kecil",
            "balance": 2000000,
            "children": []
          }
        ]
      }
    ],
    "summary": {
      "total_accounts": 10,
      "total_balance": 15000000,
      "formatted_total_balance": "15.000.000,00"
    }
  }
}
```

## 🎯 Catatan Teknis Penting

### 🏦 Parent-Child Akun

Sistem menggunakan hierarchical account structure:
- **Parent Accounts**: Akun induk yang bisa memiliki child accounts
- **Child Accounts**: Akun anak yang terhubung ke parent
- **Unlimited Depth**: Child bisa memiliki child lagi
- **Balance Aggregation**: Parent menampilkan total saldo termasuk children

```php
// Contoh structure
Kas (1000)
├── Kas Kecil (1001)
└── Kas Besar (1002)

Bank (1100)
├── Bank BCA (1101)
└── Bank Mandiri (1102)
```

### 💰 Single Entry Transaksi

Sistem menggunakan single-entry accounting:
- **Debit OR Credit**: Hanya salah satu yang diisi
- **Validation**: Tidak boleh keduanya terisi atau kosong
- **Amount Calculation**: `amount = debit - credit`
- **Account Validation**: Hanya akun aktif yang bisa digunakan

```php
// Valid transaction examples
{
  "debit": 500000,  // ✅ Valid
  "credit": 0
}

{
  "debit": 0,        // ✅ Valid
  "credit": 250000
}

{
  "debit": 500000,  // ❌ Invalid
  "credit": 100000
}
```

### 📊 Real-time Balance Calculation

Saldo dihitung real-time tanpa menyimpan di database:
- **No Redundancy**: Tidak ada field saldo di database
- **Always Up-to-date**: Selalu mengikuti transaksi terbaru
- **Performance**: Optimized dengan eager loading
- **Recursive**: Parent accounts meng-aggregate children

```php
// Balance calculation logic
$balance = $total_debit - $total_credit;
$total_balance = $balance + sum($children_balances);
```

## 🛠️ Development Commands

### Database

```bash
# Fresh migration
docker-compose exec app php artisan migrate:fresh

# Seed data
docker-compose exec app php artisan db:seed

# Create new migration
docker-compose exec app php artisan make:migration create_table_name

# Create new seeder
docker-compose exec app php artisan make:seeder SeederName
```

### Testing

```bash
# Run tests
docker-compose exec app php artisan test

# Run specific test
docker-compose exec app php artisan test --filter TransactionTest

# Generate test coverage
docker-compose exec app php artisan test --coverage
```

### Cache & Optimization

```bash
# Clear cache
docker-compose exec app php artisan cache:clear

# Clear config cache
docker-compose exec app php artisan config:clear

# Clear route cache
docker-compose exec app php artisan route:clear

# Optimize for production
docker-compose exec app php artisan optimize
```

## 🔧 Konfigurasi Environment

### Database Options

**MySQL (Default):**
```env
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=sistem_keuangan
DB_USERNAME=root
DB_PASSWORD=rootpassword
```

**PostgreSQL:**
```env
DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=sistem_keuangan
DB_USERNAME=postgres
DB_PASSWORD=postgrespassword
```

### Cache Options

**Redis (Recommended):**
```env
CACHE_DRIVER=redis
REDIS_HOST=redis
REDIS_PORT=6379
```

**File Cache:**
```env
CACHE_DRIVER=file
```

## 📚 Documentation

- [API Documentation](docs/api_response_examples.md)
- [Transaction API Examples](docs/transaction_api_examples.md)
- [Account Summary API Examples](docs/account_summary_api_examples.md)
- [Design Notes](docs/account_summary_design_notes.md)

## 🚀 Production Deployment

### Environment Setup

```bash
# Production environment
APP_ENV=production
APP_DEBUG=false

# Production cache
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Production database
DB_CONNECTION=mysql
DB_HOST=production-db-host
DB_DATABASE=sistem_keuangan_prod
```

### Build & Deploy

```bash
# Build production image
docker build -t sistem-keuangan:latest .

# Run with production environment
docker-compose -f docker-compose.prod.yml up -d
```

## 🤝 Contributing

1. Fork repository
2. Create feature branch (`git checkout -b feature/amazing-feature`)
3. Commit changes (`git commit -m 'Add amazing feature'`)
4. Push to branch (`git push origin feature/amazing-feature`)
5. Open Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🆘️ Support

Jika mengalami masalah:

1. Cek [Issues](../../issues) untuk solusi yang sudah ada
2. Buat issue baru dengan detail error
3. Sertakan logs dan environment setup
4. Berikan contoh request/response yang gagal

---

**🚀 Sistem Keuangan - Modern Financial Management System**

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
