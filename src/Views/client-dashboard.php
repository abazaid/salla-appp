<?php
declare(strict_types=1);

$appBasePath = (string) parse_url((string) \App\Config::get('APP_URL', ''), PHP_URL_PATH);
$appBasePath = rtrim($appBasePath, '/');
if ($appBasePath === '/') {
    $appBasePath = '';
}
?>
<div class="dashboard-shell" data-app-base-path="<?= htmlspecialchars($appBasePath, ENT_QUOTES, 'UTF-8') ?>" data-merchant-id="<?= htmlspecialchars($merchantId, ENT_QUOTES, 'UTF-8') ?>">
  <aside class="card dashboard-sidebar">
    <div>
      <h3 style="margin:0 0 8px;">أدوات المتجر</h3>
      <p class="muted" style="margin:0;">تنقّل بين الأقسام وأدر المحتوى والاشتراك من مكان واحد.</p>
    </div>

    <nav class="sidebar-nav">
      <button type="button" class="sidebar-link is-active" data-section-target="home">الرئيسية</button>
      <button type="button" class="sidebar-link" data-section-target="products">سيو المنتجات</button>
      <button type="button" class="sidebar-link" data-section-target="alt-images">كاتب ALT للصور</button>
      <button type="button" class="sidebar-link" data-section-target="keywords">الكلمات المفتاحية</button>
      <button type="button" class="sidebar-link" data-section-target="domain-seo">سيو الدومين</button>
      <button type="button" class="sidebar-link" data-section-target="store-seo">سيو المتجر</button>
      <button type="button" class="sidebar-link" data-section-target="brand-seo">
        سيو الماركات <span style="background:#F59E0B;color:#fff;padding:2px 8px;border-radius:999px;font-size:11px;">قريباً</span>
      </button>
      <button type="button" class="sidebar-link" data-section-target="category-seo">
        سيو الأقسام <span style="background:#F59E0B;color:#fff;padding:2px 8px;border-radius:999px;font-size:11px;">قريباً</span>
      </button>
      <button type="button" class="sidebar-link" data-section-target="operations">سجل العمليات</button>
      <button type="button" class="sidebar-link" data-section-target="account-settings">الحساب والإعدادات</button>
    </nav>

  </aside>

  <main class="panel-stack">
    <section id="section-home" data-app-section="home" class="panel-stack">
      <div class="card">
        <div class="section-head">
          <div>
            <div class="pill">لوحة العميل</div>
            <h1 style="margin:14px 0 10px;">مرحبًا بك في RankX SEO</h1>
            <p class="muted" style="margin:0;max-width:980px;">
              هذه الصفحة هي نقطة البداية: ستتعرف منها على كل أقسام المنصة، وكيف تستفيد منها خطوة بخطوة.
              اختر القسم المناسب، نفّذ التحسين، راجع النتائج، ثم احفظ مباشرة داخل سلة.
            </p>
          </div>
        </div>
      </div>

      <div class="grid">
        <div class="card surface-soft stat" style="min-height:auto;">
          <span class="stat-label">اسم المتجر</span>
          <span class="stat-value" style="font-size:24px;line-height:1.4;"><?= htmlspecialchars($storeName, ENT_QUOTES, 'UTF-8') ?></span>
        </div>
        <div class="card surface-soft stat" style="min-height:auto;">
          <span class="stat-label">Merchant ID</span>
          <span class="stat-value" style="font-size:22px;"><?= htmlspecialchars($merchantId, ENT_QUOTES, 'UTF-8') ?></span>
        </div>
        <div class="card surface-soft stat" style="min-height:auto;">
          <span class="stat-label">الحساب</span>
          <span class="stat-value" style="font-size:16px;line-height:1.6;"><?= htmlspecialchars($ownerEmail, ENT_QUOTES, 'UTF-8') ?></span>
        </div>
      </div>

      <div class="grid">
        <div class="card surface-soft" style="box-shadow:none;">
          <h3 style="margin:0 0 8px;">قسم سيو المنتجات</h3>
          <p class="muted" style="margin:0 0 12px;">تحسين وصف المنتج أو سيو المنتج أو الاثنين معًا مع مراجعة قبل الحفظ.</p>
          <ul style="margin:0 0 14px;padding-right:18px;line-height:1.9;">
            <li>بحث وفلاتر متقدمة</li>
            <li>تحسين فردي وجماعي</li>
            <li>عرض قبل/بعد ثم حفظ في سلة</li>
          </ul>
          <button class="btn btn-sky" type="button" data-home-go="products">الانتقال إلى سيو المنتجات</button>
        </div>

        <div class="card surface-soft" style="box-shadow:none;">
          <h3 style="margin:0 0 8px;">قسم كاتب ALT للصور</h3>
          <p class="muted" style="margin:0 0 12px;">كتابة نص بديل احترافي للصور لتحسين الظهور في محركات البحث وتجربة المستخدم.</p>
          <ul style="margin:0 0 14px;padding-right:18px;line-height:1.9;">
            <li>تحسين صورة واحدة أو كل صور المنتج</li>
            <li>حالات واضحة: محسّن/غير محسّن</li>
            <li>حفظ ALT مباشرة داخل سلة</li>
          </ul>
          <button class="btn btn-sky" type="button" data-home-go="alt-images">الانتقال إلى كاتب ALT</button>
        </div>

        <div class="card surface-soft" style="box-shadow:none;">
          <h3 style="margin:0 0 8px;">قسم الكلمات المفتاحية</h3>
          <p class="muted" style="margin:0 0 12px;">تحليل الكلمة المستهدفة (حجم البحث، المنافسة، كلمات ذات صلة، اقتراحات).</p>
          <ul style="margin:0 0 14px;padding-right:18px;line-height:1.9;">
            <li>اختيار الدولة واللغة ونوع المتصفح</li>
            <li>قراءة مؤشرات البحث بسرعة</li>
            <li>سجل بحث للرجوع للنتائج السابقة</li>
          </ul>
          <button class="btn btn-sky" type="button" data-home-go="keywords">الانتقال إلى الكلمات المفتاحية</button>
        </div>

        <div class="card surface-soft" style="box-shadow:none;">
          <h3 style="margin:0 0 8px;">قسم سيو الدومين</h3>
          <p class="muted" style="margin:0 0 12px;">تحليل دومين المتجر ومنافسيه لفهم ترتيب الكلمات وفرص النمو.</p>
          <ul style="margin:0 0 14px;padding-right:18px;line-height:1.9;">
            <li>حفظ الدومين بشكل دائم</li>
            <li>تحديث البيانات عند الطلب</li>
            <li>عرض المنافسين وأهم المؤشرات</li>
          </ul>
          <button class="btn btn-sky" type="button" data-home-go="domain-seo">الانتقال إلى سيو الدومين</button>
        </div>

        <div class="card surface-soft" style="box-shadow:none;">
          <h3 style="margin:0 0 8px;">قسم سيو المتجر</h3>
          <p class="muted" style="margin:0 0 12px;">توليد أو تعديل عنوان المتجر، الوصف، والكلمات المفتاحية للموقع بالكامل.</p>
          <ul style="margin:0 0 14px;padding-right:18px;line-height:1.9;">
            <li>قراءة البيانات الحالية</li>
            <li>إنشاء ذكي حسب تعليماتك</li>
            <li>حفظ مباشر في إعدادات سلة</li>
          </ul>
          <button class="btn btn-sky" type="button" data-home-go="store-seo">الانتقال إلى سيو المتجر</button>
        </div>

        <div class="card surface-soft" style="box-shadow:none;opacity:0.7;">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
            <h3 style="margin:0;">قسم سيو الماركات</h3>
            <span style="background:#F59E0B;color:#fff;padding:4px 12px;border-radius:999px;font-size:12px;font-weight:600;">قريباً</span>
          </div>
          <p class="muted" style="margin:0 0 12px;">تحسين وصف الماركات التجارية و Meta Tags بالذكاء الاصطناعي.</p>
          <ul style="margin:0 0 14px;padding-right:18px;line-height:1.9;">
            <li>جلب جميع الماركات من المتجر</li>
            <li>تحسين وصف و Meta Tags</li>
            <li>حفظ مباشر في سلة</li>
          </ul>
          <button class="btn btn-sky" type="button" disabled style="opacity:0.5;cursor:not-allowed;">الانتقال إلى سيو الماركات</button>
        </div>

        <div class="card surface-soft" style="box-shadow:none;opacity:0.7;">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
            <h3 style="margin:0;">قسم سيو الأقسام</h3>
            <span style="background:#F59E0B;color:#fff;padding:4px 12px;border-radius:999px;font-size:12px;font-weight:600;">قريباً</span>
          </div>
          <p class="muted" style="margin:0 0 12px;">تحسين Meta Title و Meta Description لأقسام المتجر بالذكاء الاصطناعي.</p>
          <ul style="margin:0 0 14px;padding-right:18px;line-height:1.9;">
            <li>جلب جميع أقسام المتجر</li>
            <li>تحسين Meta Tags</li>
            <li>حفظ مباشر في سلة</li>
          </ul>
          <button class="btn btn-sky" type="button" disabled style="opacity:0.5;cursor:not-allowed;">الانتقال إلى سيو الأقسام</button>
        </div>

        <div class="card surface-soft" style="box-shadow:none;">
          <h3 style="margin:0 0 8px;">المتابعة والإدارة</h3>
          <p class="muted" style="margin:0 0 12px;">تابع كل العمليات المنفذة، وادخل على الحساب والاشتراك والإعدادات من مكان واحد.</p>
          <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <button class="btn btn-secondary" type="button" data-home-go="operations">سجل العمليات</button>
            <button class="btn btn-secondary" type="button" data-home-go="account-settings">الحساب والإعدادات</button>
          </div>
        </div>
      </div>
    </section>

    <section id="section-products" data-app-section="products" class="panel-stack">
      <div class="card">
        <div class="section-head">
          <div>
            <div class="pill">لوحة العميل</div>
            <h1 style="margin:14px 0 10px;">إدارة المنتجات من لوحة واحدة</h1>
            <p class="muted" style="margin:0;">حسّن وصف المنتج أو سيو المنتج أو الاثنين معًا، ثم راجع قبل الحفظ داخل سلة.</p>
          </div>
        </div>
      </div>

      <details class="card" style="background:#FEF3C7;border:1px solid #FCD34D;margin-bottom:16px;">
        <summary style="cursor:pointer;padding:16px;list-style:none;display:flex;justify-content:space-between;align-items:center;">
          <div style="display:flex;gap:12px;align-items:center;">
            <span style="font-size:20px;">💡</span>
            <div>
              <h3 style="margin:0;color:#92400E;">مهم: الربط الداخلي للمنتجات</h3>
              <p style="margin:4px 0 0;color:#78350F;font-size:13px;">بدون السايت ماب، لن يتم إضافة روابط داخلية للمنتجات.</p>
            </div>
          </div>
          <span style="color:#92400E;font-size:18px;">▼</span>
        </summary>
        <div style="padding:0 16px 16px;">
          <p style="margin:12px 0 8px;color:#78350F;font-size:14px;line-height:1.7;">
            لإضافة روابط داخلية للمنتجات داخل الأوصاف، يجب:
          </p>
          <ol style="margin:0 0 10px;padding-right:20px;color:#78350F;font-size:14px;line-height:1.8;">
            <li>أضف رابط <strong>السايت ماب</strong> في الحقل المخصص</li>
            <li>اضغط على <strong>حفظ روابط السايت ماب</strong></li>
            <li>سيتم جلب الروابط واستخدامها تلقائيًا في الأوصاف</li>
          </ol>
          <div style="margin-top:16px;padding-top:16px;border-top:1px dashed #FCD34D;">
            <label for="setting-sitemap-url"><strong>رابط السايت ماب</strong></label>
            <input id="setting-sitemap-url" type="url" placeholder="https://yourstore.com/sitemap.xml" style="margin-top:8px;width:100%;padding:10px;border-radius:8px;border:1px solid #D97706;">
            <div class="helper-row" style="margin-top:8px;">
              <span>الروابط المحفوظة: <strong id="setting-sitemap-links-count">0</strong></span>
              <span id="setting-sitemap-last-fetched">لم يتم الجلب بعد</span>
            </div>
            <button id="save-sitemap-settings" class="btn btn-sky" type="button" style="margin-top:12px;">حفظ روابط السايت ماب</button>
          </div>
          <div id="sitemap-alert" style="margin-top:12px;"></div>
        </div>
      </details>

      <details class="card" style="margin-bottom:0;">
        <summary style="cursor:pointer;padding:0 0 16px;list-style:none;display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid var(--border-color);margin-bottom:16px;">
          <div>
            <h2 style="margin:0 0 4px;">خيارات التحسين</h2>
            <p class="muted" style="margin:0;font-size:14px;">إعدادات عامة للتوليد لكل متجر. إذا تركت أي حقل فارغًا سيتم تجاوزه.</p>
          </div>
          <div style="display:flex;gap:10px;align-items:center;">
            <button id="save-optimization-settings" class="btn btn-sky" type="button">حفظ التعليمات</button>
            <span style="color:var(--muted);font-size:18px;">▼</span>
          </div>
        </summary>

        <div id="optimization-settings-alert"></div>

        <div class="grid" style="margin-top:0;">
          <div>
            <label for="setting-output-language"><strong>لغة التوليد الأساسية</strong></label>
            <select id="setting-output-language">
              <option value="ar" selected>العربية</option>
              <option value="en">English</option>
            </select>
          </div>
          <div style="grid-column:1/-1;">
            <label for="setting-global-instructions"><strong>تعليمات عامة</strong></label>
            <textarea id="setting-global-instructions" rows="14">اكتب محتوى عربي احترافي موجه للعميل السعودي.
