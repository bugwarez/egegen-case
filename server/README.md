
#  📰 Egegen Haber Yönetim Sistemi API

  

**Geliştirici:** Tunahan Gündüz

**Teknoloji:** Laravel 11 + SQLite

**Proje Türü:** RESTful API - Haber Yönetim Sistemi

**Postman Docs:** https://www.postman.com/loremsoft/workspace/egegen-case/request/19169115-703e4e11-b907-470a-a6b0-7cff60a0e13a?action=share&creator=19169115&ctx=documentation&active-environment=19169115-1534f681-74c4-4111-a66b-2c449ba58911


  

Bu proje, Egegen Case Study kapsamında geliştirilmiş modern bir haber yönetim sistemi API'sidir. Laravel 11 framework'ü kullanılarak geliştirilmiş olup, güvenli bearer token authentication, görsel işleme, arama fonksiyonları ve kapsamlı loglama özelliklerine sahiptir.

  

##  🚀 Özellikler

  

###  🔐 Güvenlik

-  **Bearer Token Authentication** (Token: `2BH52wAHrAymR7wP3CASt`)

-  **IP Blacklisting** (10 başarısız deneme = 10 dakika ban)

-  **Request Logging** (Tüm API istekleri loglanır)

-  **Input Validation** (Kapsamlı veri doğrulama)

  

###  📝 Haber Yönetimi

-  **CRUD İşlemleri** (Create, Read, Update, Delete)

-  **Durum Yönetimi** (draft, published, archived)

