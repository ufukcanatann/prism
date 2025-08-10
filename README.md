# PRISM Framework

**Modern PHP Framework for Enterprise Applications**

PRISM Framework is a powerful, secure, and developer-friendly PHP framework designed for building modern web applications and APIs. Built with enterprise-grade features and best practices in mind.

## Key Features

- ** MVC Architecture**: Clean, organized Model-View-Controller structure
- ** Blade Template Engine**: Powerful and intuitive templating system
- ** Built-in Security**: CSRF protection, XSS prevention, input validation
- ** High Performance**: Optimized routing, caching, and database operations
- ** Dependency Injection**: Modern service container and dependency injection
- ** Powerful CLI Tools**: Code generators and management commands
- ** Database Features**: Migrations, seeders, factories, and query builder
- ** RESTful APIs**: Built-in API support with JSON responses
- ** Middleware System**: Flexible request/response processing
- ** Modern UI**: Bootstrap 5 integration for responsive design

##  Requirements

- PHP 8.0 or higher
- MySQL 5.7+ / PostgreSQL 10+ / SQLite 3
- Composer
- Web server (Apache/Nginx)

##  Quick Start

### 🚀 Composer ile Kurulum (Önerilen)

```bash
# Yeni proje oluştur
composer create-project ufukcanatann/prism my-project

# Belirli versiyon ile kurulum
composer create-project ufukcanatann/prism my-project "1.0.*"

# Dist paketi tercih et
composer create-project ufukcanatann/prism my-project --prefer-dist

# Proje dizinine git
cd my-project

# Sunucuyu başlat
php prism system:serve
```

### 📋 Manuel Kurulum

#### 1. Proje İndir
```bash
git clone <repository-url> my-app
cd my-app
```

#### 2. Bağımlılıkları Yükle
```bash
composer install
```

#### 3. Environment Setup
```bash
cp .env.example .env
```

#### 4. Environment Konfigürasyonu
`.env` dosyasını düzenle:
```env
APP_NAME="My Application"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=my_app
DB_USERNAME=root
DB_PASSWORD=
```

#### 5. Framework Kurulumu
```bash
php prism system:install
```

#### 6. Geliştirme Sunucusunu Başlat
```bash
php prism system:serve
```

`http://127.0.0.1:8000` adresine giderek uygulamanızı görün! 🎉

##  Project Structure

