# PRISM Framework Console Commands

Bu dosya PRISM Framework'ün mevcut console komutlarını ve kullanımlarını açıklar.

## Mevcut Komutlar

### Sistem Komutları (`SystemCommands`)

#### `system:serve`
Uygulamayı geliştirme sunucusunda başlatır.
```bash
php prism system:serve
```

#### `system:clear:cache`
Uygulama cache'ini temizler.
```bash
php prism system:clear:cache
```

#### `system:install`
Framework'ü kurar ve veritabanı bağlantısını test eder.
```bash
php prism system:install
```

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
1. Migration'ları çalıştır: `php prism db:migrate`
2. Seeder'ları çalıştır: `php prism db:seed`
3. Uygulamayı başlat: `php prism system:serve`

### Veritabanı Komutları (`DatabaseCommands`)

#### `db:migrate`
Migration'ları çalıştırır.
```bash
php prism db:migrate
```

#### `db:seed`
Seeder'ları çalıştırır.
```bash
php prism db:seed
```

## Komut Geliştirme

Yeni komut eklemek için:

1. `core/Console/Commands/` klasöründe yeni komut dosyası oluştur
2. `Command` sınıfından türet
3. `core/Console/Console.php` dosyasında komutu kaydet

## Yardım

Komut listesini görmek için:
```bash
php prism list
```

Belirli bir komut hakkında yardım almak için:
```bash
php prism help [komut_adı]
```
