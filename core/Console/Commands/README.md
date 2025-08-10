# PRISM Framework Console Commands

Bu dosya PRISM Framework'ün mevcut console komutlarını ve kullanımlarını açıklar.

## Mevcut Komutlar

### Sistem Komutları (`SystemCommands`)

#### `system serve`
Uygulamayı geliştirme sunucusunda başlatır.
```bash
php prism system serve
```

#### `system clear:cache`
Uygulama cache'ini temizler.
```bash
php prism system clear:cache
```

#### `system install`
Framework'ü kurar ve veritabanı bağlantısını test eder.
```bash
php prism system install
```

**Önemli Özellikler:**
- Veritabanı otomatik olarak oluşturulur
- Gerekli tablolar otomatik oluşturulur
- Application key otomatik generate edilir
- Dizin izinleri otomatik ayarlanır

### Generator Komutları (`GeneratorCommands`)

#### `make:controller`
Yeni bir controller oluşturur.
```bash
php prism make:controller UserController
```

#### `make:model`
Yeni bir model oluşturur.
```bash
php prism make:model User
```

#### `make:migration`
Yeni bir migration oluşturur.
```bash
php prism make:migration create_users_table
```

#### `make:seeder`
Yeni bir seeder oluşturur.
```bash
php prism make:seeder UserSeeder
```

#### `make:factory`
Yeni bir factory oluşturur.
```bash
php prism make:factory UserFactory
```

**Önemli Özellikler:**
- Tüm komutlar otomatik dizin oluşturur
- Path çözümleme sorunları giderildi
- Symfony Console uyumluluğu sağlandı

#### `make:scaffold`
**YENİ!** Tek komutla tam bir MVC yapısı oluşturur (Model, Controller, Migration, Seeder, Factory, Views, Routes).

```bash
php prism make:scaffold ModelName [options]
```

**Seçenekler:**
- `--views`: View dosyalarını oluşturur
- `--routes`: Route'ları oluşturur
- `--fields`: Model alanlarını tanımlar (örn: "name:string,email:string,age:integer")
- `--api`: API controller oluşturur
- `--resource`: Resource controller oluşturur
- `--relations`: Model ilişkilerini tanımlar (örn: "hasMany:Product,belongsTo:User")
- `--fillable`: Mass assignment için alanları tanımlar (örn: "name,email,age")
- `--hidden`: Serialization'da gizlenecek alanları tanımlar (örn: "password,secret")
- `--casts`: Veri tipi dönüşümlerini tanımlar (örn: "age:integer,active:boolean")

**Oluşturulan Dosyalar:**
- Model (`app/Models/ModelName.php`)
- Controller (`app/Http/Controllers/ModelNameController.php`)
- Migration (`database/migrations/YYYY_MM_DD_HHMMSS_create_modelnames_table.php`)
- Seeder (`database/seeders/ModelNameSeeder.php`)
- Factory (`database/factories/ModelNameFactory.php`)
- Views (`resources/views/modelnames/` klasörü)
- Routes (`routes/web.php` içine eklenir)

**Özellikler:**
- Otomatik model özellikleri (`$fillable`, `$hidden`, `$casts`)
- Otomatik model ilişkileri (`relations()` metodu)
- Tam CRUD işlemleri
- Flash mesajları
- Validation desteği
- Responsive Bootstrap UI

**Desteklenen Alan Tipleri:**
- `string`, `text`, `integer`, `bigint`, `decimal`, `float`, `boolean`, `date`, `datetime`, `timestamp`, `json`

**Desteklenen İlişki Tipleri:**
- `hasOne`, `hasMany`, `belongsTo`, `belongsToMany`, `hasOneThrough`, `hasManyThrough`, `morphOne`, `morphMany`, `morphTo`, `morphToMany`, `morphedByMany`

**Örnek Kullanımlar:**

Basit scaffold:
```bash
php prism make:scaffold Product --views --routes
```

Alanlarla scaffold:
```bash
php prism make:scaffold User --fields="name:string,email:string,password:string,age:integer" --views --routes
```

Tam özellikli scaffold (tüm seçenekler):
```bash
php prism make:scaffold Product \
  --fields="name:string,description:text,price:decimal,stock:integer,user_id:bigint,category_id:bigint" \
  --relations="belongsTo:User,belongsTo:Category" \
  --fillable="name,description,price,stock,user_id,category_id" \
  --hidden="secret_key" \
  --casts="price:decimal,stock:integer,active:boolean" \
  --views \
  --routes
```

**Post-Scaffold Adımları:**
1. Migration'ları çalıştır: `php prism db migrate`
2. Seeder'ları çalıştır: `php prism db db:seed`
3. Uygulamayı başlat: `php prism system serve`

**Tam Özellikli Örnek (Test Scaffold):**
```bash
php prism make:scaffold Test \
  --fields="name:string,email:string,age:integer,active:boolean" \
  --relations="Product:hasMany,User:belongsTo" \
  --fillable="name,email,age" \
  --hidden="password" \
  --casts="age:integer,active:boolean" \
  --views \
  --routes
```

Bu komut şunları oluşturur:
- Model: `app/Models/Test.php` (fillable, hidden, casts ve relations ile)
- Migration: `database/migrations/YYYY_MM_DD_HHMMSS_create_tests_table.php` (doğru class name ile)
- Controller: `app/Http/Controllers/TestController.php` (CRUD metodları ile)
- Views: `resources/views/tests/` (index, create, edit, show)
- Routes: `routes/web.php` içine eklenir
- Factory: `database/factories/TestFactory.php`
- Seeder: `database/seeders/TestSeeder.php`

### Veritabanı Komutları (`DatabaseCommands`)

#### `db migrate`
Migration'ları çalıştırır.
```bash
php prism db migrate
```

#### `db db:seed`
Seeder'ları çalıştırır.
```bash
php prism db db:seed
```

**Önemli Notlar:**
- Migration class name'leri otomatik olarak `Create{TableName}Table` formatında oluşturulur
- Veritabanı otomatik olarak `system install` komutu ile oluşturulur
- Migration'lar PRISM Framework'ün native sınıflarını kullanır

## Komut Geliştirme

Yeni komut eklemek için:

1. `core/Console/Commands/` klasöründe yeni komut dosyası oluştur
2. `Command` sınıfından türet
3. `core/Console/Console.php` dosyasında komutu kaydet

## Son Güncellemeler

### v3.0.0 - Scaffold Sistemi
- **Yeni `make:scaffold` komutu** eklendi
- **Otomatik model özellikleri** (fillable, hidden, casts, relations)
- **Migration class name düzeltmesi** (Create{TableName}Table formatı)
- **Veritabanı otomatik oluşturma** (`system install` ile)
- **Path çözümleme sorunları** giderildi
- **Symfony Console uyumluluğu** sağlandı

### Düzeltilen Hatalar
- Migration class name bulunamama hatası
- Veritabanı otomatik oluşturulamama hatası
- Path duplication hataları
- Cache temizleme dosya silme sorunu
- Controller oluşturma dizin hatası

## Yardım

Komut listesini görmek için:
```bash
php prism list
```

Belirli bir komut hakkında yardım almak için:
```bash
php prism help [komut_adı]
```

## Test Etme

Test scaffold'u oluşturmak için:
```bash
php prism make:scaffold Test --fields "name:string,email:string,age:integer,active:boolean" --views --routes --relations "Product:hasMany,User:belongsTo" --fillable "name,email,age" --hidden "password" --casts "age:integer,active:boolean"
```

Migration ve seeding:
```bash
php prism db migrate
php prism db db:seed
```

Sunucuyu başlatma:
```bash
php prism system serve
```