ركّز على مساعدة العميل في اتخاذ قرار الشراء.
اجعل النص:
- واضح
- سهل القراءة
- عملي (يفيد العميل فعليًا)

القواعد:
- لا تنسخ من المنافسين
- لا تخترع معلومات أو مواصفات
- استخدم اسم المنتج + البراند بشكل طبيعي
- ركّز على الفوائد (مو الوصف فقط)
- تجنب الحشو والكلمات الفارغة
- لا تذكر مواقع أو منافسين
- لا تضع روابط خارجية (فقط روابط داخلية)

الهدف:
- رفع التحويل (Conversion)
- تحسين SEO</textarea>
          </div>
          <div style="grid-column:1/-1;">
            <label for="setting-product-description-instructions"><strong>تعليمات وصف المنتج</strong></label>
            <textarea id="setting-product-description-instructions" rows="28">🧩 أهم نقطة: تحديد نوع المنتج

قبل كتابة أي وصف لازم تحدد نوع المنتج:
• ملابس (رجالي / نسائي)
• أحذية
• إكسسوارات
• إلكترونيات
• أدوات منزلية

🧠 قواعد حسب نوع المنتج (عام)

إذا المنتج ملابس:
ركّز على:
- الخامة
- المقاس
- الراحة
- الاستخدام (يومي / رسمي)

