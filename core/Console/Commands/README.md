# PRISM Framework Console Commands Kullanım Kılavuzu

Bu kılavuz, PRISM Framework'ün console komutlarının nasıl kullanılacağını detaylı bir şekilde açıklar.

## 📁 Klasör Yapısı

```
core/Console/Commands/
├── Command.php              # Ana command sınıfı (tüm komutların base'i)
├── GeneratorCommands.php    # Kod üretici komutları (make:*)
├── DatabaseCommands.php     # Veritabanı işlem komutları 
├── SystemCommands.php       # Sistem yönetim komutları
└── README.md               # Bu kılavuz
```

## 🎯 Komut Kategorileri

### 1. Generator Commands (Kod Üreticiler)
Uygulama bileşenlerini otomatik oluşturur.

### 2. Database Commands (Veritabanı)
Veritabanı migration ve seeding işlemleri.

### 3. System Commands (Sistem Yönetimi)
Uygulama yönetimi, güvenlik ve geliştirme araçları.

---

## 🔨 Generator Commands

**Dosya:** `GeneratorCommands.php`  
**Amaç:** Model, Controller, Migration gibi kod dosyalarını otomatik oluşturur.

### Kullanım Şablonu
```bash
php prism make <type> <name> [options]
```

### 📋 Kullanılabilir Generatorlar

#### 1. Controller Oluşturma
```bash
# Basit controller
php prism make controller UserController

# Resource controller (CRUD methodları ile)
php prism make controller UserController --resource

# API controller (JSON response'lar ile)
php prism make controller UserController --api

# Resource + API controller
php prism make controller UserController --resource --api
```

**Oluşturulan dosya:** `app/Http/Controllers/UserController.php`

#### 2. Model Oluşturma
```bash
# Basit model
php prism make model User

# Model + Migration
php prism make model User --migration

# Model + Factory
php prism make model User --factory

# Model + Seeder
php prism make model User --seeder

# Model + Controller
php prism make model User --controller

# Hepsini birden oluştur
php prism make model User --all

# Özel kombinasyonlar
php prism make model User --migration --factory --controller --resource
```

**Oluşturulan dosya:** `app/Models/User.php`

#### 3. Migration Oluşturma
```bash
# Basit migration
php prism make migration create_users_table

# Tablo oluşturma migration'ı
php prism make migration create_users_table --create=users

# Tablo değiştirme migration'ı
php prism make migration add_email_to_users_table --table=users

# Özel path
php prism make migration create_posts_table --path=database/custom_migrations
```

**Oluşturulan dosya:** `database/migrations/{timestamp}_create_users_table.php`

#### 4. Factory Oluşturma
```bash
# Basit factory
php prism make factory UserFactory

# Model belirterek factory
php prism make factory UserFactory --model=User
```

**Oluşturulan dosya:** `database/factories/UserFactory.php`

#### 5. Seeder Oluşturma
```bash
# Basit seeder
php prism make seeder UserSeeder

# Model ile ilişkili seeder
php prism make seeder UserSeeder --model=User
```

**Oluşturulan dosya:** `database/seeders/UserSeeder.php`

#### 6. Middleware Oluşturma
```bash
# Middleware oluştur
php prism make middleware AuthMiddleware
php prism make middleware AdminMiddleware
```

**Oluşturulan dosya:** `core/Middleware/AuthMiddleware.php`

#### 7. Form Request Oluşturma
```bash
# Form request oluştur
php prism make request StoreUserRequest
php prism make request UpdatePostRequest
```

**Oluşturulan dosya:** `app/Http/Requests/StoreUserRequest.php`

### 💡 Generator Örnekleri

#### Tam Blog Sistemi Oluşturma
```bash
# Post model'i ve tüm bileşenlerini oluştur
php prism make model Post --all

# Comment model'i oluştur
php prism make model Comment --migration --factory

# Admin middleware oluştur
php prism make middleware AdminMiddleware

# Post validation request'i oluştur
php prism make request StorePostRequest
```

#### E-ticaret Kategori Sistemi
```bash
# Category controller (API)
php prism make controller CategoryController --resource --api

# Product model ve migration
php prism make model Product --migration --factory --seeder

# Ürün filtreleme middleware
php prism make middleware ProductFilterMiddleware
```

---

## 💾 Database Commands

**Dosya:** `DatabaseCommands.php`  
**Amaç:** Veritabanı migration ve seeding işlemleri.

### Kullanım Şablonu
```bash
php prism db <action> [options]
```

### 📋 Kullanılabilir Komutlar

#### 1. Migration Çalıştırma
```bash
# Tüm pending migration'ları çalıştır
php prism db migrate

# Migration'ları preview et (çalıştırmadan göster)
php prism db migrate --pretend

# Production'da zorla çalıştır
php prism db migrate --force

# Migration sonrası seeder'ları da çalıştır
php prism db migrate --seed

# Belirli path'ten migration'ları çalıştır
php prism db migrate --path=database/custom_migrations

# Step by step migration
php prism db migrate --step=1
```

