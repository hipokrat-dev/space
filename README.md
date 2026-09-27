# Yörünge · Uzay Kaşifleri

5. sınıf düzeyinde Türkçe uzay öğrenme sitesi. Dokuz keşif durağı (Güneş ve sekiz gezegen), önemli doğal uydular, Güneş’in altı katmanı, Dünya’nın dört iç katmanı, sekiz Ay evresi, gezegen sıralama oyunu, uydu eşleştirme oyunu ve açıklamalı 12 soruluk sınav içerir.

## Mevcut durum

- Arayüz ve oyunlar çalışır. NASA görselleri yerel dosyalardır; kaynak ve kredi bilgileri sitededir.
- PHP API ve MySQL şeması hazırdır. **Henüz gerçek Hostinger veritabanına bağlanmadı.**
- Kaynak deposu: **https://github.com/hipokrat-dev/space** (`main`). **Hostinger yayını ve otomatik dağıtımı henüz etkinleştirilmedi.** Alan adı ve Hostinger paket bilgisi bekleniyor.
- Yerel önizleme API olmadan çalışır. Tamamlanan konular ve en iyi sınav puanı bu tarayıcıda `localStorage` ile tutulur. Ekranın alt kısmı yerel modu açıkça gösterir.

## Mimari

Bağımlılıksız HTML + CSS + ES modülleri, PHP 8.1+ API, MySQL/MariaDB (InnoDB, JSON desteği). Web sunucusu Apache/LiteSpeed ve `.htaccess` desteği gerektirir. Node.js yalnızca kaynak kontrolleri için kullanılır; üretimde Node.js veya derleme gerekmez. Standart Hostinger PHP/HTML web hosting veya cloud hosting planlarına yöneliktir. Website Builder, agency veya yalnızca Node.js planlarında bu dağıtım yolu ayrıca değerlendirilmelidir.

```text
GitHub main → Hostinger yerleşik Git otomatik dağıtımı → public_html
                                                           ├─ index.html + assets
                                                           └─ api/index.php → MySQL
public_html dışındaki yorunge-config.php → veritabanı erişim bilgileri
```

## Hostinger kurulumu

1. Hostinger’da alan adına bağlı **Custom PHP/HTML** site ve HTTPS oluşturun. PHP 8.1 veya daha yeni bir sürüm ve `pdo_mysql` uzantısı seçin. Mevcut sitenin üzerine kurmayın; yeni/boş site kullanın.
2. Bu klasörün **içeriğini** GitHub deposunun köküne koyun; `.github`, `.gitignore` ve `.htaccess` dosyalarını dahil edin. `main` dağıtım dalıdır. Gizli bilgi eklemeyin.
3. hPanel → Websites → site Dashboard → Advanced → Git → Connect with GitHub. Hostinger uygulamasına yalnızca ilgili depoyu yetkilendirin. Depoyu, `main` dalını ve hedef dizin `public_html` seçimini yapın; ilk dağıtımı çalıştırın.
4. Git ekranında **Auto-deployment** durumunu etkin olarak doğrulayın. Bu bağlantı kurulduktan sonra `main` dalına gönderilen/birleştirilen güncellemeler otomatik yayımlanır. Ayrı FTP anahtarı veya eski webhook betiği gerekmez.
5. hPanel → Databases bölümünden bir MySQL veritabanı ve kullanıcı oluşturun. phpMyAdmin üzerinden `database/schema.sql` dosyasını seçilen veritabanına bir kez aktarın. Bu işlem mevcut tabloları silmez.
6. `database/config.example.php` dosyasının bir kopyasını **public_html klasörünün bir üstüne** `yorunge-config.php` adıyla koyun. Örnek: `/home/USER/domains/DOMAIN/yorunge-config.php`. API’nin varsayılan yolu bu konumdur. Alternatif konum için sunucu ortamında `YORUNGE_CONFIG` tanımlanabilir.
7. Bu özel dosyada gerçek DSN, veritabanı kullanıcı adı/parolası ve tam HTTPS alan adını yazın. `origin` sonunda `/` olmamalıdır. `app_key` için sunucuda `php -r 'echo bin2hex(random_bytes(32)), PHP_EOL;'` çalıştırın. Anahtarı ve şifreleri GitHub’a koymayın; sohbette paylaşmayın. Dosya yalnızca hosting hesabınca okunabilir olmalıdır.
8. Siteyi HTTPS ile açın. Alt bölümde sunucuda kayıt mesajını görün. Bir konuyu tamamlayın, sayfayı yenileyin; kayıt korunmalıdır. Bir sınav bitirin ve yeniden açarak en iyi puanı doğrulayın.
9. Küçük bir metin değişikliğini GitHub `main` dalına gönderin. hPanel Deployment History’de **yeni commit kimliğini ve başarılı dağıtımı** doğrulayın; sitede değişikliği görün. Ancak bu kontrolden sonra otomatik dağıtım tamamlanmış sayılır.

