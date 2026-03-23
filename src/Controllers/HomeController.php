<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Config;
use App\Support\Response;

final class HomeController
{
    public function index(): void
    {
        $appUrl = (string) Config::get('APP_URL', 'http://localhost:8000');
        $safeAppUrl = htmlspecialchars(rtrim($appUrl, '/'), ENT_QUOTES, 'UTF-8');
        $loginHref = $safeAppUrl . '/login';
        $logoSrc = $safeAppUrl . '/assets/rankxseo-logo.svg';

        $html = <<<HTML
<!doctype html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>RankX SEO | منصة احترافية لتحسين محتوى متاجر سلة</title>
  <meta name="description" content="منصة RankX SEO تساعدك على تحسين وصف المنتجات، سيو المنتجات، النص البديل ALT للصور، وتحليل الكلمات المفتاحية والدومين لمتاجر سلة عبر لوحة واحدة احترافية.">
  <meta name="robots" content="index,follow,max-image-preview:large">
  <link rel="canonical" href="{$safeAppUrl}/">
  <meta property="og:type" content="website">
  <meta property="og:title" content="RankX SEO | تحسين سيو ومحتوى متاجر سلة">
  <meta property="og:description" content="حل شامل لتحسين وصف المنتجات ورفع جودة سيو المتجر وكتابة ALT احترافي للصور مع أدوات تحليل كلمات ومنافسين.">
  <meta property="og:url" content="{$safeAppUrl}/">
  <meta property="og:image" content="{$logoSrc}">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="RankX SEO | منصة تحسين متاجر سلة">
  <meta name="twitter:description" content="وصف منتجات أقوى، سيو أدق، وقرارات مبنية على بيانات حقيقية من لوحة واحدة.">
  <meta name="twitter:image" content="{$logoSrc}">
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&display=swap');
    :root{
      --primary-1:#3B82F6;
      --primary-2:#6366F1;
      --primary-3:#8B5CF6;
      --gradient-main:linear-gradient(135deg, #3B82F6 0%, #6366F1 50%, #8B5CF6 100%);
      --bg:#F8FAFC;
      --surface:#FFFFFF;
      --ink:#0F172A;
      --muted:#64748B;
      --border:#E2E8F0;
      --success:#10B981;
      --success-soft:#D1FAE5;
      --danger:#EF4444;
      --danger-soft:#FEE2E2;
      --glow-primary:0 0 20px rgba(99, 102, 241, 0.35);
      --shadow:0 22px 60px rgba(15,23,42,.08);
      --shadow-soft:0 12px 28px rgba(15,23,42,.04);
    }
    *{box-sizing:border-box}
    body{
      margin:0;
      font-family:"Tajawal","Segoe UI",sans-serif;
      color:var(--ink);
      background:var(--bg);
      min-height:100vh;
    }
    .wrap{width:min(1180px,100% - 28px);margin:22px auto 42px}
    .surface{
      background:var(--surface);
      border:1px solid var(--border);
      border-radius:16px;
      box-shadow:var(--shadow);
      padding:24px;
    }
    .top{
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:14px;
      margin-bottom:14px;
      flex-wrap:wrap;
    }
    .brand{
      display:flex;
      align-items:center;
      gap:14px;
      flex-wrap:wrap;
    }
    .brand img{width:min(280px,70vw);height:auto;display:block;filter:drop-shadow(0 0 20px rgba(99, 102, 241, 0.3))}
    .pill{
      display:inline-flex;
      align-items:center;
      padding:7px 12px;
      border-radius:999px;
      background:#EEF2FF;
      color:var(--primary-2);
      font-size:13px;
      font-weight:700;
      white-space:nowrap;
    }
    .hero{padding:32px 22px}
    h1{
      margin:0 0 14px;
      font-size:clamp(34px,5vw,62px);
      line-height:1.04;
      letter-spacing:-.02em;
    }
    .lead{
      margin:0;
      color:var(--muted);
      font-size:clamp(18px,2.2vw,24px);
      line-height:1.8;
      max-width:980px;
    }
    .hero-actions{
      display:flex;
      gap:12px;
      flex-wrap:wrap;
      margin-top:28px;
    }
    .btn{
      display:inline-flex;
      justify-content:center;
      align-items:center;
      min-width:170px;
      border:none;
      border-radius:12px;
      padding:13px 20px;
      font:700 18px/1 "Tajawal","Segoe UI",sans-serif;
      text-decoration:none;
      cursor:pointer;
      transition:.16s ease;
    }
    .btn-primary{
      color:#fff;
      background:var(--gradient-main);
      box-shadow:var(--glow-primary);
    }
    .btn-primary:hover{transform:translateY(-1px);box-shadow:0 0 30px rgba(99, 102, 241, 0.45)}
    .btn-secondary{
      color:#fff;
      background:var(--primary-1);
      box-shadow:0 0 10px rgba(59, 130, 246, 0.2);
    }
    .btn-secondary:hover{background:#2563EB;transform:translateY(-1px)}
    .btn-disabled{
      background:#E2E8F0;
      color:#94A3B8;
      cursor:not-allowed;
      box-shadow:none;
      pointer-events:none;
    }
    .section{margin-top:18px}
    .section h2{margin:0 0 10px;font-size:34px}
    .section p{margin:0;color:var(--muted);line-height:1.9;font-size:18px}
    .grid{
      display:grid;
      grid-template-columns:repeat(2,minmax(0,1fr));
      gap:14px;
      margin-top:18px;
    }
    .card{
      border:1px solid var(--border);
      background:var(--surface);
      border-radius:16px;
      padding:18px;
      box-shadow:var(--shadow-soft);
    }
    .card h3{margin:0 0 10px;font-size:25px}
    .list{
      margin:12px 0 0;
      padding:0 18px 0 0;
      color:#475569;
      line-height:2;
      font-size:17px;
    }
    .kpis{
      display:grid;
      grid-template-columns:repeat(4,minmax(0,1fr));
      gap:12px;
      margin-top:18px;
    }
    .kpi{
      text-align:center;
      border:1px solid var(--border);
      border-radius:16px;
      background:var(--surface);
      padding:14px 10px;
    }
    .kpi strong{
      display:block;
      margin-top:6px;
      font-size:30px;
      background:var(--gradient-main);
      -webkit-background-clip:text;
      -webkit-text-fill-color:transparent;
      line-height:1;
    }
    .steps{
      counter-reset:step;
      display:grid;
      grid-template-columns:repeat(4,minmax(0,1fr));
      gap:12px;
      margin-top:16px;
    }
    .step{
      border:1px solid var(--border);
      border-radius:16px;
      background:var(--surface);
      padding:14px;
      position:relative;
      min-height:142px;
    }
    .step::before{
      counter-increment:step;
      content:counter(step);
      position:absolute;
      top:10px;left:10px;
      width:34px;height:34px;
      border-radius:50%;
      display:grid;place-items:center;
      font-weight:800;
      color:#fff;
      background:var(--gradient-main);
    }
    .step h4{margin:36px 0 8px;font-size:21px}
    .step p{margin:0;color:var(--muted);font-size:16px;line-height:1.8}
    .faq{
      display:grid;
      gap:10px;
      margin-top:16px;
    }
    details{
      border:1px solid var(--border);
      background:var(--surface);
      border-radius:14px;
      padding:12px 14px;
    }
    summary{
      cursor:pointer;
      font-size:19px;
      font-weight:700;
      color:#0F172A;
    }
    details p{margin:8px 0 0;color:var(--muted);font-size:16px;line-height:1.9}
    .foot{
      margin-top:32px;
      text-align:center;
      color:var(--muted);
      font-size:14px;
      padding-top:20px;
      border-top:1px solid var(--border);
    }
    .foot a{color:var(--muted);text-decoration:none;transition:color .2s}
    .foot a:hover{color:var(--primary-2)}
    @media (max-width:1040px){
      .grid,.kpis,.steps{grid-template-columns:repeat(2,minmax(0,1fr))}
    }
    @media (max-width:700px){
      .wrap{width:min(100% - 14px,1180px);margin:12px auto 28px}
      .surface{border-radius:16px;padding:14px}
      .hero{padding:12px 6px}
      h1{font-size:clamp(30px,9vw,44px)}
      .lead{font-size:17px}
      .hero-actions .btn{width:100%}
      .grid,.kpis,.steps{grid-template-columns:1fr}
      .section h2{font-size:29px}
      .card h3{font-size:22px}
    }
  </style>
</head>
<body>
  <div class="wrap">
    <div class="surface">
      <div class="top">
        <div class="brand">
          <img src="{$logoSrc}" alt="RankX SEO">
          <span class="pill">منصة متخصصة لمتاجر سلة</span>
        </div>
      </div>

      <section class="hero">
        <h1>منصة احترافية لرفع نتائج متجرك في البحث وزيادة التحويل</h1>
        <p class="lead">
          RankX SEO تجمع لك أدوات تحسين المحتوى في لوحة واحدة: كتابة وصف منتجات متوافق مع SEO،
          تحسين سيو المتجر، إنشاء ALT احترافي للصور، تحليل الكلمات المفتاحية، ومتابعة منافسيك
          بخطوات عملية واضحة تناسب متاجر سلة التي تريد نموًا فعليًا وليس مجرد نصوص شكلية.
        </p>
        <div class="hero-actions">
          <a class="btn btn-primary" href="{$loginHref}">تسجيل الدخول</a>
          <button class="btn btn-secondary btn-disabled" type="button" aria-disabled="true">التسجيل قريبًا</button>
        </div>
      </section>
    </div>

    <section class="surface section">
      <h2>الخدمات الأساسية داخل RankX SEO</h2>
      <p>كل خدمة مصممة لتدعم قرارًا تجاريًا واضحًا: جذب زيارات أعلى، رفع جودة الصفحة، وتحويل الزائر إلى عميل.</p>
      <div class="grid">
        <article class="card">
          <h3>تحسين وصف المنتجات</h3>
          <ul class="list">
            <li>صياغة وصف احترافي منظم بعناوين وقوائم واضحة وسهل القراءة.</li>
            <li>تحسين متوافق مع نية البحث والكلمات المفتاحية داخل سياق طبيعي.</li>
            <li>إمكانية التعديل اليدوي قبل الحفظ المباشر داخل سلة.</li>
          </ul>
        </article>
        <article class="card">
          <h3>سيو المتجر (Meta Title / Description / Keywords)</h3>
          <ul class="list">
            <li>توليد صياغات قوية لصفحة المتجر الرئيسية وفق طول مناسب لمحركات البحث.</li>
            <li>تحسين النصوص الحالية بدل استبدالها عشوائيًا، مع مراعاة هوية المتجر.</li>
            <li>تحديث فوري من اللوحة إلى إعدادات SEO في سلة.</li>
          </ul>
        </article>
        <article class="card">
          <h3>كاتب ALT للصور</h3>
          <ul class="list">
            <li>إنشاء ALT واضح ودقيق لكل صورة بما يخدم تحسين الظهور في البحث المرئي.</li>
            <li>تحسين صورة واحدة، مجموعة صور، أو جميع صور المنتج بضغطة واحدة.</li>
            <li>تمييز الصور المحسنة وغير المحسنة مع سجل عمليات واضح.</li>
          </ul>
        </article>
        <article class="card">
          <h3>ذكاء الكلمات والمنافسين</h3>
          <ul class="list">
            <li>تحليل الكلمة المفتاحية: حجم البحث، المنافسة، CPC، واقتراحات مرتبطة.</li>
            <li>تحليل الدومين والمنافسين مع مؤشرات ترتيب الكلمات العضوية.</li>
            <li>حفظ نتائج البحث السابقة لتقليل الهدر في النقاط وتسريع العمل.</li>
          </ul>
        </article>
      </div>
    </section>

    <section class="surface section">
      <h2>لماذا هذه المنصة تفرق فعليًا؟</h2>
      <p>الهدف ليس فقط “إنشاء نص”، بل بناء نظام محتوى كامل يساعد متجرك على النمو بشكل مستمر.</p>
      <div class="kpis">
        <div class="kpi">واجهة موحدة<strong>7</strong>أقسام تشغيل</div>
        <div class="kpi">تحسين قابل للقياس<strong>100%</strong>سجل عمليات</div>
        <div class="kpi">تحكم كامل<strong>يدوي + AI</strong>قبل الحفظ</div>
        <div class="kpi">جاهز للنشر<strong>مباشر</strong>داخل سلة</div>
      </div>
    </section>

    <section class="surface section">
      <h2>كيف تبدأ خلال دقائق؟</h2>
      <p>التدفق واضح وبسيط حتى لو فريقك غير تقني.</p>
      <div class="steps">
        <article class="step">
          <h4>دخول اللوحة</h4>
          <p>سجّل الدخول بحسابك ثم اختر القسم المناسب حسب هدفك اليومي.</p>
        </article>
        <article class="step">
          <h4>توليد التحسين</h4>
          <p>استخدم الذكاء الاصطناعي مع تعليمات متجرك للحصول على مخرجات أدق.</p>
        </article>
        <article class="step">
          <h4>مراجعة وتعديل</h4>
          <p>راجع قبل/بعد، عدّل يدويًا إذا رغبت، ثم ثبّت النسخة النهائية.</p>
        </article>
        <article class="step">
          <h4>حفظ داخل سلة</h4>
          <p>احفظ التحديث مباشرة داخل متجرك وتابع أثره من نفس اللوحة.</p>
        </article>
      </div>
    </section>

    <section class="surface section">
      <h2>أسئلة شائعة</h2>
      <div class="faq">
        <details>
          <summary>هل المنصة مناسبة لمتجر صغير أو جديد؟</summary>
          <p>نعم، لأنها تقلل وقت كتابة المحتوى وتمنحك خطوات واضحة للبدء دون تعقيد تقني.</p>
        </details>
        <details>
          <summary>هل أقدر تعديل النص قبل الحفظ؟</summary>
          <p>أكيد. كل مخرجات AI تمر بمرحلة مراجعة كاملة قبل اعتمادها داخل سلة.</p>
        </details>
        <details>
          <summary>هل التحسين يشمل المنتجات فقط؟</summary>
          <p>لا، يشمل أيضًا سيو المتجر، ALT الصور، تحليل الكلمات المفتاحية، وتحليل الدومين والمنافسين.</p>
        </details>
      </div>
      <div class="hero-actions" style="margin-top:18px">
        <a class="btn btn-primary" href="{$loginHref}">ابدأ الآن عبر تسجيل الدخول</a>
        <button class="btn btn-secondary btn-disabled" type="button" aria-disabled="true">التسجيل سيتاح قريبًا</button>
      </div>
    </section>

    <div class="foot">
      <p style="margin:0 0 10px;">
        <a href="{$safeAppUrl}/" style="color:var(--muted);text-decoration:none;margin:0 8px;">الرئيسية</a>
        <a href="{$safeAppUrl}/pricing" style="color:var(--muted);text-decoration:none;margin:0 8px;">الباقات</a>
        <a href="{$safeAppUrl}/about" style="color:var(--muted);text-decoration:none;margin:0 8px;">من نحن</a>
        <a href="{$safeAppUrl}/faq" style="color:var(--muted);text-decoration:none;margin:0 8px;">الأسئلة الشائعة</a>
        <a href="{$safeAppUrl}/privacy" style="color:var(--muted);text-decoration:none;margin:0 8px;">الخصوصية</a>
        <a href="{$safeAppUrl}/terms" style="color:var(--muted);text-decoration:none;margin:0 8px;">الشروط</a>
      </p>
      <p style="margin:0;">Powered by RankX SEO | <a href="mailto:seo@rankxseo.com" style="color:var(--primary-2);">seo@rankxseo.com</a></p>
    </div>
  </div>
</body>
</html>
HTML;

        Response::html($html);
    }
}

