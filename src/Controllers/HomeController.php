<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Support\Response;
use App\Support\View;

final class HomeController
{
    public function index(): void
    {
        $appUrl = \App\Config::get('APP_URL', 'http://localhost:8000');

        $html = View::render('Salla Description Optimizer', <<<HTML
<div class="card">
  <span class="pill">Salla App Starter</span>
  <span class="pill">Product Description AI</span>
  <h1>تطبيق لتحسين وصف المنتجات داخل سلة</h1>
  <p class="muted">هذه نواة عملية تساعدك تبدأ بسرعة: ربط متجر، جلب المنتجات، اقتراح وصف أفضل، ثم تحديث المنتج في سلة عبر الـ API.</p>
  <div class="grid">
    <div class="card">
      <h2>ربط المتجر</h2>
      <p>نقطة الاستقبال الجاهزة: <code>{$appUrl}/oauth/callback</code></p>
    </div>
    <div class="card">
      <h2>استقبال Webhooks</h2>
      <p>نقطة الاستقبال الجاهزة: <code>{$appUrl}/webhooks/salla</code></p>
    </div>
    <div class="card">
      <h2>الواجهة المدمجة</h2>
      <p>واجهة التاجر الجاهزة: <code>{$appUrl}/embedded</code></p>
    </div>
  </div>
  <p><a class="btn" href="/embedded">فتح الواجهة المدمجة</a></p>
</div>
HTML);

        Response::html($html);
    }
}
