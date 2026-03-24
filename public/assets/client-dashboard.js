(function () {
  if (window.__CLIENT_DASHBOARD_PRODUCTS_INIT__) {
    return;
  }
  window.__CLIENT_DASHBOARD_PRODUCTS_INIT__ = true;
  console.log('Dashboard JS loaded');
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

  function parseApiMessage(value) {
    if (value == null) return null;
    if (typeof value === 'object') return value;
    if (typeof value !== 'string') return null;

    const text = value.trim();
    if (!text) return null;
    if (!(text.startsWith('{') || text.startsWith('['))) return null;

    try {
      return JSON.parse(text);
    } catch (error) {
      return null;
    }
  }

  function pickFirstFieldMessage(fields) {
    if (!fields || typeof fields !== 'object') return '';
    const values = Object.values(fields);
    if (!values.length) return '';
    const first = values[0];
    if (Array.isArray(first)) return String(first[0] || '').trim();
    if (typeof first === 'string') return first.trim();
    return '';
  }

  function normalizeApiMessage(rawMessage, fallbackMessage) {
    const fallback = fallbackMessage || 'حدث خطأ غير متوقع.';
    if (!rawMessage) return fallback;

    if (typeof rawMessage === 'string') {
      const parsed = parseApiMessage(rawMessage);
      if (!parsed) {
        if (rawMessage.includes('metadata.read') || rawMessage.includes('metadata.read_write')) {
          return 'يلزم تفعيل صلاحية Meta Data (Read/Write) للتطبيق ثم إعادة تثبيته من سلة.';
        }
        if (rawMessage.includes('Route not found')) {
          return 'المسار غير متاح حالياً. تأكد من رفع آخر نسخة على الاستضافة.';
        }
        return rawMessage;
      }
      rawMessage = parsed;
    }

    if (typeof rawMessage === 'object' && rawMessage !== null) {
      const fieldsMessage = pickFirstFieldMessage(rawMessage.fields);
      if (fieldsMessage) return fieldsMessage;
      if (typeof rawMessage.message === 'string' && rawMessage.message.trim()) return rawMessage.message.trim();
      if (typeof rawMessage.error === 'string' && rawMessage.error.trim()) return rawMessage.error.trim();
      if (typeof rawMessage.error_description === 'string' && rawMessage.error_description.trim()) return rawMessage.error_description.trim();
    }

    return fallback;
  }

  function sanitizeAltForSalla(value) {
    let text = String(value ?? '');
    text = text.replace(/[^\p{L}\p{N}\s]/gu, ' ').replace(/\s+/gu, ' ').trim();
    if (!text) return '';

    // Match strict server behavior with conservative cap for Salla validation.
    while (text.length > 0) {
      const charsOk = Array.from(text).length <= 60;
      const bytesOk = new TextEncoder().encode(text).length <= 60;
      if (charsOk && bytesOk) break;
      text = Array.from(text).slice(0, -1).join('').trim();
    }

    return text;
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
    alt: {
      page: 1,
      filters: {
        name: '',
        sku: '',
        status: 'all',
        content: 'all'
      },
      quickFilter: 'all'
    },
    loadingProductId: null,
    modalLoading: false,
    editor: null,
    altSelectedProductIds: new Set(),
    altEditor: null,
    keywords: {
      loading: false,
      lastResult: null,
      history: []
    },
    domainSeo: {
      loading: false,
      initialized: false,
      data: null,
      history: [],
      config: {
        domain: '',
        country: 'sa',
        device: 'desktop'
      }
    }
  };

  function readSettingValue(primaryId, secondaryId, fallback = '') {
    const primary = document.getElementById(primaryId)?.value;
    if (typeof primary === 'string' && primary !== '') return primary;
    const secondary = document.getElementById(secondaryId)?.value;
    if (typeof secondary === 'string' && secondary !== '') return secondary;
    return fallback;
  }

  function getOutputLanguage(source = 'products') {
    const value = source === 'alt'
      ? readSettingValue('alt-setting-output-language', 'setting-output-language', '')
      : readSettingValue('setting-output-language', 'alt-setting-output-language', '');
    return value === 'ar' || value === 'en' ? value : '';
  }

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

  function normalizeComparableText(value) {
    return String(value ?? '')
      .trim()
      .toLowerCase()
      .replace(/\s+/g, ' ');
  }

  function isImageAltOptimized(image, product = null) {
    const altRaw = String(image?.alt || '').trim();
    if (!altRaw) return false;

    const alt = normalizeComparableText(altRaw);
    const productName = normalizeComparableText(product?.name || '');
    const productSku = normalizeComparableText(product?.sku || '');

    if (!alt || alt.length < 4) return false;
    if (productName && alt === productName) return false;
    if (productSku && alt === productSku) return false;

    const genericValues = new Set([
      'صورة المنتج',
      'صورة',
      'منتج',
      'product image',
      'image',
      'product',
    ]);

    if (genericValues.has(alt)) return false;
    return true;
  }

  function getImageAltText(image) {
    return String(image?.alt || '').trim();
  }

  function getAltStats(product) {
    const images = getProductImages(product);
    const ready = images.filter((image) => isImageAltOptimized(image, product)).length;
    const total = images.length;
    return {
      total,
      ready,
      missing: Math.max(0, total - ready)
    };
  }

  function getFilteredAltProducts() {
    const nameFilter = normalizeText(state.alt.filters.name);
    const skuFilter = normalizeText(state.alt.filters.sku);
    const statusFilter = state.alt.filters.status;
    const contentFilter = state.alt.quickFilter !== 'all' ? state.alt.quickFilter : state.alt.filters.content;

    return state.products.filter((product) => {
      const name = normalizeText(product.name);
      const sku = normalizeText(product.sku);
      const isOut = product.is_available === false || Number(product.quantity ?? 1) === 0;
      const stats = getAltStats(product);
      const hasImages = stats.total > 0;
      const allReady = hasImages && stats.ready === stats.total;
      const allMissing = hasImages && stats.ready === 0;
      const mixed = hasImages && !allReady && !allMissing;

      if (nameFilter && !name.includes(nameFilter)) return false;
      if (skuFilter && !sku.includes(skuFilter)) return false;
      if (statusFilter === 'sale' && product.status !== 'sale') return false;
      if (statusFilter === 'hidden' && product.status !== 'hidden') return false;
      if (statusFilter === 'out' && !isOut) return false;
      if (contentFilter === 'alt_ready' && !allReady) return false;
      if (contentFilter === 'alt_missing' && !allMissing) return false;
      if (contentFilter === 'alt_mixed' && !mixed) return false;

      return true;
    });
  }

  function getPagedAltProducts() {
    const filtered = getFilteredAltProducts();
    const totalPages = Math.max(1, Math.ceil(filtered.length / state.pageSize));

    if (state.alt.page > totalPages) state.alt.page = totalPages;

    const offset = (state.alt.page - 1) * state.pageSize;
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

  function renderAltPagination(containerId, totalPages) {
    const root = document.getElementById(containerId);
    if (!root) return;

    if (totalPages <= 1) {
      root.innerHTML = '';
      return;
    }

    const start = Math.max(1, state.alt.page - 2);
    const end = Math.min(totalPages, state.alt.page + 2);
    let html = `<button type="button" ${state.alt.page === 1 ? 'disabled' : ''} data-page="${state.alt.page - 1}">‹</button>`;

    for (let page = start; page <= end; page += 1) {
      html += `<button type="button" class="${page === state.alt.page ? 'is-active' : ''}" data-page="${page}">${page}</button>`;
    }

    html += `<button type="button" ${state.alt.page === totalPages ? 'disabled' : ''} data-page="${state.alt.page + 1}">›</button>`;
    root.innerHTML = html;

    root.querySelectorAll('[data-page]').forEach((button) => {
      button.addEventListener('click', () => {
        state.alt.page = Number(button.dataset.page || 1);
        renderAltProducts();
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
    const stats = getAltStats(product);
    const previewImages = images.slice(0, 6).map((image) => {
      const ready = isImageAltOptimized(image, product);
      return `
      <div style="display:grid;gap:6px;justify-items:stretch;min-width:78px;">
        <span class="status-badge ${ready ? 'success' : 'danger'}" style="padding:4px 8px;font-size:11px;justify-content:center;">${ready ? 'نص ALT محسّن' : 'لا نص ALT'}</span>
        <img src="${escapeHtml(image.url || '')}" alt="${escapeHtml(image.alt || product.name || 'image')}" style="width:78px;height:78px;object-fit:cover;border-radius:10px;border:2px solid ${ready ? 'rgba(15,123,102,.42)' : 'rgba(185,65,54,.36)'};display:block;cursor:default;">
        <button class="btn btn-sky" type="button" data-open-alt-single="${Number(product.id)}:${Number(image.id)}" style="padding:8px 8px;font-size:12px;">تحسين</button>
      </div>
      `;
    }).join('');

    return `
      <article class="product-card">
        <div style="display:flex;justify-content:space-between;gap:12px;align-items:center;">
          <label style="display:flex;gap:8px;align-items:center;">
            <input type="checkbox" data-alt-select-product="${Number(product.id)}" ${selected ? 'checked' : ''}>
            <span class="muted">تحديد</span>
          </label>
          <span class="status-badge ${images.length ? 'success' : 'danger'}">${images.length} صورة</span>
        </div>
        <h3 class="product-title" style="margin-bottom:0;">${escapeHtml(product.name || 'منتج')}</h3>
        <div class="meta-list"><span>SKU: <code>${escapeHtml(product.sku || '-')}</code></span></div>
        <div class="product-badges" style="margin-top:-2px;">
          <span class="status-badge ${stats.ready > 0 ? 'success' : 'danger'}">الصور المحسّنة: ${stats.ready}</span>
          <span class="status-badge ${stats.missing > 0 ? 'danger' : 'success'}">الصور غير المحسّنة: ${stats.missing}</span>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;min-height:78px;">
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

    document.querySelectorAll('[data-alt-quick-filter]').forEach((chip) => {
      chip.classList.toggle('is-active', chip.dataset.altQuickFilter === state.alt.quickFilter);
    });

    const { filtered, totalPages, items, from, to } = getPagedAltProducts();
    summary.textContent = filtered.length
      ? `عرض ${from} إلى ${to} من أصل ${filtered.length} منتج`
      : 'لا توجد منتجات مطابقة لفلاتر ALT الحالية.';

    if (!items.length) {
      root.innerHTML = '<div class="empty-state" style="grid-column:1/-1;"><p class="muted">لا توجد منتجات لعرض ALT.</p></div>';
      renderAltPagination('alt-products-pagination-top', totalPages);
      renderAltPagination('alt-products-pagination-bottom', totalPages);
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
    root.querySelectorAll('[data-open-alt-single]').forEach((button) => {
      button.addEventListener('click', () => {
        const raw = button.getAttribute('data-open-alt-single') || '';
        const [productIdRaw, imageIdRaw] = raw.split(':');
        const productId = Number(productIdRaw);
        const imageId = Number(imageIdRaw);
        if (!productId || !imageId) return;
        openImageAltEditor(productId, imageId, true);
      });
    });

    renderAltPagination('alt-products-pagination-top', totalPages);
    renderAltPagination('alt-products-pagination-bottom', totalPages);
  }

  function openImageAltModal() {
    document.getElementById('image-alt-modal')?.classList.add('is-open');
  }

  function closeImageAltModal() {
    document.getElementById('image-alt-modal')?.classList.remove('is-open');
    state.altEditor = null;
    renderImageAltBody();
  }


  async function saveSelectedImagesAlt() {
    if (!state.altEditor) return;
    const payload = state.altEditor.images
      .filter((image) => image.selected)
      .map((image) => ({ image_id: image.image_id, alt: sanitizeAltForSalla(image.optimized_alt || '') }))
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
        state.altEditor.notice = { type: 'error', message: normalizeApiMessage(data.message, 'تعذر حفظ ALT.') };
        renderImageAltBody();
        return;
      }

      const failed = Array.isArray(data.errors) ? data.errors.length : 0;
      if (failed > 0) {
        state.altEditor.notice = { type: 'error', message: `تم حفظ ${data.saved_images?.length || 0} صورة، وفشل ${failed} صور. راجع النصوص الطويلة أو الأحرف الخاصة.` };
      } else {
        state.altEditor.notice = { type: 'success', message: 'تم حفظ ALT للصور المحددة.' };
      }
      renderImageAltBody();
      await loadProducts();
      await loadOperations();
      await loadUsage();
    } catch (error) {
      state.altEditor.notice = { type: 'error', message: 'حدث خطأ أثناء حفظ ALT.' };
      renderImageAltBody();
    }
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
    subtitle.textContent = 'اكتب وصف ALT كمحترف سيو: وصف واضح، طبيعي، ودقيق (حتى 70 حرفًا).';
    alert.innerHTML = editor.notice ? `<div class="notice ${editor.notice.type}">${escapeHtml(editor.notice.message)}</div>` : '';

    const rows = editor.images.map((image) => {
      const isReady = isImageAltOptimized(
        { alt: image.current_alt },
        { name: editor.productName, sku: editor.productSku || '' }
      );
      return `
      <div class="card surface-soft" style="padding:12px;box-shadow:none;">
        <div style="display:flex;justify-content:space-between;gap:10px;align-items:center;margin-bottom:10px;flex-wrap:wrap;">
          <span class="status-badge ${isReady ? 'success' : 'danger'}">${isReady ? 'الصورة محسّنة' : 'الصورة غير محسّنة'}</span>
          <button class="btn btn-sky" type="button" data-alt-optimize-one="${image.image_id}" style="padding:8px 12px;">تحسين هذه الصورة</button>
        </div>
        <div style="display:flex;gap:10px;align-items:flex-start;">
          <label style="margin-top:4px;"><input type="checkbox" data-alt-image-select="${image.image_id}" ${image.selected ? 'checked' : ''}></label>
          <img src="${escapeHtml(image.image_url || '')}" alt="" style="width:70px;height:70px;object-fit:cover;border-radius:10px;border:2px solid ${isReady ? 'rgba(15,123,102,.42)' : 'rgba(185,65,54,.36)'};">
          <div style="flex:1;display:grid;gap:8px;">
            <label><strong>ALT الحالي</strong></label>
            <textarea readonly rows="2">${escapeHtml(image.current_alt || '')}</textarea>
            <label><strong>ALT بعد التحسين</strong></label>
            <textarea rows="2" maxlength="70" placeholder="اكتب وصف ALT كمحترف سيو (حتى 70 حرفًا)" data-alt-image-value="${image.image_id}">${escapeHtml(image.optimized_alt || '')}</textarea>
          </div>
        </div>
      </div>
      `;
    }).join('');

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
        if (!item) return;
        const safe = sanitizeAltForSalla(input.value);
        if (safe !== input.value) input.value = safe;
        item.optimized_alt = safe;
      });
    });
    root.querySelectorAll('[data-alt-optimize-one]').forEach((button) => {
      button.addEventListener('click', () => {
        const imageId = Number(button.getAttribute('data-alt-optimize-one'));
        if (!imageId) return;
        optimizeSingleImageAlt(imageId);
      });
    });

    document.getElementById('optimize-selected-images')?.addEventListener('click', optimizeSelectedImagesAlt);
    document.getElementById('save-selected-images')?.addEventListener('click', saveSelectedImagesAlt);
  }

  async function openImageAltEditor(productId, focusImageId = null, autoOptimize = false) {
    const product = getProductById(productId);
    if (!product) return;

    state.altEditor = {
      productId: Number(productId),
      productName: product.name || 'منتج',
      productSku: product.sku || '',
      notice: null,
      images: getProductImages(product).map((image) => {
        const imageId = Number(image.id);
        const selected = focusImageId ? imageId === Number(focusImageId) : true;
        return {
          image_id: imageId,
          image_url: image.url || '',
          current_alt: image.alt || '',
          optimized_alt: image.alt || '',
          selected
        };
      })
    };
    renderImageAltBody();
    openImageAltModal();
    if (autoOptimize && focusImageId) {
      await optimizeSingleImageAlt(Number(focusImageId));
    }
  }

  async function optimizeImagesAlt(imageIds, successMessage) {
    if (!state.altEditor) return;
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
          language: getOutputLanguage('alt') || 'ar'
        })
      });
      const data = await response.json();
      if (!data.success) {
        state.altEditor.notice = { type: 'error', message: normalizeApiMessage(data.message, 'تعذر توليد ALT.') };
        renderImageAltBody();
        return;
      }

      (data.images || []).forEach((image) => {
        const item = state.altEditor?.images.find((img) => Number(img.image_id) === Number(image.image_id));
        if (item) item.optimized_alt = image.optimized_alt || item.optimized_alt;
      });
      state.altEditor.notice = { type: 'success', message: successMessage || 'تم توليد ALT للصور المحددة.' };
      renderImageAltBody();
    } catch (error) {
      state.altEditor.notice = { type: 'error', message: 'حدث خطأ أثناء توليد ALT.' };
      renderImageAltBody();
    }
  }

  async function optimizeSelectedImagesAlt() {
    if (!state.altEditor) return;
    const imageIds = state.altEditor.images.filter((image) => image.selected).map((image) => image.image_id);
    await optimizeImagesAlt(imageIds, 'تم توليد ALT للصور المحددة.');
  }

  async function optimizeSingleImageAlt(imageId) {
    if (!state.altEditor || !imageId) return;
    await optimizeImagesAlt([Number(imageId)], 'تم توليد ALT للصورة المحددة.');
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
          language: getOutputLanguage('alt') || 'ar'
        })
      });
      const data = await response.json();
      if (!data.success) {
        setAltAlert('error', normalizeApiMessage(data.message, 'تعذر تنفيذ التحسين الجماعي.'));
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
          language: getOutputLanguage('products') || 'ar',
          mode
        })
      });
      const data = await response.json();

      if (!data.success) {
        state.editor = {
          product,
          mode,
          notice: { type: 'error', message: normalizeApiMessage(data.message, 'فشل التحسين.') },
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
        state.editor.notice = { type: 'error', message: normalizeApiMessage(data.message, 'تعذّر حفظ المحتوى.') };
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
          root.innerHTML = `<div class="empty-state" style="grid-column:1/-1;"><p class="muted">${escapeHtml(normalizeApiMessage(data.message, 'تعذّر تحميل المنتجات.'))}</p></div>`;
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

  async function persistStoreSeoInstructions(options = {}) {
    const {
      showSuccess = true,
      showProgress = true,
      button = null
    } = options;

    const oldText = button?.textContent || 'حفظ تعليمات سيو المتجر';
    const payload = {
      store_seo_instructions: document.getElementById('setting-store-seo-instructions')?.value || '',
      output_language: getOutputLanguage('products') || ''
    };

    if (button) {
      button.disabled = true;
      button.textContent = 'جاري الحفظ...';
    }

    if (showProgress) {
      setStoreSeoAlert('success', 'جاري حفظ تعليمات سيو المتجر...');
    }

    try {
      const response = await apiFetch('/store-seo/instructions/save', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const data = await response.json();

      if (!data.success) {
        setStoreSeoAlert('error', normalizeApiMessage(data.message, 'تعذر حفظ تعليمات سيو المتجر.'));
        return false;
      }

      if (data.settings) {
        fillOptimizationSettings(data.settings);
      }

      if (showSuccess) {
        setStoreSeoAlert('success', normalizeApiMessage(data.message, 'تم حفظ تعليمات سيو المتجر.'));
      }

      return true;
    } catch (error) {
      setStoreSeoAlert('error', 'حدث خطأ أثناء حفظ تعليمات سيو المتجر.');
      return false;
    } finally {
      if (button) {
        button.disabled = false;
        button.textContent = oldText;
      }
    }
  }

  async function saveStoreSeoInstructions() {
    const button = document.getElementById('save-store-seo-instructions');
    await persistStoreSeoInstructions({
      showSuccess: true,
      showProgress: true,
      button
    });
  }

  function setOptimizationSettingsAlert(type, message, source = 'products') {
    const root = source === 'alt'
      ? document.getElementById('optimization-settings-alt-alert')
      : document.getElementById('optimization-settings-alert');
    if (!root) return;
    root.innerHTML = message ? `<div class="notice ${type}">${escapeHtml(message)}</div>` : '';
  }

  function fillOptimizationSettings(settings) {
    if (document.getElementById('setting-output-language')) document.getElementById('setting-output-language').value = settings.output_language || '';
    if (document.getElementById('alt-setting-output-language')) document.getElementById('alt-setting-output-language').value = settings.output_language || '';
    if (document.getElementById('setting-global-instructions')) document.getElementById('setting-global-instructions').value = settings.global_instructions || '';
    if (document.getElementById('alt-setting-global-instructions')) document.getElementById('alt-setting-global-instructions').value = settings.global_instructions || '';
    if (document.getElementById('setting-product-description-instructions')) document.getElementById('setting-product-description-instructions').value = settings.product_description_instructions || '';
    if (document.getElementById('setting-meta-title-instructions')) document.getElementById('setting-meta-title-instructions').value = settings.meta_title_instructions || '';
    if (document.getElementById('setting-meta-description-instructions')) document.getElementById('setting-meta-description-instructions').value = settings.meta_description_instructions || '';
    if (document.getElementById('setting-image-alt-instructions')) document.getElementById('setting-image-alt-instructions').value = settings.image_alt_instructions || '';
    if (document.getElementById('alt-setting-image-alt-instructions')) document.getElementById('alt-setting-image-alt-instructions').value = settings.image_alt_instructions || '';
    if (document.getElementById('setting-store-seo-instructions')) document.getElementById('setting-store-seo-instructions').value = settings.store_seo_instructions || '';
    if (document.getElementById('setting-sitemap-url')) document.getElementById('setting-sitemap-url').value = settings.sitemap_url || '';
    if (document.getElementById('setting-sitemap-links-count')) {
      const count = Number(settings.sitemap_links_count || 0);
      document.getElementById('setting-sitemap-links-count').textContent = `${count} روابط`;
    }
    if (document.getElementById('setting-sitemap-last-fetched')) {
      const raw = String(settings.sitemap_last_fetched_at || '').trim();
      if (!raw) {
        document.getElementById('setting-sitemap-last-fetched').textContent = 'لم يتم الجلب بعد';
      } else {
        const date = new Date(raw);
        document.getElementById('setting-sitemap-last-fetched').textContent = Number.isNaN(date.getTime())
          ? raw
          : date.toLocaleString('ar-SA');
      }
    }
  }

  async function loadOptimizationSettingsLegacy() {
    try {
      const data = await apiFetch('/settings').then((response) => response.json());
      if (!data.success) {
        setOptimizationSettingsAlert('error', normalizeApiMessage(data.message, 'تعذر جلب إعدادات التحسين.'));
        return;
      }
      fillOptimizationSettings(data.settings || {});
      setOptimizationSettingsAlert('', '');
    } catch (error) {
      setOptimizationSettingsAlert('error', 'تعذر جلب إعدادات التحسين.');
    }
  }

  async function saveOptimizationSettingsLegacy() {
    const button = document.getElementById('save-optimization-settings');
    const oldText = button?.textContent || 'حفظ التعليمات';
    const payload = {
      output_language: getOutputLanguage(),
      global_instructions: document.getElementById('setting-global-instructions')?.value || '',
      product_description_instructions: document.getElementById('setting-product-description-instructions')?.value || '',
      meta_title_instructions: document.getElementById('setting-meta-title-instructions')?.value || '',
      meta_description_instructions: document.getElementById('setting-meta-description-instructions')?.value || '',
      image_alt_instructions: document.getElementById('setting-image-alt-instructions')?.value || ''
    };

    if (button) {
      button.disabled = true;
      button.textContent = 'جاري الحفظ...';
    }
    setOptimizationSettingsAlert('success', 'جاري حفظ إعدادات التحسين...');

    try {
      const data = await apiFetch('/settings/save', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      }).then((response) => response.json());

      if (!data.success) {
        setOptimizationSettingsAlert('error', normalizeApiMessage(data.message, 'تعذر حفظ إعدادات التحسين.'));
        return;
      }

      fillOptimizationSettings(data.settings || payload);
      setOptimizationSettingsAlert('success', normalizeApiMessage(data.message, 'تم حفظ إعدادات التحسين.'));
    } catch (error) {
      setOptimizationSettingsAlert('error', 'حدث خطأ أثناء حفظ إعدادات التحسين.');
    } finally {
      if (button) {
        button.disabled = false;
        button.textContent = oldText;
      }
    }
  }

  function setSitemapAlert(type, message) {
    const root = document.getElementById('sitemap-alert');
    if (!root) return;
    root.innerHTML = message ? `<div class="notice ${type}">${escapeHtml(message)}</div>` : '';
  }

  async function saveSitemapSettings() {
    const button = document.getElementById('save-sitemap-settings');
    const oldText = button?.textContent || 'حفظ روابط السايت ماب';
    const sitemapUrl = document.getElementById('setting-sitemap-url')?.value.trim() || '';

    if (button) {
      button.disabled = true;
      button.textContent = 'جاري الحفظ...';
    }
    setSitemapAlert('success', 'جاري حفظ رابط السايت ماب...');

    try {
      const response = await apiFetch('/sitemap/save', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ sitemap_url: sitemapUrl })
      });
      const data = await response.json();

      if (!data.success) {
        setSitemapAlert('error', normalizeApiMessage(data.message, 'تعذر حفظ رابط السايت ماب.'));
        return;
      }

      const count = Number(data.links_count || 0);
      const linksCountEl = document.getElementById('setting-sitemap-links-count');
      if (linksCountEl) linksCountEl.textContent = `${count} رابط`;
      
      const lastFetchedEl = document.getElementById('setting-sitemap-last-fetched');
      if (lastFetchedEl) {
        const raw = String(data.last_fetched || '').trim();
        lastFetchedEl.textContent = raw ? formatDate(raw) : 'لم يتم الجلب بعد';
      }

      setSitemapAlert('success', normalizeApiMessage(data.message, 'تم حفظ رابط السايت ماب بنجاح.'));
    } catch (error) {
      setSitemapAlert('error', 'حدث خطأ أثناء حفظ رابط السايت ماب.');
    } finally {
      if (button) {
        button.disabled = false;
        button.textContent = oldText;
      }
    }
  }

  async function reconnectStore() {
    const button = document.getElementById('reconnect-store');
    const alertDiv = document.getElementById('reconnect-alert');
    const oldText = button?.textContent || 'إعادة ربط المتجر الآن';
    
    if (button) {
      button.disabled = true;
      button.textContent = 'جاري التحويل...';
    }
    if (alertDiv) {
      alertDiv.innerHTML = '<div class="notice success">جاري تحويلك لصفحة ربط المتجر...</div>';
    }

    try {
      const data = await apiFetch('/auth/reconnect').then((response) => response.json());
      if (data.success && data.redirect_url) {
        window.location.href = data.redirect_url;
      } else {
        if (alertDiv) {
          alertDiv.innerHTML = '<div class="notice error">تعذر الحصول على رابط إعادة الربط.</div>';
        }
        if (button) {
          button.disabled = false;
          button.textContent = oldText;
        }
      }
    } catch (error) {
      if (alertDiv) {
        alertDiv.innerHTML = '<div class="notice error">حدث خطأ أثناء محاولة إعادة الربط.</div>';
      }
      if (button) {
        button.disabled = false;
        button.textContent = oldText;
      }
    }
  }

  async function loadStoreSeo() {
    try {
      const data = await apiFetch('/store-seo').then((response) => response.json());
      if (!data.success) {
        setStoreSeoAlert('error', normalizeApiMessage(data.message, 'تعذر جلب سيو المتجر.'));
        return;
      }

      const seo = data.seo || {};
      const title = String(seo.title || seo.meta_title || seo.metadata_title || seo.homepage_title || '').trim();
      const description = String(seo.description || seo.meta_description || seo.metadata_description || seo.homepage_description || '').trim();
      const keywords = Array.isArray(seo.keywords)
        ? seo.keywords.map((item) => String(item || '').trim()).filter(Boolean).join(', ')
        : String(seo.keywords || '').trim();
      if (document.getElementById('store-seo-title')) document.getElementById('store-seo-title').value = title;
      if (document.getElementById('store-seo-description')) document.getElementById('store-seo-description').value = description;
      if (document.getElementById('store-seo-keywords')) document.getElementById('store-seo-keywords').value = keywords;
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

    const saved = await persistStoreSeoInstructions({
      showSuccess: false,
      showProgress: false
    });
    if (!saved) {
      if (button) {
        button.disabled = false;
        button.textContent = oldText;
      }
      return;
    }

    setStoreSeoAlert('success', 'جاري إنشاء سيو المتجر...');
    try {
      const data = await apiFetch('/store-seo/optimize', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          language: getOutputLanguage('products') || 'ar'
        })
      }).then((response) => response.json());

      if (!data.success) {
        setStoreSeoAlert('error', normalizeApiMessage(data.message, 'تعذر توليد سيو المتجر.'));
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

  async function saveStoreSeoLegacy() {
    const button = document.getElementById('save-store-seo');
    const oldText = button?.textContent || 'حفظ في المتجر';
    const title = document.getElementById('store-seo-title')?.value.trim() || '';
    const description = document.getElementById('store-seo-description')?.value.trim() || '';
    const keywords = document.getElementById('store-seo-keywords')?.value.trim() || '';

    if (!title || !description) {
      setStoreSeoAlert('error', 'أدخل عنوان ووصف المتجر قبل الحفظ.');
      return;
      }
    } finally {
      if (button) {
        button.disabled = false;
        button.textContent = oldText;
      }
    }
  }

  // Brand SEO State - add to existing state object
  state.brands = {
    list: [],
    current: null,
    page: 1,
    pageSize: 12,
    filter: {
      name: '',
      status: 'all',
    },
  };

  function setBrandSeoAlert(type, message) {
    const root = document.getElementById('brand-seo-alert');
    if (!root) return;
    root.innerHTML = message ? `<div class="notice ${type}">${escapeHtml(message)}</div>` : '';
  }

  function setBrandEditorAlert(type, message) {
    const root = document.getElementById('brand-editor-alert');
    if (!root) return;
    root.innerHTML = message ? `<div class="notice ${type}">${escapeHtml(message)}</div>` : '';
  }

  async function loadBrands() {
    try {
      setBrandSeoAlert('success', 'جاري تحميل الماركات...');
      const data = await apiFetch('/api/brands').then((response) => response.json());
      if (!data.success) {
        setBrandSeoAlert('error', normalizeApiMessage(data.message, 'تعذر تحميل الماركات.'));
        return;
      }
      state.brands.list = data.brands || [];
      renderBrandsList();
      setBrandSeoAlert('', '');
    } catch (error) {
      setBrandSeoAlert('error', 'تعذر تحميل الماركات.');
    }
  }

  function renderBrandsList() {
    const root = document.getElementById('brands-list');
    if (!root) return;

    const nameFilter = (document.getElementById('brand-filter-name')?.value || '').toLowerCase().trim();
    const statusFilter = document.getElementById('brand-filter-status')?.value || 'all';

    let filtered = state.brands.list;
    if (nameFilter) {
      filtered = filtered.filter((b) => (b.name || '').toLowerCase().includes(nameFilter));
    }
    if (statusFilter === 'has_description') {
      filtered = filtered.filter((b) => (b.description || '').trim() !== '');
    } else if (statusFilter === 'no_description') {
      filtered = filtered.filter((b) => (b.description || '').trim() === '');
    }

    if (!filtered.length) {
      root.innerHTML = '<div class="empty-state"><p class="muted" style="margin:0;">لا توجد ماركات.</p></div>';
      return;
    }

    root.innerHTML = `
      <div class="products-grid" style="margin-top:16px;">
        ${filtered.map((brand) => `
          <article class="product-card">
            <div style="display:flex;gap:12px;align-items:center;margin-bottom:12px;">
              ${brand.logo ? `<img src="${escapeHtml(brand.logo)}" alt="${escapeHtml(brand.name)}" style="width:48px;height:48px;object-fit:contain;border-radius:8px;">` : '<div style="width:48px;height:48px;background:var(--bg-soft);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:20px;">🏷️</div>'}
              <div style="flex:1;min-width:0;">
                <h3 class="product-title" style="margin:0;font-size:15px;">${escapeHtml(brand.name || 'ماركة')}</h3>
                <p class="muted" style="margin:4px 0 0;font-size:12px;">${brand.products_count || 0} منتج</p>
              </div>
            </div>
            <p class="muted" style="font-size:13px;margin:0 0 12px;line-height:1.5;">
              ${brand.description ? escapeHtml(brand.description).substring(0, 100) + (brand.description.length > 100 ? '...' : '') : 'بدون وصف'}
            </p>
            <button class="btn btn-sky" type="button" data-brand-id="${brand.id}" style="width:100%;padding:10px;">
              تحسين SEO
            </button>
          </article>
        `).join('')}
      </div>
    `;

    root.querySelectorAll('[data-brand-id]').forEach((button) => {
      button.addEventListener('click', () => {
        const brandId = Number(button.getAttribute('data-brand-id'));
        openBrandEditor(brandId);
      });
    });
  }

  function openBrandEditor(brandId) {
    const brand = state.brands.list.find((b) => b.id === brandId);
    if (!brand) return;

    state.brands.current = brand;
    document.getElementById('brand-editor-title').textContent = `تحرير SEO: ${brand.name}`;
    document.getElementById('brand-current-description').value = brand.description || '';
    document.getElementById('brand-optimized-description').value = brand.description || '';
    document.getElementById('brand-current-meta-title').value = brand.meta_title || '';
    document.getElementById('brand-optimized-meta-title').value = brand.meta_title || '';
    document.getElementById('brand-current-meta-description').value = brand.meta_description || '';
    document.getElementById('brand-optimized-meta-description').value = brand.meta_description || '';
    setBrandEditorAlert('', '');
    document.getElementById('brand-seo-editor').style.display = 'block';
  }

  function closeBrandEditor() {
    state.brands.current = null;
    document.getElementById('brand-seo-editor').style.display = 'none';
  }

  async function generateBrandSeo() {
    if (!state.brands.current) return;
    const button = document.getElementById('generate-brand-seo');
    const oldText = button?.textContent || 'توليد بالذكاء الاصطناعي';
    if (button) {
      button.disabled = true;
      button.textContent = 'جاري التوليد...';
    }
    setBrandEditorAlert('success', 'جاري توليد SEO للماركة...');

    try {
      const response = await apiFetch(`/brands/${state.brands.current.id}/optimize`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({})
      });
      const data = await response.json();

      if (!data.success) {
        setBrandEditorAlert('error', normalizeApiMessage(data.message, 'تعذر توليد SEO.'));
        return;
      }

      document.getElementById('brand-optimized-description').value = data.optimized_description || '';
      document.getElementById('brand-optimized-meta-title').value = data.optimized_meta_title || '';
      document.getElementById('brand-optimized-meta-description').value = data.optimized_meta_description || '';
      setBrandEditorAlert('success', 'تم توليد SEO بنجاح. راجع ثم احفظ.');
    } catch (error) {
      setBrandEditorAlert('error', 'حدث خطأ أثناء التوليد.');
    } finally {
      if (button) {
        button.disabled = false;
        button.textContent = oldText;
      }
    }
  }

  async function saveBrandSeoToStore() {
    if (!state.brands.current) return;
    const button = document.getElementById('save-brand-seo');
    const oldText = button?.textContent || 'حفظ في المتجر';
    if (button) {
      button.disabled = true;
      button.textContent = 'جاري الحفظ...';
    }
    setBrandEditorAlert('success', 'جاري الحفظ...');

    try {
      const response = await apiFetch(`/brands/${state.brands.current.id}/save`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          description: document.getElementById('brand-optimized-description')?.value || '',
          meta_title: document.getElementById('brand-optimized-meta-title')?.value || '',
          meta_description: document.getElementById('brand-optimized-meta-description')?.value || '',
        })
      });
      const data = await response.json();

      if (!data.success) {
        setBrandEditorAlert('error', normalizeApiMessage(data.message, 'تعذر الحفظ.'));
        return;
      }

      setBrandEditorAlert('success', 'تم حفظ SEO الماركة بنجاح.');
      closeBrandEditor();
      await loadBrands();
    } catch (error) {
      setBrandEditorAlert('error', 'حدث خطأ أثناء الحفظ.');
    } finally {
      if (button) {
        button.disabled = false;
        button.textContent = oldText;
      }
    }
  }

  function setKeywordAlert(type, message) {
    const root = document.getElementById('keyword-alert');
    if (!root) return;
    root.innerHTML = message ? `<div class="notice ${type}">${escapeHtml(message)}</div>` : '';
  }

  function renderKeywordHistory() {
    const root = document.getElementById('keyword-history-list');
    if (!root) return;

    const rows = Array.isArray(state.keywords.history) ? state.keywords.history : [];
    if (!rows.length) {
      root.innerHTML = '<div class="empty-state"><p class="muted" style="margin:0;">لا يوجد سجل بحث حتى الآن.</p></div>';
      return;
    }

    root.innerHTML = rows.map((row, index) => `
      <div class="card surface-soft" style="box-shadow:none;">
        <div style="display:flex;justify-content:space-between;gap:10px;align-items:center;flex-wrap:wrap;">
          <div>
            <strong>${escapeHtml(row.keyword || '-')}</strong>
            <p class="muted" style="margin:6px 0 0;">
              ${escapeHtml(row.country === 'sa' ? 'السعودية' : (row.country || '-'))}
              • ${escapeHtml(row.language === 'en' ? 'English' : 'العربية')}
              • ${escapeHtml(getKeywordDeviceLabel(row.device || 'desktop'))}
              • ${escapeHtml(formatDate(row.searched_at || ''))}
            </p>
          </div>
          <button class="btn btn-sky" type="button" data-keyword-history-index="${index}">استعراض</button>
        </div>
      </div>
    `).join('');

    root.querySelectorAll('[data-keyword-history-index]').forEach((button) => {
      button.addEventListener('click', () => {
        const idx = Number(button.getAttribute('data-keyword-history-index'));
        const item = rows[idx];
        if (!item || !item.result) return;
        if (document.getElementById('keyword-query')) document.getElementById('keyword-query').value = item.keyword || '';
        if (document.getElementById('keyword-country')) document.getElementById('keyword-country').value = item.country || 'sa';
        if (document.getElementById('keyword-language')) document.getElementById('keyword-language').value = item.language || 'ar';
        if (document.getElementById('keyword-device')) document.getElementById('keyword-device').value = item.device || 'desktop';
        state.keywords.lastResult = item.result;
        renderKeywordResults(state.keywords.lastResult);
        setKeywordAlert('success', 'تم استعراض نتيجة محفوظة بدون إجراء بحث جديد.');
      });
    });
  }

  async function loadKeywordHistory() {
    try {
      const data = await apiFetch('/keywords/history?limit=15').then((response) => response.json());
      if (!data.success) {
        state.keywords.history = [];
        renderKeywordHistory();
        return;
      }
      state.keywords.history = Array.isArray(data.history) ? data.history : [];
      renderKeywordHistory();
    } catch (error) {
      state.keywords.history = [];
      renderKeywordHistory();
    }
  }

  function formatKeywordNumber(value) {
    const numeric = Number(value || 0);
    if (!Number.isFinite(numeric)) return '0';
    return new Intl.NumberFormat('en-US', { maximumFractionDigits: 2 }).format(numeric);
  }

  function formatKeywordCurrency(value) {
    const numeric = Number(value || 0);
    if (!Number.isFinite(numeric)) return '$0.00';
    return `$${numeric.toFixed(2)}`;
  }

  function getKeywordDeviceLabel(device) {
    return device === 'mobile' ? 'جوال' : 'كمبيوتر';
  }

  function renderKeywordResults(payload) {
    const root = document.getElementById('keyword-results');
    const summary = document.getElementById('keyword-summary');
    if (!root || !summary) return;

    if (!payload) {
      summary.textContent = 'أدخل كلمة مفتاحية ثم اضغط بحث.';
      root.innerHTML = '<div class="empty-state"><p class="muted" style="margin:0;">لم يتم إجراء بحث بعد.</p></div>';
      return;
    }

    const metrics = payload.metrics || {};
    const trend = Array.isArray(payload.trend) ? payload.trend : [];
    const serp = payload.serp || {};
    const serpItems = Array.isArray(serp.items) ? serp.items : [];

    summary.textContent = `نتائج: ${payload.keyword || '-'} • ${payload.country_name || 'السعودية'} • ${getKeywordDeviceLabel(payload.device)}`;

    const trendRows = trend.length
      ? trend.map((row) => `
          <tr>
            <td>${escapeHtml(String(row.month || '-'))}/${escapeHtml(String(row.year || '-'))}</td>
            <td>${escapeHtml(formatKeywordNumber(row.search_volume || 0))}</td>
          </tr>
        `).join('')
      : '<tr><td colspan="2" class="muted">لا توجد بيانات اتجاه شهرية.</td></tr>';

    const serpRows = serpItems.length
      ? serpItems.map((item, index) => `
          <tr>
            <td style="width:56px;text-align:center;">${index + 1}</td>
            <td>${escapeHtml(item.title || '-')}</td>
            <td>${escapeHtml(item.domain || '-')}</td>
            <td><a href="${escapeHtml(item.url || '#')}" target="_blank" rel="noopener">فتح</a></td>
          </tr>
        `).join('')
      : '<tr><td colspan="4" class="muted">لا توجد نتائج SERP متاحة.</td></tr>';

    root.innerHTML = `
      <div class="grid" style="margin-top:0;">
        <div class="card surface-soft stat" style="min-height:auto;box-shadow:none;">
          <span class="stat-label">حجم البحث الشهري</span>
          <span class="stat-value" style="font-size:30px;">${escapeHtml(formatKeywordNumber(metrics.search_volume || 0))}</span>
        </div>
        <div class="card surface-soft stat" style="min-height:auto;box-shadow:none;">
          <span class="stat-label">المنافسة</span>
          <span class="stat-value" style="font-size:30px;">${escapeHtml(formatKeywordNumber(metrics.competition || 0))}</span>
          <span class="muted">${escapeHtml(metrics.competition_level || '-')}</span>
        </div>
        <div class="card surface-soft stat" style="min-height:auto;box-shadow:none;">
          <span class="stat-label">CPC تقريبي</span>
          <span class="stat-value" style="font-size:30px;">${escapeHtml(formatKeywordCurrency(metrics.cpc || 0))}</span>
        </div>
        <div class="card surface-soft stat" style="min-height:auto;box-shadow:none;">
          <span class="stat-label">مدى سعر الإعلان</span>
          <span class="stat-value" style="font-size:20px;">${escapeHtml(formatKeywordCurrency(metrics.low_bid || 0))} - ${escapeHtml(formatKeywordCurrency(metrics.high_bid || 0))}</span>
        </div>
      </div>

      <div class="grid" style="margin-top:16px;">
        <div class="card surface-soft" style="box-shadow:none;">
          <h3 style="margin:0 0 10px;">الاتجاه الشهري</h3>
          <div style="overflow:auto;border:1px solid rgba(202,177,149,.35);border-radius:14px;">
          <table style="margin:0;">
            <thead>
              <tr>
                <th>الشهر</th>
                <th>حجم البحث</th>
              </tr>
            </thead>
            <tbody>${trendRows}</tbody>
          </table>
          </div>
        </div>
        <div class="card surface-soft" style="box-shadow:none;">
          <h3 style="margin:0 0 10px;">أفضل نتائج البحث (SERP)</h3>
          <div style="overflow:auto;border:1px solid rgba(202,177,149,.35);border-radius:14px;">
          <table style="margin:0;">
            <thead>
              <tr>
                <th>#</th>
                <th>العنوان</th>
                <th>الدومين</th>
                <th>الرابط</th>
              </tr>
            </thead>
            <tbody>${serpRows}</tbody>
          </table>
          </div>
        </div>
      </div>
    `;
  }

  async function searchKeywordResearch() {
    const keyword = document.getElementById('keyword-query')?.value.trim() || '';
    const country = document.getElementById('keyword-country')?.value || 'sa';
    const language = document.getElementById('keyword-language')?.value || 'ar';
    const device = document.getElementById('keyword-device')?.value || 'desktop';
    const button = document.getElementById('keyword-search-btn');
    const oldText = button?.textContent || 'بحث الكلمات المفتاحية';

    if (!keyword) {
      setKeywordAlert('error', 'اكتب الكلمة المفتاحية أولًا.');
      return;
    }

    if (button) {
      button.disabled = true;
      button.textContent = 'جاري البحث...';
    }
    state.keywords.loading = true;
    setKeywordAlert('success', 'جاري جلب بيانات الكلمة المفتاحية...');

    try {
      const response = await apiFetch('/keywords/research', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ keyword, country, language, device }),
      });

      let data = null;
      try {
        data = await response.json();
      } catch (parseError) {
        const raw = await response.text();
        throw new Error(raw || `HTTP ${response.status}`);
      }

      if (!data.success) {
        setKeywordAlert('error', normalizeApiMessage(data.message, 'تعذر جلب بيانات الكلمة المفتاحية.'));
        return;
      }

      state.keywords.lastResult = data.keyword_data || null;
      try {
        renderKeywordResults(state.keywords.lastResult);
      } catch (renderError) {
        console.error('Keyword render error:', renderError);
        setKeywordAlert('error', 'تم جلب البيانات لكن حدث خطأ في عرض النتائج. حدّث الصفحة وحاول مرة أخرى.');
        return;
      }

      try {
        if (data.history_entry) {
          state.keywords.history = [data.history_entry, ...(state.keywords.history || [])].slice(0, 15);
          renderKeywordHistory();
        } else {
          await loadKeywordHistory();
        }
      } catch (historyError) {
        console.error('Keyword history error:', historyError);
      }
      setKeywordAlert('success', 'تم جلب بيانات الكلمة المفتاحية بنجاح.');
    } catch (error) {
      setKeywordAlert('error', 'حدث خطأ أثناء جلب بيانات الكلمات المفتاحية.');
    } finally {
      state.keywords.loading = false;
      if (button) {
        button.disabled = false;
        button.textContent = oldText;
      }
    }
  }

  function setDomainSeoAlert(type, message) {
    const root = document.getElementById('domain-seo-alert');
    if (!root) return;
    root.innerHTML = message ? `<div class="notice ${type}">${escapeHtml(message)}</div>` : '';
  }

  function formatMoneyUsd(value) {
    const numeric = Number(value || 0);
    if (!Number.isFinite(numeric)) return '$0.00';
    return `$${numeric.toFixed(2)}`;
  }

  function fillDomainSeoForm(config) {
    if (document.getElementById('domain-seo-domain')) document.getElementById('domain-seo-domain').value = config.domain || '';
    if (document.getElementById('domain-seo-country')) document.getElementById('domain-seo-country').value = config.country || 'sa';
    if (document.getElementById('domain-seo-device')) document.getElementById('domain-seo-device').value = config.device || 'desktop';
  }

  function renderDomainSeoResults(payload) {
    const root = document.getElementById('domain-seo-results');
    const summary = document.getElementById('domain-seo-summary');
    if (!root || !summary) return;

    if (!payload || !payload.last_data) {
      summary.textContent = 'احفظ الدومين واضغط تحديث البيانات.';
      root.innerHTML = '<div class="empty-state"><p class="muted" style="margin:0;">لا توجد بيانات محفوظة بعد.</p></div>';
      return;
    }

    const data = payload.last_data || {};
    const overview = data.overview || {};
    const organic = overview.organic || {};
    const paid = overview.paid || {};
    const topKeywords = Array.isArray(data.top_keywords) ? data.top_keywords : [];
    const allKeywords = Array.isArray(data.all_keywords) ? data.all_keywords : topKeywords;
    const normalizeDomain = (value) => String(value || '')
      .toLowerCase()
      .replace(/^www\./, '')
      .replace(/\.+$/, '')
      .trim();
    const targetDomain = normalizeDomain(payload.domain || data.domain || '');
    const competitors = (Array.isArray(data.competitors) ? data.competitors : [])
      .filter((item) => {
        const candidate = normalizeDomain(item?.domain || '');
        if (!candidate) return false;
        if (!targetDomain) return true;
        return candidate !== targetDomain;
      });
    const fetchedAt = data.fetched_at ? formatDate(data.fetched_at) : '-';
    const refreshedAt = payload.refreshed_at ? formatDate(payload.refreshed_at) : '-';
    const deviceLabel = (payload.device || 'desktop') === 'mobile' ? 'جوال' : 'كمبيوتر';

    summary.textContent = `الدومين: ${payload.domain || '-'} • السعودية • ${deviceLabel} • آخر تحديث: ${refreshedAt}`;

    const keywordsRows = topKeywords.length
      ? topKeywords.map((item, index) => `
          <tr>
            <td style="width:56px;text-align:center;">${index + 1}</td>
            <td style="min-width:240px;max-width:340px;white-space:normal;line-height:1.55;">${escapeHtml(item.keyword || '-')}</td>
            <td>${escapeHtml(formatKeywordNumber(item.position || 0))}</td>
            <td>${escapeHtml(formatKeywordNumber(item.search_volume || 0))}</td>
            <td>${escapeHtml(formatKeywordCurrency(item.cpc || 0))}</td>
            <td>${escapeHtml(item.intent || '-')}</td>
          </tr>
        `).join('')
      : '<tr><td colspan="6" class="muted">لا توجد كلمات مرتبة حاليًا.</td></tr>';

    const allKeywordsRows = allKeywords.length
      ? allKeywords.map((item, index) => `
          <tr>
            <td style="width:56px;text-align:center;">${index + 1}</td>
            <td style="min-width:240px;max-width:340px;white-space:normal;line-height:1.55;">${escapeHtml(item.keyword || '-')}</td>
            <td>${escapeHtml(formatKeywordNumber(item.position || 0))}</td>
            <td>${escapeHtml(formatKeywordNumber(item.search_volume || 0))}</td>
            <td>${escapeHtml(formatKeywordCurrency(item.cpc || 0))}</td>
            <td>${escapeHtml(item.intent || '-')}</td>
          </tr>
        `).join('')
      : '<tr><td colspan="6" class="muted">Ù„Ø§ ØªÙˆØ¬Ø¯ Ø¨ÙŠØ§Ù†Ø§Øª Ø¥Ø¶Ø§ÙÙŠØ© Ù„Ø¹Ø±Ø¶Ù‡Ø§.</td></tr>';

    const competitorsRows = competitors.length
      ? competitors.map((item, index) => `
          <tr>
            <td style="width:56px;text-align:center;">${index + 1}</td>
            <td>${escapeHtml(item.domain || '-')}</td>
            <td>${escapeHtml(formatKeywordNumber(item.intersections || 0))}</td>
            <td>${escapeHtml(formatKeywordNumber(item.avg_position || 0))}</td>
            <td>${escapeHtml(formatKeywordNumber(item.organic_keywords || 0))}</td>
            <td>${escapeHtml(formatKeywordNumber(item.organic_traffic || 0))}</td>
            <td>${escapeHtml(formatMoneyUsd(item.organic_cost || 0))}</td>
          </tr>
        `).join('')
      : '<tr><td colspan="7" class="muted">لا توجد بيانات منافسين متاحة.</td></tr>';

    root.innerHTML = `
      <div class="grid" style="margin-top:0;">
        <div class="card surface-soft stat" style="min-height:auto;box-shadow:none;">
          <span class="stat-label">Organic Keywords</span>
          <span class="stat-value" style="font-size:30px;">${escapeHtml(formatKeywordNumber(organic.keywords_count || 0))}</span>
        </div>
        <div class="card surface-soft stat" style="min-height:auto;box-shadow:none;">
          <span class="stat-label">Organic Traffic (ETV)</span>
          <span class="stat-value" style="font-size:30px;">${escapeHtml(formatKeywordNumber(organic.traffic || 0))}</span>
        </div>
        <div class="card surface-soft stat" style="min-height:auto;box-shadow:none;">
          <span class="stat-label">Organic Traffic Cost</span>
          <span class="stat-value" style="font-size:30px;">${escapeHtml(formatMoneyUsd(organic.traffic_cost || 0))}</span>
        </div>
        <div class="card surface-soft stat" style="min-height:auto;box-shadow:none;">
          <span class="stat-label">Paid Keywords</span>
          <span class="stat-value" style="font-size:30px;">${escapeHtml(formatKeywordNumber(paid.keywords_count || 0))}</span>
        </div>
        <div class="card surface-soft stat" style="min-height:auto;box-shadow:none;">
          <span class="stat-label">Paid Traffic (ETV)</span>
          <span class="stat-value" style="font-size:30px;">${escapeHtml(formatKeywordNumber(paid.traffic || 0))}</span>
        </div>
        <div class="card surface-soft stat" style="min-height:auto;box-shadow:none;">
          <span class="stat-label">Paid Traffic Cost</span>
          <span class="stat-value" style="font-size:30px;">${escapeHtml(formatMoneyUsd(paid.traffic_cost || 0))}</span>
        </div>
      </div>

      <div class="grid" style="margin-top:16px;">
        <div class="card surface-soft" style="box-shadow:none;">
          <h3 style="margin:0 0 10px;">ملخص ترتيب الكلمات المفتاحية</h3>
          <div style="overflow:auto;border:1px solid rgba(202,177,149,.35);border-radius:14px;">
            <table style="margin:0;">
            <tbody>
              <tr><th>Top 3</th><td>${escapeHtml(formatKeywordNumber((organic.positions || {}).top_3 || 0))}</td></tr>
              <tr><th>Top 10</th><td>${escapeHtml(formatKeywordNumber((organic.positions || {}).top_10 || 0))}</td></tr>
              <tr><th>Top 20</th><td>${escapeHtml(formatKeywordNumber((organic.positions || {}).top_20 || 0))}</td></tr>
              <tr><th>Top 100</th><td>${escapeHtml(formatKeywordNumber((organic.positions || {}).top_100 || 0))}</td></tr>
              <tr><th>جديد</th><td>${escapeHtml(formatKeywordNumber(organic.new || 0))}</td></tr>
              <tr><th>صاعد</th><td>${escapeHtml(formatKeywordNumber(organic.up || 0))}</td></tr>
              <tr><th>هابط</th><td>${escapeHtml(formatKeywordNumber(organic.down || 0))}</td></tr>
              <tr><th>مفقود</th><td>${escapeHtml(formatKeywordNumber(organic.lost || 0))}</td></tr>
            </tbody>
            </table>
          <details style="margin-top:12px;">
            <summary class="btn btn-sky" style="display:inline-flex;cursor:pointer;">استعراض جميع الكلمات (${escapeHtml(formatKeywordNumber(allKeywords.length))})</summary>
            <div style="margin-top:6px;border:1px solid rgba(202,177,149,.35);border-radius:14px;overflow:auto;max-height:460px;">
              <table style="margin:0;min-width:820px;">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>الكلمة</th>
                    <th>الترتيب</th>
                    <th>الحجم</th>
                    <th>CPC</th>
                    <th>النية</th>
                  </tr>
                </thead>
                <tbody>${allKeywordsRows}</tbody>
              </table>
            </div>
          </details>
          <p class="muted" style="margin:10px 0 0;">تاريخ آخر جلب: ${escapeHtml(fetchedAt)}</p>
        </div>

        <div class="card surface-soft" style="box-shadow:none;">
          <h3 style="margin:0 0 10px;">اهم الكلمات المفتاحية</h3>
          <div style="overflow:auto;border:1px solid rgba(202,177,149,.35);border-radius:14px;">
          <table style="margin:0;">
            <thead>
              <tr>
                <th>#</th>
                <th>الكلمة</th>
                <th>الترتيب</th>
                <th>الحجم</th>
                <th>CPC</th>
                <th>النية</th>
              </tr>
            </thead>
            <tbody>${keywordsRows}</tbody>
          </table>
          </div>
        </div>
      </div>

      <div class="card surface-soft" style="box-shadow:none;margin-top:16px;">
        <h3 style="margin:0 0 10px;">أهم المنافسين</h3>
        <table>
          <thead>
            <tr>
              <th>#</th>
              <th>الدومين</th>
              <th>تقاطع الكلمات</th>
              <th>متوسط الترتيب</th>
              <th>Organic Keywords</th>
              <th>Organic Traffic</th>
              <th>Traffic Cost</th>
            </tr>
          </thead>
          <tbody>${competitorsRows}</tbody>
        </table>
      </div>
    `;
  }

  async function loadDomainSeo() {
    try {
      const data = await apiFetch('/domain-seo').then((response) => response.json());
      if (!data.success) {
        setDomainSeoAlert('error', normalizeApiMessage(data.message, 'تعذّر تحميل إعدادات سيو الدومين.'));
        return;
      }

      const payload = data.domain_seo || {};
      state.domainSeo.initialized = true;
      state.domainSeo.data = payload;
      state.domainSeo.config = {
        domain: payload.domain || '',
        country: payload.country || 'sa',
        device: payload.device || 'desktop'
      };
      fillDomainSeoForm(state.domainSeo.config);
      renderDomainSeoResults(payload);
      await loadDomainSeoHistory();
      setDomainSeoAlert('', '');
    } catch (error) {
      setDomainSeoAlert('error', 'تعذّر تحميل قسم سيو الدومين.');
    }
  }

  function renderDomainSeoHistory() {
    const root = document.getElementById('domain-seo-history-list');
    if (!root) return;

    const rows = Array.isArray(state.domainSeo.history) ? state.domainSeo.history : [];
    if (!rows.length) {
      root.innerHTML = '<div class="empty-state"><p class="muted" style="margin:0;">لا يوجد سجل تحديث للدومين حتى الآن.</p></div>';
      return;
    }

    root.innerHTML = rows.map((row, index) => `
      <div class="card surface-soft" style="box-shadow:none;">
        <div style="display:flex;justify-content:space-between;gap:10px;align-items:center;flex-wrap:wrap;">
          <div>
            <strong>${escapeHtml(row.domain || '-')}</strong>
            <p class="muted" style="margin:6px 0 0;">
              ${escapeHtml(row.country === 'sa' ? 'السعودية' : (row.country || '-'))}
              • ${escapeHtml(getKeywordDeviceLabel(row.device || 'desktop'))}
              • ${escapeHtml(formatDate(row.searched_at || ''))}
            </p>
          </div>
          <button class="btn btn-sky" type="button" data-domain-history-index="${index}">استعراض</button>
        </div>
      </div>
    `).join('');

    root.querySelectorAll('[data-domain-history-index]').forEach((button) => {
      button.addEventListener('click', () => {
        const idx = Number(button.getAttribute('data-domain-history-index'));
        const item = rows[idx];
        if (!item || !item.result) return;
        const payload = {
          domain: item.domain || '',
          country: item.country || 'sa',
          device: item.device || 'desktop',
          refreshed_at: item.searched_at || '',
          last_data: item.result,
        };
        state.domainSeo.data = payload;
        state.domainSeo.config = {
          domain: payload.domain,
          country: payload.country,
          device: payload.device,
        };
        fillDomainSeoForm(state.domainSeo.config);
        renderDomainSeoResults(payload);
        setDomainSeoAlert('success', 'تم استعراض نتيجة محفوظة بدون تحديث جديد.');
      });
    });
  }

  async function loadDomainSeoHistory() {
    try {
      const data = await apiFetch('/domain-seo/history?limit=15').then((response) => response.json());
      if (!data.success) {
        state.domainSeo.history = [];
        renderDomainSeoHistory();
        return;
      }
      state.domainSeo.history = Array.isArray(data.history) ? data.history : [];
      renderDomainSeoHistory();
    } catch (error) {
      state.domainSeo.history = [];
      renderDomainSeoHistory();
    }
  }

  async function saveDomainSeoConfig() {
    const button = document.getElementById('domain-seo-save-btn');
    const oldText = button?.textContent || 'حفظ الدومين';
    const domain = document.getElementById('domain-seo-domain')?.value.trim() || '';
    const country = document.getElementById('domain-seo-country')?.value || 'sa';
    const device = document.getElementById('domain-seo-device')?.value || 'desktop';

    if (!domain) {
      setDomainSeoAlert('error', 'أدخل الدومين أولًا.');
      return;
    }

    if (button) {
      button.disabled = true;
      button.textContent = 'جاري الحفظ...';
    }
    setDomainSeoAlert('success', 'جاري حفظ الدومين...');

    try {
      const data = await apiFetch('/domain-seo/save', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ domain, country, device }),
      }).then((response) => response.json());

      if (!data.success) {
        setDomainSeoAlert('error', normalizeApiMessage(data.message, 'تعذّر حفظ إعدادات الدومين.'));
        return;
      }

      const payload = data.domain_seo || {};
      state.domainSeo.data = payload;
      state.domainSeo.config = {
        domain: payload.domain || '',
        country: payload.country || 'sa',
        device: payload.device || 'desktop'
      };
      fillDomainSeoForm(state.domainSeo.config);
      renderDomainSeoResults(payload);
      setDomainSeoAlert('success', normalizeApiMessage(data.message, 'تم حفظ الدومين بنجاح.'));
    } catch (error) {
      setDomainSeoAlert('error', 'حدث خطأ أثناء حفظ إعدادات الدومين.');
    } finally {
      if (button) {
        button.disabled = false;
        button.textContent = oldText;
      }
    }
  }

  async function refreshDomainSeoData() {
    const button = document.getElementById('domain-seo-refresh-btn');
    const oldText = button?.textContent || 'تحديث البيانات';
    const domain = document.getElementById('domain-seo-domain')?.value.trim() || '';
    const country = document.getElementById('domain-seo-country')?.value || 'sa';
    const device = document.getElementById('domain-seo-device')?.value || 'desktop';

    if (!domain) {
      setDomainSeoAlert('error', 'احفظ الدومين أولًا ثم حدّث البيانات.');
      return;
    }

    if (button) {
      button.disabled = true;
      button.textContent = 'جاري التحديث...';
    }
    setDomainSeoAlert('success', 'جاري جلب بيانات الدومين...');

    try {
      const data = await apiFetch('/domain-seo/refresh', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ domain, country, device }),
      }).then((response) => response.json());

      if (!data.success) {
        setDomainSeoAlert('error', normalizeApiMessage(data.message, 'تعذّر تحديث بيانات الدومين.'));
        return;
      }

      const payload = data.domain_seo || {};
      state.domainSeo.data = payload;
      state.domainSeo.config = {
        domain: payload.domain || '',
        country: payload.country || 'sa',
        device: payload.device || 'desktop'
      };
      fillDomainSeoForm(state.domainSeo.config);
      renderDomainSeoResults(payload);
      if (data.history_entry) {
        state.domainSeo.history = [data.history_entry, ...(state.domainSeo.history || [])].slice(0, 15);
        renderDomainSeoHistory();
      } else {
        await loadDomainSeoHistory();
      }
      setDomainSeoAlert('success', normalizeApiMessage(data.message, 'تم تحديث بيانات سيو الدومين.'));
    } catch (error) {
      setDomainSeoAlert('error', 'حدث خطأ أثناء تحديث بيانات الدومين.');
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
        root.innerHTML = `<h2>الاستهلاك</h2><p class="muted">${escapeHtml(normalizeApiMessage(data.message, 'تعذر تحميل بيانات الاستهلاك.'))}</p>`;
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
      root.innerHTML = `<div class="empty-state"><p class="muted">${escapeHtml(normalizeApiMessage(data.message, 'تعذّر تحميل السجل.'))}</p></div>`;
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

  function applyAltFilters() {
    state.alt.filters = {
      name: document.getElementById('alt-filter-name')?.value.trim() || '',
      sku: document.getElementById('alt-filter-sku')?.value.trim() || '',
      status: document.getElementById('alt-filter-status')?.value || 'all',
      content: document.getElementById('alt-filter-content')?.value || 'all'
    };
    state.alt.page = 1;
    renderAltProducts();
  }

  function clearAltFilters() {
    if (document.getElementById('alt-filter-name')) document.getElementById('alt-filter-name').value = '';
    if (document.getElementById('alt-filter-sku')) document.getElementById('alt-filter-sku').value = '';
    if (document.getElementById('alt-filter-status')) document.getElementById('alt-filter-status').value = 'all';
    if (document.getElementById('alt-filter-content')) document.getElementById('alt-filter-content').value = 'all';

    state.alt.filters = { name: '', sku: '', status: 'all', content: 'all' };
    state.alt.quickFilter = 'all';
    state.alt.page = 1;
    renderAltProducts();
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
    if (section === 'keywords') {
      renderKeywordResults(state.keywords.lastResult);
      loadKeywordHistory();
    }
    if (section === 'domain-seo') {
      if (!state.domainSeo.initialized) {
        loadDomainSeo();
      } else {
        fillDomainSeoForm(state.domainSeo.config);
        renderDomainSeoResults(state.domainSeo.data);
        loadDomainSeoHistory();
      }
    }
    if (section === 'operations') {
      loadOperations();
    }
    if (section === 'brand-seo') {
      if (!state.brands.list.length) {
        loadBrands();
      }
    }
    if (section === 'brand-seo') {
      if (!state.brands.list.length) {
        loadBrands();
      }
    }
  }

  // Override settings handlers to support both Products and ALT sections.
  async function loadOptimizationSettings() {
    try {
      const data = await apiFetch('/settings').then((response) => response.json());
      if (!data.success) {
        const message = normalizeApiMessage(data.message, 'تعذر جلب إعدادات التحسين.');
        setOptimizationSettingsAlert('error', message, 'products');
        setOptimizationSettingsAlert('error', message, 'alt');
        return;
      }
      fillOptimizationSettings(data.settings || {});
      setOptimizationSettingsAlert('', '', 'products');
      setOptimizationSettingsAlert('', '', 'alt');
    } catch (error) {
      setOptimizationSettingsAlert('error', 'تعذر جلب إعدادات التحسين.', 'products');
      setOptimizationSettingsAlert('error', 'تعذر جلب إعدادات التحسين.', 'alt');
    }
  }

  async function saveOptimizationSettings(source = 'products') {
    const button = source === 'alt'
      ? document.getElementById('save-optimization-settings-alt')
      : document.getElementById('save-optimization-settings');
    const oldText = button?.textContent || 'حفظ التعليمات';

    const payload = {
      output_language: getOutputLanguage(source),
      global_instructions: source === 'alt'
        ? readSettingValue('alt-setting-global-instructions', 'setting-global-instructions', '')
        : readSettingValue('setting-global-instructions', 'alt-setting-global-instructions', ''),
      product_description_instructions: document.getElementById('setting-product-description-instructions')?.value || '',
      meta_title_instructions: document.getElementById('setting-meta-title-instructions')?.value || '',
      meta_description_instructions: document.getElementById('setting-meta-description-instructions')?.value || '',
      image_alt_instructions: source === 'alt'
        ? readSettingValue('alt-setting-image-alt-instructions', 'setting-image-alt-instructions', '')
        : undefined
    };

    if (button) {
      button.disabled = true;
      button.textContent = 'جاري الحفظ...';
    }
    setOptimizationSettingsAlert('success', 'جاري حفظ إعدادات التحسين...', source);

    try {
      const data = await apiFetch('/settings/save', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      }).then((response) => response.json());

      if (!data.success) {
        setOptimizationSettingsAlert('error', normalizeApiMessage(data.message, 'تعذر حفظ إعدادات التحسين.'), source);
        return;
      }

      fillOptimizationSettings(data.settings || payload);
      setOptimizationSettingsAlert('success', normalizeApiMessage(data.message, 'تم حفظ إعدادات التحسين.'), source);
    } catch (error) {
      setOptimizationSettingsAlert('error', 'حدث خطأ أثناء حفظ إعدادات التحسين.', source);
    } finally {
      if (button) {
        button.disabled = false;
        button.textContent = oldText;
      }
    }
  }

  // Override keywords renderer with richer output (includes related keywords table).
  function renderKeywordResults(payload) {
    const root = document.getElementById('keyword-results');
    const summary = document.getElementById('keyword-summary');
    if (!root || !summary) return;

    if (!payload) {
      summary.textContent = 'أدخل كلمة مفتاحية ثم اضغط بحث.';
      root.innerHTML = '<div class="empty-state"><p class="muted" style="margin:0;">لم يتم إجراء بحث بعد.</p></div>';
      return;
    }

    const metrics = payload.metrics || {};
    const trend = Array.isArray(payload.trend) ? payload.trend : [];
    const serp = payload.serp || {};
    const serpItems = Array.isArray(serp.items) ? serp.items : [];
    const relatedKeywords = Array.isArray(payload.related_keywords) ? payload.related_keywords : [];
    const keywordSuggestions = Array.isArray(payload.keyword_suggestions) ? payload.keyword_suggestions : [];

    summary.textContent = `نتائج: ${payload.keyword || '-'} • ${payload.country_name || 'السعودية'} • ${getKeywordDeviceLabel(payload.device)}`;

    const trendRows = trend.length
      ? trend.map((row) => `
          <tr>
            <td>${escapeHtml(String(row.month || '-'))}/${escapeHtml(String(row.year || '-'))}</td>
            <td>${escapeHtml(formatKeywordNumber(row.search_volume || 0))}</td>
          </tr>
        `).join('')
      : '<tr><td colspan="2" class="muted">لا توجد بيانات اتجاه شهرية.</td></tr>';

    const serpRows = serpItems.length
      ? serpItems.map((item, index) => `
          <tr>
            <td style="width:56px;text-align:center;">${index + 1}</td>
            <td>${escapeHtml(item.title || '-')}</td>
            <td>${escapeHtml(item.domain || '-')}</td>
            <td><a href="${escapeHtml(item.url || '#')}" target="_blank" rel="noopener">فتح</a></td>
          </tr>
        `).join('')
      : '<tr><td colspan="4" class="muted">لا توجد نتائج SERP متاحة.</td></tr>';

    const relatedRows = relatedKeywords.length
      ? relatedKeywords.map((item, index) => `
          <tr>
            <td style="width:56px;text-align:center;">${index + 1}</td>
            <td style="min-width:240px;max-width:340px;white-space:normal;line-height:1.55;">${escapeHtml(item.keyword || '-')}</td>
            <td>${escapeHtml(formatKeywordNumber(item.search_volume || 0))}</td>
            <td>${escapeHtml(item.competition_level || '-')}</td>
            <td>${escapeHtml(formatKeywordNumber(item.competition || 0))}</td>
            <td>${escapeHtml(formatKeywordCurrency(item.cpc || 0))}</td>
          </tr>
        `).join('')
      : '<tr><td colspan="6" class="muted">لا توجد كلمات مشابهة متاحة.</td></tr>';

    const suggestionRows = keywordSuggestions.length
      ? keywordSuggestions.map((item, index) => `
          <tr>
            <td style="width:56px;text-align:center;">${index + 1}</td>
            <td style="min-width:240px;max-width:340px;white-space:normal;line-height:1.55;">${escapeHtml(item.keyword || '-')}</td>
            <td>${escapeHtml(formatKeywordNumber(item.search_volume || 0))}</td>
            <td>${escapeHtml(item.competition_level || '-')}</td>
            <td>${escapeHtml(formatKeywordNumber(item.competition || 0))}</td>
            <td>${escapeHtml(formatKeywordCurrency(item.cpc || 0))}</td>
          </tr>
        `).join('')
      : '<tr><td colspan="6" class="muted">لا توجد اقتراحات كلمات رئيسية متاحة.</td></tr>';

    root.innerHTML = `
      <div class="grid" style="margin-top:0;">
        <div class="card surface-soft stat" style="min-height:auto;box-shadow:none;">
          <span class="stat-label">حجم البحث الشهري</span>
          <span class="stat-value" style="font-size:30px;">${escapeHtml(formatKeywordNumber(metrics.search_volume || 0))}</span>
        </div>
        <div class="card surface-soft stat" style="min-height:auto;box-shadow:none;">
          <span class="stat-label">المنافسة</span>
          <span class="stat-value" style="font-size:30px;">${escapeHtml(formatKeywordNumber(metrics.competition || 0))}</span>
          <span class="muted">${escapeHtml(metrics.competition_level || '-')}</span>
        </div>
        <div class="card surface-soft stat" style="min-height:auto;box-shadow:none;">
          <span class="stat-label">CPC تقريبي</span>
          <span class="stat-value" style="font-size:30px;">${escapeHtml(formatKeywordCurrency(metrics.cpc || 0))}</span>
        </div>
        <div class="card surface-soft stat" style="min-height:auto;box-shadow:none;">
          <span class="stat-label">مدى سعر الإعلان</span>
          <span class="stat-value" style="font-size:20px;">${escapeHtml(formatKeywordCurrency(metrics.low_bid || 0))} - ${escapeHtml(formatKeywordCurrency(metrics.high_bid || 0))}</span>
        </div>
      </div>

      <div class="grid" style="margin-top:16px;">
        <div class="card surface-soft" style="box-shadow:none;">
          <h3 style="margin:0 0 10px;">الاتجاه الشهري</h3>
          <div style="overflow:auto;border:1px solid rgba(202,177,149,.35);border-radius:14px;">
            <table style="margin:0;">
            <thead>
              <tr>
                <th>الشهر</th>
                <th>حجم البحث</th>
              </tr>
            </thead>
            <tbody>${trendRows}</tbody>
          </table>
          </div>
        </div>
        <div class="card surface-soft" style="box-shadow:none;">
          <h3 style="margin:0 0 10px;">أفضل نتائج البحث (SERP)</h3>
          <div style="overflow:auto;border:1px solid rgba(202,177,149,.35);border-radius:14px;">
            <table style="margin:0;">
            <thead>
              <tr>
                <th>#</th>
                <th>العنوان</th>
                <th>الدومين</th>
                <th>الرابط</th>
              </tr>
            </thead>
            <tbody>${serpRows}</tbody>
          </table>
          </div>
        </div>
      </div>

      <div class="card surface-soft" style="box-shadow:none;margin-top:16px;">
        <h3 style="margin:0 0 10px;">الكلمات المفتاحية ذات الصلة</h3>
        <table>
          <thead>
            <tr>
              <th>#</th>
              <th>الكلمة</th>
              <th>حجم البحث</th>
              <th>مستوى المنافسة</th>
              <th>مؤشر المنافسة</th>
              <th>CPC</th>
            </tr>
          </thead>
          <tbody>${relatedRows}</tbody>
        </table>
      </div>

      <div class="card surface-soft" style="box-shadow:none;margin-top:16px;">
        <h3 style="margin:0 0 10px;">اقتراحات الكلمات الرئيسية</h3>
        <table>
          <thead>
            <tr>
              <th>#</th>
              <th>الكلمة</th>
              <th>حجم البحث</th>
              <th>مستوى المنافسة</th>
              <th>مؤشر المنافسة</th>
              <th>CPC</th>
            </tr>
          </thead>
          <tbody>${suggestionRows}</tbody>
        </table>
      </div>
    `;
  }

  // Override Domain SEO renderer to support full keyword list view and resilient empty states.
  function renderDomainSeoResults(payload) {
    const root = document.getElementById('domain-seo-results');
    const summary = document.getElementById('domain-seo-summary');
    if (!root || !summary) return;

    if (!payload || !payload.last_data) {
      summary.textContent = 'احفظ الدومين واضغط تحديث البيانات.';
      root.innerHTML = '<div class="empty-state"><p class="muted" style="margin:0;">لا توجد بيانات محفوظة بعد.</p></div>';
      return;
    }

    const data = payload.last_data || {};
    const overview = data.overview || {};
    const organic = overview.organic || {};
    const paid = overview.paid || {};
    const topKeywords = Array.isArray(data.top_keywords) ? data.top_keywords : [];
    const allKeywords = Array.isArray(data.all_keywords) ? data.all_keywords : topKeywords;
    const normalizeDomain = (value) => String(value || '')
      .toLowerCase()
      .replace(/^www\./, '')
      .replace(/\.+$/, '')
      .trim();
    const targetDomain = normalizeDomain(payload.domain || data.domain || '');
    const competitors = (Array.isArray(data.competitors) ? data.competitors : [])
      .filter((item) => {
        const candidate = normalizeDomain(item?.domain || '');
        if (!candidate) return false;
        if (!targetDomain) return true;
        return candidate !== targetDomain;
      });

    const fetchedAt = data.fetched_at ? formatDate(data.fetched_at) : '-';
    const refreshedAt = payload.refreshed_at ? formatDate(payload.refreshed_at) : '-';
    const deviceLabel = (payload.device || 'desktop') === 'mobile' ? 'جوال' : 'كمبيوتر';

    summary.textContent = `الدومين: ${payload.domain || '-'} • السعودية • ${deviceLabel} • آخر تحديث: ${refreshedAt}`;

    const keywordsRows = topKeywords.length
      ? topKeywords.map((item, index) => `
          <tr>
            <td style="width:56px;text-align:center;">${index + 1}</td>
            <td style="min-width:240px;max-width:340px;white-space:normal;line-height:1.55;">${escapeHtml(item.keyword || '-')}</td>
            <td>${escapeHtml(formatKeywordNumber(item.position || 0))}</td>
            <td>${escapeHtml(formatKeywordNumber(item.search_volume || 0))}</td>
            <td>${escapeHtml(formatKeywordCurrency(item.cpc || 0))}</td>
            <td>${escapeHtml(item.intent || '-')}</td>
          </tr>
        `).join('')
      : '<tr><td colspan="6" class="muted">لا توجد كلمات مرتبة حاليًا.</td></tr>';

    const allKeywordsRows = allKeywords.length
      ? allKeywords.map((item, index) => `
          <tr>
            <td style="width:56px;text-align:center;">${index + 1}</td>
            <td style="min-width:260px;max-width:380px;white-space:normal;line-height:1.55;">${escapeHtml(item.keyword || '-')}</td>
            <td>${escapeHtml(formatKeywordNumber(item.position || 0))}</td>
            <td>${escapeHtml(formatKeywordNumber(item.search_volume || 0))}</td>
            <td>${escapeHtml(formatKeywordCurrency(item.cpc || 0))}</td>
            <td>${escapeHtml(item.intent || '-')}</td>
          </tr>
        `).join('')
      : '<tr><td colspan="6" class="muted">لا توجد بيانات إضافية لعرضها.</td></tr>';

    const competitorsRows = competitors.length
      ? competitors.map((item, index) => `
          <tr>
            <td>${index + 1}</td>
            <td>${escapeHtml(item.domain || '-')}</td>
            <td>${escapeHtml(formatKeywordNumber(item.intersections || 0))}</td>
            <td>${escapeHtml(formatKeywordNumber(item.avg_position || 0))}</td>
            <td>${escapeHtml(formatKeywordNumber(item.organic_keywords || 0))}</td>
            <td>${escapeHtml(formatKeywordNumber(item.organic_traffic || 0))}</td>
            <td>${escapeHtml(formatMoneyUsd(item.organic_cost || 0))}</td>
          </tr>
        `).join('')
      : '<tr><td colspan="7" class="muted">لا توجد بيانات منافسين متاحة.</td></tr>';

    root.innerHTML = `
      <div class="grid" style="margin-top:0;">
        <div class="card surface-soft stat" style="min-height:auto;box-shadow:none;">
          <span class="stat-label">Organic Keywords</span>
          <span class="stat-value" style="font-size:30px;">${escapeHtml(formatKeywordNumber(organic.keywords_count || 0))}</span>
        </div>
        <div class="card surface-soft stat" style="min-height:auto;box-shadow:none;">
          <span class="stat-label">Organic Traffic (ETV)</span>
          <span class="stat-value" style="font-size:30px;">${escapeHtml(formatKeywordNumber(organic.traffic || 0))}</span>
        </div>
        <div class="card surface-soft stat" style="min-height:auto;box-shadow:none;">
          <span class="stat-label">Organic Traffic Cost</span>
          <span class="stat-value" style="font-size:30px;">${escapeHtml(formatMoneyUsd(organic.traffic_cost || 0))}</span>
        </div>
        <div class="card surface-soft stat" style="min-height:auto;box-shadow:none;">
          <span class="stat-label">Paid Keywords</span>
          <span class="stat-value" style="font-size:30px;">${escapeHtml(formatKeywordNumber(paid.keywords_count || 0))}</span>
        </div>
        <div class="card surface-soft stat" style="min-height:auto;box-shadow:none;">
          <span class="stat-label">Paid Traffic (ETV)</span>
          <span class="stat-value" style="font-size:30px;">${escapeHtml(formatKeywordNumber(paid.traffic || 0))}</span>
        </div>
        <div class="card surface-soft stat" style="min-height:auto;box-shadow:none;">
          <span class="stat-label">Paid Traffic Cost</span>
          <span class="stat-value" style="font-size:30px;">${escapeHtml(formatMoneyUsd(paid.traffic_cost || 0))}</span>
        </div>
      </div>

      <div class="grid" style="margin-top:16px;">
        <div class="card surface-soft" style="box-shadow:none;">
          <h3 style="margin:0 0 10px;">ملخص ترتيب الكلمات المفتاحية</h3>
          <div style="border:1px solid rgba(202,177,149,.35);border-radius:14px;overflow:auto;max-height:420px;">
            <table style="margin:0;min-width:760px;">
              <tbody>
              <tr><th>Top 3</th><td>${escapeHtml(formatKeywordNumber((organic.positions || {}).top_3 || 0))}</td></tr>
              <tr><th>Top 10</th><td>${escapeHtml(formatKeywordNumber((organic.positions || {}).top_10 || 0))}</td></tr>
              <tr><th>Top 20</th><td>${escapeHtml(formatKeywordNumber((organic.positions || {}).top_20 || 0))}</td></tr>
              <tr><th>Top 100</th><td>${escapeHtml(formatKeywordNumber((organic.positions || {}).top_100 || 0))}</td></tr>
              <tr><th>جديد</th><td>${escapeHtml(formatKeywordNumber(organic.new || 0))}</td></tr>
              <tr><th>صاعد</th><td>${escapeHtml(formatKeywordNumber(organic.up || 0))}</td></tr>
              <tr><th>هابط</th><td>${escapeHtml(formatKeywordNumber(organic.down || 0))}</td></tr>
              <tr><th>مفقود</th><td>${escapeHtml(formatKeywordNumber(organic.lost || 0))}</td></tr>
              </tbody>
            </table>
          </div>
          <p class="muted" style="margin:10px 0 0;">تاريخ آخر جلب: ${escapeHtml(fetchedAt)}</p>
        </div>

        <div class="card surface-soft" style="box-shadow:none;">
          <h3 style="margin:0 0 10px;">أهم الكلمات المفتاحية</h3>
          <div style="border:1px solid rgba(202,177,149,.35);border-radius:14px;overflow:auto;max-height:420px;">
            <table style="margin:0;min-width:760px;">
            <thead>
              <tr>
                <th>#</th>
                <th>الكلمة</th>
                <th>الترتيب</th>
                <th>الحجم</th>
                <th>CPC</th>
                <th>النية</th>
              </tr>
            </thead>
              <tbody>${keywordsRows}</tbody>
            </table>
          </div>
          <details style="margin-top:14px;border:1px dashed rgba(202,177,149,.5);border-radius:14px;padding:12px 12px 8px;">
            <summary class="btn btn-sky" style="display:inline-flex;cursor:pointer;">استعراض الجميع (${escapeHtml(formatKeywordNumber(allKeywords.length))})</summary>
            <div style="margin-top:6px;border:1px solid rgba(202,177,149,.35);border-radius:14px;overflow:auto;max-height:460px;">
              <table style="margin:0;min-width:820px;">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>الكلمة</th>
                    <th>الترتيب</th>
                    <th>الحجم</th>
                    <th>CPC</th>
                    <th>النية</th>
                  </tr>
                </thead>
                <tbody>${allKeywordsRows}</tbody>
              </table>
            </div>
          </details>
        </div>
      </div>

      <div class="card surface-soft" style="box-shadow:none;margin-top:16px;">
        <h3 style="margin:0 0 10px;">أهم المنافسين</h3>
        <table>
          <thead>
            <tr>
              <th>#</th>
              <th>الدومين</th>
              <th>تقاطع الكلمات</th>
              <th>متوسط الترتيب</th>
              <th>Organic Keywords</th>
              <th>Organic Traffic</th>
              <th>Traffic Cost</th>
            </tr>
          </thead>
          <tbody>${competitorsRows}</tbody>
        </table>
      </div>
    `;
  }

  function bindEvents() {
    document.querySelectorAll('[data-section-target]').forEach((button) => {
      button.addEventListener('click', () => switchSection(button.getAttribute('data-section-target') || 'products'));
    });
    document.querySelectorAll('[data-home-go]').forEach((button) => {
      button.addEventListener('click', () => switchSection(button.getAttribute('data-home-go') || 'products'));
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
    document.getElementById('keyword-search-btn')?.addEventListener('click', searchKeywordResearch);
    document.getElementById('keyword-query')?.addEventListener('keydown', (event) => {
      if (event.key === 'Enter') {
        event.preventDefault();
        searchKeywordResearch();
      }
    });
    document.getElementById('domain-seo-save-btn')?.addEventListener('click', saveDomainSeoConfig);
    document.getElementById('domain-seo-refresh-btn')?.addEventListener('click', refreshDomainSeoData);
    document.getElementById('domain-seo-domain')?.addEventListener('keydown', (event) => {
      if (event.key === 'Enter') {
        event.preventDefault();
        saveDomainSeoConfig();
      }
    });
    document.getElementById('generate-store-seo')?.addEventListener('click', optimizeStoreSeo);
    document.getElementById('save-store-seo')?.addEventListener('click', saveStoreSeo);
    document.getElementById('save-store-seo-instructions')?.addEventListener('click', saveStoreSeoInstructions);
    document.getElementById('save-optimization-settings')?.addEventListener('click', saveOptimizationSettings);
    document.getElementById('save-optimization-settings-alt')?.addEventListener('click', () => saveOptimizationSettings('alt'));
    
    document.getElementById('save-sitemap-settings')?.addEventListener('click', saveSitemapSettings);
    document.getElementById('reconnect-store')?.addEventListener('click', reconnectStore);

    // Brand SEO events
    document.getElementById('refresh-brands')?.addEventListener('click', loadBrands);
    document.getElementById('brand-filter-name')?.addEventListener('input', renderBrandsList);
    document.getElementById('brand-filter-status')?.addEventListener('change', renderBrandsList);
    document.getElementById('generate-brand-seo')?.addEventListener('click', generateBrandSeo);
    document.getElementById('save-brand-seo')?.addEventListener('click', saveBrandSeoToStore);
    document.getElementById('cancel-brand-seo')?.addEventListener('click', closeBrandEditor);

    document.getElementById('alt-optimize-selected-products')?.addEventListener('click', optimizeSelectedProductsAlt);
    document.getElementById('alt-clear-selection')?.addEventListener('click', () => {
      state.altSelectedProductIds = new Set();
      renderAltProducts();
      setAltAlert('', '');
    });
    document.getElementById('alt-apply-filters')?.addEventListener('click', applyAltFilters);
    document.getElementById('alt-clear-filters')?.addEventListener('click', clearAltFilters);
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
    document.querySelectorAll('[data-alt-quick-filter]').forEach((chip) => {
      chip.addEventListener('click', () => {
        state.alt.quickFilter = chip.dataset.altQuickFilter || 'all';
        state.alt.page = 1;
        renderAltProducts();
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

  function removeAltGeneralInstructionsField() {
    const input = document.getElementById('alt-setting-global-instructions');
    if (!input) return;
    const wrapper = input.closest('div');
    if (wrapper) {
      wrapper.remove();
    } else {
      input.remove();
    }
  }

  function removeProductsAltInstructionsField() {
    const input = document.getElementById('setting-image-alt-instructions');
    if (!input) return;
    const wrapper = input.closest('div');
    if (wrapper) {
      wrapper.remove();
    } else {
      input.remove();
    }
  }

  function moveStoreSeoInstructionsFieldToStoreSeoSection() {
    const input = document.getElementById('setting-store-seo-instructions');
    if (!input) return;

    const fieldWrapper = input.closest('div');
    if (!fieldWrapper) return;

    const storeSeoGrid = document.querySelector('#section-store-seo .grid');
    if (!storeSeoGrid) return;

    if (fieldWrapper.parentElement === storeSeoGrid) {
      return;
    }

    fieldWrapper.style.gridColumn = '1 / -1';
    storeSeoGrid.insertBefore(fieldWrapper, storeSeoGrid.firstChild);
  }

  removeAltGeneralInstructionsField();
  removeProductsAltInstructionsField();
  moveStoreSeoInstructionsFieldToStoreSeoSection();
  bindEvents();
  switchSection('home');
  renderEditorBody();
  renderImageAltBody();
  renderKeywordResults(null);
  renderDomainSeoResults(null);
  loadProducts();
  loadOptimizationSettings();
  loadOperations();
  loadStoreSeo();
  loadUsage();
})();
