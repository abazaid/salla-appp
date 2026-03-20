# RankX SEO - Salla Content Optimizer

منصة خارجية مرتبطة بسلة لتحسين:
- وصف المنتجات
- SEO المنتج (Meta Title / Meta Description)
- ALT الصور
- SEO المتجر

مع لوحة عميل + لوحة أدمن + تتبع استهلاك وتكلفة OpenAI.

---

## 1) حالة المشروع الحالية

المشروع يعمل كبنية SaaS خارج سلة، ويتضمن:
- OAuth + Webhook مع سلة.
- لوحة عميل خارجية (`/dashboard`) مقسمة إلى:
1. المنتجات
2. سيو المتجر
3. كاتب ALT للصور
4. سجل العمليات
5. الحساب والإعدادات
- لوحة أدمن (`/admin/dashboard`) تعرض:
1. المتاجر والاشتراكات
2. تكلفة OpenAI الإجمالية
3. تكلفة OpenAI حسب نوع التوليد
4. تفاصيل تكلفة كل عملية (logs)
5. إدارة المتاجر + حذف متجر + تعديل الاشتراك + سجل نشاط الأدمن

---

## 2) الميزات المنفذة

### تحسين المنتجات
- تحسين وصف فقط.
- تحسين SEO فقط.
- تحسين الكل (وصف + SEO).
- نافذة مقارنة قبل/بعد مع تعديل يدوي قبل الحفظ.
- حفظ مباشر في سلة.

### تحسين ALT للصور
- تحسين صورة واحدة أو مجموعة صور لمنتج.
- تحسين جماعي للمنتجات المحددة.
- حفظ ALT المحدد في سلة.
- فلترة ALT (محسن/غير محسن/مختلط).

### SEO المتجر
- جلب إعدادات SEO الحالية.
- توليد SEO مقترح عبر الذكاء الاصطناعي بناءً على:
1. بيانات المتجر الحالية
2. سياق من المنتجات
- حفظ إعدادات SEO في سلة.

### الاستهلاك والتكلفة
- تسجيل كل عملية AI في `ai_usage_logs`.
- حفظ نوع العملية في عمود `mode` مثل:
`description`, `seo`, `all`, `image_alt`, `image_alt_bulk`, `store_seo`.
- عرض التكلفة حسب النوع + تفاصيل كل عملية في لوحة الأدمن.

---

## 3) المتطلبات

- PHP 8.1+ (مفضل 8.2)
- MySQL 8+ أو MariaDB حديثة
- OpenSSL مفعّل
- امتدادات PHP:
`pdo`, `pdo_mysql`, `curl`, `mbstring`, `json`
- اتصال إنترنت للوصول إلى:
1. Salla API
2. OpenAI API
3. SMTP (Hostinger)

---

## 4) الإعداد المحلي

1. نسخ ملف البيئة:
```bash
copy .env.example .env
```

2. تعبئة القيم الأساسية في `.env`:
- إعدادات Salla
- إعدادات DB
- مفاتيح OpenAI
- إعدادات SMTP
- حساب الأدمن

3. استيراد قاعدة البيانات:
```sql
database/schema.sql
```

4. تشغيل السيرفر:
```bash
php -S localhost:8000 -t public
```

5. فتح:
```text
http://localhost:8000
```

---

## 5) إعداد سلة (Partner Portal)

### OAuth / Webhook / Embedded
- Redirect URL:
```text
https://app.rankxseo.com/oauth/callback
```
- Webhook URL:
```text
https://app.rankxseo.com/webhooks/salla
```
- Embedded URL (اختياري حالياً):
```text
https://app.rankxseo.com/embedded
```

### Scopes المطلوبة
الحد الأدنى لهذا المشروع:
- `products.read_write`
- `metadata.read` أو `metadata.read_write` (ضروري لسيو المتجر)
- `webhooks.read_write` (حسب إعداداتك)

إذا عدلت Scopes، يفضّل إعادة تثبيت التطبيق على المتجر التجريبي لتحديث الـ access token.

---

## 6) المسارات (Routes)

### واجهات عامة / Auth
- `GET /`
- `GET /login`
- `POST /login`
- `GET /logout`
- `GET /forgot-password`
- `POST /forgot-password`
- `GET /set-password`
- `POST /set-password`
- `GET /dashboard`

### OAuth / Webhook
- `GET /oauth/callback`
- `POST /webhooks/salla`

