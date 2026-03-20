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
      <button type="button" class="sidebar-link is-active" data-section-target="products">المنتجات</button>
      <button type="button" class="sidebar-link" data-section-target="store-seo">سيو المتجر</button>
      <button type="button" class="sidebar-link" data-section-target="alt-images">كاتب ALT للصور</button>
      <button type="button" class="sidebar-link" data-section-target="operations">سجل العمليات</button>
      <button type="button" class="sidebar-link" data-section-target="account-settings">الحساب والإعدادات</button>
    </nav>

  </aside>

  <main class="panel-stack">
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
            <h2 style="margin:0 0 6px;">خيارات التحسين والفلترة</h2>
            <p class="muted" style="margin:0;">فلترة متقدمة + تنقل بالصفحات + تحسين سريع لكل منتج.</p>
          </div>
        </div>

        <div class="toolbar">
          <div class="toolbar-row">
            <div>
              <label for="tone"><strong>نبرة الوصف</strong></label>
              <select id="tone">
                <option value="احترافي مقنع">احترافي مقنع</option>
                <option value="فاخر أنيق">فاخر أنيق</option>
                <option value="عملي مباشر">عملي مباشر</option>
                <option value="ودود بسيط">ودود بسيط</option>
              </select>
            </div>
            <div>
              <label for="language"><strong>لغة الإخراج</strong></label>
              <select id="language">
                <option value="ar">العربية</option>
                <option value="en">English</option>
              </select>
            </div>
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

    <section id="section-store-seo" data-app-section="store-seo" class="panel-stack" style="display:none;">
      <div class="card">
        <div class="section-head">
          <div>
            <div class="pill">سيو المتجر</div>
            <h2 style="margin:12px 0 8px;">إعدادات SEO المتجر</h2>
            <p class="muted" style="margin:0;">عدّل عنوان ووصف المتجر يدويًا أو أنشئهما بالذكاء الاصطناعي ثم احفظ مباشرة في سلة.</p>
          </div>
          <div style="display:flex;gap:10px;flex-wrap:wrap;">
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
        <p id="image-alt-subtitle" class="muted" style="margin:0;">اكتب وصف ALT كمحترف سيو: وصف واضح ودقيق (بحد أقصى 60 حرفًا) ثم احفظه في المتجر.</p>
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
      base + '/public/assets/client-dashboard.js?v=products-v17',
      base + '/assets/client-dashboard.js?v=products-v17'
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
