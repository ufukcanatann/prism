@extends('layouts.app')

@section('title', 'PRISM - Modern PHP Framework')

@section('content')
    <!-- Hero Section -->
    <div class="hero-gradient">
        <div class="container">
            <div class="hero-content">
                <!-- Framework Logo & Title -->
                <div class="prism-logo">
                    <img src="{{ asset('images/logo/logo_native_white.png') }}" alt="PRISM Logo" class="logo-image">
                </div>

                <h1 class="hero-title">Create Layered Systems</h1>
                <p class="hero-subtitle">
                    Modern PHP framework built for developers who value elegant code,
                    powerful features, and exceptional performance.
                </p>

                <!-- Version Badge -->
                <div class="version-badge">
                    <span class="badge">v3.0.0</span>
                    <span class="php-version">PHP 8.0+</span>
                </div>
            </div>

            <!-- Quick Start Code Preview -->
            <div class="code-preview-section">
                <div class="code-preview">
                    <div class="code-header">
                        <div class="code-dots">
                            <span class="dot red"></span>
                            <span class="dot yellow"></span>
                            <span class="dot green"></span>
                        </div>
                        <span class="code-title">Quick Start</span>
                    </div>
                    <div class="code-content">
                        <div class="code-line">
                            <span class="line-number">1</span>
                            <span class="line-content"><span class="comment"># Install PRISM Framework</span></span>
                        </div>
                        <div class="code-line">
                            <span class="line-number">2</span>
                            <span class="line-content"><span class="command">composer create-project prism/framework
                                    my-app</span></span>
                        </div>
                        <div class="code-line">
                            <span class="line-number">3</span>
                            <span class="line-content"></span>
                        </div>
                        <div class="code-line">
                            <span class="line-number">4</span>
                            <span class="line-content"><span class="comment"># Generate your first controller</span></span>
                        </div>
                        <div class="code-line">
                            <span class="line-number">5</span>
                            <span class="line-content"><span class="command">php prism make controller UserController
                                    --resource</span></span>
                        </div>
                        <div class="code-line">
                            <span class="line-number">6</span>
                            <span class="line-content"></span>
                        </div>
                        <div class="code-line">
                            <span class="line-number">7</span>
                            <span class="line-content"><span class="comment"># Start development server</span></span>
                        </div>
                        <div class="code-line">
                            <span class="line-number">8</span>
                            <span class="line-content"><span class="command">php prism system:serve</span></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Community Documents Section -->
    <section class="docs-section">
        <div class="container">
            <div class="section-header">
                <h2>Community Documents</h2>
                <p>Discover all the features of PRISM and start developing</p>
            </div>

            <div class="docs-preview">
                <div class="docs-nav">
                    <h3>Documentation</h3>
                    <ul class="docs-menu">
                        <li><a href="#" class="tab-link active" data-tab="getting-started"><i class="fas fa-rocket"></i>
                                Getting Started</a></li>
                        <li><a href="#" class="tab-link" data-tab="configuration"><i class="fas fa-cog"></i>
                                Configuration</a></li>
                        <li><a href="#" class="tab-link" data-tab="routing"><i class="fas fa-route"></i> Routing</a></li>
                        <li><a href="#" class="tab-link" data-tab="views"><i class="fas fa-eye"></i> Views & Blade</a></li>
                        <li><a href="#" class="tab-link" data-tab="database"><i class="fas fa-database"></i> Database &
                                ORM</a></li>
                        <li><a href="#" class="tab-link" data-tab="security"><i class="fas fa-shield-alt"></i> Security</a>
                        </li>
                        <li><a href="#" class="tab-link" data-tab="cli"><i class="fas fa-terminal"></i> CLI Tools</a></li>
                        <li><a href="#" class="tab-link" data-tab="middleware"><i class="fas fa-cogs"></i> Middleware</a>
                        </li>
                        <li><a href="#" class="tab-link" data-tab="auth"><i class="fas fa-users"></i> Authentication</a>
                        </li>
                        <li><a href="#" class="tab-link" data-tab="performance"><i class="fas fa-tachometer-alt"></i>
                                Performance</a></li>
                    </ul>
                </div>

                <div class="docs-content">
                    <div class="docs-tabs">
                        <!-- Getting Started Tab -->
                        <div class="tab-content active" id="getting-started">
                            <h4>Getting Started Guide</h4>
                            <p>Start developing modern PHP applications with the PRISM Framework. Discover our framework, which combines the elegance and performance of Laravel and offers enterprise-level features.</p>

                            <div class="docs-highlight">
                                <h5>Quick Start</h5>
                                <ul>
                                    <li><strong>Installation:</strong> Installation with a single command using Composer</li>
                                    <li><strong>CLI Tools:</strong> Powerful code generators and scaffolding system</li>
                                    <li><strong>Blade Engine:</strong> Advanced templating with 40+ custom directives</li>
                                    <li><strong>Security:</strong> CSRF, XSS, Rate Limiting, and security topics</li>
                                </ul>
                            </div>

                            <div class="code-example">
                                <h6>Create Your First Controller</h6>
                                <pre><code>php prism make controller UserController --resource
        php prism make model User
        php prism make migration create_users_table</code></pre>
                            </div>
                        </div>

                        <!-- Configuration Tab -->
                        <div class="tab-content" id="configuration">
                            <h4>Configuration Guide</h4>
                            <p>Learn all the settings and configuration options of the PRISM Framework. Environment variables, database settings, and framework configuration.</p>

                            <div class="docs-highlight">
                                <h5>Basic Configuration</h5>
                                <ul>
                                    <li><strong>Environment:</strong> Environment variables with .env file</li>
                                    <li><strong>Database:</strong> MySQL, PostgreSQL, SQLite support</li>
                                    <li><strong>Cache:</strong> Redis, Memcached, File cache options</li>
                                    <li><strong>Session:</strong> Secure session management</li>
                                </ul>
                            </div>

                            <div class="code-example">
                                <h6>Database Configuration</h6>
                                <pre><code>DB_CONNECTION=mysql
        DB_HOST=127.0.0.1
        DB_PORT=3306
        DB_DATABASE=prism_app
        DB_USERNAME=root
        DB_PASSWORD=</code></pre>
                            </div>
                        </div>

                        <!-- Routing Tab -->
                        <div class="tab-content" id="routing">
                            <h4>Routing System</h4>
                            <p>Define the URL structure of your application with the modern and flexible routing system.
                                RESTful routes, middleware support, and route groups.</p>

                            <div class="docs-highlight">
                                <h5>Route Definitions</h5>
                                <ul>
                                    <li><strong>Basic Routes:</strong> GET, POST, PUT, DELETE methods</li>
                                    <li><strong>Route Groups:</strong> Prefix, middleware and namespace groups</li>
                                    <li><strong>Route Parameters:</strong> Dynamic URL parameters</li>
                                    <li><strong>Route Model Binding:</strong> Automatic model binding</li>
                                </ul>
                            </div>

                            <div class="code-example">
                                <h6>Route Definition Examples</h6>
                                <pre><code>Route::get('/users', [UserController::class, 'index']);
        Route::post('/users', [UserController::class, 'store']);
        Route::get('/users/{user}', [UserController::class, 'show']);
        Route::group(['prefix' => 'admin', 'middleware' => 'auth'], function() {
            Route::resource('users', AdminUserController::class);
        });</code></pre>
                            </div>
                        </div>

                        <!-- Views & Blade Tab -->
                        <div class="tab-content" id="views">
                            <h4>Views & Blade Engine</h4>
                            <p>Create modern and responsive web interfaces with the powerful Blade templating engine.
                                Advanced template system with 40+ custom directives.</p>

                            <div class="docs-highlight">
                                <h5>Blade Features</h5>
                                <ul>
                                    <li><strong>Layouts:</strong> Master layout system</li>
                                    <li><strong>Components:</strong> Reusable components</li>
                                    <li><strong>Directives:</strong> Custom @directives</li>
                                    <li><strong>Stacks:</strong> Dynamic content management</li>
                                </ul>
                            </div>

                            <div class="code-example">
                                <h6>Blade Template Example</h6>
                                <pre><code>örnek</code></pre>
                            </div>
                        </div>

                        <!-- Database & ORM Tab -->
                        <div class="tab-content" id="database">
                            <h4>Database & ORM</h4>
                            <p>Simplify database operations with the powerful Eloquent ORM.
                                Migration system, seeders, and model relationships.</p>

                            <div class="docs-highlight">
                                <h5>Database Features</h5>
                                <ul>
                                    <li><strong>Eloquent ORM:</strong> Active Record pattern</li>
                                    <li><strong>Migrations:</strong> Version control friendly schema</li>
                                    <li><strong>Seeders:</strong> Test data creation</li>
                                    <li><strong>Relationships:</strong> Model relationships</li>
                                </ul>
                            </div>

                            <div class="code-example">
                                <h6>Model and Migration Example</h6>
                                <pre><code>// User Model
        class User extends Model
        {
            protected $fillable = ['name', 'email', 'password'];

            public function posts()
            {
                return $this->hasMany(Post::class);
            }
        }

        // Migration
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamps();
        });</code></pre>
                            </div>
                        </div>

                        <!-- Security Tab -->
                        <div class="tab-content" id="security">
                            <h4>Security Features</h4>
                            <p>Protect your application with enterprise-level security features.
                                CSRF protection, XSS prevention, and rate limiting.</p>

                            <div class="docs-highlight">
                                <h5>Security Features</h5>
                                <ul>
                                    <li><strong>CSRF Protection:</strong> Cross-site request forgery protection</li>
                                    <li><strong>XSS Prevention:</strong> Cross-site scripting prevention</li>
                                    <li><strong>Rate Limiting:</strong> API rate limiting</li>
                                    <li><strong>Input Validation:</strong> Secure input validation</li>
                                </ul>
                            </div>

                            <div class="code-example">
                                <h6>Security Middleware Example</h6>
                                <pre><code>// CSRF Token
        @csrf

        // Rate Limiting
        Route::middleware(['throttle:60,1'])->group(function () {
            Route::post('/api/contact', [ContactController::class, 'store']);
        });

        // Input Validation
        $request->validate([
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed'
        ]);</code></pre>
                            </div>
                        </div>

                        <!-- CLI Tools Tab -->
                        <div class="tab-content" id="cli">
                            <h4>CLI Tools</h4>
                            <p>Accelerate your development process with powerful command-line tools.
                                Artisan-like commands and scaffolding system.</p>

                            <div class="docs-highlight">
                                <h5>CLI Commands</h5>
                                <ul>
                                    <li><strong>Make Commands:</strong> Controller, Model, Migration creation</li>
                                    <li><strong>System Commands:</strong> Server start, cache clearing</li>
                                    <li><strong>Custom Commands:</strong> Custom CLI commands</li>
                                    <li><strong>Scaffolding:</strong> CRUD applications</li>
                                </ul>
                            </div>

                            <div class="code-example">
                                <h6>CLI Command Examples</h6>
                                <pre><code># Basic Commands
        php prism make controller UserController --resource
        php prism make model User -m
        php prism make migration create_posts_table

        # System Commands
        php prism system:serve
        php prism system:clear
        php prism system:migrate</code></pre>
                            </div>
                        </div>

                        <!-- Middleware Tab -->
                        <div class="tab-content" id="middleware">
                            <h4>Middleware System</h4>
                            <p>Filter and process HTTP requests with the middleware system.
                                Authentication, logging, caching, and custom middleware.</p>

                            <div class="docs-highlight">
                                <h5>Middleware Features</h5>
                                <ul>
                                    <li><strong>Global Middleware:</strong> Works on all requests</li>
                                    <li><strong>Route Middleware:</strong> Works on specific routes</li>
                                    <li><strong>Group Middleware:</strong> Works on route groups</li>
                                    <li><strong>Custom Middleware:</strong> Create custom middleware</li>
                                </ul>
                            </div>

                            <div class="code-example">
                                <h6>Middleware Usage Example</h6>
                                <pre><code>// Middleware usage
        class AuthMiddleware
        {
            public function handle($request, $next)
            {
                if (!auth()->check()) {
                    return redirect('/login');
                }
                return $next($request);
            }
        }

        // Route usage
        Route::middleware(['auth'])->group(function () {
            Route::get('/dashboard', [DashboardController::class, 'index']);
        });</code></pre>
                            </div>
                        </div>

                        <!-- Authentication Tab -->
                        <div class="tab-content" id="auth">
                            <h4>Authentication System</h4>
                            <p>Secure user authentication and authorization system.
                                Login, register, password reset, and social login.</p>

                            <div class="docs-highlight">
                                <h5>Authentication</h5>
                                <ul>
                                    <li><strong>User Registration:</strong> Secure registration system</li>
                                    <li><strong>Login System:</strong> Session-based authentication</li>
                                    <li><strong>Password Reset:</strong> Secure password reset</li>
                                    <li><strong>Social Login:</strong> OAuth integration</li>
                                </ul>
                            </div>

                            <div class="code-example">
                                <h6>Authentication Controller Example</h6>
                                <pre><code>class AuthController extends Controller
        {
            public function login(Request $request)
            {
                $credentials = $request->validate([
                    'email' => 'required|email',
                    'password' => 'required'
                ]);

                if (Auth::attempt($credentials)) {
                    return redirect()->intended('/dashboard');
                }

                return back()->withErrors(['email' => 'Invalid credentials']);
            }
        }</code></pre>
                            </div>
                        </div>

                        <!-- Performance Tab -->
                        <div class="tab-content" id="performance">
                            <h4>Performance Optimization</h4>
                            <p>Optimize your application's performance with caching, optimization
                                and monitoring tools. Database query optimization and asset minification.</p>

                            <div class="docs-highlight">
                                <h5>Performance Features</h5>
                                <ul>
                                    <li><strong>Caching:</strong> Redis, Memcached, File cache</li>
                                    <li><strong>Query Optimization:</strong> Database query caching</li>
                                    <li><strong>Asset Optimization:</strong> CSS/JS minification</li>
                                    <li><strong>Monitoring:</strong> Performance metrics</li>
                                </ul>
                            </div>

                            <div class="code-example">
                                <h6>Cache Usage Example</h6>
                                <pre><code>// Cache usage   
        $users = Cache::remember('users', 3600, function () {
            return User::with('posts')->get();
        });

        // Query optimization
        $users = User::select('id', 'name', 'email')
            ->with(['posts' => function($query) {
                $query->select('id', 'user_id', 'title');
            }])
            ->get();</code></pre>
                            </div>
                        </div>
                    </div>

                    <div class="docs-footer">
                        <div class="docs-actions">
                            <a href="#" class="btn btn-primary">
                                <i class="fas fa-book me-2"></i>Full Documentation
                            </a>
                            <a href="#" class="btn btn-outline-primary">
                                <i class="fas fa-github me-2"></i>GitHub Repository
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection