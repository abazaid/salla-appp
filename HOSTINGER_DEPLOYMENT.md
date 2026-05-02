# RankX SEO Deployment On Hostinger

هذا الملف يجهزك لرفع المشروع إلى:

- `https://rankxseo.com`
- على استضافة `Hostinger`

## 1. الدومين

- الموقع والمنصة على `rankxseo.com`

## 2. قاعدة البيانات

1. أنشئ MySQL جديدة من لوحة Hostinger.
2. استورد الملف:

```text
database/schema.sql
```

## 3. ملفات البيئة

حدّث `.env` في السيرفر إلى قيم production مثل:

```env
APP_NAME="RankX SEO"
APP_ENV=production
APP_URL=https://rankxseo.com
APP_KEY=change-this

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_hostinger_db
DB_USERNAME=your_hostinger_user
DB_PASSWORD=your_hostinger_password

ADMIN_EMAIL=seo@rankxseo.com
ADMIN_PASSWORD=change-this

MAIL_FROM_ADDRESS=seo@rankxseo.com
MAIL_FROM_NAME="رانكس سيو"
MAIL_RESET_SUBJECT="تعيين كلمة المرور"
SMTP_HOST=smtp.hostinger.com
SMTP_PORT=587
SMTP_USERNAME=seo@rankxseo.com
SMTP_PASSWORD=your_hostinger_email_password
SMTP_ENCRYPTION=tls

OPENAI_API_KEY=your_openai_key
OPENAI_MODEL=gpt-5-mini
OPENAI_REASONING_EFFORT=low
```

## 4. سلة

حدّث داخل Partner Portal:

- `Webhook URL`

```text
https://rankxseo.com/webhooks/salla
```

- `Embedded Page URL`

```text
https://rankxseo.com/embedded
```

## 5. البريد

في Hostinger تأكد من ضبط:

- SPF
- DKIM
- DMARC

## 6. الأمن

- غيّر كل المفاتيح التي استُخدمت في الاختبار
- لا ترفع `.env` إلى Git
- استخدم HTTPS فقط