إذا المنتج إلكتروني:
ركّز على:
- الأداء
- المواصفات
- الاستخدام العملي

إذا المنتج تجميلي:
ركّز على:
- النتائج
- المكونات
- الأمان

القاعدة الذهبية:
👉 كل نوع له زاوية بيع مختلفة — لا تكتب وصف عام

🧾 وصف المنتج (الزبدة العملية)

الهدف:
- محتوى مقنع + SEO
- يساعد العميل يشتري

الطول:
800 – 1200 كلمة (أو أقل بدون حشو)

🔗 الربط الداخلي (الزبدة)
استخدم 2–3 روابط فقط من نفس المتجر مرتبطة مباشرة بالمنتج
مثال (ملابس):
- رابط فئة (فساتين)
- رابط براند
- رابط منتج مشابه

🧱 هيكل الوصف (مهم جدًا)

1. مقدمة (بدون عنوان)
   - تعريف بالمنتج
   - اسم المنتج
   - البراند
   - أهم ميزة

2. H2: نظرة عامة على المنتج
   - الشركة
   - الفئة
   - الاستخدام

3. H2: أهم المميزات
   - نقاط Bullet فقط

4. H2: المواصفات
   - فقط معلومات مؤكدة

5. H2: التصميم وجودة التصنيع
   - الشكل
   - الخامة
   - الراحة

6. H2: الأداء وتجربة الاستخدام
   (حسب نوع المنتج)
   مثال ملابس:
   - الراحة
   - الحركة
   - الاستخدام اليومي

7. H2: تقييمنا للمنتج
   - رأي واقعي بدون مبالغة

8. H2: طريقة الاستخدام
   - كيف يستخدم المنتج

9. H2: مقارنة مع منتجات مشابهة
   - فرق حقيقي فقط

10. H2: لماذا يختار العملاء هذا المنتج
    - نقاط إقناع

11. H2: لمن يناسب هذا المنتج
    - تحديد الجمهور

