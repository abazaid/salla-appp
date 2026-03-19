(function () {
  const portalState = {
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
    imageAltEditor: null,
    imageAltLoading: false,
    activeTab: 'products'
  };

  function mojibakeScore(value) {
    return (String(value ?? '').match(/[\u00d8\u00d9\u00c3\u00c2]/g) || []).length;
  }

  function decodeMojibakeString(value) {
    let text = String(value ?? '');

    for (let i = 0; i < 4; i += 1) {
      if (!/[\u00d8\u00d9\u00c3\u00c2]/.test(text)) {
        break;
      }

      try {
        const bytes = Uint8Array.from(Array.from(text).map((char) => char.charCodeAt(0) & 0xff));
        const decoded = new TextDecoder('utf-8').decode(bytes);
        if (mojibakeScore(decoded) < mojibakeScore(text)) {
          text = decoded;
          continue;
        }
      } catch (error) {
        break;
      }

      break;
    }

    return text;
  }

  function fixDomMojibake(root = document.body) {
    if (!root) {
      return;
    }

    const walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT);
    let node;

    while ((node = walker.nextNode())) {
      const original = node.nodeValue || '';
      if (!/[\u00d8\u00d9\u00c3\u00c2]/.test(original)) {
        continue;
      }
      node.nodeValue = decodeMojibakeString(original);
    }

    root.querySelectorAll('[placeholder], option').forEach((element) => {
      if (element.hasAttribute('placeholder')) {
        element.setAttribute('placeholder', decodeMojibakeString(element.getAttribute('placeholder')));
      }
    });
  }

  function scheduleUiFix(root) {
    requestAnimationFrame(() => fixDomMojibake(root || document.body));
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

  function isImageAltReady(image) {
    return String(image.alt || '').trim().length >= 6;
  }

  function isProductAltOptimized(product) {
    const images = product.images || [];
    return images.length > 0 && images.every(isImageAltReady);
  }

  function getStatusLabel(product) {
    if (product.status === 'hidden') return '\u0645\u062e\u0641\u064a';
    if (product.status === 'sale') return '\u0645\u0639\u0631\u0648\u0636 \u0644\u0644\u0628\u064a\u0639';
    if (product.is_available === false) return '\u063a\u064a\u0631 \u0645\u062a\u0648\u0641\u0631';
    return product.status || '\u063a\u064a\u0631 \u0645\u062d\u062f\u062f';
  }

  function getModeLabel(mode) {
    if (mode === 'description') return '\u062a\u062d\u0633\u064a\u0646 \u0648\u0635\u0641 \u0627\u0644\u0645\u0646\u062a\u062c';
    if (mode === 'seo') return '\u062a\u062d\u0633\u064a\u0646 SEO \u0627\u0644\u0645\u0646\u062a\u062c';
    if (mode === 'image_alt') return 'ALT \u0627\u0644\u0635\u0648\u0631';
    if (mode === 'image_alt_bulk') return 'ALT \u0628\u0627\u0644\u062c\u0645\u0644\u0629';
    if (mode === 'store_seo') return '\u0633\u064a\u0648 \u0627\u0644\u0645\u062a\u062c\u0631';
    return '\u062a\u062d\u0633\u064a\u0646 \u0643\u0627\u0645\u0644';
  }

  function getModeHelp(mode) {
    if (mode === 'description') return '\u0633\u064a\u0638\u0647\u0631 \u0644\u0643 \u0627\u0644\u0648\u0635\u0641 \u0627\u0644\u062d\u0627\u0644\u064a \u062b\u0645 \u0627\u0644\u0648\u0635\u0641 \u0627\u0644\u062c\u062f\u064a\u062f \u0645\u0639 \u0625\u0645\u0643\u0627\u0646\u064a\u0629 \u0627\u0644\u062a\u0639\u062f\u064a\u0644 \u0627\u0644\u064a\u062f\u0648\u064a \u0642\u0628\u0644 \u0627\u0644\u062d\u0641\u0638.';
    if (mode === 'seo') return '\u0633\u064a\u0638\u0647\u0631 \u0644\u0643 Meta Title \u0648Meta Description \u0627\u0644\u062d\u0627\u0644\u064a\u0627\u0646 \u0648\u0627\u0644\u062c\u062f\u064a\u062f\u0627\u0646 \u0645\u0639 \u0642\u0627\u0628\u0644\u064a\u0629 \u0627\u0644\u062a\u0639\u062f\u064a\u0644 \u0627\u0644\u0643\u0627\u0645\u0644 \u0642\u0628\u0644 \u0627\u0644\u062d\u0641\u0638.';
    return '\u0633\u064a\u0638\u0647\u0631 \u0644\u0643 \u0627\u0644\u0648\u0635\u0641 \u0627\u0644\u062d\u0627\u0644\u064a \u0648\u0627\u0644\u062c\u062f\u064a\u062f \u0628\u0627\u0644\u0625\u0636\u0627\u0641\u0629 \u0625\u0644\u0649 Meta Title \u0648Meta Description \u0641\u064a \u0646\u0627\u0641\u0630\u0629 \u0648\u0627\u062d\u062f\u0629 \u0642\u0628\u0644 \u0627\u0644\u062d\u0641\u0638.';
  }

  function getFilteredProducts() {
    const nameFilter = normalizeText(portalState.filters.name);
    const skuFilter = normalizeText(portalState.filters.sku);
    const statusFilter = portalState.filters.status;
    const contentFilter = portalState.quickFilter !== 'all' ? portalState.quickFilter : portalState.filters.content;

    return portalState.products.filter((product) => {
      const name = normalizeText(product.name);
      const sku = normalizeText(product.sku);
      const descriptionReady = isDescriptionOptimized(product);
      const seoReady = isSeoOptimized(product);
      const altReady = isProductAltOptimized(product);
      const isOut = product.is_available === false || Number(product.quantity ?? 1) === 0;

      if (nameFilter && !name.includes(nameFilter)) return false;
      if (skuFilter && !sku.includes(skuFilter)) return false;
      if (statusFilter === 'sale' && product.status !== 'sale') return false;
      if (statusFilter === 'hidden' && product.status !== 'hidden') return false;
      if (statusFilter === 'out' && !isOut) return false;
      if (contentFilter === 'desc_ready' && !descriptionReady) return false;
      if (contentFilter === 'desc_missing' && descriptionReady) return false;
      if (contentFilter === 'seo_ready' && !seoReady) return false;
      if (contentFilter === 'seo_missing' && seoReady) return false;
      if (contentFilter === 'alt_ready' && !altReady) return false;
      if (contentFilter === 'alt_missing' && altReady) return false;
      if (contentFilter === 'all_missing' && (descriptionReady || seoReady || altReady)) return false;

      return true;
    });
  }

  function getProductById(productId) {
    return portalState.products.find((product) => Number(product.id) === Number(productId)) || null;
  }

  function syncProductImageAlt(productId, imageId, alt) {
    const product = getProductById(productId);

    if (!product || !Array.isArray(product.images)) {
      return;
    }

    product.images = product.images.map((image) => {
      if (Number(image.id) !== Number(imageId)) {
        return image;
      }

      return {
        ...image,
        alt
      };
    });
  }

  function getPagedProducts() {
    const filtered = getFilteredProducts();
    const totalPages = Math.max(1, Math.ceil(filtered.length / portalState.pageSize));

    if (portalState.page > totalPages) {
      portalState.page = totalPages;
    }

    const offset = (portalState.page - 1) * portalState.pageSize;

    return {
      filtered,
      totalPages,
      items: filtered.slice(offset, offset + portalState.pageSize),
      from: filtered.length ? offset + 1 : 0,
      to: Math.min(offset + portalState.pageSize, filtered.length)
    };
  }

  function renderDashboardTabState() {
    document.querySelectorAll('[data-dashboard-tab]').forEach((button) => {
      button.classList.toggle('is-active', button.dataset.dashboardTab === portalState.activeTab);
    });

    document.querySelectorAll('[data-tab-panel]').forEach((panel) => {
      panel.style.display = panel.dataset.tabPanel === portalState.activeTab ? '' : 'none';
    });
  }

  function switchDashboardTab(tab) {
    portalState.activeTab = tab === 'image-alt' ? 'image-alt' : 'products';
    renderDashboardTabState();
  }

  function renderDashboardProducts() {
    renderProducts();
    renderAltProducts();
    renderDashboardTabState();
  }

  function buildAltProductCard(product) {
    const images = Array.isArray(product.images) ? product.images : [];
    const altReadyCount = images.filter(isImageAltReady).length;
    const pendingCount = Math.max(images.length - altReadyCount, 0);
    const image = product.thumbnail || product.main_image || (images[0] && images[0].url) || '';
    const altReady = isProductAltOptimized(product);

    return `
      <article class="product-card">
        <div class="product-badges">
          <span class="status-badge ${altReady ? 'success' : 'danger'}">${altReady ? '\u0041\u004c\u0054 \u0645\u062d\u0633\u0651\u0646' : '\u0041\u004c\u0054 \u063a\u064a\u0631 \u0645\u062d\u0633\u0651\u0646'}</span>
          <span class="status-badge ${images.length ? 'warning' : 'danger'}">${images.length ? `${images.length} \u0635\u0648\u0631` : '\u0628\u062f\u0648\u0646 \u0635\u0648\u0631'}</span>
        </div>
        <img class="product-thumb" src="${escapeHtml(image)}" alt="${escapeHtml(product.name)}">
        <div>
          <h3 class="product-title">${escapeHtml(product.name)}</h3>
          <div class="meta-list">
            <span>SKU: <code>${escapeHtml(product.sku || '-')}</code></span>
            <span>\u062c\u0627\u0647\u0632: <strong>${altReadyCount}/${images.length}</strong></span>
            <span>\u0627\u0644\u0645\u062a\u0628\u0642\u064a: <strong>${pendingCount}</strong></span>
          </div>
        </div>
        <p class="muted" style="margin:0;">\u0627\u0643\u062a\u0628 \u0041\u004c\u0054 \u0644\u0635\u0648\u0631\u0629 \u0648\u0627\u062d\u062f\u0629 \u0623\u0648 \u0627\u0641\u062a\u062d \u0627\u0644\u0645\u0646\u062a\u062c \u0644\u062a\u062d\u0633\u064a\u0646 \u062c\u0645\u064a\u0639 \u0627\u0644\u0635\u0648\u0631 \u062b\u0645 \u0631\u0627\u062c\u0639 \u0627\u0644\u0646\u0635\u0648\u0635 \u0642\u0628\u0644 \u0627\u0644\u062d\u0641\u0638 \u0641\u064a \u0627\u0644\u0645\u062a\u062c\u0631.</p>
        <div class="product-actions">
          <button class="btn btn-dark" type="button" data-alt-product="${Number(product.id)}">\u0641\u062a\u062d \u0643\u0627\u062a\u0628 \u0041\u004c\u0054</button>
          <button class="btn btn-secondary" type="button" data-alt-focus-product="${Number(product.id)}">\u062a\u062d\u062f\u064a\u062f \u0648\u0641\u062a\u062d</button>
        </div>
      </article>
    `;
  }

  function renderAltProducts() {
    const root = document.getElementById('alt-products-list');
    const summary = document.getElementById('alt-products-summary');

    if (!root || !summary) {
      return;
    }

    const { filtered, totalPages, items, from, to } = getPagedProducts();

    summary.textContent = filtered.length
      ? `\u0639\u0631\u0636 ${from} \u0625\u0644\u0649 ${to} \u0645\u0646 \u0623\u0635\u0644 ${filtered.length} \u0645\u0646\u062a\u062c \u0635\u0648\u0631`
      : '\u0644\u0627 \u062a\u0648\u062c\u062f \u0645\u0646\u062a\u062c\u0627\u062a \u0645\u0637\u0627\u0628\u0642\u0629 \u0644\u0641\u0644\u0627\u062a\u0631 \u0627\u0644\u0635\u0648\u0631 \u0627\u0644\u062d\u0627\u0644\u064a\u0629.';

    if (!items.length) {
      root.innerHTML = `
        <div class="empty-state" style="grid-column:1/-1;">
          <h3>\u0644\u0627 \u062a\u0648\u062c\u062f \u0645\u0646\u062a\u062c\u0627\u062a \u0635\u0648\u0631 \u0645\u0637\u0627\u0628\u0642\u0629</h3>
          <p class="muted">\u062c\u0631\u0651\u0628 \u0625\u0632\u0627\u0644\u0629 \u0628\u0639\u0636 \u0627\u0644\u0641\u0644\u0627\u062a\u0631 \u0623\u0648 \u0627\u0641\u062a\u062d \u062a\u0628\u0648\u064a\u0628 \u0627\u0644\u0645\u0646\u062a\u062c\u0627\u062a \u062b\u0645 \u0639\u062f \u0625\u0644\u0649 \u062a\u0628\u0648\u064a\u0628 \u0041\u004c\u0054 \u0644\u0644\u0635\u0648\u0631.</p>
        </div>
      `;
      renderPagination('alt-products-pagination-top', totalPages);
      renderPagination('alt-products-pagination-bottom', totalPages);
      scheduleUiFix(root);
      return;
    }

    root.innerHTML = items.map(buildAltProductCard).join('');

    root.querySelectorAll('[data-alt-product]').forEach((button) => {
      button.addEventListener('click', () => {
        openImageAltModal(Number(button.dataset.altProduct));
      });
    });

    root.querySelectorAll('[data-alt-focus-product]').forEach((button) => {
      button.addEventListener('click', () => {
        switchDashboardTab('image-alt');
        openImageAltModal(Number(button.dataset.altFocusProduct));
      });
    });

    renderPagination('alt-products-pagination-top', totalPages);
    renderPagination('alt-products-pagination-bottom', totalPages);
    scheduleUiFix(root);
  }

  function renderSubscriptionCard(data) {
    const card = document.getElementById('portal-subscription');

    if (!data.success) {
      card.innerHTML = '<h2>\u0627\u0644\u0627\u0634\u062a\u0631\u0627\u0643 \u0648\u0627\u0644\u0627\u0633\u062a\u0647\u0644\u0627\u0643</h2><p class="muted">' + escapeHtml(data.message) + '</p>';
      return;
    }

    const sub = data.subscription;
    card.innerHTML = `
      <div class="section-head">
        <div>
          <h2 style="margin-bottom:6px;">\u0627\u0644\u0627\u0634\u062a\u0631\u0627\u0643 \u0648\u0627\u0644\u0627\u0633\u062a\u0647\u0644\u0627\u0643</h2>
          <p class="muted" style="margin:0;">\u0646\u0638\u0631\u0629 \u0633\u0631\u064a\u0639\u0629 \u0639\u0644\u0649 \u062d\u0627\u0644\u0629 \u0627\u0644\u0628\u0627\u0642\u0629 \u0648\u0639\u062f\u062f \u0627\u0644\u062a\u062d\u0633\u064a\u0646\u0627\u062a \u0627\u0644\u0645\u062a\u0628\u0642\u064a\u0629 \u062e\u0644\u0627\u0644 \u0627\u0644\u062f\u0648\u0631\u0629 \u0627\u0644\u062d\u0627\u0644\u064a\u0629.</p>
        </div>
      </div>
      <div class="grid" style="margin-top:0;">
        <div class="card surface-soft stat">
          <span class="stat-label">\u0627\u0644\u062d\u0627\u0644\u0629</span>
          <span class="stat-value" style="font-size:28px;">${escapeHtml(sub.status)}</span>
        </div>
        <div class="card surface-soft stat">
          <span class="stat-label">\u0627\u0644\u0628\u0627\u0642\u0629</span>
          <span class="stat-value" style="font-size:28px;">${escapeHtml(sub.plan_name ?? '-')}</span>
        </div>
        <div class="card surface-soft stat">
          <span class="stat-label">\u0627\u0644\u0645\u062a\u0628\u0642\u064a</span>
          <span class="stat-value">${escapeHtml(sub.remaining_products)}</span>
        </div>
        <div class="card surface-soft stat">
          <span class="stat-label">\u0627\u0644\u0627\u0633\u062a\u0647\u0644\u0627\u0643 \u0627\u0644\u062d\u0627\u0644\u064a</span>
          <span class="stat-value">${escapeHtml(sub.used_products)} / ${escapeHtml(sub.product_quota)}</span>
        </div>
      </div>
    `;
  }

  async function loadPortalSubscription() {
    try {
      const response = await fetch('/api/subscription');
      renderSubscriptionCard(await response.json());
    } catch (error) {
      renderSubscriptionCard({ success: false, message: '\u062a\u0639\u0630\u0651\u0631 \u062a\u062d\u0645\u064a\u0644 \u0628\u064a\u0627\u0646\u0627\u062a \u0627\u0644\u0627\u0634\u062a\u0631\u0627\u0643.' });
    }
  }

  function formatDate(value) {
    if (!value) return '-';

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
      return value;
    }

    return date.toLocaleString('ar-SA', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  }

  function getOperationLabel(mode) {
    if (mode === 'image_alt') return 'ALT \u0627\u0644\u0635\u0648\u0631';
    if (mode === 'image_alt_bulk') return 'ALT \u0628\u0627\u0644\u062c\u0645\u0644\u0629';
    if (mode === 'description') return '\u0648\u0635\u0641 \u0627\u0644\u0645\u0646\u062a\u062c';
    if (mode === 'seo') return 'SEO \u0627\u0644\u0645\u0646\u062a\u062c';
    if (mode === 'store_seo') return '\u0633\u064a\u0648 \u0627\u0644\u0645\u062a\u062c\u0631';
    return '\u0627\u0644\u0648\u0635\u0641 + SEO';
  }

  function getOperationStatusClass(status) {
    if (status === 'completed') return 'success';
    if (status === 'failed') return 'danger';
    return 'warning';
  }

  function getOperationStatusLabel(status) {
    if (status === 'completed') return '\u0645\u0643\u062a\u0645\u0644';
    if (status === 'failed') return '\u0641\u0634\u0644';
    if (status === 'in_progress') return '\u0642\u064a\u062f \u0627\u0644\u062a\u0646\u0641\u064a\u0630';
    return status || '\u063a\u064a\u0631 \u0645\u0639\u0631\u0648\u0641';
  }

  function getOperationsQuery(limitOverride) {
    const status = document.getElementById('operations-status-filter')?.value || 'all';
    let mode = document.getElementById('operations-mode-filter')?.value || 'all';
    const limit = limitOverride || '20';

    if (mode === 'combo_all') {
      mode = 'all';
    }

    const params = new URLSearchParams();
    if (status !== 'all') params.set('status', status);
    if (mode !== 'all') params.set('mode', mode);
    params.set('limit', limit);
    const query = params.toString();
    return query ? `/api/operations?${query}` : '/api/operations';
  }

  function renderOperations(data) {
    const root = document.getElementById('operations-list');

    if (!root) {
      return;
    }

    if (!data.success) {
      root.innerHTML = `<div class="empty-state"><p class="muted">${escapeHtml(data.message || '\u062a\u0639\u0630\u0651\u0631 \u062a\u062d\u0645\u064a\u0644 \u0633\u062c\u0644 \u0627\u0644\u0639\u0645\u0644\u064a\u0627\u062a.')}</p></div>`;
      scheduleUiFix(root);
      return;
    }

    const operations = data.operations || [];

    if (!operations.length) {
      root.innerHTML = `
        <div class="empty-state">
          <h3 style="margin-bottom:8px;">\u0644\u0627 \u062a\u0648\u062c\u062f \u0639\u0645\u0644\u064a\u0627\u062a \u0628\u0639\u062f</h3>
          <p class="muted" style="margin:0;">\u0639\u0646\u062f\u0645\u0627 \u062a\u062d\u0641\u0638 \u0648\u0635\u0641\u064b\u0627 \u0623\u0648 SEO \u0623\u0648 ALT \u0633\u062a\u0638\u0647\u0631 \u0622\u062e\u0631 \u0627\u0644\u0639\u0645\u0644\u064a\u0627\u062a \u0647\u0646\u0627.</p>
        </div>
      `;
      scheduleUiFix(root);
      return;
    }

    root.innerHTML = operations.map((operation) => `
      <div class="card surface-soft" style="padding:18px;margin-bottom:12px;border-radius:22px;box-shadow:none;">
        <div class="product-badges" style="margin-bottom:10px;">
          <span class="status-badge ${getOperationStatusClass(operation.status)}">${escapeHtml(getOperationStatusLabel(operation.status))}</span>
          <span class="status-badge warning">${escapeHtml(getOperationLabel(operation.mode))}</span>
        </div>
        <h3 style="margin:0 0 8px;line-height:1.5;">${escapeHtml(operation.product_name || '\u0639\u0645\u0644\u064a\u0629 \u0628\u062f\u0648\u0646 \u0627\u0633\u0645 \u0645\u0646\u062a\u062c')}</h3>
        <div class="meta-list">
          <span>\u0645\u0639\u0631\u0651\u0641 \u0627\u0644\u0645\u0646\u062a\u062c: <code>${escapeHtml(operation.product_id || '-')}</code></span>
          <span>\u0622\u062e\u0631 \u062a\u0646\u0641\u064a\u0630: <strong>${escapeHtml(formatDate(operation.used_at))}</strong></span>
        </div>
      </div>
    `).join('');

    scheduleUiFix(root);
  }

  async function loadOperations(limitOverride) {
    try {
      const response = await fetch(getOperationsQuery(limitOverride));
      renderOperations(await response.json());
    } catch (error) {
      renderOperations({ success: false, message: '\u062a\u0639\u0630\u0651\u0631 \u062a\u062d\u0645\u064a\u0644 \u0633\u062c\u0644 \u0627\u0644\u0639\u0645\u0644\u064a\u0627\u062a.' });
    }
  }

  function renderPagination(containerId, totalPages) {
    const root = document.getElementById(containerId);

    if (!root) {
      return;
    }

    if (totalPages <= 1) {
      root.innerHTML = '';
      return;
    }

    let html = '';
    const start = Math.max(1, portalState.page - 2);
    const end = Math.min(totalPages, portalState.page + 2);

    html += `<button type="button" ${portalState.page === 1 ? 'disabled' : ''} data-page="${portalState.page - 1}">\u2039</button>`;

    for (let page = start; page <= end; page += 1) {
      html += `<button type="button" class="${page === portalState.page ? 'is-active' : ''}" data-page="${page}">${page}</button>`;
    }

    html += `<button type="button" ${portalState.page === totalPages ? 'disabled' : ''} data-page="${portalState.page + 1}">\u203a</button>`;
    root.innerHTML = html;

    root.querySelectorAll('[data-page]').forEach((button) => {
      button.addEventListener('click', () => {
        portalState.page = Number(button.dataset.page || 1);
        renderDashboardProducts();
        window.scrollTo({ top: 0, behavior: 'smooth' });
      });
    });
  }

  function buildProductCard(product) {
    const descriptionReady = isDescriptionOptimized(product);
    const seoReady = isSeoOptimized(product);
    const altReady = isProductAltOptimized(product);
    const images = Array.isArray(product.images) ? product.images : [];
    const altReadyCount = images.filter(isImageAltReady).length;
    const image = product.thumbnail || product.main_image || '';
    const preview = stripHtml(product.description || '').slice(0, 140) || '\u0644\u0627 \u064a\u0648\u062c\u062f \u0648\u0635\u0641 \u062d\u0627\u0644\u064a \u0644\u0647\u0630\u0627 \u0627\u0644\u0645\u0646\u062a\u062c.';
    const isLoading = portalState.loadingProductId === product.id;

    return `
      <article class="product-card">
        <div class="product-badges">
          <span class="status-badge ${descriptionReady ? 'success' : 'danger'}">${descriptionReady ? '\u0648\u0635\u0641 \u0645\u062d\u0633\u0651\u0646' : '\u0648\u0635\u0641 \u063a\u064a\u0631 \u0645\u062d\u0633\u0651\u0646'}</span>
          <span class="status-badge ${seoReady ? 'success' : 'danger'}">${seoReady ? 'SEO \u0645\u062d\u0633\u0651\u0646' : 'SEO \u063a\u064a\u0631 \u0645\u062d\u0633\u0651\u0646'}</span>
          <span class="status-badge ${altReady ? 'success' : 'danger'}">${altReady ? 'ALT \u0645\u062d\u0633\u0651\u0646' : 'ALT \u063a\u064a\u0631 \u0645\u062d\u0633\u0651\u0646'}</span>
        </div>
        <img class="product-thumb" src="${escapeHtml(image)}" alt="${escapeHtml(product.name)}">
        <div>
          <h3 class="product-title">${escapeHtml(product.name)}</h3>
          <div class="meta-list">
            <span>SKU: <code>${escapeHtml(product.sku || '-')}</code></span>
            <span>\u0627\u0644\u062d\u0627\u0644\u0629: <strong>${escapeHtml(getStatusLabel(product))}</strong></span>
            <span>\u0627\u0644\u0635\u0648\u0631 \u0627\u0644\u062c\u0627\u0647\u0632\u0629: <strong>${altReadyCount}/${images.length}</strong> ALT</span>
          </div>
        </div>
        <p class="muted" style="margin:0;">${escapeHtml(preview)}</p>
        <div class="product-actions">
          <button class="btn" type="button" ${isLoading ? 'disabled' : ''} data-action="description" data-id="${Number(product.id)}">${isLoading ? '\u062c\u0627\u0631\u064a \u0627\u0644\u062a\u062d\u0636\u064a\u0631...' : '\u062a\u062d\u0633\u064a\u0646 \u0627\u0644\u0648\u0635\u0641'}</button>
          <button class="btn btn-sky" type="button" ${isLoading ? 'disabled' : ''} data-action="seo" data-id="${Number(product.id)}">\u062a\u062d\u0633\u064a\u0646 \u0627\u0644\u0633\u064a\u0648</button>
          <button class="btn btn-secondary" type="button" ${isLoading ? 'disabled' : ''} data-action="all" data-id="${Number(product.id)}">\u062a\u062d\u0633\u064a\u0646 \u0627\u0644\u0643\u0644</button>
          <button class="btn btn-dark" type="button" data-alt-product="${Number(product.id)}">ALT \u0627\u0644\u0635\u0648\u0631</button>
        </div>
        <a class="btn btn-secondary" href="${escapeHtml(product.urls?.admin || '#')}" target="_blank">\u0641\u062a\u062d \u0627\u0644\u0645\u0646\u062a\u062c \u0641\u064a \u0633\u0644\u0629</a>
      </article>
    `;
  }

  function renderProducts() {
    const root = document.getElementById('products-list');
    const summary = document.getElementById('products-summary');
    const { filtered, totalPages, items, from, to } = getPagedProducts();

    document.querySelectorAll('[data-quick-filter]').forEach((chip) => {
      chip.classList.toggle('is-active', chip.dataset.quickFilter === portalState.quickFilter);
    });

    summary.textContent = filtered.length
      ? `\u0639\u0631\u0636 ${from} \u0625\u0644\u0649 ${to} \u0645\u0646 \u0623\u0635\u0644 ${filtered.length} \u0645\u0646\u062a\u062c`
      : '\u0644\u0627 \u062a\u0648\u062c\u062f \u0646\u062a\u0627\u0626\u062c \u0645\u0637\u0627\u0628\u0642\u0629 \u0644\u0644\u0641\u0644\u0627\u062a\u0631 \u0627\u0644\u062d\u0627\u0644\u064a\u0629.';

    if (!items.length) {
      root.innerHTML = `
        <div class="empty-state" style="grid-column:1/-1;">
          <h3>\u0644\u0627 \u062a\u0648\u062c\u062f \u0645\u0646\u062a\u062c\u0627\u062a \u0645\u0637\u0627\u0628\u0642\u0629</h3>
          <p class="muted">\u062c\u0631\u0651\u0628 \u0625\u0632\u0627\u0644\u0629 \u0628\u0639\u0636 \u0627\u0644\u0641\u0644\u0627\u062a\u0631 \u0623\u0648 \u062a\u0628\u062f\u064a\u0644 \u0641\u0644\u062a\u0631 \u0627\u0644\u0645\u062d\u062a\u0648\u0649 \u0644\u0625\u0638\u0647\u0627\u0631 \u0646\u062a\u0627\u0626\u062c \u0623\u0643\u062b\u0631.</p>
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

    root.querySelectorAll('[data-alt-product]').forEach((button) => {
      button.addEventListener('click', () => {
        openImageAltModal(Number(button.dataset.altProduct));
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

    if (portalState.modalLoading) {
      pill.textContent = 'جاري التحضير';
      title.textContent = 'جاري تجهيز المحتوى...';
      subtitle.textContent = 'انتظر قليلًا حتى يكتمل جلب المحتوى الحالي وإنتاج النسخة الجديدة.';
      alertRoot.innerHTML = '';
      root.innerHTML = `<div class="empty-state"><p class="muted">جاري توليد المحتوى الجديد...</p></div>`;
      return;
    }

    const editor = portalState.editor;

    if (!editor) {
      pill.textContent = 'تحسين المحتوى';
      title.textContent = 'اختر منتجًا';
      subtitle.textContent = 'افتح نافذة التحسين من بطاقة أي منتج لبدء العمل.';
      alertRoot.innerHTML = '';
      root.innerHTML = `<div class="empty-state"><p class="muted">لا يوجد محتوى مفتوح الآن.</p></div>`;
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
            <div class="helper-row"><span>قراءة فقط</span><span>${editor.currentDescription.length} حرف</span></div>
          </div>
          <div class="compare-card">
            <strong>الوصف بعد التحسين</strong>
            <textarea id="editor-description">${escapeHtml(editor.optimizedDescription)}</textarea>
            <div class="helper-row"><span>يمكنك التعديل بحرية قبل الحفظ</span><span id="editor-description-count">${editor.optimizedDescription.length} حرف</span></div>
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
            <div class="helper-row"><span>قراءة فقط</span><span>${editor.currentMetaTitle.length} حرف</span></div>
          </div>
          <div class="compare-card is-meta">
            <strong>Meta Title بعد التحسين</strong>
            <textarea id="editor-meta-title">${escapeHtml(editor.optimizedMetaTitle)}</textarea>
            <div class="helper-row"><span>مفضل بين 35 و65 حرفًا</span><span id="editor-meta-title-count">${editor.optimizedMetaTitle.length} حرف</span></div>
          </div>
        </div>
        <div class="compare-grid" style="margin-top:16px;">
          <div class="compare-card">
            <strong>Meta Description الحالية</strong>
            <textarea readonly>${escapeHtml(editor.currentMetaDescription)}</textarea>
            <div class="helper-row"><span>قراءة فقط</span><span>${editor.currentMetaDescription.length} حرف</span></div>
          </div>
          <div class="compare-card">
            <strong>Meta Description بعد التحسين</strong>
            <textarea id="editor-meta-description">${escapeHtml(editor.optimizedMetaDescription)}</textarea>
            <div class="helper-row"><span>مفضل بين 120 و160 حرفًا</span><span id="editor-meta-description-count">${editor.optimizedMetaDescription.length} حرف</span></div>
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
    attachEditorCounters();

    document.getElementById('save-editor')?.addEventListener('click', saveEditor);
    document.getElementById('cancel-editor')?.addEventListener('click', closeEditor);
  }

  function attachEditorCounters() {
    const description = document.getElementById('editor-description');
    const metaTitle = document.getElementById('editor-meta-title');
    const metaDescription = document.getElementById('editor-meta-description');

    if (description) {
      description.addEventListener('input', () => {
        document.getElementById('editor-description-count').textContent = `${description.value.length} حرف`;
      });
    }

    if (metaTitle) {
      metaTitle.addEventListener('input', () => {
        document.getElementById('editor-meta-title-count').textContent = `${metaTitle.value.length} حرف`;
      });
    }

    if (metaDescription) {
      metaDescription.addEventListener('input', () => {
        document.getElementById('editor-meta-description-count').textContent = `${metaDescription.value.length} حرف`;
      });
    }
  }

  function openEditorModal() {
    document.getElementById('editor-modal').classList.add('is-open');
  }

  function closeEditor() {
    document.getElementById('editor-modal').classList.remove('is-open');
    portalState.editor = null;
    portalState.modalLoading = false;
    renderEditorBody();
  }

  async function openOptimization(productId, mode) {
    const product = portalState.products.find((entry) => Number(entry.id) === Number(productId));

    if (!product) return;

    portalState.loadingProductId = productId;
    portalState.modalLoading = true;
    portalState.editor = null;
    renderDashboardProducts();
    renderEditorBody();
    openEditorModal();

    try {
      const response = await fetch(`/api/products/${productId}/optimize`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          tone: document.getElementById('tone').value,
          language: document.getElementById('language').value,
          mode
        })
      });

      const data = await response.json();

      if (!data.success) {
        portalState.editor = {
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
        portalState.editor = {
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
      portalState.editor = {
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
      portalState.loadingProductId = null;
      portalState.modalLoading = false;
      renderDashboardProducts();
      renderEditorBody();
    }
  }

  async function saveEditor() {
    if (!portalState.editor) return;

    const editor = portalState.editor;
    const description = document.getElementById('editor-description')?.value.trim() ?? editor.currentDescription;
    const metaTitle = document.getElementById('editor-meta-title')?.value.trim() ?? editor.currentMetaTitle;
    const metaDescription = document.getElementById('editor-meta-description')?.value.trim() ?? editor.currentMetaDescription;

    if ((editor.mode === 'description' || editor.mode === 'all') && !description) {
      portalState.editor.notice = { type: 'error', message: 'الوصف الجديد مطلوب قبل الحفظ.' };
      renderEditorBody();
      return;
    }

    portalState.editor.notice = { type: 'success', message: 'جاري حفظ المحتوى داخل سلة...' };
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
        portalState.editor.notice = { type: 'error', message: data.message || 'تعذر حفظ المحتوى.' };
        renderEditorBody();
        return;
      }

      closeEditor();
      await loadPortalSubscription();
      await loadPortalProducts();
      await loadOperations();
    } catch (error) {
      portalState.editor.notice = { type: 'error', message: 'حدث خطأ أثناء حفظ المحتوى.' };
      renderEditorBody();
    }
  }

  async function loadPortalProducts() {
    const root = document.getElementById('products-list');
    root.innerHTML = '<div class="empty-state" style="grid-column:1/-1;"><p class="muted">جاري تحميل المنتجات...</p></div>';

    try {
      const response = await fetch('/api/products');
      const data = await response.json();

      if (!data.success) {
        root.innerHTML = `<div class="empty-state" style="grid-column:1/-1;"><p class="muted">${escapeHtml(data.message)}</p></div>`;
        return;
      }

      portalState.products = data.products || [];
      renderDashboardProducts();
    } catch (error) {
      root.innerHTML = '<div class="empty-state" style="grid-column:1/-1;"><p class="muted">تعذر تحميل المنتجات.</p></div>';
    }
  }

  function openFilterModal() {
    document.getElementById('filter-modal').classList.add('is-open');
  }

  function closeFilterModal() {
    document.getElementById('filter-modal').classList.remove('is-open');
  }

  function applyFilters() {
    portalState.filters = {
      name: document.getElementById('filter-name').value.trim(),
      sku: document.getElementById('filter-sku').value.trim(),
      status: document.getElementById('filter-status').value,
      content: document.getElementById('filter-content').value
    };
    portalState.page = 1;
    closeFilterModal();
    renderDashboardProducts();
  }

  function clearFilters() {
    document.getElementById('filter-name').value = '';
    document.getElementById('filter-sku').value = '';
    document.getElementById('filter-status').value = 'all';
    document.getElementById('filter-content').value = 'all';
    portalState.filters = { name: '', sku: '', status: 'all', content: 'all' };
    portalState.quickFilter = 'all';
    portalState.page = 1;
    renderDashboardProducts();
  }

  function getSelectedImageIds() {
    if (!portalState.imageAltEditor) {
      return [];
    }

    return portalState.imageAltEditor.images
      .filter((image) => image.selected)
      .map((image) => Number(image.id));
  }

  function getImageAltItem(imageId) {
    if (!portalState.imageAltEditor) {
      return null;
    }

    return portalState.imageAltEditor.images.find((image) => Number(image.id) === Number(imageId)) || null;
  }

  function openImageAltModal(productId) {
    const product = getProductById(productId);

    if (!product) {
      return;
    }

    portalState.imageAltEditor = {
      productId,
      productName: product.name || '\u0645\u0646\u062a\u062c \u0628\u062f\u0648\u0646 \u0627\u0633\u0645',
      images: (product.images || []).map((image) => ({
        id: Number(image.id),
        url: image.url || '',
        currentAlt: String(image.alt || ''),
        optimizedAlt: String(image.alt || ''),
        selected: false
      })),
      notice: null
    };

    document.getElementById('image-alt-modal')?.classList.add('is-open');
    renderImageAltBody();
  }

  function closeImageAltModal() {
    portalState.imageAltEditor = null;
    document.getElementById('image-alt-modal')?.classList.remove('is-open');
    renderImageAltBody();
  }

  function renderImageAltBody() {
    const root = document.getElementById('image-alt-body');
    const alertRoot = document.getElementById('image-alt-alert');
    const title = document.getElementById('image-alt-title');
    const subtitle = document.getElementById('image-alt-subtitle');

    if (!root || !alertRoot || !title || !subtitle) {
      return;
    }

    if (portalState.imageAltLoading) {
      alertRoot.innerHTML = '';
      title.textContent = '\u062c\u0627\u0631\u064a \u062a\u062d\u0636\u064a\u0631 \u0627\u0644\u0635\u0648\u0631';
      subtitle.textContent = '\u0627\u0646\u062a\u0638\u0631 \u0642\u0644\u064a\u0644\u064b\u0627 \u062d\u062a\u0649 \u064a\u0643\u062a\u0645\u0644 \u062c\u0644\u0628 \u0628\u064a\u0627\u0646\u0627\u062a \u0627\u0644\u0635\u0648\u0631...';
      root.innerHTML = '<div class="empty-state"><p class="muted">\u062c\u0627\u0631\u064a \u062a\u062d\u0645\u064a\u0644 \u0645\u062d\u0631\u0631 ALT...</p></div>';
      return;
    }

    const editor = portalState.imageAltEditor;

    if (!editor) {
      alertRoot.innerHTML = '';
      title.textContent = '\u0645\u062d\u0631\u0631 ALT \u0627\u0644\u0635\u0648\u0631';
      subtitle.textContent = '\u0627\u062e\u062a\u0631 \u0645\u0646\u062a\u062c\u064b\u0627 \u0644\u0641\u062a\u062d \u0645\u062d\u0631\u0631 \u0627\u0644\u0646\u0635 \u0627\u0644\u0628\u062f\u064a\u0644 \u0644\u0644\u0635\u0648\u0631.';
      root.innerHTML = '<div class="empty-state"><p class="muted">\u0644\u0627 \u064a\u0648\u062c\u062f \u0645\u0646\u062a\u062c \u0645\u0641\u062a\u0648\u062d \u0627\u0644\u0622\u0646.</p></div>';
      return;
    }

    const selectedCount = editor.images.filter((image) => image.selected).length;
    title.textContent = editor.productName;
    subtitle.textContent = `\u0627\u0644\u0635\u0648\u0631 \u0627\u0644\u0645\u062d\u062f\u062f\u0629: ${selectedCount}`;
    alertRoot.innerHTML = editor.notice ? `<div class="notice ${editor.notice.type}">${escapeHtml(editor.notice.message)}</div>` : '';

    root.innerHTML = `
      <div class="card surface-soft" style="margin-bottom:16px;box-shadow:none;">
        <div class="section-head" style="margin-bottom:12px;">
          <div>
            <h3 style="margin-bottom:6px;">\u0625\u062f\u0627\u0631\u0629 ALT \u0627\u0644\u0635\u0648\u0631</h3>
            <p class="muted" style="margin:0;">\u062d\u062f\u062f \u0635\u0648\u0631\u0629 \u0648\u0627\u062d\u062f\u0629 \u0623\u0648 \u0639\u062f\u0629 \u0635\u0648\u0631 \u062b\u0645 \u0648\u0644\u0651\u062f ALT \u0628\u0627\u0644\u0630\u0643\u0627\u0621 \u0627\u0644\u0627\u0635\u0637\u0646\u0627\u0639\u064a \u0648\u0631\u0627\u062c\u0639\u0647 \u0642\u0628\u0644 \u0627\u0644\u062d\u0641\u0638.</p>
          </div>
          <div class="inline-actions">
            <button class="btn btn-sky" type="button" data-alt-action="select-all">\u062a\u062d\u062f\u064a\u062f \u0627\u0644\u0643\u0644</button>
            <button class="btn btn-secondary" type="button" data-alt-action="clear-selection">\u0625\u0644\u063a\u0627\u0621 \u0627\u0644\u062a\u062d\u062f\u064a\u062f</button>
            <button class="btn" type="button" data-alt-action="optimize-selected">\u062a\u062d\u0633\u064a\u0646 \u0627\u0644\u0645\u062d\u062f\u062f</button>
            <button class="btn btn-secondary" type="button" data-alt-action="save-selected">\u062d\u0641\u0638 \u0627\u0644\u0645\u062d\u062f\u062f</button>
          </div>
        </div>
      </div>
      <div class="products-grid" style="grid-template-columns:repeat(auto-fit,minmax(280px,1fr));">
        ${editor.images.map((image) => `
          <article class="product-card" style="padding:18px;gap:14px;">
            <div style="display:flex;justify-content:space-between;gap:12px;align-items:center;">
              <label style="display:flex;align-items:center;gap:8px;font-weight:700;cursor:pointer;">
                <input type="checkbox" data-alt-select="${Number(image.id)}" ${image.selected ? 'checked' : ''}>
                \u062a\u062d\u062f\u064a\u062f
              </label>
              <span class="status-badge ${String(image.currentAlt || '').trim() ? 'success' : 'danger'}">${String(image.currentAlt || '').trim() ? 'ALT \u062c\u0627\u0647\u0632' : 'ALT \u063a\u064a\u0631 \u062c\u0627\u0647\u0632'}</span>
            </div>
            <img class="product-thumb" style="aspect-ratio:1/1;max-width:100%;height:auto;object-fit:cover;border-radius:18px;" src="${escapeHtml(image.url || '')}" alt="${escapeHtml(editor.productName)}">
            <div class="card surface-soft" style="padding:14px;box-shadow:none;">
              <strong>ALT \u0627\u0644\u062d\u0627\u0644\u064a</strong>
              <p class="muted" style="margin:8px 0 0;line-height:1.8;">${escapeHtml(image.currentAlt || '\u0644\u0627 \u064a\u0648\u062c\u062f ALT \u062d\u0627\u0644\u064a \u0644\u0647\u0630\u0647 \u0627\u0644\u0635\u0648\u0631\u0629.')}</p>
            </div>
            <div>
              <label><strong>ALT \u0628\u0639\u062f \u0627\u0644\u062a\u062d\u0633\u064a\u0646</strong></label>
              <textarea rows="4" data-alt-input="${Number(image.id)}">${escapeHtml(image.optimizedAlt || '')}</textarea>
            </div>
            <div class="product-actions">
              <button class="btn btn-sky" type="button" data-alt-action="optimize-one" data-image-id="${Number(image.id)}">\u062a\u062d\u0633\u064a\u0646 ALT</button>
              <button class="btn" type="button" data-alt-action="save-one" data-image-id="${Number(image.id)}">\u062d\u0641\u0638 ALT</button>
            </div>
          </article>
        `).join('')}
      </div>
    `;
  }

  async function optimizeSingleImageAlt(imageId) {
    const editor = portalState.imageAltEditor;
    if (!editor) return;

    editor.notice = { type: 'success', message: '\u062c\u0627\u0631\u064a \u062a\u0648\u0644\u064a\u062f ALT \u0644\u0644\u0635\u0648\u0631\u0629...' };
    renderImageAltBody();

    try {
      const data = await fetch(`/api/products/${editor.productId}/images/${imageId}/optimize-alt`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ language: document.getElementById('language')?.value || 'ar' })
      }).then((response) => response.json());

      if (!data.success) {
        editor.notice = { type: 'error', message: data.message || '\u062a\u0639\u0630\u0651\u0631 \u062a\u062d\u0633\u064a\u0646 ALT \u0644\u0644\u0635\u0648\u0631\u0629.' };
        renderImageAltBody();
        return;
      }

      const image = getImageAltItem(imageId);
      if (image) {
        image.optimizedAlt = data.optimized_alt || '';
        image.selected = true;
      }

      editor.notice = { type: 'success', message: '\u062a\u0645 \u062a\u0648\u0644\u064a\u062f ALT \u0628\u0646\u062c\u0627\u062d. \u0631\u0627\u062c\u0639 \u0627\u0644\u0646\u0635 \u0642\u0628\u0644 \u0627\u0644\u062d\u0641\u0638.' };
      renderImageAltBody();
    } catch (error) {
      editor.notice = { type: 'error', message: '\u062d\u062f\u062b \u062e\u0637\u0623 \u0623\u062b\u0646\u0627\u0621 \u062a\u062d\u0633\u064a\u0646 ALT \u0644\u0644\u0635\u0648\u0631\u0629.' };
      renderImageAltBody();
    }
  }

  async function saveSingleImageAlt(imageId) {
    const editor = portalState.imageAltEditor;
    const image = getImageAltItem(imageId);
    if (!editor || !image) return;

    try {
      const data = await fetch(`/api/products/${editor.productId}/images/${imageId}/save-alt`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ alt: image.optimizedAlt || '' })
      }).then((response) => response.json());

      if (!data.success) {
        editor.notice = { type: 'error', message: data.message || '\u062a\u0639\u0630\u0651\u0631 \u062d\u0641\u0638 ALT \u0644\u0644\u0635\u0648\u0631\u0629.' };
        renderImageAltBody();
        return;
      }

      image.currentAlt = data.saved_alt || image.optimizedAlt || '';
      image.optimizedAlt = image.currentAlt;
      syncProductImageAlt(editor.productId, imageId, image.currentAlt);
      editor.notice = { type: 'success', message: '\u062a\u0645 \u062d\u0641\u0638 ALT \u0644\u0644\u0635\u0648\u0631\u0629 \u0628\u0646\u062c\u0627\u062d.' };
      renderImageAltBody();
      renderDashboardProducts();
      await loadPortalSubscription();
      await loadOperations();
    } catch (error) {
      editor.notice = { type: 'error', message: '\u062d\u062f\u062b \u062e\u0637\u0623 \u0623\u062b\u0646\u0627\u0621 \u062d\u0641\u0638 ALT \u0644\u0644\u0635\u0648\u0631\u0629.' };
      renderImageAltBody();
    }
  }

  async function optimizeSelectedImagesAlt() {
    const editor = portalState.imageAltEditor;
    if (!editor) return;

    const selectedIds = getSelectedImageIds();
    if (!selectedIds.length) {
      editor.notice = { type: 'error', message: '\u062d\u062f\u062f \u0635\u0648\u0631\u0629 \u0648\u0627\u062d\u062f\u0629 \u0639\u0644\u0649 \u0627\u0644\u0623\u0642\u0644 \u0642\u0628\u0644 \u0627\u0644\u062a\u062d\u0633\u064a\u0646.' };
      renderImageAltBody();
      return;
    }

    editor.notice = { type: 'success', message: '\u062c\u0627\u0631\u064a \u062a\u062d\u0633\u064a\u0646 ALT \u0644\u0644\u0635\u0648\u0631 \u0627\u0644\u0645\u062d\u062f\u062f\u0629...' };
    renderImageAltBody();

    try {
      const data = await fetch(`/api/products/${editor.productId}/images/optimize-alt`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          image_ids: selectedIds,
          language: document.getElementById('language')?.value || 'ar'
        })
      }).then((response) => response.json());

      if (!data.success) {
        editor.notice = { type: 'error', message: data.message || '\u062a\u0639\u0630\u0651\u0631 \u062a\u062d\u0633\u064a\u0646 ALT \u0644\u0644\u0635\u0648\u0631 \u0627\u0644\u0645\u062d\u062f\u062f\u0629.' };
        renderImageAltBody();
        return;
      }

      (data.images || []).forEach((payload) => {
        const image = getImageAltItem(payload.image_id);
        if (image) {
          image.optimizedAlt = payload.optimized_alt || '';
        }
      });

      editor.notice = { type: 'success', message: '\u062a\u0645 \u062a\u0648\u0644\u064a\u062f ALT \u0644\u0644\u0635\u0648\u0631 \u0627\u0644\u0645\u062d\u062f\u062f\u0629. \u0631\u0627\u062c\u0639 \u0627\u0644\u0646\u0635\u0648\u0635 \u062b\u0645 \u0627\u062d\u0641\u0638\u0647\u0627.' };
      renderImageAltBody();
    } catch (error) {
      editor.notice = { type: 'error', message: '\u062d\u062f\u062b \u062e\u0637\u0623 \u0623\u062b\u0646\u0627\u0621 \u062a\u062d\u0633\u064a\u0646 ALT \u0644\u0644\u0635\u0648\u0631 \u0627\u0644\u0645\u062d\u062f\u062f\u0629.' };
      renderImageAltBody();
    }
  }

  async function saveSelectedImagesAlt() {
    const editor = portalState.imageAltEditor;
    if (!editor) return;

    const selectedImages = editor.images.filter((image) => image.selected);
    if (!selectedImages.length) {
      editor.notice = { type: 'error', message: '\u062d\u062f\u062f \u0635\u0648\u0631\u0629 \u0648\u0627\u062d\u062f\u0629 \u0639\u0644\u0649 \u0627\u0644\u0623\u0642\u0644 \u0642\u0628\u0644 \u0627\u0644\u062d\u0641\u0638.' };
      renderImageAltBody();
      return;
    }

    try {
      const data = await fetch(`/api/products/${editor.productId}/images/save-alt`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          images: selectedImages.map((image) => ({
            image_id: image.id,
            alt: image.optimizedAlt || ''
          }))
        })
      }).then((response) => response.json());

      if (!data.success) {
        editor.notice = { type: 'error', message: data.message || '\u062a\u0639\u0630\u0651\u0631 \u062d\u0641\u0638 ALT \u0644\u0644\u0635\u0648\u0631 \u0627\u0644\u0645\u062d\u062f\u062f\u0629.' };
        renderImageAltBody();
        return;
      }

      selectedImages.forEach((image) => {
        image.currentAlt = image.optimizedAlt || '';
        syncProductImageAlt(editor.productId, image.id, image.currentAlt);
      });

      editor.notice = { type: 'success', message: '\u062a\u0645 \u062d\u0641\u0638 ALT \u0644\u0644\u0635\u0648\u0631 \u0627\u0644\u0645\u062d\u062f\u062f\u0629 \u0628\u0646\u062c\u0627\u062d.' };
      renderImageAltBody();
      renderDashboardProducts();
      await loadPortalSubscription();
      await loadOperations();
    } catch (error) {
      editor.notice = { type: 'error', message: '\u062d\u062f\u062b \u062e\u0637\u0623 \u0623\u062b\u0646\u0627\u0621 \u062d\u0641\u0638 ALT \u0644\u0644\u0635\u0648\u0631 \u0627\u0644\u0645\u062d\u062f\u062f\u0629.' };
      renderImageAltBody();
    }
  }

  async function bulkOptimizeVisibleAlt() {
    const visibleIds = getPagedProducts().items.map((product) => Number(product.id));
    if (!visibleIds.length) {
      return;
    }

    const button = document.getElementById('bulk-alt-visible');
    const oldLabel = button?.textContent || '\u062a\u062d\u0633\u064a\u0646 ALT \u0644\u0644\u0645\u0639\u0631\u0648\u0636';

    if (button) {
      button.disabled = true;
      button.textContent = '\u062c\u0627\u0631\u064a \u0627\u0644\u062a\u062d\u0633\u064a\u0646...';
    }

    try {
      const data = await fetch('/api/products/alt/bulk', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          product_ids: visibleIds,
          language: document.getElementById('language')?.value || 'ar'
        })
      }).then((response) => response.json());

      if (!data.success && !(Array.isArray(data.processed) && data.processed.length)) {
        alert(data.message || '\u062a\u0639\u0630\u0651\u0631 \u062a\u062d\u0633\u064a\u0646 ALT \u0644\u0644\u0645\u0639\u0631\u0648\u0636.');
        return;
      }

      await loadPortalProducts();
      await loadPortalSubscription();
      await loadOperations();
      alert(`\u062a\u0645 \u062a\u062d\u0633\u064a\u0646 ALT \u0644\u0644\u0635\u0648\u0631 \u0641\u064a ${Array.isArray(data.processed) ? data.processed.length : 0} \u0645\u0646\u062a\u062c.`);
    } catch (error) {
      alert('\u062d\u062f\u062b \u062e\u0637\u0623 \u0623\u062b\u0646\u0627\u0621 \u062a\u062d\u0633\u064a\u0646 ALT \u0644\u0644\u0645\u0639\u0631\u0648\u0636.');
    } finally {
      if (button) {
        button.disabled = false;
        button.textContent = oldLabel;
      }
    }
  }

  function injectAltFilters() {
    const filterSelect = document.getElementById('filter-content');

    if (filterSelect && !filterSelect.querySelector('option[value="alt_ready"]')) {
      filterSelect.insertAdjacentHTML('beforeend', `
        <option value="alt_ready">ALT \u0645\u062d\u0633\u0651\u0646</option>
        <option value="alt_missing">ALT \u063a\u064a\u0631 \u0645\u062d\u0633\u0651\u0646</option>
      `);
    }

    const chipsRow = document.querySelector('.toolbar .chips');

    if (chipsRow && !chipsRow.querySelector('[data-quick-filter="alt_missing"]')) {
      chipsRow.insertAdjacentHTML('beforeend', '<button class="chip" data-quick-filter="alt_missing" type="button">\u0639\u0631\u0636 \u0627\u0644\u0645\u0646\u062a\u062c\u0627\u062a \u0627\u0644\u062a\u064a \u0644\u064a\u0633 \u0644\u0647\u0627 ALT \u0645\u062d\u0633\u0651\u0646</button>');
    }
  }

  function updateStoreSeoCounters() {
    const title = document.getElementById('store-seo-title')?.value || '';
    const description = document.getElementById('store-seo-description')?.value || '';
    const keywords = document.getElementById('store-seo-keywords')?.value || '';

    if (document.getElementById('store-seo-title-count')) {
      document.getElementById('store-seo-title-count').textContent = `${title.length} \u062d\u0631\u0641`;
    }
    if (document.getElementById('store-seo-description-count')) {
      document.getElementById('store-seo-description-count').textContent = `${description.length} \u062d\u0631\u0641`;
    }
    if (document.getElementById('store-seo-keywords-count')) {
      document.getElementById('store-seo-keywords-count').textContent = `${keywords.length} \u062d\u0631\u0641`;
    }
  }

  function setStoreSeoAlert(type, message) {
    const root = document.getElementById('store-seo-alert');
    if (!root) return;
    root.innerHTML = message ? `<div class="notice ${type}">${escapeHtml(message)}</div>` : '';
  }

  async function loadStoreSeo() {
    try {
      const data = await fetch('/api/store-seo').then((response) => response.json());
      if (!data.success) {
        setStoreSeoAlert('error', data.message || '\u062a\u0639\u0630\u0651\u0631 \u062c\u0644\u0628 \u0633\u064a\u0648 \u0627\u0644\u0645\u062a\u062c\u0631.');
        return;
      }

      const seo = data.seo || {};
      if (document.getElementById('store-seo-title')) document.getElementById('store-seo-title').value = seo.title || '';
      if (document.getElementById('store-seo-description')) document.getElementById('store-seo-description').value = seo.description || '';
      if (document.getElementById('store-seo-keywords')) document.getElementById('store-seo-keywords').value = seo.keywords || '';
      setStoreSeoAlert('', '');
      updateStoreSeoCounters();
    } catch (error) {
      setStoreSeoAlert('error', '\u062a\u0639\u0630\u0651\u0631 \u062c\u0644\u0628 \u0633\u064a\u0648 \u0627\u0644\u0645\u062a\u062c\u0631.');
    }
  }

  async function optimizeStoreSeo() {
    const button = document.getElementById('generate-store-seo');
    const oldText = button?.textContent || '\u0625\u0646\u0634\u0627\u0621 \u0628\u0627\u0644\u0630\u0643\u0627\u0621 \u0627\u0644\u0627\u0635\u0637\u0646\u0627\u0639\u064a';

    if (button) {
      button.disabled = true;
      button.textContent = '\u062c\u0627\u0631\u064a \u0627\u0644\u062a\u0648\u0644\u064a\u062f...';
    }

    setStoreSeoAlert('success', '\u062c\u0627\u0631\u064a \u0625\u0646\u0634\u0627\u0621 \u0639\u0646\u0648\u0627\u0646 \u0648\u0648\u0635\u0641 \u0627\u0644\u0645\u062a\u062c\u0631...');

    try {
      const data = await fetch('/api/store-seo/optimize', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          tone: document.getElementById('tone')?.value || '\u0627\u062d\u062a\u0631\u0627\u0641\u064a \u0645\u0642\u0646\u0639',
          language: document.getElementById('language')?.value || 'ar'
        })
      }).then((response) => response.json());

      if (!data.success) {
        setStoreSeoAlert('error', data.message || '\u062a\u0639\u0630\u0651\u0631 \u062a\u0648\u0644\u064a\u062f \u0633\u064a\u0648 \u0627\u0644\u0645\u062a\u062c\u0631.');
        return;
      }

      if (document.getElementById('store-seo-title')) document.getElementById('store-seo-title').value = data.optimized_title || '';
      if (document.getElementById('store-seo-description')) document.getElementById('store-seo-description').value = data.optimized_description || '';
      if (document.getElementById('store-seo-keywords')) document.getElementById('store-seo-keywords').value = data.optimized_keywords || '';
      updateStoreSeoCounters();
      setStoreSeoAlert('success', '\u062a\u0645 \u0625\u0646\u0634\u0627\u0621 \u0633\u064a\u0648 \u0627\u0644\u0645\u062a\u062c\u0631. \u0631\u0627\u062c\u0639 \u0627\u0644\u062d\u0642\u0648\u0644 \u0642\u0628\u0644 \u0627\u0644\u062d\u0641\u0638.');
    } catch (error) {
      setStoreSeoAlert('error', '\u062d\u062f\u062b \u062e\u0637\u0623 \u0623\u062b\u0646\u0627\u0621 \u062a\u0648\u0644\u064a\u062f \u0633\u064a\u0648 \u0627\u0644\u0645\u062a\u062c\u0631.');
    } finally {
      if (button) {
        button.disabled = false;
        button.textContent = oldText;
      }
    }
  }

  async function saveStoreSeo() {
    const button = document.getElementById('save-store-seo');
    const oldText = button?.textContent || '\u062d\u0641\u0638 \u0627\u0644\u062a\u063a\u064a\u064a\u0631\u0627\u062a';
    const title = document.getElementById('store-seo-title')?.value.trim() || '';
    const description = document.getElementById('store-seo-description')?.value.trim() || '';
    const keywords = document.getElementById('store-seo-keywords')?.value.trim() || '';

    if (!title || !description) {
      setStoreSeoAlert('error', '\u0623\u062f\u062e\u0644 \u0639\u0646\u0648\u0627\u0646 \u0627\u0644\u0645\u062a\u062c\u0631 \u0648\u0648\u0635\u0641\u0647 \u0642\u0628\u0644 \u0627\u0644\u062d\u0641\u0638.');
      return;
    }

    if (button) {
      button.disabled = true;
      button.textContent = '\u062c\u0627\u0631\u064a \u0627\u0644\u062d\u0641\u0638...';
    }

    setStoreSeoAlert('success', '\u062c\u0627\u0631\u064a \u062d\u0641\u0638 \u0633\u064a\u0648 \u0627\u0644\u0645\u062a\u062c\u0631...');

    try {
      const data = await fetch('/api/store-seo/save', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ title, description, keywords })
      }).then((response) => response.json());

      if (!data.success) {
        setStoreSeoAlert('error', data.message || '\u062a\u0639\u0630\u0651\u0631 \u062d\u0641\u0638 \u0633\u064a\u0648 \u0627\u0644\u0645\u062a\u062c\u0631.');
        return;
      }

      setStoreSeoAlert('success', data.message || '\u062a\u0645 \u062d\u0641\u0638 \u0633\u064a\u0648 \u0627\u0644\u0645\u062a\u062c\u0631 \u0628\u0646\u062c\u0627\u062d.');
      await loadPortalSubscription();
      await loadOperations();
    } catch (error) {
      setStoreSeoAlert('error', '\u062d\u062f\u062b \u062e\u0637\u0623 \u0623\u062b\u0646\u0627\u0621 \u062d\u0641\u0638 \u0633\u064a\u0648 \u0627\u0644\u0645\u062a\u062c\u0631.');
    } finally {
      if (button) {
        button.disabled = false;
        button.textContent = oldText;
      }
    }
  }

  function bindStaticEvents() {
    document.getElementById('page-size')?.addEventListener('change', (event) => {
      portalState.pageSize = Number(event.target.value || 12);
      portalState.page = 1;
      renderDashboardProducts();
    });

    document.querySelectorAll('[data-dashboard-tab]').forEach((button) => {
      button.addEventListener('click', () => {
        switchDashboardTab(button.dataset.dashboardTab || 'products');
      });
    });

    document.getElementById('open-filter-modal')?.addEventListener('click', openFilterModal);
    document.getElementById('close-filter-modal')?.addEventListener('click', closeFilterModal);
    document.getElementById('apply-filters')?.addEventListener('click', applyFilters);
    document.getElementById('clear-filters')?.addEventListener('click', clearFilters);
    document.getElementById('reset-filters')?.addEventListener('click', clearFilters);
    document.getElementById('close-editor')?.addEventListener('click', closeEditor);
    document.getElementById('close-image-alt')?.addEventListener('click', closeImageAltModal);
    document.getElementById('bulk-alt-visible')?.addEventListener('click', bulkOptimizeVisibleAlt);
    document.getElementById('alt-tab-bulk-optimize')?.addEventListener('click', bulkOptimizeVisibleAlt);
    document.getElementById('operations-apply-filter')?.addEventListener('click', () => loadOperations());
    document.getElementById('operations-show-all')?.addEventListener('click', () => loadOperations('all'));
    document.getElementById('generate-store-seo')?.addEventListener('click', optimizeStoreSeo);
    document.getElementById('save-store-seo')?.addEventListener('click', saveStoreSeo);

    document.getElementById('store-seo-title')?.addEventListener('input', updateStoreSeoCounters);
    document.getElementById('store-seo-description')?.addEventListener('input', updateStoreSeoCounters);
    document.getElementById('store-seo-keywords')?.addEventListener('input', updateStoreSeoCounters);

    document.querySelectorAll('[data-quick-filter]').forEach((chip) => {
      chip.addEventListener('click', () => {
        portalState.quickFilter = chip.dataset.quickFilter || 'all';
        portalState.page = 1;
        renderDashboardProducts();
      });
    });

    renderDashboardTabState();

    document.getElementById('filter-modal')?.addEventListener('click', (event) => {
      if (event.target.id === 'filter-modal') {
        closeFilterModal();
      }
    });

    document.getElementById('editor-modal')?.addEventListener('click', (event) => {
      if (event.target.id === 'editor-modal') {
        closeEditor();
      }
    });

    document.getElementById('image-alt-modal')?.addEventListener('click', (event) => {
      if (event.target.id === 'image-alt-modal') {
        closeImageAltModal();
      }
    });

    document.getElementById('image-alt-body')?.addEventListener('change', (event) => {
      const imageId = event.target.getAttribute('data-alt-select');

      if (!imageId || !portalState.imageAltEditor) {
        return;
      }

      const image = getImageAltItem(Number(imageId));

      if (!image) {
        return;
      }

      image.selected = event.target.checked;
      renderImageAltBody();
    });

    document.getElementById('image-alt-body')?.addEventListener('input', (event) => {
      const imageId = event.target.getAttribute('data-alt-input');

      if (!imageId || !portalState.imageAltEditor) {
        return;
      }

      const image = getImageAltItem(Number(imageId));

      if (!image) {
        return;
      }

      image.optimizedAlt = event.target.value;
    });

    document.getElementById('image-alt-body')?.addEventListener('click', async (event) => {
      const button = event.target.closest('[data-alt-action]');

      if (!button) {
        return;
      }

      const action = button.getAttribute('data-alt-action');
      const imageId = Number(button.getAttribute('data-image-id') || 0);

      if (action === 'select-all' && portalState.imageAltEditor) {
        portalState.imageAltEditor.images.forEach((image) => {
          image.selected = true;
        });
        renderImageAltBody();
        return;
      }

      if (action === 'clear-selection' && portalState.imageAltEditor) {
        portalState.imageAltEditor.images.forEach((image) => {
          image.selected = false;
        });
        renderImageAltBody();
        return;
      }

      const oldText = button.textContent;
      button.disabled = true;

      try {
        if (action === 'optimize-one') {
          button.textContent = '\u062c\u0627\u0631\u064a \u0627\u0644\u062a\u062d\u0633\u064a\u0646...';
          await optimizeSingleImageAlt(imageId);
        }

        if (action === 'save-one') {
          button.textContent = '\u062c\u0627\u0631\u064a \u0627\u0644\u062d\u0641\u0638...';
          await saveSingleImageAlt(imageId);
        }

        if (action === 'optimize-selected') {
          button.textContent = '\u062c\u0627\u0631\u064a \u0627\u0644\u062a\u062d\u0633\u064a\u0646...';
          await optimizeSelectedImagesAlt();
        }

        if (action === 'save-selected') {
          button.textContent = '\u062c\u0627\u0631\u064a \u0627\u0644\u062d\u0641\u0638...';
          await saveSelectedImagesAlt();
        }
      } finally {
        button.disabled = false;
        button.textContent = oldText;
      }
    });
  }

  injectAltFilters();
  bindStaticEvents();
  renderEditorBody();
  renderImageAltBody();
  loadPortalSubscription();
  loadStoreSeo();
  loadPortalProducts();
  loadOperations();
  scheduleUiFix(document.body);
})();
