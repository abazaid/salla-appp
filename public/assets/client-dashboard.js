(function () {
  if (window.__CLIENT_DASHBOARD_PRODUCTS_INIT__) {
    return;
  }
  window.__CLIENT_DASHBOARD_PRODUCTS_INIT__ = true;
  const state = {
    products: [],
    page: 1,
    pageSize: 12,
    filters: {
      name: '',
      sku: '',
      status: 'all',
      content: 'all'
    },
    quickFilter: 'all',
    loadingProductId: null,
    modalLoading: false,
    editor: null
  };

  function escapeHtml(value) {
    return String(value ?? '')
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;')
      .replaceAll("'", '&#039;');
  }

  function stripHtml(html) {
    const div = document.createElement('div');
    div.innerHTML = html || '';
    return (div.textContent || div.innerText || '').trim();
  }

  function normalizeText(value) {
    return String(value ?? '').toLowerCase().trim();
  }

  function isDescriptionOptimized(product) {
    return stripHtml(product.description || '').length >= 120;
  }

  function isSeoOptimized(product) {
    const title = String(product.metadata?.title || '').trim();
    const description = String(product.metadata?.description || '').trim();
    return title.length >= 20 && description.length >= 80;
  }

  function getStatusLabel(product) {
    if (product.status === 'hidden') return 'مخفي';
    if (product.status === 'sale') return 'معروض للبيع';
    if (product.is_available === false) return 'غير متوفر';
    return product.status || 'غير محدد';
  }

  function getModeLabel(mode) {
    if (mode === 'description') return 'تحسين وصف المنتج';
    if (mode === 'seo') return 'تحسين SEO المنتج';
    return 'تحسين كامل';
  }

  function getModeHelp(mode) {
    if (mode === 'description') return 'سيظهر الوصف القديم والجديد مع إمكانية تعديل يدوي قبل الحفظ.';
    if (mode === 'seo') return 'سيظهر Meta Title وMeta Description قبل وبعد مع تعديل يدوي كامل.';
    return 'سيظهر الوصف والـ Meta قبل وبعد في نفس النافذة مع إمكانية تعديل كل الحقول.';
  }

  function getProductById(productId) {
    return state.products.find((product) => Number(product.id) === Number(productId)) || null;
  }

  function getFilteredProducts() {
    const nameFilter = normalizeText(state.filters.name);
    const skuFilter = normalizeText(state.filters.sku);
    const statusFilter = state.filters.status;
    const contentFilter = state.quickFilter !== 'all' ? state.quickFilter : state.filters.content;

    return state.products.filter((product) => {
      const name = normalizeText(product.name);
      const sku = normalizeText(product.sku);
      const descriptionReady = isDescriptionOptimized(product);
      const seoReady = isSeoOptimized(product);
      const isOut = product.is_available === false || Number(product.quantity ?? 1) === 0;

      if (nameFilter && !name.includes(nameFilter)) return false;
      if (skuFilter && !sku.includes(skuFilter)) return false;
      if (statusFilter === 'sale' && product.status !== 'sale') return false;
      if (statusFilter === 'hidden' && product.status !== 'hidden') return false;
      if (statusFilter === 'out' && !isOut) return false;
      if (contentFilter === 'desc_missing' && descriptionReady) return false;
      if (contentFilter === 'seo_missing' && seoReady) return false;
      if (contentFilter === 'all_missing' && (descriptionReady || seoReady)) return false;

      return true;
    });
  }

  function getPagedProducts() {
    const filtered = getFilteredProducts();
    const totalPages = Math.max(1, Math.ceil(filtered.length / state.pageSize));

    if (state.page > totalPages) state.page = totalPages;

    const offset = (state.page - 1) * state.pageSize;
    return {
      filtered,
      totalPages,
      items: filtered.slice(offset, offset + state.pageSize),
      from: filtered.length ? offset + 1 : 0,
      to: Math.min(offset + state.pageSize, filtered.length)
    };
  }

  function renderPagination(containerId, totalPages) {
    const root = document.getElementById(containerId);
    if (!root) return;

    if (totalPages <= 1) {
      root.innerHTML = '';
      return;
    }

    const start = Math.max(1, state.page - 2);
    const end = Math.min(totalPages, state.page + 2);
    let html = `<button type="button" ${state.page === 1 ? 'disabled' : ''} data-page="${state.page - 1}">‹</button>`;

    for (let page = start; page <= end; page += 1) {
      html += `<button type="button" class="${page === state.page ? 'is-active' : ''}" data-page="${page}">${page}</button>`;
    }

    html += `<button type="button" ${state.page === totalPages ? 'disabled' : ''} data-page="${state.page + 1}">›</button>`;
    root.innerHTML = html;

    root.querySelectorAll('[data-page]').forEach((button) => {
      button.addEventListener('click', () => {
        state.page = Number(button.dataset.page || 1);
        renderProducts();
        window.scrollTo({ top: 0, behavior: 'smooth' });
      });
    });
  }

  function buildProductCard(product) {
    const descriptionReady = isDescriptionOptimized(product);
    const seoReady = isSeoOptimized(product);
    const image = product.thumbnail || product.main_image || '';
    const preview = stripHtml(product.description || '').slice(0, 140) || 'لا يوجد وصف حالي لهذا المنتج.';
    const isLoading = state.loadingProductId === product.id;

    return `
      <article class="product-card">
        <div class="product-badges">
          <span class="status-badge ${descriptionReady ? 'success' : 'danger'}">${descriptionReady ? 'وصف محسّن' : 'وصف غير محسّن'}</span>
          <span class="status-badge ${seoReady ? 'success' : 'danger'}">${seoReady ? 'SEO محسّن' : 'SEO غير محسّن'}</span>
        </div>
        <img class="product-thumb" src="${escapeHtml(image)}" alt="${escapeHtml(product.name)}">
        <div>
          <h3 class="product-title">${escapeHtml(product.name)}</h3>
          <div class="meta-list">
            <span>SKU: <code>${escapeHtml(product.sku || '-')}</code></span>
            <span>الحالة: <strong>${escapeHtml(getStatusLabel(product))}</strong></span>
          </div>
        </div>
        <p class="muted" style="margin:0;">${escapeHtml(preview)}</p>
        <div class="product-actions">
          <button class="btn" type="button" ${isLoading ? 'disabled' : ''} data-action="description" data-id="${Number(product.id)}">${isLoading ? 'جاري التحضير...' : 'تحسين الوصف'}</button>
          <button class="btn btn-sky" type="button" ${isLoading ? 'disabled' : ''} data-action="seo" data-id="${Number(product.id)}">تحسين السيو</button>
          <button class="btn btn-secondary" type="button" ${isLoading ? 'disabled' : ''} data-action="all" data-id="${Number(product.id)}">تحسين الكل</button>
        </div>
      </article>
    `;
  }

  function renderProducts() {
    const root = document.getElementById('products-list');
    const summary = document.getElementById('products-summary');
    if (!root || !summary) return;

    const { filtered, totalPages, items, from, to } = getPagedProducts();

    document.querySelectorAll('[data-quick-filter]').forEach((chip) => {
      chip.classList.toggle('is-active', chip.dataset.quickFilter === state.quickFilter);
    });

    summary.textContent = filtered.length
      ? `عرض ${from} إلى ${to} من أصل ${filtered.length} منتج`
      : 'لا توجد نتائج مطابقة للفلاتر الحالية.';

    if (!items.length) {
      root.innerHTML = `
        <div class="empty-state" style="grid-column:1/-1;">
          <h3>لا توجد منتجات مطابقة</h3>
          <p class="muted">جرّب إزالة بعض الفلاتر أو تغيير نوع الفلترة.</p>
        </div>
      `;
      renderPagination('products-pagination-top', totalPages);
      renderPagination('products-pagination-bottom', totalPages);
      return;
    }

    root.innerHTML = items.map(buildProductCard).join('');
    root.querySelectorAll('[data-action]').forEach((button) => {
      button.addEventListener('click', () => {
        openOptimization(Number(button.dataset.id), button.dataset.action || 'all');
      });
    });

    renderPagination('products-pagination-top', totalPages);
    renderPagination('products-pagination-bottom', totalPages);
  }

  function renderEditorBody() {
    const root = document.getElementById('editor-body');
    const alertRoot = document.getElementById('editor-alert');
    const pill = document.getElementById('editor-pill');
    const title = document.getElementById('editor-title');
    const subtitle = document.getElementById('editor-subtitle');

    if (!root || !alertRoot || !pill || !title || !subtitle) return;

    if (state.modalLoading) {
      pill.textContent = 'جاري التحضير';
      title.textContent = 'جاري تجهيز المحتوى...';
      subtitle.textContent = 'انتظر حتى يكتمل توليد النسخة الجديدة.';
      alertRoot.innerHTML = '';
      root.innerHTML = '<div class="empty-state"><p class="muted">جاري توليد المحتوى...</p></div>';
      return;
    }

    const editor = state.editor;
    if (!editor) {
      pill.textContent = 'تحسين المحتوى';
      title.textContent = 'اختر منتجًا';
      subtitle.textContent = 'افتح نافذة التحسين من بطاقة أي منتج.';
      alertRoot.innerHTML = '';
      root.innerHTML = '<div class="empty-state"><p class="muted">لا يوجد محتوى مفتوح الآن.</p></div>';
      return;
    }

    pill.textContent = getModeLabel(editor.mode);
    title.textContent = editor.product.name;
    subtitle.textContent = getModeHelp(editor.mode);
    alertRoot.innerHTML = editor.notice ? `<div class="notice ${editor.notice.type}">${escapeHtml(editor.notice.message)}</div>` : '';

    const showDescription = editor.mode === 'description' || editor.mode === 'all';
    const showSeo = editor.mode === 'seo' || editor.mode === 'all';
    let html = '';

    if (showDescription) {
      html += `
        <div class="compare-grid">
          <div class="compare-card">
            <strong>الوصف الحالي</strong>
            <textarea readonly>${escapeHtml(editor.currentDescription)}</textarea>
          </div>
          <div class="compare-card">
            <strong>الوصف بعد التحسين</strong>
            <textarea id="editor-description">${escapeHtml(editor.optimizedDescription)}</textarea>
          </div>
        </div>
      `;
    }

    if (showSeo) {
      html += `
        <div class="compare-grid" style="margin-top:16px;">
          <div class="compare-card is-meta">
            <strong>Meta Title الحالي</strong>
            <textarea readonly>${escapeHtml(editor.currentMetaTitle)}</textarea>
          </div>
          <div class="compare-card is-meta">
            <strong>Meta Title بعد التحسين</strong>
            <textarea id="editor-meta-title">${escapeHtml(editor.optimizedMetaTitle)}</textarea>
          </div>
        </div>
        <div class="compare-grid" style="margin-top:16px;">
          <div class="compare-card">
            <strong>Meta Description الحالية</strong>
            <textarea readonly>${escapeHtml(editor.currentMetaDescription)}</textarea>
          </div>
          <div class="compare-card">
            <strong>Meta Description بعد التحسين</strong>
            <textarea id="editor-meta-description">${escapeHtml(editor.optimizedMetaDescription)}</textarea>
          </div>
        </div>
      `;
    }

    html += `
      <div style="display:flex;gap:12px;flex-wrap:wrap;justify-content:flex-end;margin-top:20px;">
        <button class="btn" id="save-editor" type="button">حفظ في المتجر</button>
        <button class="btn btn-secondary" id="cancel-editor" type="button">إلغاء</button>
      </div>
    `;

    root.innerHTML = html;
    document.getElementById('save-editor')?.addEventListener('click', saveEditor);
    document.getElementById('cancel-editor')?.addEventListener('click', closeEditor);
  }

  function openEditorModal() {
    document.getElementById('editor-modal')?.classList.add('is-open');
  }

  function closeEditor() {
    document.getElementById('editor-modal')?.classList.remove('is-open');
    state.editor = null;
    state.modalLoading = false;
    renderEditorBody();
  }

  async function openOptimization(productId, mode) {
    const product = getProductById(productId);
    if (!product) return;

    state.loadingProductId = productId;
    state.modalLoading = true;
    state.editor = null;
    renderProducts();
    renderEditorBody();
    openEditorModal();

    try {
      const response = await fetch(`/api/products/${productId}/optimize`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          tone: document.getElementById('tone')?.value || 'احترافي مقنع',
          language: document.getElementById('language')?.value || 'ar',
          mode
        })
      });
      const data = await response.json();

      if (!data.success) {
        state.editor = {
          product,
          mode,
          notice: { type: 'error', message: data.message || 'فشل التحسين.' },
          currentDescription: '',
          optimizedDescription: '',
          currentMetaTitle: '',
          optimizedMetaTitle: '',
          currentMetaDescription: '',
          optimizedMetaDescription: ''
        };
      } else {
        state.editor = {
          product,
          mode,
          notice: null,
          currentDescription: data.current_description || '',
          optimizedDescription: data.optimized_description || '',
          currentMetaTitle: data.current_metadata_title || '',
          optimizedMetaTitle: data.optimized_metadata_title || '',
          currentMetaDescription: data.current_metadata_description || '',
          optimizedMetaDescription: data.optimized_metadata_description || ''
        };
      }
    } catch (error) {
      state.editor = {
        product,
        mode,
        notice: { type: 'error', message: 'حدث خطأ أثناء تجهيز المحتوى.' },
        currentDescription: '',
        optimizedDescription: '',
        currentMetaTitle: '',
        optimizedMetaTitle: '',
        currentMetaDescription: '',
        optimizedMetaDescription: ''
      };
    } finally {
      state.loadingProductId = null;
      state.modalLoading = false;
      renderProducts();
      renderEditorBody();
    }
  }

  async function saveEditor() {
    if (!state.editor) return;

    const editor = state.editor;
    const description = document.getElementById('editor-description')?.value.trim() ?? editor.currentDescription;
    const metaTitle = document.getElementById('editor-meta-title')?.value.trim() ?? editor.currentMetaTitle;
    const metaDescription = document.getElementById('editor-meta-description')?.value.trim() ?? editor.currentMetaDescription;

    if ((editor.mode === 'description' || editor.mode === 'all') && !description) {
      state.editor.notice = { type: 'error', message: 'الوصف الجديد مطلوب قبل الحفظ.' };
      renderEditorBody();
      return;
    }

    state.editor.notice = { type: 'success', message: 'جاري الحفظ داخل سلة...' };
    renderEditorBody();

    try {
      const response = await fetch(`/api/products/${editor.product.id}/save-description`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          mode: editor.mode,
          description,
          metadata_title: metaTitle,
          metadata_description: metaDescription
        })
      });
      const data = await response.json();

      if (!data.success) {
        state.editor.notice = { type: 'error', message: data.message || 'تعذّر حفظ المحتوى.' };
        renderEditorBody();
        return;
      }

      closeEditor();
      await loadProducts();
      await loadOperations();
    } catch (error) {
      state.editor.notice = { type: 'error', message: 'حدث خطأ أثناء حفظ المحتوى.' };
      renderEditorBody();
    }
  }

  async function loadProducts() {
    const root = document.getElementById('products-list');
    if (root) {
      root.innerHTML = '<div class="empty-state" style="grid-column:1/-1;"><p class="muted">جاري تحميل المنتجات...</p></div>';
    }

    try {
      const response = await fetch('/api/products');
      const data = await response.json();
      if (!data.success) {
        if (root) {
          root.innerHTML = `<div class="empty-state" style="grid-column:1/-1;"><p class="muted">${escapeHtml(data.message || 'تعذّر تحميل المنتجات.')}</p></div>`;
        }
        return;
      }

      state.products = data.products || [];
      renderProducts();
    } catch (error) {
      if (root) {
        root.innerHTML = '<div class="empty-state" style="grid-column:1/-1;"><p class="muted">تعذّر تحميل المنتجات.</p></div>';
      }
    }
  }

  function getOperationsQuery(limitOverride) {
    const status = document.getElementById('operations-status-filter')?.value || 'all';
    let mode = document.getElementById('operations-mode-filter')?.value || 'all';
    const limit = limitOverride || '20';
    if (mode === 'combo_all') mode = 'all';

    const params = new URLSearchParams();
    if (status !== 'all') params.set('status', status);
    if (mode !== 'all') params.set('mode', mode);
    params.set('limit', limit);
    return `/api/operations?${params.toString()}`;
  }

  function getOperationStatusClass(status) {
    if (status === 'completed') return 'success';
    if (status === 'failed') return 'danger';
    return 'warning';
  }

  function getOperationStatusLabel(status) {
    if (status === 'completed') return 'مكتمل';
    if (status === 'failed') return 'فشل';
    if (status === 'in_progress') return 'قيد التنفيذ';
    return 'غير معروف';
  }

  function getOperationLabel(mode) {
    if (mode === 'description') return 'وصف المنتج';
    if (mode === 'seo') return 'SEO المنتج';
    return 'الوصف + SEO';
  }

  function formatDate(value) {
    if (!value) return '-';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return value;
    return date.toLocaleString('ar-SA', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  }

  function renderOperations(data) {
    const root = document.getElementById('operations-list');
    if (!root) return;

    if (!data.success) {
      root.innerHTML = `<div class="empty-state"><p class="muted">${escapeHtml(data.message || 'تعذّر تحميل السجل.')}</p></div>`;
      return;
    }

    const operations = data.operations || [];
    if (!operations.length) {
      root.innerHTML = '<div class="empty-state"><p class="muted">لا توجد عمليات بعد.</p></div>';
      return;
    }

    root.innerHTML = operations.map((operation) => `
      <div class="card surface-soft" style="padding:14px;border-radius:18px;box-shadow:none;">
        <div class="product-badges" style="margin-bottom:8px;">
          <span class="status-badge ${getOperationStatusClass(operation.status)}">${escapeHtml(getOperationStatusLabel(operation.status))}</span>
          <span class="status-badge warning">${escapeHtml(getOperationLabel(operation.mode))}</span>
        </div>
        <strong style="display:block;margin-bottom:6px;">${escapeHtml(operation.product_name || 'عملية بدون اسم منتج')}</strong>
        <div class="muted" style="font-size:13px;">${escapeHtml(formatDate(operation.used_at))}</div>
      </div>
    `).join('');
  }

  async function loadOperations(limitOverride) {
    try {
      const response = await fetch(getOperationsQuery(limitOverride));
      renderOperations(await response.json());
    } catch (error) {
      renderOperations({ success: false, message: 'تعذّر تحميل السجل.' });
    }
  }

  function applyFilters() {
    state.filters = {
      name: document.getElementById('filter-name')?.value.trim() || '',
      sku: document.getElementById('filter-sku')?.value.trim() || '',
      status: document.getElementById('filter-status')?.value || 'all',
      content: document.getElementById('filter-content')?.value || 'all'
    };
    state.page = 1;
    renderProducts();
  }

  function clearFilters() {
    if (document.getElementById('filter-name')) document.getElementById('filter-name').value = '';
    if (document.getElementById('filter-sku')) document.getElementById('filter-sku').value = '';
    if (document.getElementById('filter-status')) document.getElementById('filter-status').value = 'all';
    if (document.getElementById('filter-content')) document.getElementById('filter-content').value = 'all';

    state.filters = { name: '', sku: '', status: 'all', content: 'all' };
    state.quickFilter = 'all';
    state.page = 1;
    renderProducts();
  }

  function switchSection(section) {
    document.querySelectorAll('[data-app-section]').forEach((panel) => {
      panel.style.display = panel.getAttribute('data-app-section') === section ? '' : 'none';
    });
    document.querySelectorAll('[data-section-target]').forEach((button) => {
      button.classList.toggle('is-active', button.getAttribute('data-section-target') === section);
    });
  }

  function bindEvents() {
    document.querySelectorAll('[data-section-target]').forEach((button) => {
      button.addEventListener('click', () => switchSection(button.getAttribute('data-section-target') || 'products'));
    });

    document.getElementById('page-size')?.addEventListener('change', (event) => {
      state.pageSize = Number(event.target.value || 12);
      state.page = 1;
      renderProducts();
    });

    document.getElementById('apply-filters')?.addEventListener('click', applyFilters);
    document.getElementById('clear-filters')?.addEventListener('click', clearFilters);
    document.getElementById('close-editor')?.addEventListener('click', closeEditor);
    document.getElementById('operations-apply-filter')?.addEventListener('click', () => loadOperations());
    document.getElementById('operations-show-all')?.addEventListener('click', () => loadOperations('all'));

    document.querySelectorAll('[data-quick-filter]').forEach((chip) => {
      chip.addEventListener('click', () => {
        state.quickFilter = chip.dataset.quickFilter || 'all';
        state.page = 1;
        renderProducts();
      });
    });

    document.getElementById('editor-modal')?.addEventListener('click', (event) => {
      if (event.target.id === 'editor-modal') closeEditor();
    });
  }

  bindEvents();
  switchSection('products');
  renderEditorBody();
  loadProducts();
  loadOperations();
})();
