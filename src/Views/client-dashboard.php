<?php
declare(strict_types=1);

$appBasePath = (string) parse_url((string) \App\Config::get('APP_URL', ''), PHP_URL_PATH);
$appBasePath = rtrim($appBasePath, '/');
if ($appBasePath === '/') {
    $appBasePath = '';
}
?>
<div class="dashboard-shell" data-app-base-path="<?= htmlspecialchars($appBasePath, ENT_QUOTES, 'UTF-8') ?>">
  <aside class="card dashboard-sidebar">
    <div>
      <h3 style="margin:0 0 8px;">أدوات المتجر</h3>
      <p class="muted" style="margin:0;">تنقّل بين الأقسام وأدر المحتوى والاشتراك من مكان واحد.</p>
    </div>

    <nav class="sidebar-nav">
      <button type="button" class="sidebar-link is-active" data-section-target="home">الرئيسية</button>
      <button type="button" class="sidebar-link" data-section-target="products">المنتجات</button>

      <button type="button" class="sidebar-link" data-section-target="alt-images">كاتب ALT للصور</button>
      <button type="button" class="sidebar-link" data-section-target="keywords">الكلمات المفتاحية</button>
      <button type="button" class="sidebar-link" data-section-target="domain-seo">سيو الدومين</button>
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
          <h3 style="margin:0 0 8px;">قسم المنتجات</h3>
          <p class="muted" style="margin:0 0 12px;">تحسين وصف المنتج أو سيو المنتج أو الاثنين معًا مع مراجعة قبل الحفظ.</p>
          <ul style="margin:0 0 14px;padding-right:18px;line-height:1.9;">
            <li>بحث وفلاتر متقدمة</li>
            <li>تحسين فردي وجماعي</li>
            <li>عرض قبل/بعد ثم حفظ في سلة</li>
          </ul>
          <button class="btn btn-sky" type="button" data-home-go="products">الانتقال إلى المنتجات</button>
        </div>

        <div class="card surface-soft" style="box-shadow:none;">
          <h3 style="margin:0 0 8px;">قسم سيو المتجر</h3>
          <p class="muted" style="margin:0 0 12px;">توليد أو تعديل عنوان المتجر، الوصف، والكلمات المفتاحية للموقع بالكامل.</p>
          <ul style="margin:0 0 14px;padding-right:18px;line-height:1.9;">
            <li>قراءة البيانات الحالية</li>
            <li>إنشاء ذكي حسب تعليماتك</li>
            <li>حفظ مباشر في إعدادات سلة</li>
          </ul>

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

      <div class="card">
        <div class="section-head">
          <div>
            <h2 style="margin:0 0 6px;">خيارات التحسين</h2>
            <p class="muted" style="margin:0;">إعدادات عامة للتوليد لكل متجر. إذا تركت أي حقل فارغًا سيتم تجاوزه.</p>
          </div>
          <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <button id="save-optimization-settings" class="btn btn-sky" type="button">حفظ التعليمات</button>
          </div>
        </div>

        <div id="optimization-settings-alert"></div>

        <div class="grid" style="margin-top:0;">
          <div>
            <label for="setting-output-language"><strong>لغة التوليد الأساسية</strong></label>
            <select id="setting-output-language">
              <option value="">بدون تحديد</option>
              <option value="ar">العربية</option>
              <option value="en">English</option>
            </select>
          </div>
          <div style="grid-column:1/-1;">
            <label for="setting-global-instructions"><strong>تعليمات عامة</strong></label>
            <textarea id="setting-global-instructions" rows="4" placeholder="تعليمات تنطبق على جميع أنواع التوليد..."></textarea>
          </div>
          <div style="grid-column:1/-1;">
            <label for="setting-product-description-instructions"><strong>تعليمات وصف المنتج</strong></label>
            <textarea id="setting-product-description-instructions" rows="4" placeholder="تعليمات خاصة بتوليد وصف المنتج..."></textarea>
          </div>
          <div>
            <label for="setting-meta-title-instructions"><strong>تعليمات Meta Title</strong></label>
            <textarea id="setting-meta-title-instructions" rows="4" placeholder="تعليمات خاصة بعنوان الميتا..."></textarea>
          </div>
          <div>
            <label for="setting-meta-description-instructions"><strong>تعليمات Meta Description</strong></label>
            <textarea id="setting-meta-description-instructions" rows="4" placeholder="تعليمات خاصة بوصف الميتا..."></textarea>
          </div>
          <div>
            <label for="setting-image-alt-instructions"><strong>تعليمات ALT للصور</strong></label>
            <textarea id="setting-image-alt-instructions" rows="4" placeholder="اكتب ALT كمحترف سيو: دقيق، طبيعي، وواضح..."></textarea>
          </div>
          <div>

          </div>
          <div>
            <label for="setting-sitemap-url"><strong>رابط السايت ماب للروابط الداخلية</strong></label>
            <input id="setting-sitemap-url" type="url" placeholder="https://your-store.com/sitemap.xml">
            <div class="helper-row">
              <span id="setting-sitemap-links-count">0 روابط</span>
              <span id="setting-sitemap-last-fetched">لم يتم الجلب بعد</span>
            </div>
          </div>
        </div>
      </div>

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
    var candidates = [
      base + '/public/assets/client-dashboard.js?v=products-v30',
      base + '/assets/client-dashboard.js?v=products-v30'
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
