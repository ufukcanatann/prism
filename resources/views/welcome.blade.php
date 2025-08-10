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
                            <span class="line-content"><span class="command">php prism system serve</span></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Topluluk Dökümanları Section -->
    <section class="docs-section">
        <div class="container">
            <div class="section-header">
                <h2>Topluluk Dökümanları</h2>
                <p>PRISM Framework'ün tüm özelliklerini keşfedin ve geliştirmeye başlayın</p>
            </div>

            <div class="docs-preview">
                <div class="docs-nav">
                    <h3>Dökümantasyon</h3>
                    <ul class="docs-menu">
                        <li><a href="#" class="tab-link active" data-tab="getting-started"><i class="fas fa-rocket"></i>
                                Başlangıç</a></li>
                        <li><a href="#" class="tab-link" data-tab="configuration"><i class="fas fa-cog"></i>
                                Konfigürasyon</a></li>
                        <li><a href="#" class="tab-link" data-tab="routing"><i class="fas fa-route"></i> Routing</a></li>
                        <li><a href="#" class="tab-link" data-tab="views"><i class="fas fa-eye"></i> Views & Blade</a></li>
                        <li><a href="#" class="tab-link" data-tab="database"><i class="fas fa-database"></i> Database &
                                ORM</a></li>
                        <li><a href="#" class="tab-link" data-tab="security"><i class="fas fa-shield-alt"></i> Güvenlik</a>
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
                        <!-- Başlangıç Tab -->
                        <div class="tab-content active" id="getting-started">
                            <h4>Başlangıç Rehberi</h4>
                            <p>PRISM Framework ile modern PHP uygulamaları geliştirmeye başlayın.
                                Laravel'in zarafetini ve performansını birleştiren, enterprise seviyesinde
                                özellikler sunan framework'ümüz ile tanışın.</p>

                            <div class="docs-highlight">
                                <h5>Hızlı Başlangıç</h5>
                                <ul>
                                    <li><strong>Kurulum:</strong> Composer ile tek komutla kurulum</li>
                                    <li><strong>CLI Tools:</strong> Güçlü kod üreticileri ve scaffolding sistemi</li>
                                    <li><strong>Blade Engine:</strong> 40+ custom directive ile gelişmiş templating</li>
                                    <li><strong>Security:</strong> CSRF, XSS, Rate Limiting ve güvenlik başlıkları</li>
                                </ul>
                            </div>

                            <div class="code-example">
                                <h6>İlk Controller'ınızı Oluşturun</h6>
                                <pre><code>php prism make controller UserController --resource
        php prism make model User
        php prism make migration create_users_table</code></pre>
                            </div>
                        </div>

                        <!-- Konfigürasyon Tab -->
                        <div class="tab-content" id="configuration">
                            <h4>Konfigürasyon Rehberi</h4>
                            <p>PRISM Framework'ün tüm ayarlarını ve konfigürasyon seçeneklerini öğrenin.
                                Environment variables, database ayarları ve framework konfigürasyonu.</p>

                            <div class="docs-highlight">
                                <h5>Temel Konfigürasyon</h5>
                                <ul>
                                    <li><strong>Environment:</strong> .env dosyası ile ortam ayarları</li>
                                    <li><strong>Database:</strong> MySQL, PostgreSQL, SQLite desteği</li>
                                    <li><strong>Cache:</strong> Redis, Memcached, File cache seçenekleri</li>
                                    <li><strong>Session:</strong> Güvenli session yönetimi</li>
                                </ul>
                            </div>

                            <div class="code-example">
                                <h6>Database Konfigürasyonu</h6>
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
                            <h4>Routing Sistemi</h4>
                            <p>Modern ve esnek routing sistemi ile uygulamanızın URL yapısını tanımlayın.
                                RESTful routes, middleware desteği ve route grupları.</p>

                            <div class="docs-highlight">
                                <h5>Route Tanımları</h5>
                                <ul>
                                    <li><strong>Basic Routes:</strong> GET, POST, PUT, DELETE metodları</li>
                                    <li><strong>Route Groups:</strong> Prefix, middleware ve namespace grupları</li>
                                    <li><strong>Route Parameters:</strong> Dinamik URL parametreleri</li>
                                    <li><strong>Route Model Binding:</strong> Otomatik model binding</li>
                                </ul>
                            </div>

                            <div class="code-example">
                                <h6>Route Tanımlama Örnekleri</h6>
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
                            <p>Güçlü Blade templating engine ile modern ve responsive web arayüzleri oluşturun.
                                40+ custom directive ile gelişmiş template sistemi.</p>

                            <div class="docs-highlight">
                                <h5>Blade Özellikleri</h5>
                                <ul>
                                    <li><strong>Layouts:</strong> Master layout sistemi</li>
                                    <li><strong>Components:</strong> Yeniden kullanılabilir bileşenler</li>
                                    <li><strong>Directives:</strong> Custom @directive'lar</li>
                                    <li><strong>Stacks:</strong> Dinamik içerik yönetimi</li>
                                </ul>
                            </div>

                            <div class="code-example">
                                <h6>Blade Template Örneği</h6>
                                <pre><code>örnek</code></pre>
                            </div>
                        </div>

                        <!-- Database & ORM Tab -->
                        <div class="tab-content" id="database">
                            <h4>Database & ORM</h4>
                            <p>Güçlü Eloquent ORM ile veritabanı işlemlerini kolaylaştırın.
                                Migration sistemi, seeder'lar ve model ilişkileri.</p>

                            <div class="docs-highlight">
                                <h5>Database Özellikleri</h5>
                                <ul>
                                    <li><strong>Eloquent ORM:</strong> Active Record pattern</li>
                                    <li><strong>Migrations:</strong> Version control friendly schema</li>
                                    <li><strong>Seeders:</strong> Test verisi oluşturma</li>
                                    <li><strong>Relationships:</strong> Model ilişkileri</li>
                                </ul>
                            </div>

                            <div class="code-example">
                                <h6>Model ve Migration Örneği</h6>
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

                        <!-- Güvenlik Tab -->
                        <div class="tab-content" id="security">
                            <h4>Güvenlik Özellikleri</h4>
                            <p>Enterprise seviyesinde güvenlik özellikleri ile uygulamanızı koruyun.
                                CSRF koruması, XSS önleme ve rate limiting.</p>

                            <div class="docs-highlight">
                                <h5>Güvenlik Özellikleri</h5>
                                <ul>
                                    <li><strong>CSRF Protection:</strong> Cross-site request forgery koruması</li>
                                    <li><strong>XSS Prevention:</strong> Cross-site scripting önleme</li>
                                    <li><strong>Rate Limiting:</strong> API rate limiting</li>
                                    <li><strong>Input Validation:</strong> Güvenli input doğrulama</li>
                                </ul>
                            </div>

                            <div class="code-example">
                                <h6>Güvenlik Middleware Örneği</h6>
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
                            <p>Güçlü command-line araçları ile geliştirme sürecinizi hızlandırın.
                                Artisan benzeri komutlar ve scaffolding sistemi.</p>

                            <div class="docs-highlight">
                                <h5>CLI Komutları</h5>
                                <ul>
                                    <li><strong>Make Commands:</strong> Controller, Model, Migration oluşturma</li>
                                    <li><strong>System Commands:</strong> Server başlatma, cache temizleme</li>
                                    <li><strong>Custom Commands:</strong> Özel CLI komutları</li>
                                    <li><strong>Scaffolding:</strong> CRUD uygulamaları</li>
                                </ul>
                            </div>

                            <div class="code-example">
                                <h6>CLI Komut Örnekleri</h6>
                                <pre><code># Temel komutlar
        php prism make controller UserController --resource
        php prism make model User -m
        php prism make migration create_posts_table

        # Sistem komutları
        php prism system serve
        php prism system cache:clear
        php prism system migrate</code></pre>
                            </div>
                        </div>

                        <!-- Middleware Tab -->
                        <div class="tab-content" id="middleware">
                            <h4>Middleware Sistemi</h4>
                            <p>HTTP isteklerini filtreleyen ve işleyen middleware sistemi.
                                Authentication, logging, caching ve custom middleware'ler.</p>

                            <div class="docs-highlight">
                                <h5>Middleware Özellikleri</h5>
                                <ul>
                                    <li><strong>Global Middleware:</strong> Tüm isteklerde çalışan</li>
                                    <li><strong>Route Middleware:</strong> Belirli route'larda çalışan</li>
                                    <li><strong>Group Middleware:</strong> Route gruplarında çalışan</li>
                                    <li><strong>Custom Middleware:</strong> Özel middleware oluşturma</li>
                                </ul>
                            </div>

                            <div class="code-example">
                                <h6>Middleware Kullanım Örneği</h6>
                                <pre><code>// Middleware tanımlama
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

        // Route'larda kullanım
        Route::middleware(['auth'])->group(function () {
            Route::get('/dashboard', [DashboardController::class, 'index']);
        });</code></pre>
                            </div>
                        </div>

                        <!-- Authentication Tab -->
                        <div class="tab-content" id="auth">
                            <h4>Authentication Sistemi</h4>
                            <p>Güvenli kullanıcı kimlik doğrulama ve yetkilendirme sistemi.
                                Login, register, password reset ve social login.</p>

                            <div class="docs-highlight">
                                <h5>Kimlik Doğrulama</h5>
                                <ul>
                                    <li><strong>User Registration:</strong> Güvenli kayıt sistemi</li>
                                    <li><strong>Login System:</strong> Session-based authentication</li>
                                    <li><strong>Password Reset:</strong> Güvenli şifre sıfırlama</li>
                                    <li><strong>Social Login:</strong> OAuth entegrasyonu</li>
                                </ul>
                            </div>

                            <div class="code-example">
                                <h6>Authentication Controller Örneği</h6>
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
                            <h4>Performance Optimizasyonu</h4>
                            <p>Uygulamanızın performansını artırmak için caching, optimization
                                ve monitoring araçları. Database query optimization ve asset minification.</p>

                            <div class="docs-highlight">
                                <h5>Performans Özellikleri</h5>
                                <ul>
                                    <li><strong>Caching:</strong> Redis, Memcached, File cache</li>
                                    <li><strong>Query Optimization:</strong> Database query caching</li>
                                    <li><strong>Asset Optimization:</strong> CSS/JS minification</li>
                                    <li><strong>Monitoring:</strong> Performance metrics</li>
                                </ul>
                            </div>

                            <div class="code-example">
                                <h6>Cache Kullanım Örneği</h6>
                                <pre><code>// Cache kullanımı
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
                                <i class="fas fa-book me-2"></i>Tam Dökümantasyon
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