12. H2: لماذا تشتري من متجرنا
    - سرعة الشحن
    - جودة
    - ضمان

13. H2: منتجات قد تهمك
    - روابط داخلية فقط

14. H2: الأسئلة الشائعة
    - 5–7 أسئلة حقيقية

⚠️ أهم الأخطاء (لازم تتجنبها)

❌ كتابة وصف عام يصلح لأي منتج
❌ اختراع مواصفات
❌ تكرار الكلمات المفتاحية
❌ حشو بدون فائدة
❌ نسخ من المنافسين</textarea>
          </div>
          <div>
            <label for="setting-meta-title-instructions"><strong>تعليمات Meta Title</strong></label>
            <textarea id="setting-meta-title-instructions" rows="12">🏷️ Meta Title
المطلوب:
- 50-60 حرف
- يبدأ باسم المنتج

الصيغة: اسم المنتج + الفئة + ميزة قوية

مثال (ملابس):
فستان سهرة ساتان نسائي تصميم أنيق وقصة مريحة

مثال (إلكترونيات):
سماعة بلوتوث لاسلكية بجودة صوت عالية وعمر بطارية طويل

تجنب:
- التكرار
- الكلمات المبالغ فيها
- الحشو</textarea>
          </div>
          <div>
            <label for="setting-meta-description-instructions"><strong>تعليمات Meta Description</strong></label>
            <textarea id="setting-meta-description-instructions" rows="12">📝 Meta Description
المطلوب:
- 140-155 حرف
- يحتوي اسم المنتج
- يحفّز على الشراء

الصيغة: اشتري + المنتج + ميزة + فائدة + عنصر ثقة

مثال (ملابس):
اشتري فستان سهرة ساتان نسائي بتصميم أنيق وخامة ناعمة مريحة. مثالي للمناسبات ويوفر لك إطلالة راقية بجودة عالية.

مثال (إلكترونيات):
اشتري سماعة بلوتوث لاسلكية بصوت واضح ونقي مع عزل ضوضاء متقدم. بطارية تدوم 24 ساعة وشحن سريع عبر USB-C.

تجنب:
- التكرار
- الكلمات المبالغ فيها
- الحشو</textarea>
          </div>
          <div>
            <label for="setting-image-alt-instructions"><strong>تعليمات ALT للصور</strong></label>
            <textarea id="setting-image-alt-instructions" rows="12">🖼️ ALT للصور - القاعدة الذهبية:
"كل نوع له زاوية بيع مختلفة"

أمثلة حسب نوع المنتج:
• ملابس: "صورة فستان سهرة نسائي ساتان أرجواني، تصميم سهرة أنيق"
• إلكترونيات: "سماعة بلوتوث لاسلكية بيضاء مع علبة شحن"
• تجميلي: "عبوة كريم مرطب للوجه 50ml بتركيبة فيتامين E"