```
prism-framework/
├── app/
│   ├── Http/Controllers/    # Controller classes
│   |   ├── ApiController.php
│   |   └── Controller.php
│   └── Models/              # Model classes
│       └── Model.php
|
├── config/                  # Configuration files
│   ├── app.php
│   ├── database.php
│   ├── rate_limit.php
│   ├── security.php
│   ├── mail.php
│   └── cache.php
|
├── core/                    # Framework core classes
│   ├── Application.php
│   ├── Config.php
│   ├── Database.php
│   ├── Session.php
│   ├── helpers.php
│   ├── install.php
│   ├── Auth/
│   |   └── Auth.php
|   |
│   ├── Console/
│   |   ├── Commands/
│   |   |   ├── ClearCacheCommand.php
│   |   |   ├── Command.php
│   |   |   ├── DatabaseCommand.php
│   |   |   ├── GeneratorCommands.php
│   |   |   ├── MakeControllerCommand.php
│   |   |   ├── README.md
│   |   |   ├── ServeCommand.php
│   |   |   └── SystemCommands.php
│   |   └── Console.php
|   |
│   ├── Container/
│   |   ├── Exception/
│   |   |   ├── ContainerException.php
│   |   |   └── NotFoundException.php
│   |   | 
│   |   ├── Interfaces/
│   |   |   ├── ContainerInterface.php
│   |   |   └── ServiceProviderInterface.php
│   |   └── Container.php
|   |
│   ├── Contracts/
|   |
│   ├── Database/
│   |   ├── Relations/
│   |   |   ├── BelongsTo.php
│   |   |   ├── BelongsToMany.php
│   |   |   ├── HasMany.php
│   |   |   ├── HasOne.php
│   |   |   └── Relation.php
|   |   |
│   |   ├── Schema/
│   |   |   ├── Blueprint.php
│   |   |   └── Schema.php
|   |   |
│   |   ├── Expression.php
│   |   ├── Factory.php
│   |   ├── Migration.php
│   |   ├── Model.php
│   |   ├── QueryBuilder.php
│   |   └── Seeder.php
│   |   
│   ├── Events/
│   |   ├── Interfaces/
│   |   |   ├── EventDispatcherInterface.php
│   |   |   ├── EventInterface.php
│   |   |   └── EventSubscriberInterface.php
│   |   ├── ApplicationTerminated.php
│   |   ├── EventDispatcher.php
│   |   ├── RequestReceived.php
│   |   └── ResponseSent.php
|   |
│   ├── Exceptions/
│   |   ├── Interfaces/
│   |   |   ├── ExceptionHandlerInterface.php
│   |   |   ├── RenderableExceptionInterface.php
│   |   |   └── ReportableExceptionInterface.php
│   |   └── Handler.php
|   |
│   ├── Helpers/
│   |   ├── AppHelpers.php
│   |   ├── ArrayHelpers.php
│   |   ├── CacheHelpers.php
│   |   ├── DatabaseHelpers.php
│   |   ├── FactoryHelpers.php
│   |   ├── FileHelpers.php
│   |   ├── SecurityHelpers.php
│   |   ├── StringHelpers.php
│   |   └── ValidationHelpers.php
|   |
│   ├── Http/
│   |   └── Request.php
|   |
│   ├── Interfaces/
|   |
│   ├── Middleware/
│   |   ├── AuthMiddleware.php
│   |   ├── CorsMiddleware.php
│   |   ├── CsrfMiddleware.php
│   |   ├── MiddlewareInterface.php
│   |   ├── RateLimitMiddleware.php
│   |   ├── ResponseMiddleware.php
│   |   ├── SecurityHeadersMiddleware.php
│   |   └── SessionMiddleware.php
|   |
│   ├── Providers/
│   |   ├── CacheServiceProvider.php
│   |   ├── ConfigServiceProvider.php
│   |   ├── DatabaseServiceProvider.php
│   |   ├── EventServiceProvider.php
│   |   ├── LogServiceProvider.php
│   |   ├── RouteServiceProvider.php
│   |   ├── ServiceProvider.php
│   |   ├── SessionServiceProvider.php
│   |   └── ViewServiceProvider.php
|   |
│   ├── Routing/
│   |   ├── Interfaces/
│   |   |   ├── RouteInterface.php
│   |   |   └── RouterInterface.php
│   |   └── AdvancedRouter.php
|   |
│   ├── Security/
│   |   ├── CsrfProtection.php
│   |   ├── InputValidator.php
│   |   └── XssProtection.php
|   |
│   ├── Services/
|   |
│   ├── Support/
│   |   └── Collection.php
|   |
│   ├── Traits/
|   |
│   └── View/
│       ├── Directives/
│       |   └── BladeDirectives.php
│       |   
│       ├── Helpers/
│       |   └── BladeHelpers.php
│       |   
│       ├── Interfaces/
│       |   ├── ViewInterface.php
│       |   └── ViewEngineInterface.php
│       |   
│       ├── AdvancedView.php
│       ├── CustomBladeEngine.php
│       └── SimplePhpEngine.php
|
├── database/
│   ├── migrations/                 # Database migrations  
│   |   └── 2024_01_01_000001_create_migrations_table.php
│   ├── seeders/  
│   |   └── DatabaseSeeder.php      # Database seeders
│   └── factories/                  # Model factories
|
├── public/                         # Web accessible files
│   ├── css/
│   ├── images/
│   ├── js/
│   ├── uploads/
│   ├── .htaccess
│   ├── favicon.png
│   ├── index.php              # Main entry file
│   └── router.php
|
├── resources/
│   └── views/               # Blade template files
│       ├── components/
│       |   └── alert.blade.php
│       |   
│       ├── layouts/
│       |   └── app.blade.php
│       |   
│       └── welcome.blade.php
|
├── routes/                  # Route definitions
│   ├── web.php
│   └── api.php
|
├── storage/                 # Cache, logs and upload files
├── tests/                   # Test files
├── vendor/                  # Composer dependencies
├── .env                     # Environment variables
├── .env.example
├── .htaccess
├── composer.json
├── composer.lock
├── prism
└── README.md
```

##  Usage Examples

### Generate Your First Components
```bash
# Create a new controller
php prism make controller UserController --resource

# Create a model with migration
php prism make model User --migration

# Create a complete CRUD setup
php prism make model Post --all
```

### Define Routes
```php
// routes/web.php
$router->get('/users', [UserController::class, 'index']);
$router->post('/users', [UserController::class, 'store']);
$router->get('/users/{id}', [UserController::class, 'show']);

// Route groups with middleware
$router->group(['middleware' => 'auth'], function($router) {
    $router->get('/dashboard', [DashboardController::class, 'index']);
});
```

### Create Controllers
```php
// app/Http/Controllers/UserController.php
namespace App\Http\Controllers;

class UserController extends Controller
{
    public function index()
    {
        return view('users.index', [
            'users' => User::all()
        ]);
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users'
        ]);
        
        User::create($validated);
        
        return redirect('/users')->with('success', 'User created!');
    }
}
```

### Build Views with Blade
```php
// resources/views/users/index.blade.php
@extends('layouts.app')

@section('title', 'Users')

@section('content')
<div class="container">
    <h1>Users</h1>
    
    @foreach($users as $user)
        <div class="card mb-3">
            <div class="card-body">
                <h5>{{ $user->name }}</h5>
                <p>{{ $user->email }}</p>
            </div>
        </div>
    @endforeach
</div>
@endsection
```

##  Configuration

### Environment Variables
Configure the following settings in `.env` file:

```env
APP_NAME=PRISM Framework
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_KEY=your-32-character-key

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=prism
DB_USERNAME=root
DB_PASSWORD=

CACHE_DRIVER=file
SESSION_DRIVER=file
MAIL_MAILER=smtp
```

### Middleware Configuration
```php
// config/app.php
'middleware' => [
    'before' => [
        \Core\Middleware\CorsMiddleware::class,
        \Core\Middleware\SessionMiddleware::class,
    ],
    'after' => [
        \Core\Middleware\ResponseMiddleware::class,
    ]
]
```

##  Security

- **CSRF Protection**: CSRF token validation for all forms
- **Encryption**: Secure password hashing with bcrypt
- **Session Security**: Secure session management
- **SQL Injection Protection**: Prepared statements usage
- **XSS Protection**: Output escaping
- **Audit Logging**: Comprehensive user activity tracking

## 📊 Database Schema

The system includes the following core tables:

- `users` - User information
- `migrations` - Migration tracking
- `password_resets` - Password reset tokens
- `failed_jobs` - Failed job queue

##  API Usage

### RESTful Endpoints
```php
// User operations
GET    /api/users              # User list
POST   /api/users              # Create new user
GET    /api/users/{id}         # User details
PUT    /api/users/{id}         # Update user
DELETE /api/users/{id}         # Delete user
```

##  Frontend

- **Bootstrap 5**: Modern and responsive design
- **Font Awesome**: Icon library
- **Chart.js**: Charts and statistics
- **jQuery**: JavaScript library

##  CLI Commands

PRISM Framework comes with powerful CLI tools to speed up development:

### Code Generators
```bash
# Generate controllers
php prism make controller UserController
php prism make controller ApiController --api

# Generate models with related files
php prism make model User --migration --factory --seeder
php prism make model Post --all

# Generate database files
php prism make migration create_users_table --create=users
php prism make seeder UserSeeder
php prism make factory UserFactory

# Generate other components
php prism make middleware AuthMiddleware
php prism make request StoreUserRequest
```

### Database Operations
```bash
# Run migrations
php prism db migrate

# Seed database
php prism db seed

# Migration + seeding
php prism db migrate --seed
```

### System Management
```bash
# Start development server
php prism system serve --port=8080

# Application maintenance
php prism system down
php prism system up

# Performance & security
php prism system optimize
php prism system security:scan
php prism system clear:cache

# Environment management
php prism system env list
php prism system env set APP_DEBUG false

# Debugging & information
php prism system route:list
php prism system inspect database
```

## 🧪 Testing

```bash
# Run tests
composer test

# Run tests with coverage
composer test-coverage
```

##  Contributing

1. Fork the project
2. Create feature branch (`git checkout -b feature/amazing-feature`)
3. Commit changes (`git commit -m 'Add amazing feature'`)
4. Push to branch (`git push origin feature/amazing-feature`)
5. Create Pull Request

##  License

This project is licensed under the MIT License.

##  Support

For any issues or suggestions:
- Open an issue
- Email: support@prism-framework.com

##  Updates

### v3.0.0
- Initial release
- Basic MVC structure
- User management
- Database migrations
- API support
- Modern UI

---

**PRISM Framework** - Modern PHP Framework for Enterprise Applications