-  **Slug Otomatik Oluşturma** (SEO dostu URL'ler)

-  **UUID Primary Key** (Güvenli kimlik numaraları)

-  **Pagination** (Sayfalama desteği)

  

###  🔍 Arama ve Filtreleme

-  **Gelişmiş Arama** (Başlık ve içerikte arama)

-  **Durum Filtreleme** (Statüye göre filtreleme)

-  **Yayınlanan Haberler** (Public endpoint)

-  **Slug ile Erişim** (SEO dostu erişim)

  

###  🖼️ Görsel İşleme

-  **Otomatik Yeniden Boyutlandırma** (800x800px kare crop)

-  **WebP Dönüştürme** (Optimum dosya boyutu)

-  **Çoklu Format Desteği** (JPEG, PNG, GIF, WebP)

-  **Güvenli Dosya Yükleme** (Validation ile korunmuş)

  

###  📊 Performans

-  **250,000+ Test Verisi** (Performans testleri için)

-  **Bulk Insert** (Hızlı veri ekleme)

-  **Database Indexing** (Optimum sorgu performansı)

-  **Memory Management** (Bellek optimizasyonu)

  

##  📋 Gereksinimler

  

-  **PHP:** 8.2 veya üzeri

-  **Composer:** 2.0 veya üzeri

-  **SQLite:** 3.0 veya üzeri

-  **GD Extension:** Görsel işleme için gerekli

-  **Web Server:** Apache/Nginx (XAMPP önerilir)

  

##  🛠️ Kurulum

  

###  1. Projeyi Klonlayın

```bash

git  clone http://github.com/bugwarez/egegen-case

cd  egegen-case/server

```

  

###  2. Kütüphaneleri Yükleyin

```bash

composer  install

```

  

###  3. Environment Dosyasını Oluşturun

```bash

cp  .env.example  .env

```

  

###  4. Uygulama Anahtarını Oluşturun

```bash

php  artisan  key:generate

```

  

###  5. GD Extension'ı Etkinleştirin

**XAMPP Kullanıyorsanız:**

1.  `C:\xampp\php\php.ini` dosyasını açın

2.  `;extension=gd` satırını bulun

3. Başındaki `;` işaretini kaldırarak `extension=gd` yapın

4. XAMPP'ı yeniden başlatın


###  6. Veritabanını Oluşturun

```bash

php  artisan  migrate

```

  

###  7. Storage Bağlantısını Oluşturun

```bash

php  artisan  storage:link

```

  

###  8. Test Verilerini Yükleyin (Opsiyonel)

```bash

# 250,000 test haberi oluşturur (birkaç dakika sürer)

php  artisan  db:seed

```

  

###  9. Sunucuyu Başlatın

```bash

php  artisan  serve

```

  

API artık `http://127.0.0.1:8000` adresinde çalışmaktadır! 🎉

  

##  📡 API Endpoints

  

###  🔓 Public Endpoints (Token Gerektirmez)

  

####  Sistem Durumu

```http

GET /api/health

```

  

####  Yayınlanan Haberleri Listele

```http

GET /api/news/published?search=teknoloji&per_page=20

```

  

####  Slug ile Haber Getir

```http

GET /api/news/slug/{slug}

```

  

###  🔐 Protected Endpoints (Bearer Token Gerekli)

  

**Header:**

```

Authorization: Bearer 2BH52wAHrAymR7wP3CASt

Accept: application/json

```

  

####  Haber Listesi

```http

GET /api/news?search=sağlık&status=published&per_page=15

```

  

####  Haber Detayı

```http

GET /api/news/{id}

```

  

####  Yeni Haber Oluştur

```http

POST /api/news

Content-Type: multipart/form-data

  

Form Data:

- title: Haber Başlığı

- content: Haber içeriği

- status: published|draft|archived

- image: Görsel dosyası (opsiyonel)

```

  

####  Haber Güncelle

```http

PUT /api/news/{id}

Content-Type: multipart/form-data

  

Form Data:

- title: Güncellenmiş başlık

- content: Güncellenmiş içerik

- status: published|draft|archived

- image: Yeni görsel (opsiyonel)

```

  

####  Haber Sil

```http

DELETE /api/news/{id}

```

  

####  Haber Durumu Değiştir

```http

PATCH /api/news/{id}/status

Content-Type: application/json

  

{

"status": "published"

}

```

  

####  İstek Loglarını Görüntüle

```http

GET /api/logs

```

  

##  📝 Kullanım Örnekleri

  

###  Postman ile Test

  

####  1. Yeni Haber Oluşturma

```

POST http://127.0.0.1:8000/api/news

  

Headers:

Authorization: Bearer 2BH52wAHrAymR7wP3CASt

Accept: application/json

  

Body (form-data):

title: Teknoloji Dünyasında Yeni Gelişmeler

content: Bu haber teknoloji sektöründeki son gelişmeleri içermektedir...

status: published

image: [dosya seç - JPEG/PNG/WebP]

```

  

####  2. Haber Arama

```

GET http://127.0.0.1:8000/api/news/published?search=teknoloji&per_page=10

  

Headers:

Accept: application/json

```

  

####  3. Haber Güncelleme

```

PUT http://127.0.0.1:8000/api/news/{haber-id}

  

Headers:

Authorization: Bearer 2BH52wAHrAymR7wP3CASt

Accept: application/json

  

Body (form-data):

title: Güncellenmiş Başlık

content: Güncellenmiş içerik

status: archived

```

  


##  🖼️ Görsel İşleme

  

###  Desteklenen Formatlar

-  **Giriş:** JPEG, JPG, PNG, GIF, WebP

-  **Çıkış:** Her zaman WebP (optimum boyut)

  

###  Otomatik İşlemler

1.  **Boyut Kontrolü:** Max 5MB dosya boyutu

2.  **Kare Crop:** 800x800px merkez odaklı kırpma

3.  **Format Dönüştürme:** WebP formatına otomatik dönüşüm

4.  **Kalite Optimizasyonu:** %85 kalite ile sıkıştırma

  

###  Dosya Yapısı

```

storage/app/public/news-images/

├── 2025/

│ └── 06/

│ └── 24/

│ └── uuid.webp

```

  

##  🔒 Güvenlik Özellikleri

  

###  Bearer Token Authentication

-  **Token:**  `2BH52wAHrAymR7wP3CASt`

-  **Header:**  `Authorization: Bearer TOKEN`

-  **Geçersiz token:** 401 Unauthorized

  

##  📊 Performans Optimizasyonları

  

###  Database

-  **Indexler:** Sık kullanılan alanlarda index

-  **UUID:** Güvenli ve unique primary key

-  **Pagination:** Büyük veri setleri için sayfalama


###  Caching

-  **Failed Attempts:** IP bazlı cache

-  **Image Processing:** Optimum görsel işleme

  



###  Sağlık Kontrolü

```http

GET /api/health

```

 

  

##  🐛 Sorun Giderme

  

###  GD Extension Hatası

```

Error: GD extension gerekli ancak yüklü değil

```

**Çözüm:** php.ini dosyasında `extension=gd` satırını etkinleştirin.



##  🤝 Katkıda Bulunma

  

Bu proje Egegen Case Study kapsamında geliştirilmiştir. Önerileriniz ve geri bildirimleriniz için iletişime geçebilirsiniz.

  

##  📞 İletişim

  

**Geliştirici:** Tunahan Gündüz

**E-posta:**  apply.tuna@gmail.com

**LinkedIn:**  https://www.linkedin.com/in/tunahangunduz/

  

##  📄 Lisans

  

Bu proje MIT lisansı altında lisanslanmıştır.

  

---

  

##  🎯 Case Study Gereksinimleri Karşılama Durumu

  

###  ✅ Tamamlanan Gereksinimler

  

1.  **✅ Bearer Token Authentication** - `2BH52wAHrAymR7wP3CASt`

2.  **✅ IP Blacklisting** - 10 deneme = 10 dakika ban

3.  **✅ CRUD Operations** - Tam CRUD desteği

4.  **✅ Image Processing** - 800x800px WebP dönüşümü

5.  **✅ Request Logging** - Kapsamlı istek loglama

6.  **✅ UUID Primary Keys** - Güvenli kimlik numaraları

7.  **✅ 250,000 Test Data** - Factory ile oluşturulmuş

8.  **✅ Search Functionality** - Başlık ve içerik arama

9.  **✅ Turkish Validation** - Türkçe hata mesajları

10.  **✅ Comprehensive API** - RESTful API standardları

  

###  🎉 Proje Başarıyla Tamamlanmıştır!

  

Tüm gereksinimler karşılanmış ve test edilmiştir. API kullanıma hazırdır.