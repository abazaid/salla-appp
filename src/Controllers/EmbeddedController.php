<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\StoreRepository;
use App\Support\Request;
use App\Support\Response;
use App\Support\View;

final class EmbeddedController
{
    public function index(): void
    {
        $token = (string) Request::query('token', '');
        $lang = (string) Request::query('lang', 'ar');
        $theme = (string) Request::query('theme', 'light');
        $repository = new StoreRepository();
        $storesCount = count($repository->all());

        $html = View::render('Embedded Optimizer', <<<HTML
<div class="card">
  <span class="pill">Embedded Page</span>
  <span class="pill">Theme: {$theme}</span>
  <h1>مساعد تحسين وصف المنتجات</h1>
  <p class="muted">هذه الصفحة مصممة لتعمل كواجهة بسيطة داخل لوحة سلة: تعرض حالة الاشتراك، المنتجات، ثم تسمح للتاجر بتحسين الوصف بضغطة زر.</p>
  <div class="grid">
    <div class="card">
      <h2>حالة البيئة</h2>
      <ul>
        <li>اللغة القادمة من سلة: <code>{$lang}</code></li>
        <li>هل وصل توكن iframe؟ <code>{$this->yesNo($token !== '')}</code></li>
        <li>عدد المتاجر المخزنة محليًا: <code>{$storesCount}</code></li>
      </ul>
    </div>
    <div class="card">
      <h2>كيف يعمل الاستخدام؟</h2>
      <ul>
        <li>كل تحسين ناجح يخصم منتجًا واحدًا من الحصة</li>
        <li>يتم الخصم فقط بعد نجاح التحديث داخل سلة</li>
        <li>يمكنك اختيار نبرة الوصف قبل التنفيذ</li>
      </ul>
    </div>
  </div>

  <div id="status-card" class="card" style="margin-top:16px;">
    <h2>الاشتراك</h2>
    <p class="muted">جاري تحميل حالة الاشتراك...</p>
  </div>

  <div class="card" style="margin-top:16px;">
    <h2>خيارات التحسين</h2>
    <div class="grid">
      <div>
        <label for="tone"><strong>نبرة الوصف</strong></label>
        <select id="tone" style="width:100%;margin-top:8px;padding:12px;border-radius:12px;border:1px solid #d8c7b4;background:#fffaf3;">
          <option value="احترافي مقنع">احترافي مقنع</option>
          <option value="فاخر أنيق">فاخر أنيق</option>
          <option value="عملي مباشر">عملي مباشر</option>
          <option value="ودود بسيط">ودود بسيط</option>
        </select>
      </div>
      <div>
        <label for="language"><strong>لغة الإخراج</strong></label>
        <select id="language" style="width:100%;margin-top:8px;padding:12px;border-radius:12px;border:1px solid #d8c7b4;background:#fffaf3;">
          <option value="ar">العربية</option>
          <option value="en">English</option>
        </select>
      </div>
    </div>
  </div>

  <div class="card" style="margin-top:16px;">
    <h2>المنتجات</h2>
    <p class="muted">اضغط "تحسين المحتوى" لرؤية الوصف الحالي والميتا الحالية مقابل النسخة المحسنة، ثم عدّل يدويًا وبعدها احفظ في المتجر أو ألغِ.</p>
    <div id="products-list">جاري تحميل المنتجات...</div>
  </div>
</div>

<script>
const state = {
  products: [],
  loadingProductId: null,
  drafts: {},
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

async function loadSubscription() {
  const card = document.getElementById('status-card');

  try {
    const res = await fetch('/api/subscription');
    const data = await res.json();

    if (!data.success) {
      card.innerHTML = '<h2>الاشتراك</h2><p class="muted">' + escapeHtml(data.message) + '</p>';
      return;
    }

    const sub = data.subscription;
    card.innerHTML = `
      <h2>الاشتراك</h2>
      <div class="grid">
        <div class="card">
          <strong>الحالة</strong>
          <p>\${escapeHtml(sub.status)}</p>
        </div>
        <div class="card">
          <strong>الباقة</strong>
          <p>\${escapeHtml(sub.plan_name ?? '-')}</p>
        </div>
        <div class="card">
          <strong>المتبقي</strong>
          <p>\${escapeHtml(sub.remaining_products)}</p>
        </div>
        <div class="card">
          <strong>المستخدم</strong>
          <p>\${escapeHtml(sub.used_products)} / \${escapeHtml(sub.product_quota)}</p>
        </div>
      </div>
    `;
  } catch (error) {
    card.innerHTML = '<h2>الاشتراك</h2><p class="muted">تعذر تحميل الاشتراك.</p>';
  }
}

function renderProducts() {
  const root = document.getElementById('products-list');

  if (!state.products.length) {
    root.innerHTML = '<p class="muted">لا توجد منتجات حاليًا في هذا المتجر.</p>';
    return;
  }

  root.innerHTML = state.products.map((product) => {
    const plainDescription = stripHtml(product.description).slice(0, 280) || 'لا يوجد وصف حالياً.';
    const price = product.price?.amount ?? '-';
    const currency = product.price?.currency ?? 'SAR';
    const disabled = state.loadingProductId === product.id ? 'disabled' : '';
    const loadingLabel = state.loadingProductId === product.id ? 'جاري التحسين...' : 'تحسين الوصف';
    const draft = state.drafts[product.id];
    const draftHtml = draft ? `
      <div class="card" style="margin-top:16px;background:#fcf8f2;">
        <div class="grid">
          <div>
            <strong>الوصف الحالي</strong>
            <textarea readonly style="width:100%;min-height:220px;margin-top:10px;padding:14px;border-radius:14px;border:1px solid #d8c7b4;background:#f9f2e8;">\${escapeHtml(draft.current_description)}</textarea>
          </div>
          <div>
            <strong>الوصف بعد التحسين</strong>
            <textarea id="edited-\${Number(product.id)}" style="width:100%;min-height:220px;margin-top:10px;padding:14px;border-radius:14px;border:1px solid #d8c7b4;background:#fff;">\${escapeHtml(draft.optimized_description)}</textarea>
          </div>
        </div>
        <div class="grid" style="margin-top:12px;">
          <div>
            <strong>Meta Title الحالي</strong>
            <textarea readonly style="width:100%;min-height:90px;margin-top:10px;padding:14px;border-radius:14px;border:1px solid #d8c7b4;background:#f9f2e8;">\${escapeHtml(draft.current_metadata_title)}</textarea>
          </div>
          <div>
            <strong>Meta Title بعد التحسين</strong>
            <textarea id="meta-title-\${Number(product.id)}" style="width:100%;min-height:90px;margin-top:10px;padding:14px;border-radius:14px;border:1px solid #d8c7b4;background:#fff;">\${escapeHtml(draft.optimized_metadata_title)}</textarea>
          </div>
        </div>
        <div class="grid" style="margin-top:12px;">
          <div>
            <strong>Meta Description الحالية</strong>
            <textarea readonly style="width:100%;min-height:130px;margin-top:10px;padding:14px;border-radius:14px;border:1px solid #d8c7b4;background:#f9f2e8;">\${escapeHtml(draft.current_metadata_description)}</textarea>
          </div>
          <div>
            <strong>Meta Description بعد التحسين</strong>
            <textarea id="meta-description-\${Number(product.id)}" style="width:100%;min-height:130px;margin-top:10px;padding:14px;border-radius:14px;border:1px solid #d8c7b4;background:#fff;">\${escapeHtml(draft.optimized_metadata_description)}</textarea>
          </div>
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:14px;">
          <button onclick="saveDescription(\${Number(product.id)})" style="background:#0d7a5f;color:#fff;border:none;padding:12px 16px;border-radius:12px;cursor:pointer;">حفظ في المتجر</button>
          <button onclick="cancelDraft(\${Number(product.id)})" style="background:#efe5d7;color:#6e4721;border:none;padding:12px 16px;border-radius:12px;cursor:pointer;">إلغاء</button>
        </div>
      </div>
    ` : '';

    return `
      <div class="card" style="margin-top:14px;">
        <div style="display:flex;gap:16px;align-items:flex-start;flex-wrap:wrap;">
          <img src="\${escapeHtml(product.thumbnail || product.main_image || '')}" alt="\${escapeHtml(product.name)}" style="width:110px;height:110px;object-fit:cover;border-radius:16px;border:1px solid #e8d9c8;background:#fff;">
          <div style="flex:1;min-width:260px;">
            <h3 style="margin-bottom:8px;">\${escapeHtml(product.name)}</h3>
            <p class="muted" style="margin:0 0 8px;">ID: <code>\${escapeHtml(product.id)}</code> | السعر: <code>\${escapeHtml(price)} \${escapeHtml(currency)}</code></p>
            <p style="margin:0 0 14px;">\${escapeHtml(plainDescription)}</p>
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
              <button \${disabled} onclick="optimizeProduct(\${Number(product.id)})" style="background:#0d7a5f;color:#fff;border:none;padding:12px 16px;border-radius:12px;cursor:pointer;">\${state.loadingProductId === product.id ? 'جاري التحسين...' : 'تحسين المحتوى'}</button>
              <a href="\${escapeHtml(product.urls?.admin || '#')}" target="_blank" style="display:inline-block;text-decoration:none;background:#efe5d7;color:#6e4721;padding:12px 16px;border-radius:12px;">فتح في لوحة سلة</a>
            </div>
            <div id="result-\${Number(product.id)}" style="margin-top:14px;"></div>
            \${draftHtml}
          </div>
        </div>
      </div>
    `;
  }).join('');
}

async function loadProducts() {
  const root = document.getElementById('products-list');

  try {
    const res = await fetch('/api/products');
    const data = await res.json();

    if (!data.success) {
      root.innerHTML = '<p class="muted">' + escapeHtml(data.message) + '</p>';
      return;
    }

    state.products = data.products || [];
    renderProducts();
  } catch (error) {
    root.innerHTML = '<p class="muted">تعذر تحميل المنتجات.</p>';
  }
}

async function optimizeProduct(productId) {
  state.loadingProductId = productId;
  renderProducts();

  const result = document.getElementById('result-' + productId);
  const tone = document.getElementById('tone').value;
  const language = document.getElementById('language').value;

  result.innerHTML = '<p class="muted">جاري إرسال المنتج إلى المساعد وتحسين الوصف...</p>';

  try {
    const res = await fetch('/api/products/' + productId + '/optimize', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ tone, language })
    });

    const data = await res.json();

    if (!data.success) {
      result.innerHTML = '<p style="color:#a33;">' + escapeHtml(data.message || 'فشل التحسين') + '</p>';
      await loadSubscription();
      return;
    }

    state.drafts[productId] = {
      current_description: data.current_description || '',
      current_metadata_title: data.current_metadata_title || '',
      current_metadata_description: data.current_metadata_description || '',
      optimized_description: data.optimized_description || '',
      optimized_metadata_title: data.optimized_metadata_title || '',
      optimized_metadata_description: data.optimized_metadata_description || '',
    };

    result.innerHTML = '';

    await loadSubscription();
    renderProducts();
  } catch (error) {
    result.innerHTML = '<p style="color:#a33;">حدث خطأ أثناء التحسين.</p>';
  } finally {
    state.loadingProductId = null;
    renderProducts();
  }
}

function cancelDraft(productId) {
  delete state.drafts[productId];
  renderProducts();
}

async function saveDescription(productId) {
  const result = document.getElementById('result-' + productId);
  const textarea = document.getElementById('edited-' + productId);
  const metaTitle = document.getElementById('meta-title-' + productId);
  const metaDescription = document.getElementById('meta-description-' + productId);
  const description = textarea ? textarea.value.trim() : '';
  const metadataTitle = metaTitle ? metaTitle.value.trim() : '';
  const metadataDescription = metaDescription ? metaDescription.value.trim() : '';

  if (!description) {
    result.innerHTML = '<p style="color:#a33;">الوصف المعدل فارغ.</p>';
    return;
  }

  result.innerHTML = '<p class="muted">جاري حفظ الوصف داخل المتجر...</p>';

  try {
    const res = await fetch('/api/products/' + productId + '/save-description', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        description,
        metadata_title: metadataTitle,
        metadata_description: metadataDescription
      })
    });

    const data = await res.json();

    if (!data.success) {
      result.innerHTML = '<p style="color:#a33;">' + escapeHtml(data.message || 'فشل الحفظ') + '</p>';
      await loadSubscription();
      return;
    }

    delete state.drafts[productId];
    result.innerHTML = '<p style="color:#0d7a5f;">تم حفظ الوصف في المتجر بنجاح.</p>';
    await loadSubscription();
    await loadProducts();
  } catch (error) {
    result.innerHTML = '<p style="color:#a33;">حدث خطأ أثناء الحفظ.</p>';
  }
}

loadSubscription();
loadProducts();
</script>
HTML);

        Response::html($html);
    }

    private function yesNo(bool $value): string
    {
        return $value ? 'yes' : 'no';
    }
}