#### 2. Database Seeding
```bash
# Varsayılan seeder'ı çalıştır (DatabaseSeeder)
php prism db seed

# Belirli seeder'ı çalıştır
php prism db seed UserSeeder

# Production'da zorla çalıştır
php prism db seed --force

# Belirli seeder class'ı
php prism db seed --class=ProductSeeder
```

### 💡 Database İşlem Örnekleri

#### İlk Kurulum
```bash
# Migration'ları çalıştır ve sample data ekle
php prism db migrate --seed
```

#### Geliştirme Sırasında
```bash
# Yeni migration'ları kontrol et
php prism db migrate --pretend

# Migration'ları çalıştır
php prism db migrate

# Test verilerini yenile
php prism db seed TestDataSeeder
```

#### Production Deployment
```bash
# Production'da migration (dikkatli!)
php prism db migrate --force

# Production seed (sadece gerekli veriler)
php prism db seed ProductionSeeder --force
```

---

## ⚙️ System Commands

**Dosya:** `SystemCommands.php`  
**Amaç:** Uygulama yönetimi, güvenlik, optimizasyon ve geliştirme araçları.

### Kullanım Şablonu
```bash
php prism system <action> [options]
```

### 📋 Server Management

#### 1. Geliştirme Sunucusu
```bash
# Varsayılan ayarlarla başlat (127.0.0.1:8000)
php prism system serve

# Özel port
php prism system serve --port=8080

# Özel host
php prism system serve --host=0.0.0.0 --port=3000

# Özel public directory
php prism system serve --public=dist
```

#### 2. Framework Kurulumu
```bash
# İlk kurulum
php prism system install

# Zorla yeniden kurulum
php prism system install --force

# Kurulum + sample data
php prism system install --seed
```

#### 3. Maintenance Mode
```bash
# Maintenance mode'a al
php prism system down

# Özel mesaj ile
php prism system down --message="Sistem güncellemesi yapılıyor"

# Retry süresi belirle
php prism system down --retry=120

# Belirli IP'lere izin ver
php prism system down --allow=192.168.1.1,192.168.1.2

# Secret bypass key
php prism system down --secret=emergency123

# Maintenance mode'dan çıkar
php prism system up
```

### 📋 Utilities

#### 1. Cache Yönetimi
```bash
# Tüm cache'i temizle
php prism system clear:cache
```

#### 2. Uygulama Optimizasyonu
```bash
# Tüm optimizasyonları çalıştır
php prism system optimize
```

#### 3. Uygulama Anahtarı
```bash
# Yeni application key oluştur
php prism system key:generate
```

### 📋 Information Commands

#### 1. Route Listesi
```bash
# Tüm route'ları listele
php prism system route:list

# HTTP method'a göre filtrele
php prism system route:list --method=POST

# Route isminde arama
php prism system route:list UserController

# Path'te arama
php prism system route:list --path=/api/
```

#### 2. Uygulama İnceleme
```bash
# Hangi bileşenler incelenebilir
php prism system inspect

# Route'ları incele
php prism system inspect routes

# Konfigürasyonu incele
php prism system inspect config

# Veritabanını incele
php prism system inspect database

# Cache durumunu incele
php prism system inspect cache

# Container binding'lerini incele
php prism system inspect container

# Environment variable'ları incele
php prism system inspect env

# JSON formatında export
php prism system inspect routes --format=json --export=routes.json

# Filtreleme
php prism system inspect config --filter=database
```

#### 3. Tüm Komutları Listele
```bash
# Mevcut tüm komutları göster
php prism system list
```

### 📋 Environment Management

#### 1. Environment Variable İşlemleri
```bash
# ENV menüsünü göster
php prism system env

# Variable okuma
php prism system env get APP_NAME
php prism system env get DB_HOST

# Variable ayarlama
php prism system env set APP_DEBUG true
php prism system env set DB_PASSWORD secret123

# Variable silme
php prism system env unset OLD_SETTING

# Tüm variable'ları listele
php prism system env list

# ENV dosyasını validate et
php prism system env validate

# ENV dosyasını backup'la
php prism system env backup
php prism system env backup --backup=.env.backup.$(date +%Y%m%d)

# Backup'tan restore et
php prism system env restore .env.backup.20241201

# env.example'dan yeni .env oluştur
php prism system env generate

# Özel dosya ile çalış
php prism system env list --file=.env.production
```

### 📋 Security Commands