Hostinger’ın güncel resmî yönergesi: https://www.hostinger.com/support/1583302-how-to-deploy-a-git-repository-in-hostinger/

## GitHub kontrolleri

`.github/workflows/check.yml`, push ve pull request işlemlerinde içerik/görsel testlerini, JavaScript sözdizimini ve PHP lint kontrollerini çalıştırır. GitHub repository ruleset/branch protection ile `main` için pull request ve `check` durumunun geçmesini zorunlu kılın. **Bu workflow tek başına dağıtım yapmaz.** Yayın, yukarıdaki Hostinger Git bağlantısından gerçekleşir; bu bağlantı kontrollerin bitmesini kendiliğinden beklediği varsayılmamalıdır.

Güncellemeler `public_html` içindeki uygulama dosyalarını değiştirir. Veritabanı ve dışarıdaki yapılandırma dosyası korunur. Şema değişiklikleri gelecekte ayrı, versiyonlu SQL migration dosyalarıyla uygulanmalıdır. Yayından önce Hostinger yedeği alın; geri dönüş için GitHub’da hatalı commit’i geri alan yeni bir commit kullanın.

## Kayıt modeli

Ad, e-posta, okul veya doğum tarihi alınmaz. HMAC imzalı, HttpOnly/Secure/SameSite tarayıcı çerezi rastgele öğrenci kimliğine bağlanır. Aynı tarayıcıda bir yılda yenilenen kimlikle ilerleme korunur; cihazlar arasında hesap eşitleme yoktur. Çerez silinirse eski kayıt yeniden bulunamaz. Kaynaklar sayfasında kayıt silme düğmesi vardır.

API CSRF token’ı, tam Origin doğrulaması, izin verilen konu listesi, parametreli SQL sorguları, boyut sınırlaması ve sunucuda sınav puanı hesaplaması kullanır. Sınav cevapları öğretim amacıyla istemcide de bulunur; bu bir yüksek güvenlikli resmî sınav platformu değildir. Öğrenci bazlı sonuçlar kamuya açık listelenmez.

API bağlantısı kesilirse yerel moda geçilir. Yerel ve sunucu kayıtları otomatik birleştirilmez: bağlantı kurulunca sunucu kaydı esas alınır. Yedekleme ve saklama süresi hosting yöneticisince belirlenmelidir. Google Fonts dış bağlantısı mevcuttur; analitik veya reklam betiği yoktur.

## Doğrulama

```sh
npm test
node --check assets/app.js
php -l api/index.php
php -l database/config.example.php
```

Bu çalışma sırasında 4 içerik/görsel testi geçti; JavaScript sözdizimi doğrulandı. Tarayıcıda katman seçimi, Dolunay seçimi, sıralama oyununun yanlış/doğru yolları, beş uydu eşleşmesi ve 12 soruluk sınavın 110/120 sonucu doğrulandı. Yenilemede bir konu ve 110 puan korundu. 390 piksel genişlikte yatay taşma bulunmadı; gezegen görselleri yüklendi. WebMCP keşif aracı geçerli girdiyle paneli açtı, geçersiz gök cismini reddetti.

**Yerel ortamda PHP/MySQL bulunmadığından PHP çalışma zamanı, SQL ve canlı kayıt entegrasyonu henüz çalıştırılarak test edilmedi.** PHP lint GitHub Actions içinde tanımlıdır; ilk push sonrası sonucu incelenmelidir. Canlı bağlantı ve otomatik yayın testi, hesaplar bağlandıktan sonra yapılmalıdır.

## Dosyalar

- `index.html`, `assets/style.css`, `assets/app.js`: kullanıcı arayüzü.
- `assets/data.js`, `assets/questions.json`: eğitim içeriği ve sınav bankası.
- `assets/credits.json`: doğrulanmış NASA görsel URL’leri ve atıflar.
- `api/index.php`: ilerleme, konu, sınav ve silme API’si.
- `database/schema.sql`, `database/config.example.php`: sunucu kurulumu.
- `.htaccess`: HTTPS, özel dizin engeli ve güvenlik başlıkları.
- `.github/workflows/check.yml`: GitHub doğrulama akışı.

Görsellerin hakları ilgili kaynak ve kredi sahiplerindedir. NASA ve görsel kredi bilgileri korunmalıdır; Jüpiter görseli Kevin M. Gill CC-BY atfını içerir.
