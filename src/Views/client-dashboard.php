<?php
declare(strict_types=1);
?>
<div class="dashboard-layout" style="display:grid;grid-template-columns:minmax(0,1fr) 340px;gap:20px;align-items:start;">
  <div class="panel-stack">
    <div class="card hero">
      <div class="panel-stack">
        <div>
          <div class="pill">لوحة العميل</div>
          <h1 style="margin:14px 0 12px;">إدارة وصف المنتجات وبيانات السيو من لوحة واحدة</h1>
          <p class="muted">اعرض المنتجات ببطاقات منظمة، صفِّ النتائج، وافتح لكل منتج نافذة مستقلة لتحسين الوصف أو سيو المنتج أو النص البديل للصور ثم احفظ التغييرات مباشرة في سلة.</p>
        </div>
        <div class="chips">
          <span class="chip is-active" style="cursor:default;">تحسين الوصف</span>
          <span class="chip is-active" style="cursor:default;">تحسين السيو</span>
          <span class="chip is-active" style="cursor:default;">ALT الصور</span>
          <span class="chip is-active" style="cursor:default;">حفظ مباشر في سلة</span>
        </div>
      </div>

      <div class="panel-stack">
        <div class="card surface-soft stat">
          <span class="stat-label">اسم المتجر</span>
          <span class="stat-value" style="font-size:28px;"><?= htmlspecialchars($storeName, ENT_QUOTES, 'UTF-8') ?></span>
        </div>

        <div class="grid" style="margin-top:0;">
          <div class="card surface-soft stat">
            <span class="stat-label">Merchant ID</span>
            <span class="stat-value" style="font-size:24px;"><?= htmlspecialchars($merchantId, ENT_QUOTES, 'UTF-8') ?></span>
          </div>
          <div class="card surface-soft stat">
            <span class="stat-label">الحساب</span>
            <span class="stat-value" style="font-size:18px;line-height:1.4;"><?= htmlspecialchars($ownerEmail, ENT_QUOTES, 'UTF-8') ?></span>
          </div>
        </div>

        <div style="display:flex;gap:10px;flex-wrap:wrap;">
          <a class="btn btn-secondary" href="/forgot-password">استرجاع كلمة المرور</a>
          <a class="btn" href="/logout">تسجيل الخروج</a>
        </div>
      </div>
    </div>

    <div id="portal-subscription" class="card">
      <h2>الاشتراك والاستهلاك</h2>
      <p class="muted">جاري تحميل بيانات الاشتراك...</p>
    </div>

    <div id="store-seo-card" class="card">
      <div class="section-head">
        <div>
          <h2 style="margin-bottom:6px;">سيو المتجر</h2>
          <p class="muted" style="margin:0;">عدّل عنوان ووصف المتجر يدويًا أو أنشئهما بالذكاء الاصطناعي ثم احفظ التغييرات مباشرة في سلة.</p>
        </div>
        <div class="inline-actions">
          <button id="generate-store-seo" class="btn btn-sky" type="button">إنشاء بالذكاء الاصطناعي</button>
          <button id="save-store-seo" class="btn" type="button">حفظ التغييرات</button>
        </div>
      </div>
      <div id="store-seo-alert"></div>
      <div class="grid" style="margin-top:0;">
        <div>
          <label for="store-seo-title"><strong>عنوان المتجر</strong></label>
          <input id="store-seo-title" type="text" placeholder="عنوان صفحة المتجر في نتائج البحث">
          <div class="helper-row"><span>مفضل بين 35 و65 حرفًا</span><span id="store-seo-title-count">0 حرف</span></div>
        </div>
        <div>
          <label for="store-seo-keywords"><strong>الكلمات المفتاحية</strong></label>
          <input id="store-seo-keywords" type="text" placeholder="مثال: متجر، عروض، منتجات أصلية">
          <div class="helper-row"><span>افصل الكلمات بفاصلة</span><span id="store-seo-keywords-count">0 حرف</span></div>
        </div>
        <div style="grid-column:1/-1;">
          <label for="store-seo-description"><strong>وصف المتجر</strong></label>
          <textarea id="store-seo-description" rows="5" placeholder="الوصف الذي سيظهر في محركات البحث للمتجر"></textarea>
          <div class="helper-row"><span>مفضل بين 120 و160 حرفًا</span><span id="store-seo-description-count">0 حرف</span></div>
        </div>
      </div>
    </div>

    <div class="card surface-soft">
      <div class="section-head" style="margin-bottom:0;">
        <div>
          <h2 style="margin-bottom:6px;">أقسام إدارة المحتوى</h2>
          <p class="muted" style="margin:0;">تنقّل بين المنتجات وكاتب ALT للصور مع استمرار نفس الفلاتر وطريقة العرض.</p>
        </div>
        <div class="chips" id="dashboard-tabs">
          <button class="chip is-active" type="button" data-dashboard-tab="products">المنتجات</button>
          <button class="chip" type="button" data-dashboard-tab="image-alt">كاتب ALT للصور</button>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="section-head">
        <div>
          <h2 style="margin-bottom:6px;">خيارات التحسين والفلترة</h2>
          <p class="muted" style="margin:0;">اختر النبرة واللغة، ثم استخدم الفلاتر لإظهار المنتجات التي تحتاج تحسينًا أو التنقل بينها على دفعات.</p>
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
          <div class="inline-actions" style="align-self:end;">
            <button id="open-filter-modal" class="btn btn-sky" type="button">فلترة</button>
            <button id="bulk-alt-visible" class="btn" type="button">تحسين ALT للمعروض</button>
            <button id="reset-filters" class="btn btn-secondary" type="button">إعادة ضبط</button>
          </div>
        </div>

        <div class="chips">
          <button class="chip" data-quick-filter="desc_missing" type="button">عرض المنتجات التي ليس لها وصف محسّن</button>
          <button class="chip" data-quick-filter="seo_missing" type="button">عرض المنتجات التي ليس لها SEO محسّن</button>
          <button class="chip" data-quick-filter="alt_missing" type="button">عرض المنتجات التي ينقصها ALT</button>
          <button class="chip" data-quick-filter="all_missing" type="button">عرض المنتجات التي ينقصها الوصف وSEO وALT</button>
          <button class="chip" data-quick-filter="all" type="button">عرض جميع المنتجات</button>
        </div>
      </div>
    </div>

    <section data-tab-panel="products">
      <div class="card">
        <div class="section-head">
          <div>
            <h2 style="margin-bottom:6px;">المنتجات</h2>
            <p id="products-summary" class="muted" style="margin:0;">جاري تجهيز قائمة المنتجات...</p>
          </div>
          <div id="products-pagination-top" class="pagination"></div>
        </div>
        <div id="products-list" class="products-grid"></div>
        <div style="margin-top:22px;">
          <div id="products-pagination-bottom" class="pagination"></div>
        </div>
      </div>
    </section>

    <section data-tab-panel="image-alt" style="display:none;">
      <div class="card">
        <div class="section-head">
          <div>
            <h2 style="margin-bottom:6px;">كاتب ALT للصور</h2>
            <p class="muted" style="margin:0;">اختر منتجًا واحدًا أو افتح محرر الصور لكتابة النص البديل لصورة واحدة أو لجميع صور المنتج ثم احفظ مباشرة في سلة.</p>
          </div>
          <div class="inline-actions">
            <button class="btn" type="button" id="alt-tab-bulk-optimize">تحسين ALT للمعروض</button>
          </div>
        </div>
        <div class="grid" style="margin-top:0;">
          <div class="card surface-soft">
            <strong style="display:block;margin-bottom:8px;">خيارات العمل</strong>
            <p class="muted" style="margin:0;">يمكنك فتح أي منتج لتحسين صورة واحدة أو تحديد عدة صور ثم توليد ALT لها دفعة واحدة قبل الحفظ.</p>
          </div>
          <div class="card surface-soft">
            <strong style="display:block;margin-bottom:8px;">حالة الصور</strong>
            <p class="muted" style="margin:0;">ستظهر لكل منتج شارة توضح إن كان ALT للصور مكتملًا أم لا، مع عدد الصور الجاهزة والمتبقية.</p>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="section-head">
          <div>
            <h2 style="margin-bottom:6px;">منتجات الصور</h2>
            <p id="alt-products-summary" class="muted" style="margin:0;">جاري تجهيز قائمة الصور...</p>
          </div>
          <div id="alt-products-pagination-top" class="pagination"></div>
        </div>
        <div id="alt-products-list" class="products-grid"></div>
        <div style="margin-top:22px;">
          <div id="alt-products-pagination-bottom" class="pagination"></div>
        </div>
      </div>
    </section>
  </div>

  <aside class="panel-stack">
    <div class="card surface-soft" style="position:sticky;top:20px;">
      <div class="section-head" style="margin-bottom:10px;">
        <div>
          <h2 style="margin-bottom:6px;">سجل العمليات</h2>
          <p class="muted" style="margin:0;">آخر عمليات الوصف والسيو وALT التي تم حفظها داخل المتجر.</p>
        </div>
      </div>

      <div class="toolbar" style="margin-bottom:14px;">
        <div class="toolbar-row">
          <div>
            <label for="operations-status-filter"><strong>حالة العملية</strong></label>
            <select id="operations-status-filter">
              <option value="all">الكل</option>
              <option value="completed">مكتمل</option>
              <option value="failed">فشل</option>
              <option value="in_progress">قيد التنفيذ</option>
            </select>
          </div>
          <div>
            <label for="operations-mode-filter"><strong>نوع العملية</strong></label>
            <select id="operations-mode-filter">
              <option value="all">كل العمليات</option>
              <option value="description">وصف المنتج</option>
              <option value="seo">SEO المنتج</option>
              <option value="combo_all">الوصف + SEO</option>
              <option value="image_alt">ALT الصور</option>
              <option value="image_alt_bulk">ALT بالجملة</option>
              <option value="store_seo">سيو المتجر</option>
            </select>
          </div>
        </div>
        <div class="inline-actions" style="justify-content:flex-end;">
          <button id="operations-apply-filter" class="btn btn-sky" type="button">تطبيق</button>
          <button id="operations-show-all" class="btn btn-secondary" type="button">عرض الكل</button>
        </div>
      </div>

      <div id="operations-list">
        <div class="empty-state">
          <p class="muted" style="margin:0;">جاري تحميل العمليات...</p>
        </div>
      </div>
    </div>
  </aside>