القواعد:
- دقيق: يصف الصورة بشكل صحيح
- طبيعي: يبدو كجملة عادية
- واضح: يفهم منه محتوى الصورة
- يتضمن اسم المنتج عند الإمكان
- 70-125 حرف تقريبًا</textarea>
          </div>
          <div>
            <label for="setting-store-seo-instructions"><strong>تعليمات سيو المتجر</strong></label>
            <textarea id="setting-store-seo-instructions" rows="4" placeholder="تعليمات خاصة بتوليد عنوان/وصف/كلمات سيو المتجر..."></textarea>
          </div>
        </div>
      </details>

      <div class="card">
        <div class="section-head">
          <div>
            <h2 style="margin:0 0 6px;">خيارات الفلترة</h2>
            <p class="muted" style="margin:0;">فلترة متقدمة + تنقل بالصفحات + تحسين سريع لكل منتج.</p>
          </div>
        </div>

        <div class="toolbar">
          <div class="toolbar-row">
            <div>
              <label for="page-size"><strong>عدد المنتجات في الصفحة</strong></label>
              <select id="page-size">
                <option value="8">8</option>
                <option value="12" selected>12</option>
                <option value="24">24</option>
                <option value="48">48</option>
              </select>
            </div>
          </div>

          <div class="toolbar-row">
            <div>
              <label for="filter-name"><strong>بحث باسم المنتج</strong></label>
              <input id="filter-name" type="text" placeholder="اكتب اسم المنتج">
            </div>
            <div>
              <label for="filter-sku"><strong>بحث برمز المنتج</strong></label>
              <input id="filter-sku" type="text" placeholder="SKU">
            </div>
            <div>
              <label for="filter-status"><strong>الحالة</strong></label>
              <select id="filter-status">
                <option value="all">جميع الحالات</option>
                <option value="sale">معروض للبيع</option>
                <option value="hidden">مخفي</option>
                <option value="out">غير متوفر</option>
              </select>
            </div>
            <div>
              <label for="filter-content"><strong>فلتر المحتوى</strong></label>
              <select id="filter-content">
                <option value="all">بدون فلتر</option>
                <option value="desc_missing">لا يوجد وصف محسّن</option>
                <option value="seo_missing">لا يوجد SEO محسّن</option>
                <option value="all_missing">لا يوجد وصف + SEO محسّن</option>
              </select>
            </div>
          </div>

          <div class="toolbar-row">
            <button id="apply-filters" class="btn btn-sky" type="button">بحث وفلترة</button>
            <button id="clear-filters" class="btn btn-danger" type="button">تصفية الفلاتر</button>
          </div>

          <div class="chips">
            <button class="chip" data-quick-filter="desc_missing" type="button">التي ليس لها وصف محسّن</button>
            <button class="chip" data-quick-filter="seo_missing" type="button">التي ليس لها SEO محسّن</button>
            <button class="chip" data-quick-filter="all_missing" type="button">التي ينقصها الوصف وSEO</button>
            <button class="chip is-active" data-quick-filter="all" type="button">عرض جميع المنتجات</button>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="section-head">
          <div>
            <h2 style="margin:0 0 6px;">المنتجات</h2>
            <p id="products-summary" class="muted" style="margin:0;">جاري تحميل المنتجات...</p>
          </div>
          <div id="products-pagination-top" class="pagination"></div>
        </div>

        <div id="products-list" class="products-grid"></div>

        <div style="margin-top:20px;">
          <div id="products-pagination-bottom" class="pagination"></div>
        </div>
      </div>
    </section>

    <section id="section-brand-seo" data-app-section="brand-seo" class="panel-stack" style="display:none;">
      <div class="card">
        <div class="section-head">
          <div>
            <div class="pill">سيو الماركات</div>
            <h2 style="margin:12px 0 8px;">إدارة SEO الماركات التجارية</h2>
            <p class="muted" style="margin:0;">اختر ماركة وقم بتحسين وصفها و Meta Tags بالذكاء الاصطناعي.</p>
          </div>
          <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <button id="refresh-brands" class="btn btn-secondary" type="button">تحديث القائمة</button>
          </div>
        </div>
        <div id="brand-seo-alert"></div>
        <div class="toolbar" style="margin-top:16px;">
          <div class="toolbar-row">
            <div>
              <label for="brand-filter-name"><strong>بحث باسم الماركة</strong></label>
              <input id="brand-filter-name" type="text" placeholder="اكتب اسم الماركة">
            </div>
            <div>
              <label for="brand-filter-status"><strong>الحالة</strong></label>
              <select id="brand-filter-status">
                <option value="all">جميع الماركات</option>
                <option value="has_description">لها وصف</option>
                <option value="no_description">بدون وصف</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <div id="brands-list"></div>

      <div id="brand-seo-editor" style="display:none;">
        <div class="card">
          <div class="section-head">
            <div>
              <h2 id="brand-editor-title" style="margin:0;">تحرير SEO الماركة</h2>
            </div>
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
              <button id="generate-brand-seo" class="btn btn-sky" type="button">توليد بالذكاء الاصطناعي</button>
              <button id="save-brand-seo" class="btn" type="button">حفظ في المتجر</button>
              <button id="cancel-brand-seo" class="btn btn-secondary" type="button">إلغاء</button>
            </div>
          </div>
          <div id="brand-editor-alert"></div>
          <div class="grid" style="margin-top:16px;">
            <div>
              <label for="brand-current-description"><strong>الوصف الحالي</strong></label>
              <textarea id="brand-current-description" readonly rows="4" style="margin-top:8px;"></textarea>
            </div>
            <div>
              <label for="brand-optimized-description"><strong>الوصف بعد التحسين</strong></label>
              <textarea id="brand-optimized-description" rows="4" style="margin-top:8px;"></textarea>
            </div>
          </div>
          <div class="grid" style="margin-top:16px;">
            <div>
              <label for="brand-current-meta-title"><strong>Meta Title الحالي</strong></label>
              <input id="brand-current-meta-title" type="text" readonly style="margin-top:8px;">
            </div>
            <div>
              <label for="brand-optimized-meta-title"><strong>Meta Title بعد التحسين</strong></label>
              <input id="brand-optimized-meta-title" type="text" style="margin-top:8px;">
            </div>
          </div>
          <div style="margin-top:16px;">
            <label for="brand-current-meta-description"><strong>Meta Description الحالي</strong></label>
            <textarea id="brand-current-meta-description" readonly rows="2" style="margin-top:8px;"></textarea>
          </div>
          <div style="margin-top:16px;">
            <label for="brand-optimized-meta-description"><strong>Meta Description بعد التحسين</strong></label>
            <textarea id="brand-optimized-meta-description" rows="2" style="margin-top:8px;"></textarea>
          </div>
        </div>
      </div>
    </section>

    <section id="section-category-seo" data-app-section="category-seo" class="panel-stack" style="display:none;">
      <div class="card">
        <div class="section-head">
          <div>
            <div class="pill" style="background:#F59E0B;">قريباً</div>
            <h2 style="margin:12px 0 8px;">إدارة SEO الأقسام</h2>
            <p class="muted" style="margin:0;">قسم تحسين Meta Title و Meta Description لأقسام المتجر. قريباً!</p>
          </div>
        </div>
        <div class="card surface-soft" style="box-shadow:none;text-align:center;padding:60px 20px;">
          <div style="font-size:64px;margin-bottom:20px;">🚧</div>
          <h3 style="margin:0 0 12px;">قريباً</h3>
          <p class="muted" style="margin:0;">هذا القسم قيد التطوير وسيكون متاحاً قريباً.</p>
        </div>
      </div>
    </section>

    <section id="section-store-seo" data-app-section="store-seo" class="panel-stack" style="display:none;">
      <div class="card">
        <div class="section-head">
          <div>
            <div class="pill">سيو المتجر</div>
            <h2 style="margin:12px 0 8px;">إعدادات SEO المتجر</h2>
            <p class="muted" style="margin:0;">عدّل عنوان ووصف المتجر يدويًا أو أنشئهما بالذكاء الاصطناعي ثم احفظ مباشرة في سلة.</p>
          </div>
          <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <button id="save-store-seo-instructions" class="btn btn-secondary" type="button">حفظ تعليمات سيو المتجر</button>
            <button id="generate-store-seo" class="btn btn-sky" type="button">إنشاء بالذكاء الاصطناعي</button>
            <button id="save-store-seo" class="btn" type="button">حفظ في المتجر</button>
          </div>
        </div>
        <div id="store-seo-alert"></div>
        <div class="grid" style="margin-top:0;">
          <div>
            <label for="store-seo-title"><strong>عنوان المتجر</strong></label>
            <input id="store-seo-title" type="text" placeholder="عنوان صفحة المتجر في نتائج البحث">
            <div class="helper-row"><span>الموصى به 35-65 حرفًا</span><span id="store-seo-title-count">0 حرف</span></div>
          </div>
          <div>
            <label for="store-seo-keywords"><strong>الكلمات المفتاحية</strong></label>
            <input id="store-seo-keywords" type="text" placeholder="مثال: متجر، عروض، منتجات أصلية">
            <div class="helper-row"><span>افصل الكلمات بفاصلة</span><span id="store-seo-keywords-count">0 حرف</span></div>
          </div>
          <div style="grid-column:1/-1;">
            <label for="store-seo-description"><strong>وصف المتجر</strong></label>
            <textarea id="store-seo-description" rows="6" placeholder="الوصف الذي سيظهر في محركات البحث للمتجر"></textarea>
            <div class="helper-row"><span>الموصى به 120-160 حرفًا</span><span id="store-seo-description-count">0 حرف</span></div>
          </div>
        </div>
      </div>
    </section>

    <section id="section-alt-images" data-app-section="alt-images" class="panel-stack" style="display:none;">
      <div class="card">
        <div class="section-head">
          <div>
            <h2 style="margin:0 0 6px;">خيارات التحسين</h2>
            <p class="muted" style="margin:0;">إعدادات عامة خاصة بتوليد ALT في هذا المتجر. يمكنك ترك أي حقل فارغًا.</p>
          </div>
          <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <button id="save-optimization-settings-alt" class="btn btn-sky" type="button">حفظ التعليمات</button>
          </div>
        </div>
        <div id="optimization-settings-alt-alert"></div>
        <div class="grid" style="margin-top:0;">
          <div>
            <label for="alt-setting-output-language"><strong>لغة التوليد الأساسية</strong></label>
            <select id="alt-setting-output-language">
              <option value="">بدون تحديد</option>
              <option value="ar">العربية</option>
              <option value="en">English</option>
            </select>
          </div>
          <div style="grid-column:1/-1;">
            <label for="alt-setting-global-instructions"><strong>تعليمات عامة</strong></label>
            <textarea id="alt-setting-global-instructions" rows="4" placeholder="تعليمات تنطبق على جميع أنواع التوليد..."></textarea>
          </div>
          <div style="grid-column:1/-1;">
            <label for="alt-setting-image-alt-instructions"><strong>تعليمات ALT للصور</strong></label>
            <textarea id="alt-setting-image-alt-instructions" rows="4" placeholder="اكتب ALT كمحترف سيو: دقيق، طبيعي، وواضح..."></textarea>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="section-head">
          <div>
            <div class="pill">كاتب ALT للصور</div>
            <h2 style="margin:12px 0 8px;">إدارة النص البديل للصور</h2>
            <p class="muted" style="margin:0;">اختر منتجًا وافتح محرر الصور لكتابة ALT يدويًا أو توليده بالذكاء الاصطناعي بصياغة محترف SEO، ثم احفظ في سلة.</p>
          </div>
          <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <button id="alt-optimize-selected-products" class="btn btn-sky" type="button">تحسين ALT للمنتجات المحددة</button>
            <button id="alt-clear-selection" class="btn btn-secondary" type="button">إلغاء التحديد</button>
          </div>
        </div>
        <div id="alt-alert"></div>
        <div class="toolbar" style="margin-top:14px;">
          <div class="toolbar-row">
            <div>
              <label for="alt-filter-name"><strong>بحث باسم المنتج</strong></label>
              <input id="alt-filter-name" type="text" placeholder="اكتب اسم المنتج">
            </div>
            <div>
              <label for="alt-filter-sku"><strong>بحث برمز المنتج</strong></label>
              <input id="alt-filter-sku" type="text" placeholder="SKU">
            </div>
            <div>
              <label for="alt-filter-status"><strong>الحالة</strong></label>
              <select id="alt-filter-status">
                <option value="all">جميع الحالات</option>
                <option value="sale">معروض للبيع</option>
                <option value="hidden">مخفي</option>
                <option value="out">غير متوفر</option>
              </select>
            </div>
            <div>
              <label for="alt-filter-content"><strong>فلتر ALT</strong></label>
              <select id="alt-filter-content">
                <option value="all">بدون فلتر</option>
                <option value="alt_missing">كل الصور بدون ALT</option>
                <option value="alt_ready">كل الصور لها ALT</option>
                <option value="alt_mixed">جزء محسّن + جزء غير محسّن</option>
              </select>
            </div>
          </div>
          <div class="toolbar-row">
            <button id="alt-apply-filters" class="btn btn-sky" type="button">بحث وفلترة</button>
            <button id="alt-clear-filters" class="btn btn-danger" type="button">تصفية الفلاتر</button>
          </div>
          <div class="chips">
            <button class="chip" data-alt-quick-filter="alt_missing" type="button">التي صورها بدون ALT</button>
            <button class="chip" data-alt-quick-filter="alt_ready" type="button">التي صورها ALT محسّن</button>
            <button class="chip" data-alt-quick-filter="alt_mixed" type="button">التي صورها مختلطة</button>
            <button class="chip is-active" data-alt-quick-filter="all" type="button">عرض كل المنتجات</button>
          </div>
        </div>
      </div>
      <div class="card">
        <div class="section-head">
          <div>
            <h2 style="margin:0 0 6px;">منتجات الصور</h2>
            <p id="alt-products-summary" class="muted" style="margin:0;">جاري تجهيز المنتجات...</p>
          </div>
          <div id="alt-products-pagination-top" class="pagination"></div>
        </div>
        <div id="alt-products-list" class="products-grid"></div>
        <div style="margin-top:20px;">
          <div id="alt-products-pagination-bottom" class="pagination"></div>
        </div>
      </div>
    </section>

    <section id="section-keywords" data-app-section="keywords" class="panel-stack" style="display:none;">
      <div class="card">
        <div class="section-head">
          <div>
            <div class="pill">الكلمات المفتاحية</div>
            <h2 style="margin:12px 0 8px;">بحث احترافي عن الكلمات المفتاحية</h2>
            <p class="muted" style="margin:0;">ابحث بالكلمة المفتاحية داخل السعودية مع اختيار نوع الجهاز، ثم راقب أهم مؤشرات السيو والنتائج الأولى.</p>
          </div>
        </div>

        <div class="toolbar">
          <div class="toolbar-row">
            <div>
              <label for="keyword-query"><strong>الكلمة المفتاحية</strong></label>
              <input id="keyword-query" type="text" placeholder="مثال: فساتين نسائية">
            </div>
            <div>
              <label for="keyword-country"><strong>الدولة</strong></label>
              <select id="keyword-country">
                <option value="sa">السعودية</option>
              </select>
            </div>
            <div>
              <label for="keyword-language"><strong>لغة البحث</strong></label>
              <select id="keyword-language">
                <option value="ar">العربية</option>
                <option value="en">English</option>
              </select>
            </div>
            <div>
              <label for="keyword-device"><strong>نوع المتصفح</strong></label>
              <select id="keyword-device">
                <option value="desktop">متصفح كمبيوتر</option>
                <option value="mobile">متصفح جوال</option>
              </select>
            </div>
          </div>
          <div class="toolbar-row">
            <button id="keyword-search-btn" class="btn btn-sky" type="button">بحث الكلمات المفتاحية</button>
          </div>
        </div>

        <div id="keyword-alert"></div>
      </div>

      <div class="card">
        <div class="section-head">
          <div>
            <h2 style="margin:0 0 6px;">تقرير الكلمات المفتاحية</h2>
            <p id="keyword-summary" class="muted" style="margin:0;">أدخل كلمة مفتاحية ثم اضغط بحث.</p>
          </div>
        </div>
        <div id="keyword-results">
          <div class="empty-state"><p class="muted" style="margin:0;">لم يتم إجراء بحث بعد.</p></div>
        </div>
      </div>

      <div class="card">
        <div class="section-head">
          <div>
            <h2 style="margin:0 0 6px;">سجل بحث الكلمات المفتاحية</h2>
            <p class="muted" style="margin:0;">استعرض النتائج السابقة بدون بحث جديد وبدون استهلاك إضافي.</p>
          </div>
        </div>
        <div id="keyword-history-list" class="panel-stack">
          <div class="empty-state"><p class="muted" style="margin:0;">لا يوجد سجل بحث حتى الآن.</p></div>
        </div>
      </div>
    </section>

    <section id="section-domain-seo" data-app-section="domain-seo" class="panel-stack" style="display:none;">
      <div class="card">
        <div class="section-head">
          <div>
            <div class="pill">سيو الدومين</div>
            <h2 style="margin:12px 0 8px;">تحليل الدومين ومنافسين البحث</h2>
            <p class="muted" style="margin:0;">احفظ الدومين مرة واحدة، ثم حدّث البيانات في أي وقت. ستبقى النتائج محفوظة لكل متجر.</p>
          </div>
        </div>

        <div class="toolbar">
          <div class="toolbar-row">
            <div>
              <label for="domain-seo-domain"><strong>الدومين</strong></label>
              <input id="domain-seo-domain" type="text" placeholder="example.com">
            </div>
            <div>
              <label for="domain-seo-country"><strong>الدولة</strong></label>
              <select id="domain-seo-country">
                <option value="sa">السعودية</option>
              </select>
            </div>
            <div>
              <label for="domain-seo-device"><strong>نوع المتصفح</strong></label>
              <select id="domain-seo-device">
                <option value="desktop">متصفح كمبيوتر</option>
                <option value="mobile">متصفح جوال</option>
              </select>
            </div>
          </div>
          <div class="toolbar-row">
            <button id="domain-seo-save-btn" class="btn btn-sky" type="button">حفظ الدومين</button>
            <button id="domain-seo-refresh-btn" class="btn" type="button">تحديث البيانات</button>
          </div>
        </div>

        <div id="domain-seo-alert"></div>
      </div>

      <div class="card">
        <div class="section-head">
          <div>
            <h2 style="margin:0 0 6px;">تقرير سيو الدومين</h2>
            <p id="domain-seo-summary" class="muted" style="margin:0;">احفظ الدومين واضغط تحديث البيانات.</p>
          </div>
        </div>
        <div id="domain-seo-results">
          <div class="empty-state"><p class="muted" style="margin:0;">لا توجد بيانات محفوظة بعد.</p></div>
        </div>
      </div>

      <div class="card">
        <div class="section-head">
          <div>
            <h2 style="margin:0 0 6px;">سجل تحليل الدومين</h2>
            <p class="muted" style="margin:0;">النتائج المحفوظة لكل تحديث سابق، ويمكن استعراضها مباشرة.</p>
          </div>
        </div>
        <div id="domain-seo-history-list" class="panel-stack">
          <div class="empty-state"><p class="muted" style="margin:0;">لا يوجد سجل تحديث للدومين حتى الآن.</p></div>
        </div>
      </div>
    </section>

    <section id="section-operations" data-app-section="operations" class="panel-stack" style="display:none;">
      <div class="card">
        <div class="section-head">
          <div>
            <div class="pill">سجل العمليات</div>
            <h2 style="margin:12px 0 8px;">متابعة العمليات والنتائج</h2>
            <p class="muted" style="margin:0;">فلترة ومراجعة كل عمليات التحسين التي تمت على المنتجات، سيو المتجر، وكاتب ALT.</p>
          </div>
        </div>

        <div class="toolbar">
          <div class="toolbar-row">
            <div>
              <label for="operations-status-filter"><strong>الحالة</strong></label>
              <select id="operations-status-filter">
                <option value="all">الكل</option>
                <option value="completed">مكتمل</option>
                <option value="failed">فشل</option>
                <option value="in_progress">قيد التنفيذ</option>
              </select>
            </div>
            <div>
              <label for="operations-mode-filter"><strong>النوع</strong></label>
              <select id="operations-mode-filter">
                <option value="all">كل العمليات</option>
                <option value="description">وصف المنتج</option>
                <option value="seo">SEO المنتج</option>
                <option value="combo_all">الوصف + SEO</option>
                <option value="store_seo">سيو المتجر</option>
              </select>
            </div>
          </div>
          <div class="toolbar-row">
            <button id="operations-apply-filter" class="btn btn-sky" type="button">تطبيق</button>
            <button id="operations-show-all" class="btn btn-secondary" type="button">عرض الكل</button>
          </div>
        </div>

        <div id="operations-list" class="panel-stack">
          <div class="empty-state"><p class="muted" style="margin:0;">جاري تحميل العمليات...</p></div>
        </div>
      </div>
    </section>

    <section id="section-account-settings" data-app-section="account-settings" class="panel-stack" style="display:none;">
      <div class="card">
        <div class="section-head">
          <div>
            <div class="pill">الحساب والإعدادات</div>
            <h2 style="margin:12px 0 8px;">بيانات الحساب والمتجر</h2>
            <p class="muted" style="margin:0;">بيانات الوصول الأساسية وروابط إدارة الحساب.</p>
          </div>
        </div>
        <div class="grid" style="margin-top:0;">
          <div class="card surface-soft stat" style="min-height:auto;">
            <span class="stat-label">اسم المتجر</span>
            <span class="stat-value" style="font-size:26px;"><?= htmlspecialchars($storeName, ENT_QUOTES, 'UTF-8') ?></span>
          </div>
          <div class="card surface-soft stat" style="min-height:auto;">
            <span class="stat-label">Merchant ID</span>
            <span class="stat-value" style="font-size:20px;"><?= htmlspecialchars($merchantId, ENT_QUOTES, 'UTF-8') ?></span>
          </div>
          <div class="card surface-soft stat" style="min-height:auto;">
            <span class="stat-label">الحساب</span>
            <span class="stat-value" style="font-size:16px;line-height:1.5;"><?= htmlspecialchars($ownerEmail, ENT_QUOTES, 'UTF-8') ?></span>
          </div>
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:14px;">
          <a class="btn btn-secondary" href="<?= htmlspecialchars($appBasePath, ENT_QUOTES, 'UTF-8') ?>/forgot-password">استرجاع كلمة المرور</a>
          <a class="btn" href="<?= htmlspecialchars($appBasePath, ENT_QUOTES, 'UTF-8') ?>/logout">تسجيل الخروج</a>
        </div>


      </div>

      <div id="usage-card" class="card">
        <h2 style="margin:0 0 8px;">الاشتراك والاستهلاك</h2>
        <p class="muted" style="margin:0;">جاري تحميل بيانات الاستهلاك...</p>
      </div>
    </section>
  </main>