#### 1. Güvenlik Taraması
```bash
# Basit güvenlik taraması
php prism system security:scan

# Detaylı tarama
php prism system security:scan --level=full

# Bulunan sorunları otomatik düzelt
php prism system security:scan --fix

# Rapor oluştur
php prism system security:scan --report=security_report.json

# Düzeltme + rapor
php prism system security:scan --fix --report=security_fix_report.json
```

#### 2. Güvenlik Kurulumu
```bash
# Güvenlik özellikleri menüsü
php prism system security:setup

# CSRF koruması kur
php prism system security:setup --csrf

# Güvenlik header'ları kur
php prism system security:setup --headers

# Rate limiting kur
php prism system security:setup --rate-limit

# Tüm güvenlik özelliklerini kur
php prism system security:setup --all
```

---

## 🔄 Workflow Örnekleri

### Yeni Proje Başlatma
```bash
# 1. Framework'ü kur
php prism system install --seed

# 2. İlk model ve controller'ı oluştur
php prism make model User --all

# 3. Güvenlik özelliklerini kur
php prism system security:setup --all

# 4. Geliştirme sunucusunu başlat
php prism system serve
```

### Yeni Feature Geliştirme
```bash
# 1. Model ve migration oluştur
php prism make model Post --migration --factory --seeder

# 2. Controller oluştur
php prism make controller PostController --resource

# 3. Form request oluştur
php prism make request StorePostRequest
php prism make request UpdatePostRequest

# 4. Migration'ı çalıştır
php prism db migrate

# 5. Test verilerini ekle
php prism db seed PostSeeder
```

### Production Deployment
```bash
# 1. Güvenlik taraması yap
php prism system security:scan --level=full --report=pre_deploy_scan.json

# 2. Optimizasyon yap
php prism system optimize

# 3. Migration'ları çalıştır
php prism db migrate --force

# 4. Production seeder'ları çalıştır
php prism db seed ProductionSeeder --force

# 5. Cache'i temizle
php prism system clear:cache
```

### Debugging ve Geliştirme
```bash
# 1. Route'ları kontrol et
php prism system route:list --path=/api/

# 2. Konfigürasyonu incele
php prism system inspect config --filter=database

# 3. Veritabanı durumunu kontrol et
php prism system inspect database

# 4. Environment'ı validate et
php prism system env validate

# 5. Cache durumunu kontrol et
php prism system inspect cache
```

---

## ⚡ Hızlı Referans

### En Çok Kullanılan Komutlar
```bash
# Geliştirme
php prism system serve                    # Sunucu başlat
php prism make controller UserController  # Controller oluştur
php prism make model User --migration     # Model + Migration
php prism db migrate                      # Migration çalıştır
php prism system clear:cache             # Cache temizle

# İnceleme
php prism system route:list              # Route'ları listele
php prism system inspect database        # DB durumu
php prism system env list                # ENV variable'ları

# Güvenlik
php prism system security:scan           # Güvenlik taraması
php prism system security:setup --all    # Güvenlik kurulumu

# Bakım
php prism system down                     # Maintenance mode
php prism system up                       # Maintenance'tan çık
php prism system optimize                 # Optimize et
```

### Dosya Konumları
```
Generators tarafından oluşturulan dosyalar:
├── app/Http/Controllers/     # Controllers
├── app/Http/Requests/        # Form Requests  
├── app/Models/              # Models
├── core/Middleware/         # Middleware
├── database/migrations/     # Migrations
├── database/factories/      # Factories
└── database/seeders/        # Seeders
```

---

## 🐛 Sorun Giderme

### Yaygın Hatalar ve Çözümleri

#### 1. "Command not found" Hatası
```bash
# Çözüm: Tam path kullan
php core/Console/Console.php system serve
```

#### 2. "Permission denied" Hatası
```bash
# Çözüm: Dosya izinlerini kontrol et
chmod +x prism
php prism system security:scan --fix
```

#### 3. "Database connection failed"
```bash
# Çözüm: ENV ayarlarını kontrol et
php prism system env validate
php prism system inspect database
```

#### 4. "Migration already exists"
```bash
# Çözüm: Farklı isim kullan
php prism make migration add_email_to_users_table_v2
```

### Debug Modunu Etkinleştir
```bash
# ENV'de debug'ı aç
php prism system env set APP_DEBUG true

# Log'ları kontrol et
tail -f storage/logs/app-$(date +%Y-%m-%d).log
```

---

## 📞 Destek

Bu komutlarla ilgili sorun yaşıyorsan:

1. **Documentation:** Bu README dosyasını tekrar oku
2. **Help:** `php prism help <command>` komutunu kullan
3. **Logs:** `storage/logs/` klasöründeki log dosyalarını kontrol et
4. **Debug:** `APP_DEBUG=true` ayarlayıp detaylı hata mesajlarını incele

---

**Son güncelleme:** 2024-12-07  
**PRISM Framework Sürümü:** 2.0.0
