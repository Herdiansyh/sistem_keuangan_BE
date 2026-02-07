# Docker Setup Instructions

## 🚀 Quick Setup

### 1. Prerequisites
```bash
# Install Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sh get-docker.sh

# Install Docker Compose
sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose
```

### 2. Build & Run
```bash
# Clone repository
git clone <repository-url>
cd sistem_keuangan_BE

# Copy environment
cp .env.example .env

# Build and start containers
docker-compose up --build -d

# View logs
docker-compose logs -f
```

### 3. Database Setup
```bash
# Run migrations
docker-compose exec app php artisan migrate

# Seed initial data
docker-compose exec app php artisan db:seed

# Access database via PHPMyAdmin
# URL: http://localhost:8080
# Username: root
# Password: rootpassword
```

### 4. Access Services
- **Backend API**: http://localhost:8000
- **Frontend SPA**: http://localhost:3000
- **Database Admin**: http://localhost:8080
- **Redis**: localhost:6379

## 🛠️ Development Commands

### Inside Laravel Container
```bash
# Access container shell
docker-compose exec app bash

# Artisan commands
docker-compose exec app php artisan migrate
docker-compose exec app php artisan db:seed
docker-compose exec app php artisan tinker
docker-compose exec app php artisan serve

# Composer commands
docker-compose exec app composer install
docker-compose exec app composer require package-name
```

### Inside Frontend Container
```bash
# Access container shell
docker-compose exec frontend sh

# NPM commands
docker-compose exec frontend npm install
docker-compose exec frontend npm start
docker-compose exec frontend npm run build
docker-compose exec frontend npm test
```

### Database Operations
```bash
# Reset database
docker-compose exec app php artisan migrate:fresh --seed

# Create new migration
docker-compose exec app php artisan make:migration create_transactions_table

# View database logs
docker-compose logs db
```

## 🔧 Troubleshooting

### Common Issues

#### Port Already in Use
```bash
# Check what's using port 8000
netstat -tulpn | grep :8000

# Stop conflicting service
docker-compose down
docker-compose up -d
```

#### Permission Issues
```bash
# Fix storage permissions
docker-compose exec app chown -R www-data:www-data storage bootstrap/cache
docker-compose exec app chmod -R 775 storage bootstrap/cache
```

#### Database Connection
```bash
# Restart database container
docker-compose restart db

# Check database logs
docker-compose logs db
```

#### Build Issues
```bash
# Rebuild without cache
docker-compose build --no-cache

# Remove all containers and volumes
docker-compose down -v
docker-compose up --build -d
```

## 📊 Container Status

```bash
# View all containers
docker-compose ps

# View resource usage
docker stats

# Stop all services
docker-compose down

# Stop and remove volumes
docker-compose down -v
```

## 🔄 Environment Variables

### Development (.env)
```env
APP_ENV=local
APP_DEBUG=true
DB_CONNECTION=mysql
DB_HOST=db
DB_DATABASE=sistem_keuangan
DB_USERNAME=root
DB_PASSWORD=rootpassword
CACHE_DRIVER=redis
REDIS_HOST=redis
```

### Production
```env
APP_ENV=production
APP_DEBUG=false
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

## 🐳 Docker Compose Services

| Service | Image | Port | Description |
|---------|-------|-------|-------------|
| app | sistem-keuangan-backend | 9000 | Laravel PHP-FPM |
| nginx | nginx:alpine | 8000 | Web Server |
| db | mysql:8.0 | 3306 | Database |
| redis | redis:7-alpine | 6379 | Cache |
| frontend | node:18-alpine | 3000 | React SPA |
| phpmyadmin | phpmyadmin/phpmyadmin | 8080 | DB Admin |

## 📁 Volume Mapping

| Host Path | Container Path | Purpose |
|------------|------------------|---------|
| ./ | /var/www/html | Laravel code |
| ./storage/app/public | /var/www/html/storage/app/public | Public files |
| dbdata | /var/lib/mysql | Database data |
| redisdata | /data | Redis data |

## 🔒 Security Notes

- Change default passwords in production
- Use environment variables for sensitive data
- Enable HTTPS in production
- Regular security updates
- Monitor container logs

## 📈 Performance Tips

- Use Redis for caching in production
- Enable PHP OPcache
- Use nginx for static file serving
- Monitor resource usage
- Regular database optimization