</div>

<div id="editor-modal" class="modal-backdrop">
  <div class="modal">
    <div class="modal-head">
      <div>
        <div id="editor-pill" class="pill">تحسين المحتوى</div>
        <h2 id="editor-title" style="margin:10px 0 6px;">جاري تجهيز المنتج...</h2>
        <p id="editor-subtitle" class="muted" style="margin:0;">انتظر قليلًا حتى يكتمل توليد النسخة الجديدة.</p>
      </div>
      <button id="close-editor" class="btn btn-secondary" type="button">إغلاق</button>
    </div>
    <div id="editor-alert"></div>
    <div id="editor-body">
      <div class="empty-state">
        <p class="muted" style="margin:0;">اختر منتجًا من القائمة لبدء التحسين.</p>
      </div>
    </div>
  </div>
</div>

<div id="image-alt-modal" class="modal-backdrop">
  <div class="modal">
    <div class="modal-head">
      <div>
        <div class="pill">ALT الصور</div>
        <h2 id="image-alt-title" style="margin:10px 0 6px;">كاتب النص البديل</h2>
        <p id="image-alt-subtitle" class="muted" style="margin:0;">اكتب وصف ALT كمحترف سيو: وصف واضح ودقيق (بحد أقصى 70 حرفًا) ثم احفظه في المتجر.</p>
      </div>
      <button id="close-image-alt" class="btn btn-secondary" type="button">إغلاق</button>
    </div>
    <div id="image-alt-alert"></div>
    <div id="image-alt-body">
      <div class="empty-state">
        <p class="muted" style="margin:0;">اختر منتجًا لبدء تعديل ALT.</p>
      </div>
    </div>
  </div>
</div>

<script>
  (function () {
    var base = <?= json_encode($appBasePath, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?> || '';
    var jsVersion = <?= json_encode((string) filemtime(__DIR__ . '/../../public/assets/client-dashboard.js')) ?>;
    var candidates = [
      base + '/public/assets/client-dashboard.js?v=' + jsVersion,
      base + '/assets/client-dashboard.js?v=' + jsVersion
    ];
    var index = 0;

    function loadNext() {
      if (index >= candidates.length) {
        console.error('Failed to load client-dashboard.js from all known paths.');
        return;
      }

      var script = document.createElement('script');
      script.defer = true;
      script.src = candidates[index];
      script.onerror = function () {
        index += 1;
        loadNext();
      };
      document.body.appendChild(script);
    }

    loadNext();
  })();
</script>