</div>

<div id="filter-modal" class="modal-backdrop">
  <div class="modal">
    <div class="modal-head">
      <div>
        <h2 style="margin-bottom:6px;">بحث وفلترة المنتجات</h2>
        <p class="muted" style="margin:0;">ابحث بالاسم أو SKU وحدد الحالة أو نوع المحتوى الناقص لإظهار المنتجات المناسبة.</p>
      </div>
      <button id="close-filter-modal" class="btn btn-secondary" type="button">إغلاق</button>
    </div>
    <div class="grid" style="margin-top:0;">
      <div>
        <label for="filter-name"><strong>بحث باسم المنتج</strong></label>
        <input id="filter-name" type="text" placeholder="اكتب اسم المنتج">
      </div>
      <div>
        <label for="filter-sku"><strong>البحث بواسطة رمز المنتج</strong></label>
        <input id="filter-sku" type="text" placeholder="SKU">
      </div>
      <div>
        <label for="filter-status"><strong>الحالة</strong></label>
        <select id="filter-status">
          <option value="all">جميع الحالات</option>
          <option value="sale">معروض للبيع</option>
          <option value="hidden">مخفي</option>
          <option value="out">غير متوفر في المخزون</option>
        </select>
      </div>
      <div>
        <label for="filter-content"><strong>فلتر المحتوى</strong></label>
        <select id="filter-content">
          <option value="all">من غير فلتر محتوى</option>
          <option value="desc_ready">يوجد وصف محسّن</option>
          <option value="desc_missing">لا يوجد وصف محسّن</option>
          <option value="seo_ready">يوجد SEO محسّن</option>
          <option value="seo_missing">لا يوجد SEO محسّن</option>
          <option value="alt_ready">يوجد ALT محسّن</option>
          <option value="alt_missing">لا يوجد ALT محسّن</option>
          <option value="all_missing">لا يوجد وصف وSEO وALT محسّنة</option>
        </select>
      </div>
    </div>
    <div style="display:flex;justify-content:flex-end;gap:12px;flex-wrap:wrap;margin-top:22px;">
      <button id="apply-filters" class="btn btn-sky" type="button">بحث وفلترة</button>
      <button id="clear-filters" class="btn btn-danger" type="button">تصفية الفلاتر</button>
    </div>
  </div>
</div>

<div id="editor-modal" class="modal-backdrop">
  <div class="modal">
    <div class="modal-head">
      <div>
        <div id="editor-pill" class="pill">تحسين المحتوى</div>
        <h2 id="editor-title" style="margin:10px 0 6px;">جاري تجهيز المنتج...</h2>
        <p id="editor-subtitle" class="muted" style="margin:0;">انتظر قليلًا حتى يكتمل التوليد.</p>
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
        <div class="pill">وصف الصور ALT</div>
        <h2 id="image-alt-title" style="margin:10px 0 6px;">كاتب النص البديل للصور</h2>
        <p id="image-alt-subtitle" class="muted" style="margin:0;">اختر صورة واحدة أو حسّن كل صور المنتج ثم راجع النص قبل الحفظ.</p>
      </div>
      <button id="close-image-alt" class="btn btn-secondary" type="button">إغلاق</button>
    </div>
    <div id="image-alt-alert"></div>
    <div id="image-alt-body">
      <div class="empty-state">
        <p class="muted" style="margin:0;">افتح محرر صور ALT من بطاقة أي منتج.</p>
      </div>
    </div>
  </div>
</div>
