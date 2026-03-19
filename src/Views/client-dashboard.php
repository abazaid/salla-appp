<?php
declare(strict_types=1);
?>
<div class="dashboard-shell">
  <aside class="card dashboard-sidebar">
    <div>
      <h3 style="margin:0 0 8px;">أدوات المتجر</h3>
      <p class="muted" style="margin:0;">تنقّل بين الأقسام. سننجز الآن قسم المنتجات بالكامل.</p>
    </div>

    <nav class="sidebar-nav">
      <button type="button" class="sidebar-link is-active" data-section-target="products">المنتجات</button>
      <button type="button" class="sidebar-link" data-section-target="store-seo">سيو المتجر</button>
      <button type="button" class="sidebar-link" data-section-target="alt-images">كاتب ALT للصور</button>
      <button type="button" class="sidebar-link" data-section-target="usage">الاستهلاك</button>
    </nav>

    <hr style="border:none;border-top:1px solid rgba(202,177,149,.45);margin:0;">

    <div class="panel-stack">
      <h3 style="margin:0;">سجل العمليات</h3>
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
          <div class="panel-stack" style="min-width:300px;">
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

    <section id="section-store-seo" data-app-section="store-seo" class="card" style="display:none;">
      <h2>سيو المتجر</h2>
      <p class="muted">هذا القسم سنبنيه في الخطوة التالية مباشرة بعد تثبيت قسم المنتجات.</p>
    </section>

    <section id="section-alt-images" data-app-section="alt-images" class="card" style="display:none;">
      <h2>كاتب ALT للصور</h2>
      <p class="muted">هذا القسم سنبنيه في الخطوة التي تلي سيو المتجر.</p>
    </section>

    <section id="section-usage" data-app-section="usage" class="card" style="display:none;">
      <h2>الاستهلاك</h2>
      <p class="muted">هذا القسم سنفصله بشكل مستقل بعد ALT.</p>
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

<script defer src="/assets/client-dashboard.js?v=products-v2"></script>