### Admin
- `GET /admin/login`
- `POST /admin/login`
- `GET /admin/logout`
- `GET /admin/dashboard`
- `GET /admin/stores`
- `GET /admin/stores/{id}`
- `POST /admin/stores/{id}/subscription`
- `POST /admin/stores/{id}/delete`
- `GET /admin/activity`
- `POST /admin/email-test`

### API
- `GET /api/products`
- `GET /api/subscription`
- `GET /api/operations`
- `GET /api/store-seo`
- `POST /api/store-seo/optimize`
- `POST /api/store-seo/save`
- `POST /api/products/{id}/optimize`
- `POST /api/products/{id}/save-description`
- `POST /api/products/alt/bulk`
- `POST /api/products/{id}/images/optimize-alt`
- `POST /api/products/{id}/images/save-alt`
- `POST /api/products/{id}/images/{imageId}/optimize-alt`
- `POST /api/products/{id}/images/{imageId}/save-alt`

---

## 7) قاعدة البيانات

الملف الرسمي:
- `database/schema.sql`

الجداول الرئيسية:
- `stores`
- `users`
- `subscriptions`
- `password_reset_tokens`
- `admin_activity_logs`
- `ai_usage_logs`

### ملاحظة ترقية مهمة
إذا كانت قاعدة البيانات قديمة، تأكد من وجود عمود `mode` في `ai_usage_logs`:
```sql
SHOW COLUMNS FROM ai_usage_logs LIKE 'mode';
```
إذا غير موجود:
```sql
ALTER TABLE ai_usage_logs
ADD COLUMN mode VARCHAR(50) NULL AFTER product_id;
```

---

## 8) النشر على Hostinger (Production)

### المسار المقترح
- كود التطبيق: `public_html/app`
- Document Root للدومين الفرعي `app.rankxseo.com`:
```text
/public_html/app/public
```

### نقاط مهمة
- لا ترفع `.env` إلى Git.
- لا تعتمد على `public/storage/stores.json` للنسخة الإنتاجية إذا أنت تستخدم DB (هذا الملف runtime/history).
- فعّل SSL على `app.rankxseo.com`.
- تأكد من سجلات البريد SPF / DKIM / DMARC.

### إن كان عندك 404 في static أو API
تأكد من:
1. أن `public/.htaccess` مرفوع.
2. أن الجذر يشير إلى `.../public`.
3. أن آخر نسخة JS مرفوعة (version query).

---

## 9) فحص الجودة قبل كل نشر

```bash
php -l public/index.php
php -l src/Controllers/ProductController.php
php -l src/Controllers/AdminController.php
node --check public/assets/client-dashboard.js
```

ثم:
```bash
git status
git add .
git commit -m "your message"
git push origin main
```

---

## 10) أخطاء شائعة وحلولها

### `Route not found` في `/api/...`
الأسباب:
1. كود قديم على السيرفر.
2. Document Root خاطئ.
3. `.htaccess` غير مفعل.

الحل:
- deploy آخر نسخة من `main`.
- تأكد الجذر `.../public`.
- اختبر مباشرة:
`/api/operations` و `/api/products`.

### خطأ صلاحيات SEO:
رسالة مثل:
`The access token should have access to metadata.read...`

الحل:
- فعّل Scope `Meta Data` في سلة.
- أعد تثبيت التطبيق على المتجر.

### ALT مرفوض من سلة
بعض متاجر سلة تتشدد في ALT (طول/رموز).
المشروع يطبّق sanitization تلقائي، لكن إذا استمر الرفض:
- قلل الطول
- تجنب الرموز الخاصة
- استخدم نص وصفي بسيط

---

## 11) الأمان

- لا تشارك مفاتيح OpenAI/Salla/SMTP.
- غيّر أي أسرار تم استخدامها أثناء التطوير.
- استخدم كلمات مرور قوية للأدمن.
- استخدم HTTPS فقط في الإنتاج.

---

## 12) ملفات مهمة

- Entry:
  - `index.php`
  - `public/index.php`
- Dashboard UI:
  - `src/Views/client-dashboard.php`
  - `public/assets/client-dashboard.js`
- Core logic:
  - `src/Controllers/ProductController.php`
  - `src/Controllers/AdminController.php`
  - `src/Repositories/SaaSRepository.php`
  - `src/Services/OpenAIClient.php`
  - `src/Services/SallaApiClient.php`
- DB schema:
  - `database/schema.sql`

---

## 13) وثائق إضافية داخل المشروع

- `HOSTINGER_DEPLOYMENT.md`
- `GITHUB_HOSTINGER_DEPLOY.md`

