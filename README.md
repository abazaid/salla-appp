# Salla Description Optimizer

هذا المشروع نواة أولية لتطبيق داخل سلة يساعد المتاجر على تحسين وصف المنتجات ورفعه مباشرة إلى المتجر.

## ماذا يفعل؟

- يربط المتجر مع سلة عبر OAuth.
- يستقبل Webhooks من سلة ويتحقق من توقيعها.
- يعرض صفحة مدمجة داخل لوحة التاجر.
- يقرأ المنتجات من Salla Merchant API.
- يرسل بيانات المنتج إلى OpenAI ويقترح وصفًا محسّنًا ثم يحدّث `description` في سلة.
- يخزن إعدادات خاصة بكل متجر مثل نبرة الكتابة واللغة.
- يتتبع عدد المنتجات المحسنة ضمن حصة الاشتراك لكل متجر.

## لماذا هذا الهيكل مناسب لسلة؟

- سلة تعتمد على OAuth 2.0 لتثبيت التطبيق ومنحه صلاحيات المتجر.
- التطبيقات المنشورة تعتمد على Easy Mode للتفويض عند النشر في متجر تطبيقات سلة.
- تحديث وصف المنتج يتم عبر `PUT /products/{product}` مع صلاحية `products.read_write`.
- إعدادات التطبيق لكل متجر يمكن قراءتها وكتابتها عبر App Settings API.
- الواجهة المدمجة داخل لوحة سلة يمكن تحميلها عبر Embedded Pages داخل Partner Portal.
- عداد الاستخدام يمكن ربطه بأحداث اشتراك التطبيق من سلة مثل `app.subscription.started` و`app.subscription.renewed`.

## التوثيق الذي بني عليه هذا المشروع

- Welcome to Salla CLI: https://docs.salla.dev/429774m0
- Authorization: https://docs.salla.dev/421118m0
- Salla Webhooks: https://docs.salla.dev/421119m0
- Get Started - Partner Apps API: https://docs.salla.dev/doc-421117
- App Setting Details: https://docs.salla.dev/5401096e0
- App Events: https://docs.salla.dev/421413m0
- Create an Embedded App: https://docs.salla.dev/embedded-sdk/getting-started/create-app
- List Products: https://docs.salla.dev/5394168e0
- Product Details: https://docs.salla.dev/5394169e0
- Update Product: https://docs.salla.dev/5394170e0

## التشغيل المحلي

1. انسخ الملف:

```bash
copy .env.example .env
```

2. حدّث القيم داخل `.env`.

أهم القيم:

- `AI_PROVIDER=openai`
- `OPENAI_API_KEY` مفتاح OpenAI الخاص بك
- `OPENAI_MODEL=gpt-5-mini`
- `OPENAI_REASONING_EFFORT=low`

3. شغّل السيرفر المحلي:

```bash
php -S localhost:8000 -t public
```

4. افتح:

```text
http://localhost:8000
```

## ما الذي تحتاج تضبطه في Salla Partners Portal؟

1. أنشئ التطبيق داخل Partner Portal.
2. فعّل الصلاحيات المطلوبة، وأهمها `products.read_write`.
3. عرّف `Redirect URL` على:

```text
http://localhost:8000/oauth/callback
```

4. عرّف Webhook URL على:

```text
http://localhost:8000/webhooks/salla
```

5. أضف Embedded Page مثل:

```text
Route Slug: optimizer
Iframe URL: http://localhost:8000/embedded
```

6. في التطبيق الحقيقي، انقل هذه الروابط إلى دومين HTTPS عام.

## المسارات المتوفرة

- `GET /` صفحة تعريفية سريعة
- `GET /oauth/callback` استقبال بيانات الربط من سلة
- `POST /webhooks/salla` استقبال Webhooks
- `GET /embedded` واجهة مدمجة داخل لوحة التاجر
- `GET /api/products` سحب المنتجات من سلة
- `GET /api/subscription` حالة الاشتراك واستهلاك الحصة
- `POST /api/products/{id}/optimize` تحسين وصف منتج ورفعه إلى سلة

## ملاحظات مهمة قبل البيع في متجر تطبيقات سلة

- هذا الـ starter يستخدم `GET /oauth/callback` لتبسيط التطوير المحلي. لكن عند تجهيز النسخة التجارية داخل سلة، راجع `Easy Mode` وحدث منطق التثبيت ليستقبل `app.store.authorize` عبر webhook كما توصي الوثائق.
- لا تجعل التحديث تلقائيًا بالكامل في البداية. ابدأ بـ "اقتراح ثم اعتماد" لتقليل المخاطر على التجار.
- احتفظ بسجل للتغييرات حتى يمكن التراجع عن الوصف السابق.
- إذا استخدمت AI خارجيًا، وضّح سياسة الخصوصية وطريقة معالجة بيانات المنتج.
- اربط الفوترة لاحقًا حسب باقة التاجر أو عدد المنتجات المحسنة شهريًا.
- خصم الحصة في هذا المشروع يتم فقط بعد نجاح توليد الوصف وتحديث المنتج في سلة.

## الخطوة التالية المقترحة

أقرب تطوير منطقي بعد هذا الـ starter:

1. إضافة شاشة مراجعة فعلية داخل embedded page قبل اعتماد التحديث.
2. إضافة قاعدة بيانات بدل التخزين المحلي JSON.
3. إضافة شاشة مراجعة جماعية للمنتجات قبل النشر.
4. ربط أحداث مثل إنشاء/تحديث المنتج لتوليد اقتراحات ذكية.

## مراجع OpenAI الرسمية

- Responses API للمشاريع الجديدة: https://platform.openai.com/docs/guides/chat-completions
- دليل النماذج: https://developers.openai.com/api/docs/models
- نموذج `gpt-5-mini`: https://developers.openai.com/api/docs/models/gpt-5-mini
