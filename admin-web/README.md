# Admin Web - Laravel Jetstream API Project

Laravel Jetstream admin panel with REST API for Flutter mobile app integration.

## Installation

### Prerequisites
- PHP 8.2 or higher
- Composer
- MySQL 8.0 or higher (XAMPP)
- Node.js and NPM

### Setup Steps

1. Install Composer dependencies:
```bash
composer install
```

2. Copy environment file:
```bash
copy .env.example .env
```

3. Generate application key:
```bash
php artisan key:generate
```

4. Create MySQL database `myapp_db` in phpMyAdmin or MySQL:
```sql
CREATE DATABASE myapp_db;
```

5. Run migrations:
```bash
php artisan migrate
```

6. Seed database with admin user and sample data:
```bash
php artisan db:seed
```

7. Install NPM dependencies:
```bash
npm install
```

8. Build assets:
```bash
npm run build
```

9. Create storage link:
```bash
php artisan storage:link
```

10. Start development server:
```bash
php artisan serve
```

## Default Credentials

- **Email:** admin@simtek.com
- **Password:** admin123

## API Endpoints

### Public Endpoints
- `GET /api/health` - Health check
- `POST /api/register` - User registration
- `POST /api/login` - User login

### Protected Endpoints (Require Authentication)
- `GET /api/user` - Get authenticated user
- `POST /api/logout` - Logout user
- `GET /api/products` - Get all products
- `GET /api/products/{id}` - Get single product

## API Testing

Import `postman_collection.json` into Postman to test all API endpoints.

## Flutter/Android Emulator

For Android emulator testing, use base URL: `http://10.0.2.2:8000`

## Database Backup

Run the backup script:
```bash
bash backup.sh
```

## Deployment

1. Set environment to production in `.env`:
```
APP_ENV=production
APP_DEBUG=false
```

2. Optimize application:
```bash
php artisan optimize
```

3. Clear caches:
```bash
php artisan optimize:clear
```

## Security Features

- Bcrypt password hashing
- Rate limiting (5 attempts per minute on login/register)
- CSRF protection
- XSS protection
- Sanctum token authentication
- Input validation and sanitization

## Testing

Run PHPUnit tests:
```bash
php artisan test
```

## License

MIT License

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
