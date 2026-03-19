(function () {
  if (window.__CLIENT_DASHBOARD_PRODUCTS_INIT__) {
    return;
  }
  window.__CLIENT_DASHBOARD_PRODUCTS_INIT__ = true;
  const appBasePath = (document.querySelector('.dashboard-shell')?.dataset.appBasePath || '').replace(/\/+$/, '');
  const apiPrefixes = Array.from(new Set([
    `${appBasePath}/api`,
    `${appBasePath}/public/api`
  ]));

  async function apiFetch(path, options = {}) {
    let lastResponse = null;
    let lastError = null;

    for (let i = 0; i < apiPrefixes.length; i += 1) {
      const prefix = apiPrefixes[i].replace(/\/+$/, '');
      try {
        const response = await fetch(`${prefix}${path}`, options);
        lastResponse = response;
        if (response.status !== 404) {
          return response;
        }
      } catch (error) {
        lastError = error;
      }
    }

    if (lastResponse) {
      return lastResponse;
    }
    throw lastError || new Error('API request failed');
  }

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
    editor: null,
    altSelectedProductIds: new Set(),
    altEditor: null
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

  function buildDescriptionPreview(rawHtml) {
    let text = stripHtml(rawHtml || '');
    text = text
      .replace(/https?:\/\/\S+/gi, ' ')
      .replace(/[{}[\]<>|]/g, ' ')
      .replace(/\s+/g, ' ')
      .trim();

    if (!text) {
      return 'لا يوجد وصف حالي لهذا المنتج.';
    }

    const cleaned = text
      .split(' ')
      .filter((token) => {
        if (!token) return false;
        if (token.length > 42) return false;
        if (/^[0-9.\-_/]{8,}$/.test(token)) return false;
        return true;
      })
      .join(' ')
      .trim();

    const finalText = cleaned || text;
    if (finalText.length <= 170) return finalText;
    return `${finalText.slice(0, 170).trim()}...`;
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
    const preview = buildDescriptionPreview(product.description || '');
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
        <p class="muted product-preview" style="margin:0;">${escapeHtml(preview)}</p>
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
      renderAltProducts();
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
    renderAltProducts();
  }

  function setAltAlert(type, message) {
    const root = document.getElementById('alt-alert');
    if (!root) return;
    root.innerHTML = message ? `<div class="notice ${type}">${escapeHtml(message)}</div>` : '';
  }

  function getProductImages(product) {
    return Array.isArray(product?.images) ? product.images.filter((image) => Number(image?.id || 0) > 0) : [];
  }

  function buildAltProductCard(product) {
    const images = getProductImages(product);
    const selected = state.altSelectedProductIds.has(Number(product.id));
    const previewImages = images.slice(0, 4).map((image) => `
      <img src="${escapeHtml(image.url || '')}" alt="${escapeHtml(image.alt || product.name || 'image')}" style="width:58px;height:58px;object-fit:cover;border-radius:10px;border:1px solid rgba(202,177,149,.5);">
    `).join('');

    return `
      <article class="product-card">
        <div style="display:flex;justify-content:space-between;gap:12px;align-items:center;">
          <label style="display:flex;gap:8px;align-items:center;">
            <input type="checkbox" data-alt-select-product="${Number(product.id)}" ${selected ? 'checked' : ''}>
            <span class="muted">تحديد</span>
          </label>
          <span class="status-badge ${images.length ? 'success' : 'danger'}">${images.length} صور</span>
        </div>
        <h3 class="product-title" style="margin-bottom:0;">${escapeHtml(product.name || 'منتج')}</h3>
        <div class="meta-list"><span>SKU: <code>${escapeHtml(product.sku || '-')}</code></span></div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;min-height:58px;">
          ${previewImages || '<span class="muted">لا توجد صور متاحة لهذا المنتج.</span>'}
        </div>
        <div class="product-actions">
          <button class="btn btn-sky" type="button" data-open-alt-editor="${Number(product.id)}" ${images.length ? '' : 'disabled'}>كتابة ALT للصور</button>
        </div>
      </article>
    `;
  }

  function renderAltProducts() {
    const root = document.getElementById('alt-products-list');
    const summary = document.getElementById('alt-products-summary');
    if (!root || !summary) return;

    const { filtered, totalPages, items, from, to } = getPagedProducts();
    summary.textContent = filtered.length
      ? `عرض ${from} إلى ${to} من أصل ${filtered.length} منتج`
      : 'لا توجد منتجات مطابقة للفلاتر الحالية.';

    if (!items.length) {
      root.innerHTML = '<div class="empty-state" style="grid-column:1/-1;"><p class="muted">لا توجد منتجات لعرض ALT.</p></div>';
      renderPagination('alt-products-pagination-top', totalPages);
      renderPagination('alt-products-pagination-bottom', totalPages);
      return;
    }

    root.innerHTML = items.map(buildAltProductCard).join('');
    root.querySelectorAll('[data-alt-select-product]').forEach((input) => {
      input.addEventListener('change', () => {
        const productId = Number(input.getAttribute('data-alt-select-product'));
        if (!productId) return;
        if (input.checked) state.altSelectedProductIds.add(productId);
        else state.altSelectedProductIds.delete(productId);
      });
    });
    root.querySelectorAll('[data-open-alt-editor]').forEach((button) => {
      button.addEventListener('click', () => {
        openImageAltEditor(Number(button.getAttribute('data-open-alt-editor')));
      });
    });

    renderPagination('alt-products-pagination-top', totalPages);
    renderPagination('alt-products-pagination-bottom', totalPages);
  }

  function openImageAltModal() {
    document.getElementById('image-alt-modal')?.classList.add('is-open');
  }

  function closeImageAltModal() {
    document.getElementById('image-alt-modal')?.classList.remove('is-open');
    state.altEditor = null;
    renderImageAltBody();
  }

  function renderImageAltBody() {
    const root = document.getElementById('image-alt-body');
    const title = document.getElementById('image-alt-title');
    const subtitle = document.getElementById('image-alt-subtitle');
    const alert = document.getElementById('image-alt-alert');
    if (!root || !title || !subtitle || !alert) return;

    const editor = state.altEditor;
    if (!editor) {
      title.textContent = 'كاتب النص البديل';
      subtitle.textContent = 'اختر منتجًا لبدء تعديل ALT.';
      alert.innerHTML = '';
      root.innerHTML = '<div class="empty-state"><p class="muted">اختر منتجًا من قسم ALT للصور.</p></div>';
      return;
    }

    title.textContent = editor.productName || 'كاتب ALT';
    subtitle.textContent = 'يمكنك تحديد صورة واحدة أو عدة صور، ثم توليد ALT أو حفظه يدويًا.';
    alert.innerHTML = editor.notice ? `<div class="notice ${editor.notice.type}">${escapeHtml(editor.notice.message)}</div>` : '';

    const rows = editor.images.map((image) => `
      <div class="card surface-soft" style="padding:12px;box-shadow:none;">
        <div style="display:flex;gap:10px;align-items:flex-start;">
          <label style="margin-top:4px;"><input type="checkbox" data-alt-image-select="${image.image_id}" ${image.selected ? 'checked' : ''}></label>
          <img src="${escapeHtml(image.image_url || '')}" alt="" style="width:70px;height:70px;object-fit:cover;border-radius:10px;border:1px solid rgba(202,177,149,.5);">
          <div style="flex:1;display:grid;gap:8px;">
            <label><strong>ALT الحالي</strong></label>
            <textarea readonly rows="2">${escapeHtml(image.current_alt || '')}</textarea>
            <label><strong>ALT بعد التحسين</strong></label>
            <textarea rows="2" data-alt-image-value="${image.image_id}">${escapeHtml(image.optimized_alt || '')}</textarea>
          </div>
        </div>
      </div>
    `).join('');

    root.innerHTML = `
      <div style="display:flex;gap:10px;flex-wrap:wrap;justify-content:flex-end;margin-bottom:12px;">
        <button class="btn btn-sky" id="optimize-selected-images" type="button">توليد ALT للمحدد</button>
        <button class="btn" id="save-selected-images" type="button">حفظ المحدد في المتجر</button>
      </div>
      <div class="panel-stack">${rows}</div>
    `;

    root.querySelectorAll('[data-alt-image-select]').forEach((input) => {
      input.addEventListener('change', () => {
        const imageId = Number(input.getAttribute('data-alt-image-select'));
        const item = state.altEditor?.images.find((img) => Number(img.image_id) === imageId);
        if (item) item.selected = input.checked;
      });
    });
    root.querySelectorAll('[data-alt-image-value]').forEach((input) => {
      input.addEventListener('input', () => {
        const imageId = Number(input.getAttribute('data-alt-image-value'));
        const item = state.altEditor?.images.find((img) => Number(img.image_id) === imageId);
        if (item) item.optimized_alt = input.value;
      });
    });

    document.getElementById('optimize-selected-images')?.addEventListener('click', optimizeSelectedImagesAlt);
    document.getElementById('save-selected-images')?.addEventListener('click', saveSelectedImagesAlt);
  }

  async function openImageAltEditor(productId) {
    const product = getProductById(productId);
    if (!product) return;

    state.altEditor = {
      productId: Number(productId),
      productName: product.name || 'منتج',
      notice: null,
      images: getProductImages(product).map((image) => ({
        image_id: Number(image.id),
        image_url: image.url || '',
        current_alt: image.alt || '',
        optimized_alt: image.alt || '',
        selected: true
      }))
    };
    renderImageAltBody();
    openImageAltModal();
  }

  async function optimizeSelectedImagesAlt() {
    if (!state.altEditor) return;
    const imageIds = state.altEditor.images.filter((image) => image.selected).map((image) => image.image_id);
    if (!imageIds.length) {
      state.altEditor.notice = { type: 'error', message: 'اختر صورة واحدة على الأقل.' };
      renderImageAltBody();
      return;
    }

    state.altEditor.notice = { type: 'success', message: 'جاري توليد ALT للصور المحددة...' };
    renderImageAltBody();

    try {
      const response = await apiFetch(`/products/${state.altEditor.productId}/images/optimize-alt`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          image_ids: imageIds,
          language: document.getElementById('language')?.value || 'ar'
        })
      });
      const data = await response.json();
      if (!data.success) {
        state.altEditor.notice = { type: 'error', message: data.message || 'تعذر توليد ALT.' };
        renderImageAltBody();
        return;
      }

      (data.images || []).forEach((image) => {
        const item = state.altEditor?.images.find((img) => Number(img.image_id) === Number(image.image_id));
        if (item) item.optimized_alt = image.optimized_alt || item.optimized_alt;
      });
      state.altEditor.notice = { type: 'success', message: 'تم توليد ALT للصور المحددة.' };
      renderImageAltBody();
    } catch (error) {
      state.altEditor.notice = { type: 'error', message: 'حدث خطأ أثناء توليد ALT.' };
      renderImageAltBody();
    }
  }

  async function saveSelectedImagesAlt() {
    if (!state.altEditor) return;
    const payload = state.altEditor.images
      .filter((image) => image.selected)
      .map((image) => ({ image_id: image.image_id, alt: String(image.optimized_alt || '').trim() }))
      .filter((image) => image.alt);

    if (!payload.length) {
      state.altEditor.notice = { type: 'error', message: 'لا توجد بيانات ALT صالحة للحفظ.' };
      renderImageAltBody();
      return;
    }

    state.altEditor.notice = { type: 'success', message: 'جاري حفظ ALT داخل المتجر...' };
    renderImageAltBody();

    try {
      const response = await apiFetch(`/products/${state.altEditor.productId}/images/save-alt`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ images: payload })
      });
      const data = await response.json();
      if (!data.success) {
        state.altEditor.notice = { type: 'error', message: data.message || 'تعذر حفظ ALT.' };
        renderImageAltBody();
        return;
      }

      state.altEditor.notice = { type: 'success', message: 'تم حفظ ALT للصور المحددة.' };
      renderImageAltBody();
      await loadProducts();
      await loadOperations();
      await loadUsage();
    } catch (error) {
      state.altEditor.notice = { type: 'error', message: 'حدث خطأ أثناء حفظ ALT.' };
      renderImageAltBody();
    }
  }

  async function optimizeSelectedProductsAlt() {
    const selected = Array.from(state.altSelectedProductIds);
    if (!selected.length) {
      setAltAlert('error', 'اختر منتجًا واحدًا على الأقل.');
      return;
    }

    setAltAlert('success', 'جاري تحسين ALT لكل صور المنتجات المحددة...');
    try {
      const response = await apiFetch('/products/alt/bulk', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          product_ids: selected,
          language: document.getElementById('language')?.value || 'ar'
        })
      });
      const data = await response.json();
      if (!data.success) {
        setAltAlert('error', data.message || 'تعذر تنفيذ التحسين الجماعي.');
        return;
      }

      const count = Array.isArray(data.processed) ? data.processed.length : 0;
      const errors = Array.isArray(data.errors) ? data.errors.length : 0;
      setAltAlert('success', `تمت معالجة ${count} منتج${errors ? `، مع ${errors} أخطاء` : ''}.`);
      await loadProducts();
      await loadOperations();
      await loadUsage();
      renderAltProducts();
    } catch (error) {
      setAltAlert('error', 'حدث خطأ أثناء التحسين الجماعي لصور ALT.');
    }
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
      const response = await apiFetch(`/products/${productId}/optimize`, {
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
    const descriptionInput = document.getElementById('editor-description');
    const metaTitleInput = document.getElementById('editor-meta-title');
    const metaDescriptionInput = document.getElementById('editor-meta-description');

    const description = descriptionInput ? descriptionInput.value.trim() : (editor.currentDescription || '');
    const metaTitle = metaTitleInput ? metaTitleInput.value.trim() : (editor.currentMetaTitle || '');
    const metaDescription = metaDescriptionInput ? metaDescriptionInput.value.trim() : (editor.currentMetaDescription || '');

    if ((editor.mode === 'description' || editor.mode === 'all') && !description) {
      state.editor.notice = { type: 'error', message: 'الوصف الجديد مطلوب قبل الحفظ.' };
      renderEditorBody();
      return;
    }

    state.editor.notice = { type: 'success', message: 'جاري الحفظ داخل سلة...' };
    renderEditorBody();

    try {
      const payload = {
        mode: editor.mode,
        description
      };

      if (editor.mode === 'seo' || editor.mode === 'all') {
        payload.metadata_title = metaTitle;
        payload.metadata_description = metaDescription;
      }

      const response = await apiFetch(`/products/${editor.product.id}/save-description`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
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
      const response = await apiFetch('/products');
      const data = await response.json();
      if (!data.success) {
        if (root) {
          root.innerHTML = `<div class="empty-state" style="grid-column:1/-1;"><p class="muted">${escapeHtml(data.message || 'تعذّر تحميل المنتجات.')}</p></div>`;
        }
        const altSummary = document.getElementById('alt-products-summary');
        if (altSummary) {
          altSummary.textContent = 'تعذّر تحميل منتجات قسم ALT.';
        }
        return;
      }

      state.products = data.products || [];
      renderProducts();
    } catch (error) {
      if (root) {
        root.innerHTML = '<div class="empty-state" style="grid-column:1/-1;"><p class="muted">تعذّر تحميل المنتجات.</p></div>';
      }
      const altSummary = document.getElementById('alt-products-summary');
      if (altSummary) {
        altSummary.textContent = 'تعذّر تحميل منتجات قسم ALT.';
      }
    }
  }

  function setStoreSeoAlert(type, message) {
    const root = document.getElementById('store-seo-alert');
    if (!root) return;
    root.innerHTML = message ? `<div class="notice ${type}">${escapeHtml(message)}</div>` : '';
  }

  function updateStoreSeoCounters() {
    const title = document.getElementById('store-seo-title')?.value || '';
    const description = document.getElementById('store-seo-description')?.value || '';
    const keywords = document.getElementById('store-seo-keywords')?.value || '';

    if (document.getElementById('store-seo-title-count')) document.getElementById('store-seo-title-count').textContent = `${title.length} حرف`;
    if (document.getElementById('store-seo-description-count')) document.getElementById('store-seo-description-count').textContent = `${description.length} حرف`;
    if (document.getElementById('store-seo-keywords-count')) document.getElementById('store-seo-keywords-count').textContent = `${keywords.length} حرف`;
  }

  async function loadStoreSeo() {
    try {
      const data = await apiFetch('/store-seo').then((response) => response.json());
      if (!data.success) {
        setStoreSeoAlert('error', data.message || 'تعذر جلب سيو المتجر.');
        return;
      }

      const seo = data.seo || {};
      if (document.getElementById('store-seo-title')) document.getElementById('store-seo-title').value = seo.title || '';
      if (document.getElementById('store-seo-description')) document.getElementById('store-seo-description').value = seo.description || '';
      if (document.getElementById('store-seo-keywords')) document.getElementById('store-seo-keywords').value = seo.keywords || '';
      setStoreSeoAlert('', '');
      updateStoreSeoCounters();
    } catch (error) {
      setStoreSeoAlert('error', 'تعذر جلب سيو المتجر.');
    }
  }

  async function optimizeStoreSeo() {
    const button = document.getElementById('generate-store-seo');
    const oldText = button?.textContent || 'إنشاء بالذكاء الاصطناعي';
    if (button) {
      button.disabled = true;
      button.textContent = 'جاري التوليد...';
    }

    setStoreSeoAlert('success', 'جاري إنشاء سيو المتجر...');
    try {
      const data = await apiFetch('/store-seo/optimize', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          tone: document.getElementById('tone')?.value || 'احترافي مقنع',
          language: document.getElementById('language')?.value || 'ar'
        })
      }).then((response) => response.json());

      if (!data.success) {
        setStoreSeoAlert('error', data.message || 'تعذر توليد سيو المتجر.');
        return;
      }

      if (document.getElementById('store-seo-title')) document.getElementById('store-seo-title').value = data.optimized_title || '';
      if (document.getElementById('store-seo-description')) document.getElementById('store-seo-description').value = data.optimized_description || '';
      if (document.getElementById('store-seo-keywords')) document.getElementById('store-seo-keywords').value = data.optimized_keywords || '';
      updateStoreSeoCounters();
      setStoreSeoAlert('success', 'تم إنشاء سيو المتجر. راجع ثم احفظ.');
    } catch (error) {
      setStoreSeoAlert('error', 'حدث خطأ أثناء توليد سيو المتجر.');
    } finally {
      if (button) {
        button.disabled = false;
        button.textContent = oldText;
      }
    }
  }

  async function saveStoreSeo() {
    const button = document.getElementById('save-store-seo');
    const oldText = button?.textContent || 'حفظ في المتجر';
    const title = document.getElementById('store-seo-title')?.value.trim() || '';
    const description = document.getElementById('store-seo-description')?.value.trim() || '';
    const keywords = document.getElementById('store-seo-keywords')?.value.trim() || '';

    if (!title || !description) {
      setStoreSeoAlert('error', 'أدخل عنوان ووصف المتجر قبل الحفظ.');
      return;
    }

    if (button) {
      button.disabled = true;
      button.textContent = 'جاري الحفظ...';
    }

    setStoreSeoAlert('success', 'جاري حفظ سيو المتجر...');
    try {
      const data = await apiFetch('/store-seo/save', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ title, description, keywords })
      }).then((response) => response.json());

      if (!data.success) {
        setStoreSeoAlert('error', data.message || 'تعذر حفظ سيو المتجر.');
        return;
      }

      setStoreSeoAlert('success', data.message || 'تم حفظ سيو المتجر بنجاح.');
      await loadOperations();
      await loadUsage();
    } catch (error) {
      setStoreSeoAlert('error', 'حدث خطأ أثناء حفظ سيو المتجر.');
    } finally {
      if (button) {
        button.disabled = false;
        button.textContent = oldText;
      }
    }
  }

  async function loadUsage() {
    const root = document.getElementById('usage-card');
    if (!root) return;

    try {
      const data = await apiFetch('/subscription').then((response) => response.json());
      if (!data.success) {
        root.innerHTML = `<h2>الاستهلاك</h2><p class="muted">${escapeHtml(data.message || 'تعذر تحميل بيانات الاستهلاك.')}</p>`;
        return;
      }

      const sub = data.subscription || {};
      root.innerHTML = `
        <h2 style="margin:0 0 8px;">الاستهلاك</h2>
        <p class="muted" style="margin:0 0 14px;">ملخص حالة الباقة واستهلاك التحسينات.</p>
        <div class="grid" style="margin-top:0;">
          <div class="card surface-soft stat" style="min-height:auto;">
            <span class="stat-label">الحالة</span>
            <span class="stat-value" style="font-size:24px;">${escapeHtml(sub.status || '-')}</span>
          </div>
          <div class="card surface-soft stat" style="min-height:auto;">
            <span class="stat-label">الباقة</span>
            <span class="stat-value" style="font-size:24px;">${escapeHtml(sub.plan_name || '-')}</span>
          </div>
          <div class="card surface-soft stat" style="min-height:auto;">
            <span class="stat-label">المستخدم</span>
            <span class="stat-value" style="font-size:24px;">${escapeHtml(sub.used_products ?? 0)}</span>
          </div>
          <div class="card surface-soft stat" style="min-height:auto;">
            <span class="stat-label">المتبقي</span>
            <span class="stat-value" style="font-size:24px;">${escapeHtml(sub.remaining_products ?? 0)}</span>
          </div>
        </div>
      `;
    } catch (error) {
      root.innerHTML = '<h2>الاستهلاك</h2><p class="muted">تعذر تحميل بيانات الاستهلاك.</p>';
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
    return `/operations?${params.toString()}`;
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
    if (mode === 'store_seo') return 'سيو المتجر';
    if (mode === 'image_alt') return 'ALT الصور';
    if (mode === 'image_alt_bulk') return 'ALT الصور (جملة)';
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
      const response = await apiFetch(getOperationsQuery(limitOverride));
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

    if (section === 'alt-images') {
      renderAltProducts();
    }
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
    document.getElementById('generate-store-seo')?.addEventListener('click', optimizeStoreSeo);
    document.getElementById('save-store-seo')?.addEventListener('click', saveStoreSeo);
    document.getElementById('alt-optimize-selected-products')?.addEventListener('click', optimizeSelectedProductsAlt);
    document.getElementById('alt-clear-selection')?.addEventListener('click', () => {
      state.altSelectedProductIds = new Set();
      renderAltProducts();
      setAltAlert('', '');
    });
    document.getElementById('store-seo-title')?.addEventListener('input', updateStoreSeoCounters);
    document.getElementById('store-seo-description')?.addEventListener('input', updateStoreSeoCounters);
    document.getElementById('store-seo-keywords')?.addEventListener('input', updateStoreSeoCounters);

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
    document.getElementById('close-image-alt')?.addEventListener('click', closeImageAltModal);
    document.getElementById('image-alt-modal')?.addEventListener('click', (event) => {
      if (event.target.id === 'image-alt-modal') closeImageAltModal();
    });
  }

  bindEvents();
  switchSection('products');
  renderEditorBody();
  renderImageAltBody();
  loadProducts();
  loadOperations();
  loadStoreSeo();
  loadUsage();
})();
