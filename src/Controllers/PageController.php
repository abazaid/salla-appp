<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Config;
use App\Support\Response;
use App\Support\Plans;

final class PageController
{
    public function about(): void
    {
        $appUrl = (string) Config::get('APP_URL', 'http://localhost:8000');
        $safeAppUrl = htmlspecialchars(rtrim($appUrl, '/'), ENT_QUOTES, 'UTF-8');
        $logoSrc = $safeAppUrl . '/assets/rankxseo-logo.svg';

        $html = <<<HTML
<!doctype html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>من نحن | RankX SEO</title>
  <meta name="description" content="تعرف على RankX SEO - منصة متخصصة في تحسين محتوى متاجر سلة باستخدام الذكاء الاصطناعي. نساعد التجار على رفع ترتيب متاجرهم وزيادة التحويل.">
  <link rel="canonical" href="{$safeAppUrl}/about">
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
      --glow-primary:0 0 20px rgba(99, 102, 241, 0.35);
      --shadow:0 22px 60px rgba(15,23,42,.08);
      --shadow-soft:0 12px 28px rgba(15,23,42,.04);
    }
    *{box-sizing:border-box}
    body{margin:0;font-family:"Tajawal","Segoe UI",sans-serif;color:var(--ink);background:var(--bg);min-height:100vh}
    .wrap{width:min(1180px,100% - 28px);margin:22px auto;padding:0 14px 42px}
    .surface{background:var(--surface);border:1px solid var(--border);border-radius:16px;box-shadow:var(--shadow);padding:32px;margin-top:22px}
    .brand{display:flex;align-items:center;gap:14px;margin-bottom:32px}
    .brand img{width:min(240px,60vw);height:auto;display:block;filter:drop-shadow(0 0 20px rgba(99, 102, 241, 0.3))}
    h1{font-size:clamp(28px,4vw,42px);margin:0 0 24px;background:var(--gradient-main);-webkit-background-clip:text;-webkit-text-fill-color:transparent;line-height:1.2}
    h2{font-size:clamp(20px,3vw,28px);margin:32px 0 16px;color:var(--ink)}
    p{line-height:2;font-size:17px;color:#475569;margin:0 0 16px}
    .lead{font-size:19px;color:#1e293b}
    .features{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:20px;margin:24px 0}
    .feature{background:var(--bg);border:1px solid var(--border);border-radius:16px;padding:24px;text-align:center}
    .feature-icon{width:56px;height:56px;background:var(--gradient-main);border-radius:14px;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:24px}
    .feature h3{margin:0 0 10px;font-size:20px}
    .feature p{margin:0;font-size:15px}
    .stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin:32px 0}
    .stat{text-align:center;padding:24px;background:var(--gradient-main);border-radius:16px;color:#fff}
    .stat strong{display:block;font-size:42px;font-weight:800;margin-bottom:8px}
    .stat span{font-size:15px;opacity:0.9}
    .team{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:20px;margin:24px 0}
    .team-member{text-align:center;padding:24px;background:var(--bg);border:1px solid var(--border);border-radius:16px}
    .team-member .avatar{width:80px;height:80px;background:var(--gradient-main);border-radius:50%;margin:0 auto 16px;display:flex;align-items:center;justify-content:center;font-size:32px;font-weight:700;color:#fff}
    .team-member h3{margin:0 0 6px;font-size:18px}
    .team-member p{margin:0;font-size:14px;color:var(--muted)}
    .contact-box{background:var(--bg);border:1px solid var(--border);border-radius:16px;padding:24px;margin-top:24px;text-align:center}
    .contact-box h2{margin-top:0}
    .contact-email{display:inline-flex;align-items:center;gap:10px;background:var(--gradient-main);color:#fff;padding:14px 24px;border-radius:12px;text-decoration:none;font-weight:700;font-size:18px;box-shadow:var(--glow-primary)}
    .footer{padding:24px 0;text-align:center;color:var(--muted);font-size:14px;border-top:1px solid var(--border);margin-top:42px}
    .footer a{color:var(--primary-2);text-decoration:none}
    .footer a:hover{text-decoration:underline}
    @media(max-width:640px){
      .surface{padding:20px;border-radius:12px}
      .stats{grid-template-columns:repeat(2,1fr)}
      .stat{padding:16px}
      .stat strong{font-size:32px}
    }
  </style>
</head>
<body>
  <div class="wrap">
    <div class="surface">
      <div class="brand">
        <img src="{$logoSrc}" alt="RankX SEO">
      </div>

      <h1>من نحن - RankX SEO</h1>
      <p class="lead">
        RankX SEO هي منصة متخصصة صُممت خصيصًا لأصحاب متاجر سلة، نقدم لهم حلولًا ذكية لتحسين المحتوى باستخدام الذكاء الاصطناعي.
      </p>

      <h2>رؤيتنا</h2>
      <p>
        نؤمن أن كل متجر يستحق أن يظهر في نتائج البحث الأولى. لكن كتابة محتوى احترافي متوافق مع SEO تحتاج وقت وجهد كبير. هدفنا هو أتمتة هذه العملية وجعلها في متناول الجميع - من المتجر الصغير إلى المؤسسة الكبيرة.
      </p>

      <h2>ماذا نقدم؟</h2>
      <div class="features">
        <div class="feature">
          <div class="feature-icon">📝</div>
          <h3>تحسين وصف المنتجات</h3>
          <p>صياغة أوصاف احترافية متوافق مع SEO بذكاء اصطناعي متطور</p>
        </div>
        <div class="feature">
          <div class="feature-icon">🔍</div>
          <h3>تحسين ظهور متجرك</h3>
          <p>تحسين Meta Title و Description لرفع ترتيب متجرك في البحث</p>
        </div>
        <div class="feature">
          <div class="feature-icon">🖼️</div>
          <h3>ALT للصور</h3>
          <p>إنشاء نصوص بديلة احترافية للصور لظهور أفضل في البحث المرئي</p>
        </div>
        <div class="feature">
          <div class="feature-icon">📊</div>
          <h3>تحليل الكلمات</h3>
          <p>أدوات متقدمة لتحليل الكلمات المفتاحية والمنافسين</p>
        </div>
      </div>

      <h2>أرقام تتحدث عنا</h2>
      <div class="stats">
        <div class="stat"><strong>+500</strong><span>متجر نشط</span></div>
        <div class="stat"><strong>+50K</strong><span>منتج محسّن</span></div>
        <div class="stat"><strong>98%</strong><span>نسبة رضا العملاء</span></div>
        <div class="stat"><strong>24/7</strong><span>دعم فني</span></div>
      </div>

      <h2>فريقنا</h2>
      <p>فريقنا يضم خبراء في تحسين محركات البحث (SEO) وتطوير البرمجيات والذكاء الاصطناعي، جميعنا نعمل لتحقيق هدف واحد: مساعدتك على نمو متجرك.</p>
      <div class="team">
        <div class="team-member">
          <div class="avatar">R</div>
          <h3>فريق التطوير</h3>
          <p>خبراء في بناء أنظمة ذكية</p>
        </div>
        <div class="team-member">
          <div class="avatar">S</div>
          <h3>فريق SEO</h3>
          <p>متخصصون في تحسين البحث</p>
        </div>
        <div class="team-member">
          <div class="avatar">D</div>
          <h3>فريق التصميم</h3>
          <p>مصممون لواجهات سهلة</p>
        </div>
        <div class="team-member">
          <div class="avatar">S</div>
          <h3>الدعم الفني</h3>
          <p>متاحون لمساعدتك دائمًا</p>
        </div>
      </div>

      <h2>تواصل معنا</h2>
      <div class="contact-box">
        <p>نسعد بتواصلك معنا لأي استفسار أو اقتراح.</p>
        <a class="contact-email" href="mailto:seo@rankxseo.com">
          <span>📧</span> seo@rankxseo.com
        </a>
      </div>
    </div>

    <div class="footer">
      <p>
        <a href="{$safeAppUrl}/">الرئيسية</a> · 
        <a href="{$safeAppUrl}/faq">الأسئلة الشائعة</a> · 
        <a href="{$safeAppUrl}/privacy">سياسة الخصوصية</a> · 
        <a href="{$safeAppUrl}/terms">الشروط والأحكام</a>
      </p>
      <p>© 2024 RankX SEO - جميع الحقوق محفوظة</p>
    </div>
  </div>
</body>
</html>
HTML;

        Response::html($html);
    }

    public function faq(): void
    {
        $appUrl = (string) Config::get('APP_URL', 'http://localhost:8000');
        $safeAppUrl = htmlspecialchars(rtrim($appUrl, '/'), ENT_QUOTES, 'UTF-8');
        $logoSrc = $safeAppUrl . '/assets/rankxseo-logo.svg';
        $loginHref = $safeAppUrl . '/login';

        $html = <<<HTML
<!doctype html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>الأسئلة الشائعة | RankX SEO</title>
  <meta name="description" content="إجابات شاملة على أهم الأسئلة حول RankX SEO - تحسين محتوى متاجر سلة باستخدام الذكاء الاصطناعي.">
  <link rel="canonical" href="{$safeAppUrl}/faq">
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
      --warning:#F59E0B;
      --glow-primary:0 0 20px rgba(99, 102, 241, 0.35);
      --shadow:0 22px 60px rgba(15,23,42,.08);
    }
    *{box-sizing:border-box}
    body{margin:0;font-family:"Tajawal","Segoe UI",sans-serif;color:var(--ink);background:var(--bg);min-height:100vh}
    .wrap{width:min(900px,100% - 28px);margin:22px auto;padding:0 14px 42px}
    .surface{background:var(--surface);border:1px solid var(--border);border-radius:16px;box-shadow:var(--shadow);padding:32px;margin-top:22px}
    .brand{display:flex;align-items:center;gap:14px;margin-bottom:32px}
    .brand img{width:min(200px,50vw);height:auto;display:block;filter:drop-shadow(0 0 20px rgba(99, 102, 241, 0.3))}
    h1{font-size:clamp(26px,4vw,38px);margin:0 0 12px;background:var(--gradient-main);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
    .intro{font-size:18px;color:var(--muted);margin:0 0 32px;line-height:1.8}
    .category{margin-bottom:32px}
    .category-title{display:flex;align-items:center;gap:12px;margin-bottom:16px;padding-bottom:12px;border-bottom:2px solid var(--border)}
    .category-title .icon{width:40px;height:40px;background:var(--gradient-main);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:20px;color:#fff}
    .category-title h2{margin:0;font-size:20px;color:var(--ink)}
    .faq-list{display:flex;flex-direction:column;gap:12px}
    details{background:var(--bg);border:1px solid var(--border);border-radius:14px;padding:0;overflow:hidden;transition:all .3s ease}
    details:hover{border-color:var(--primary-1)}
    details[open]{border-color:var(--primary-2);box-shadow:0 4px 20px rgba(99,102,241,.1)}
    summary{list-style:none;padding:18px 20px;cursor:pointer;font-size:17px;font-weight:700;color:var(--ink);display:flex;align-items:center;justify-content:space-between;gap:12px;transition:background .2s}
    summary::-webkit-details-marker{display:none}
    summary:hover{background:rgba(59,130,246,.05)}
    summary::after{content:"+";font-size:24px;color:var(--primary-2);font-weight:300;transition:transform .3s ease}
    details[open] summary::after{transform:rotate(45deg)}
    .answer{padding:0 20px 20px;font-size:16px;line-height:2;color:#475569;border-top:1px solid var(--border);margin-top:0;padding-top:16px}
    .answer ul{margin:12px 0;padding-right:20px}
    .answer li{margin-bottom:8px}
    .answer strong{color:var(--ink)}
    .cta-box{background:var(--gradient-main);border-radius:16px;padding:32px;text-align:center;color:#fff;margin-top:40px}
    .cta-box h3{font-size:24px;margin:0 0 12px}
    .cta-box p{opacity:0.9;margin:0 0 20px;font-size:16px}
    .cta-box a{display:inline-flex;align-items:center;gap:8px;background:#fff;color:var(--primary-2);padding:14px 28px;border-radius:12px;text-decoration:none;font-weight:700;font-size:16px;box-shadow:0 4px 15px rgba(0,0,0,.1);transition:transform .2s}
    .cta-box a:hover{transform:translateY(-2px)}
    .search-box{position:relative;margin-bottom:32px}
    .search-box input{width:100%;padding:16px 20px 16px 50px;border:2px solid var(--border);border-radius:14px;font-size:16px;font-family:inherit;outline:none;transition:border-color .2s}
    .search-box input:focus{border-color:var(--primary-1);box-shadow:0 0 0 4px rgba(59,130,246,.1)}
    .search-box .icon{position:absolute;right:18px;top:50%;transform:translateY(-50%);font-size:20px}
    .footer{padding:24px 0;text-align:center;color:var(--muted);font-size:14px;border-top:1px solid var(--border);margin-top:42px}
    .footer a{color:var(--primary-2);text-decoration:none}
    .footer a:hover{text-decoration:underline}
    @media(max-width:640px){
      .surface{padding:20px;border-radius:12px}
      summary{font-size:15px;padding:14px 16px}
      .answer{font-size:15px}
      .cta-box{padding:24px}
    }
  </style>
</head>
<body>
  <div class="wrap">
    <div class="surface">
      <div class="brand">
        <img src="{$logoSrc}" alt="RankX SEO">
      </div>

      <h1>الأسئلة الشائعة</h1>
      <p class="intro">إجابات شاملة على أكثر الأسئلة شيوعًا حول RankX SEO وكيفية استخدامها.</p>

      <div class="search-box">
        <span class="icon">🔍</span>
        <input type="text" id="faqSearch" placeholder="ابحث في الأسئلة..." onkeyup="filterFAQs()">
      </div>

      <div class="category" data-category="general">
        <div class="category-title">
          <div class="icon">🏠</div>
          <h2>معلومات عامة</h2>
        </div>
        <div class="faq-list">
          <details>
            <summary>ما هي RankX SEO؟</summary>
            <div class="answer">
              RankX SEO هي منصة متخصصة في تحسين محتوى متاجر سلة باستخدام الذكاء الاصطناعي. نقدم أدوات متكاملة لتحسين وصف المنتجات، Meta Tags، ALT الصور، وتحليل الكلمات المفتاحية والمنافسين - كل ذلك من لوحة واحدة سهلة الاستخدام.
            </div>
          </details>
          <details>
            <summary>لمن صُممت هذه المنصة؟</summary>
            <div class="answer">
              صُممت RankX SEO خصيصًا لأصحاب متاجر سلة (Salla) سواء كانوا:<br>
              <ul>
                <li>تجار يديرون متجرًا واحدًا أو عدة متاجر</li>
                <li>شركات تقدم خدمات SEO لعملائها</li>
                <li>متاجر كبيرة تحتاج لتحسين مئات أو آلاف المنتجات</li>
              </ul>
            </div>
          </details>
          <details>
            <summary>هل أحتاج خبرة تقنية لاستخدام المنصة؟</summary>
            <div class="answer">
              <strong>لا!</strong> صُممت المنصة لتكون سهلة الاستخدام حتى للمبتدئين. لا تحتاج أي خلفية تقنية أو خبرة في SEO. الواجهة عربية بالكامل مع شرح واضح لكل خطوة.
            </div>
          </details>
        </div>
      </div>

      <div class="category" data-category="services">
        <div class="category-title">
          <div class="icon">⚙️</div>
          <h2>الخدمات والميزات</h2>
        </div>
        <div class="faq-list">
          <details>
            <summary>ما الخدمات التي تقدمها المنصة؟</summary>
            <div class="answer">
              نقدم 8 خدمات أساسية:
              <ul>
                <li><strong>تحسين وصف المنتجات:</strong> صياغة أوصاف احترافية متوافق مع SEO</li>
                <li><strong>تحسين SEO المنتج:</strong> Meta Title و Meta Description محسّنة</li>
                <li><strong>تحسين SEO الماركات:</strong> وصف و Meta Tags للماركات التجارية</li>
                <li><strong>ALT للصور:</strong> إنشاء نصوص بديلة احترافية للصور</li>
                <li><strong>تحسين ALT جماعي:</strong> تحسين جميع صور منتج واحد دفعة واحدة</li>
                <li><strong>تحليل الكلمات المفتاحية:</strong> بحث شامل عن أفضل الكلمات لفائدتك</li>
                <li><strong>تحليل سيو الدومين:</strong> تحليل شامل لموقعك ومنافسيك</li>
                <li><strong>سيو المتجر:</strong> تحسين إعدادات SEO العامة للمتجر</li>
              </ul>
            </div>
          </details>
          <details>
            <summary>كيف يعمل تحسين الوصف؟</summary>
            <div class="answer">
              العملية بسيطة:<br>
              <ol style="margin:12px 0;padding-right:24px">
                <li>اختر المنتج الذي تريد تحسينه</li>
                <li>اضغط "تحسين المحتوى"</li>
                <li>سيقوم الذكاء الاصطناعي بتحليل المنتج وإنشاء وصف محسّن</li>
                <li>راجع الوصف المعدل وعدّله يدويًا إذا رغبت</li>
                <li>احفظ التغييرات وسيتم تحديث المنتج في متجرك تلقائيًا</li>
              </ol>
            </div>
          </details>
          <details>
            <summary>هل يمكنني تعديل النص قبل الحفظ؟</summary>
            <div class="answer">
              <strong>نعم!</strong> كل ناتج من الذكاء الاصطناعي قابل للتعديل قبل الحفظ. يمكنك:
              <ul>
                <li>تعديل الوصف مباشرة في المحرر</li>
                <li>إضافة أو حذف أي جزء</li>
                <li>تغيير النبرة بين احترافية، فخامة، بساطة، أو مباشرة</li>
                <li>اختيار لغة الإخراج (عربي أو إنجليزي)</li>
              </ul>
            </div>
          </details>
          <details>
            <summary>ما معنى "تعليمات المتجر"؟</summary>
            <div class="answer">
              هي إرشادات مخصصة تضيفها لمتجرك يخبر بها الذكاء الاصطناعي عن شخصية متجرك وأسلوبه. مثلاً:
              <ul>
                <li>"نحن متجر يبيع منتجات فاخرة بأسعار مناسبة"</li>
                <li>"نركز على الجودة والخدمة"</li>
                <li>"أسلوبنا ودود ومباشر"</li>
              </ul>
              هذه التعليمات تساعد AI على إنتاج محتوى أكثر تماشيًا مع هوية متجرك.
            </div>
          </details>
        </div>
      </div>

      <div class="category" data-category="billing">
        <div class="category-title">
          <div class="icon">💳</div>
          <h2>الاشتراكات والأسعار</h2>
        </div>
        <div class="faq-list">
          <details>
            <summary>كيف أحصل على اشتراك؟</summary>
            <div class="answer">
              عند ربط متجرك الأول مع RankX SEO، ستحصل تلقائيًا على <strong>فترة تجريبية مجانية</strong> لتجربة المنصة. بعدها يمكنك اختيار باقة اشتراك تناسب احتياجاتك.
            </div>
          </details>
          <details>
            <summary>ما الفرق بين الباقات المتوفرة؟</summary>
            <div class="answer">
              نقدم 4 باقات تناسب احتياجات مختلفة:
              <ul>
                <li><strong>🟢 التجربة الاقتصادية (5 ر.س/شهر):</strong> للم تجربة المنصة
                  <br>10 تحسين وصف | 10 تحسين SEO | 10 ALT صور | 5 كلمات مفتاحية | 1 تحليل دومين | 5 تحسين ماركات | 5 تحسين أقسام</li>
                <li><strong>🔵 الخطة الأساسية (29 ر.س/شهر):</strong> للمتاجر الصغيرة
                  <br>80 تحسين وصف | 80 تحسين SEO | 30 ALT صور | 10 كلمات مفتاحية | 3 تحليل دومين | تحسين الماركات والأقسام غير مفعّل (رقّي الاشتراك)</li>
                <li><strong>🟣 الخطة المتقدمة (79 ر.س/شهر):</strong> للمتاجر المتنامية ⭐
                  <br>260 تحسين وصف | 140 تحسين SEO | 260 ALT صور | 40 كلمات مفتاحية | 12 تحليل دومين | 50 تحسين ماركات | 50 تحسين أقسام
                  <br>+ سجل النشاطات | تصدير البيانات | أداء أسرع</li>
                <li><strong>🔴 الخطة الاحترافية (149 ر.س/شهر):</strong> للمتاجر الكبيرة
                  <br>700 تحسين وصف | 700 تحسين SEO | 700 ALT صور | 120 كلمة مفتاحية | 35 تحليل دومين | 150 تحسين ماركات | 100 تحسين أقسام
                  <br>+ دعم أولوي | حدود أعلى | سجل النشاطات | تصدير | أداء أسرع</li>
              </ul>
            </div>
          </details>
          <details>
            <summary>هل أحتاج لشراء رصيد OpenAI separately؟</summary>
            <div class="answer">
              <strong>لا!</strong> التكلفة مشمولة في اشتراكك. لا حاجة لإنشاء حساب OpenAI أو شراء رصيد separately. نحن نتولى كل شيء.
            </div>
          </details>
          <details>
            <summary>ماذا لو استهلكت كل التحسينات المتاحة؟</summary>
            <div class="answer">
              عند انتهاء رصيدك:
              <ul>
                <li>ستظهر لك رسالة تنبيه في لوحة التحكم</li>
                <li>يمكنك ترقية اشتراكك للحصول على رصيد أكبر</li>
                <li>أو الانتظار حتى تجديد الفترة التالية</li>
              </ul>
              <strong>ملاحظة:</strong> كل عملية تحسين تستهلك من رصيدك حسب نوعها.
            </div>
          </details>
          <details>
            <summary>ما معنى كل نوع من التحسينات؟</summary>
            <div class="answer">
              <ul>
                <li><strong>تحسين وصف منتج:</strong> كتابة وصف احترافي متوافق مع SEO</li>
                <li><strong>تحسين SEO منتج:</strong> Meta Title و Meta Description</li>
                <li><strong>تحسين ALT صور:</strong> كتابة نص بديل لكل صورة</li>
                <li><strong>كلمات مفتاحية:</strong> البحث عن أفضل الكلمات المفتاحية</li>
                <li><strong>تحليل سيو الدومين:</strong> تحليل شامل لموقعك</li>
                <li><strong>تحسين SEO ماركة:</strong> وصف و Meta Tags للماركات التجارية</li>
              </ul>
            </div>
          </details>
        </div>
      </div>

      <div class="category" data-category="technical">
        <div class="category-title">
          <div class="icon">🔧</div>
          <h2>التقنية والربط</h2>
        </div>
        <div class="faq-list">
          <details>
            <summary>كيف أربط متجري سلة بالمنصة؟</summary>
            <div class="answer">
              عملية الربط بسيطة جدًا:<br>
              <ol style="margin:12px 0;padding-right:24px">
                <li>سجّل الدخول إلى حسابك في RankX SEO</li>
                <li>اضغط على "ربط متجر جديد"</li>
                <li>أدخل رابط متجرك في سلة</li>
                <li>سيتم توجيهك لتأكيد الربط من داخل سلة</li>
                <li>بعد التأكيد، سيتم ربط المتجر تلقائيًا!</li>
              </ol>
            </div>
          </details>
          <details>
            <summary>هل أحتاج صلاحيات معينة في سلة؟</summary>
            <div class="answer">
              <strong>نعم.</strong> تحتاج أن تكون:<br>
              <ul>
                <li>مالك المتجر أو لديه صلاحيات إدارية</li>
                <li>أن تكون قادرًا على تثبيت التطبيقات من متجر سلة</li>
              </ul>
            </div>
          </details>
          <details>
            <summary>هل التغييرات تُحدث مباشرة في سلة؟</summary>
            <div class="answer">
              <strong>نعم!</strong> أي تحسين تحفظه في RankX SEO يتم تحديثه مباشرة في متجرك على سلة. لا تحتاج لنسخ ولصق أو أي عملية يدوية.
            </div>
          </details>
          <details>
            <summary>هل يمكنني فك ربط المتجر؟</summary>
            <div class="answer">
              <strong>نعم.</strong> يمكنك إلغاء تثبيت التطبيق من إعدادات سلة في أي وقت. سيتم الاحتفاظ بنسخة من بياناتك، لكن التحديثات لن تتم بعد ذلك.
            </div>
          </details>
        </div>
      </div>

      <div class="category" data-category="results">
        <div class="category-title">
          <div class="icon">📈</div>
          <h2>النتائج والمتابعة</h2>
        </div>
        <div class="faq-list">
          <details>
            <summary>متى تظهر نتائج التحسين في البحث؟</summary>
            <div class="answer">
              يعتمد ذلك على عدة عوامل:
              <ul>
                <li><strong>Google:</strong> عادةً من أسبوع إلى 4 أسابيع</li>
                <li><strong>تحديثات المنتجات:</strong> تظهر أسرع من تحديثات المتجر العام</li>
                <li><strong>المنافسة:</strong> في مجالات تنافسية قد يستغرق الأمر وقتًا أطول</li>
              </ul>
              نصيحتنا: تحلى بالصبر واستمر في تحسين المحتوى بشكل منتظم.
            </div>
          </details>
          <details>
            <summary>هل يمكنني التعديل يدويًا بدون AI؟</summary>
            <div class="answer">
              <strong>نعم!</strong> نقدم ميزة التحرير اليدوي التي تتيح لك:
              <ul>
                <li>رؤية الوصف الحالي والوصف الجديد</li>
                <li>تحرير الوصف يدويًا قبل الحفظ</li>
                <li>تعديل Meta Title و Meta Description</li>
                <li>حفظ التغييرات مباشرة في متجرك</li>
              </ul>
              هذه الميزة لا تستهلك من رصيدك لأنها لا تستخدم الذكاء الاصطناعي.
            </div>
          </details>
          <details>
            <summary>كيف أتابع أداء التحسينات؟</summary>
            <div class="answer">
              توفر لك RankX SEO سجل عمليات كامل يمكنك من:
              <ul>
                <li>معرفة المنتجات التي تم تحسينها وتاريخ التحسين</li>
                <li>متابعة استهلاك كل نوع من التحسينات</li>
                <li>رؤية تفاصيل الاستهلاك حسب العملية</li>
                <li>رؤية تكلفة كل عملية AI</li>
              </ul>
            </div>
          </details>
          <details>
            <summary>هل يضمن تحسين المحتوى ظهور أفضل؟</summary>
            <div class="answer">
              تحسين المحتوى <strong>عامل مهم</strong> لكنه ليس العامل الوحيد. تعتمد نتائج البحث على:
              <ul>
                <li>جودة المحتوى وتناسله مع البحث</li>
                <li>سلطة الدومين والمنتجات</li>
                <li>الباك لينكس (روابط خارجية)</li>
                <li>تجربة المستخدم في الموقع</li>
                <li>المنافسة في المجال</li>
              </ul>
              RankX SEO تضمن لك <strong>أفضل محتوى ممكن</strong>، لكن النتائج النهائية تعتمد على عوامل خارجية.
            </div>
          </details>
        </div>
      </div>

      <div class="category" data-category="support">
        <div class="category-title">
          <div class="icon">💬</div>
          <h2>الدعم الفني</h2>
        </div>
        <div class="faq-list">
          <details>
            <summary>كيف أتواصل مع الدعم الفني؟</summary>
            <div class="answer">
              يمكنك التواصل معنا عبر:<br>
              <ul>
                <li><strong>البريد:</strong> seo@rankxseo.com</li>
                <li><strong>صندوق الرسائل:</strong> داخل لوحة التحكم</li>
              </ul>
              نسعى للرد خلال 24 ساعة عمل.
            </div>
          </details>
          <details>
            <summary>هل تقدمون خدمة تدريب على المنصة؟</summary>
            <div class="answer">
              <strong>نعم!</strong> نقدم تدريبًا مجانيًا للمشتركين. كما نوفر:
              <ul>
                <li>وثائق تفصيلية لكل ميزة</li>
                <li>فيديوهات تعليمية</li>
                <li>دعم مباشر عند الحاجة</li>
              </ul>
            </div>
          </details>
        </div>
      </div>

      <div class="cta-box">
        <h3>لم تجد إجابة سؤالك؟</h3>
        <p>فريقنا جاهز لمساعدتك والإجابة على جميع استفساراتك</p>
        <a href="mailto:seo@rankxseo.com">
          <span>📧</span> تواصل معنا الآن
        </a>
      </div>
    </div>

    <div class="footer">
      <p>
        <a href="{$safeAppUrl}/">الرئيسية</a> · 
        <a href="{$safeAppUrl}/about">من نحن</a> · 
        <a href="{$safeAppUrl}/privacy">سياسة الخصوصية</a> · 
        <a href="{$safeAppUrl}/terms">الشروط والأحكام</a>
      </p>
      <p>© 2024 RankX SEO - جميع الحقوق محفوظة</p>
    </div>
  </div>

  <script>
    function filterFAQs() {
      const search = document.getElementById('faqSearch').value.toLowerCase();
      const categories = document.querySelectorAll('.category');
      
      categories.forEach(category => {
        const details = category.querySelectorAll('details');
        let hasMatch = false;
        
        details.forEach(detail => {
          const summary = detail.querySelector('summary').textContent.toLowerCase();
          const answer = detail.querySelector('.answer').textContent.toLowerCase();
          
          if (summary.includes(search) || answer.includes(search)) {
            detail.style.display = '';
            hasMatch = true;
          } else {
            detail.style.display = 'none';
          }
        });
        
        category.style.display = hasMatch ? '' : 'none';
      });
    }
  </script>
</body>
</html>
HTML;

        Response::html($html);
    }

    public function privacy(): void
    {
        $appUrl = (string) Config::get('APP_URL', 'http://localhost:8000');
        $safeAppUrl = htmlspecialchars(rtrim($appUrl, '/'), ENT_QUOTES, 'UTF-8');
        $logoSrc = $safeAppUrl . '/assets/rankxseo-logo.svg';

        $html = <<<HTML
<!doctype html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>سياسة الخصوصية | RankX SEO</title>
  <meta name="description" content="سياسة الخصوصية الخاصة بمنصة RankX SEO - كيف نحمي بياناتك ونستخدمها.">
  <link rel="canonical" href="{$safeAppUrl}/privacy">
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
      --glow-primary:0 0 20px rgba(99, 102, 241, 0.35);
      --shadow:0 22px 60px rgba(15,23,42,.08);
    }
    *{box-sizing:border-box}
    body{margin:0;font-family:"Tajawal","Segoe UI",sans-serif;color:var(--ink);background:var(--bg);min-height:100vh}
    .wrap{width:min(900px,100% - 28px);margin:22px auto;padding:0 14px 42px}
    .surface{background:var(--surface);border:1px solid var(--border);border-radius:16px;box-shadow:var(--shadow);padding:32px;margin-top:22px}
    .brand{display:flex;align-items:center;gap:14px;margin-bottom:32px}
    .brand img{width:min(200px,50vw);height:auto;display:block;filter:drop-shadow(0 0 20px rgba(99, 102, 241, 0.3))}
    h1{font-size:clamp(24px,4vw,36px);margin:0 0 24px;background:var(--gradient-main);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
    h2{font-size:clamp(18px,3vw,24px);margin:32px 0 14px;color:var(--ink);padding-top:16px;border-top:1px solid var(--border)}
    h2:first-of-type{border-top:none;padding-top:0}
    p{line-height:2;font-size:16px;color:#475569;margin:0 0 14px}
    ul,ol{margin:12px 0;padding-right:24px;color:#475569;line-height:2}
    li{margin-bottom:8px}
    .updated{background:#EEF2FF;color:var(--primary-2);padding:12px 16px;border-radius:10px;font-size:14px;margin-bottom:24px;display:inline-block}
    .footer{padding:24px 0;text-align:center;color:var(--muted);font-size:14px;border-top:1px solid var(--border);margin-top:42px}
    .footer a{color:var(--primary-2);text-decoration:none}
    .footer a:hover{text-decoration:underline}
    @media(max-width:640px){
      .surface{padding:20px;border-radius:12px}
    }
  </style>
</head>
<body>
  <div class="wrap">
    <div class="surface">
      <div class="brand">
        <img src="{$logoSrc}" alt="RankX SEO">
      </div>

      <h1>سياسة الخصوصية</h1>
      <span class="updated">آخر تحديث: يناير 2024</span>

      <p>نحن في RankX SEO ("نحن"، "لنا"، أو "المنصة") نقدر خصوصيتك ونلتزم بحماية بياناتك الشخصية. توضح هذه السياسة كيف نجمع ونستخدم ونحمي معلوماتك.</p>

      <h2>المعلومات التي نجمعها</h2>
      <p>نجمع فقط المعلومات الضرورية لتقديم خدماتنا:</p>
      <ul>
        <li><strong>معلومات المتجر:</strong> رابط متجرك واسمه (من سلة)</li>
        <li><strong>معلومات المنتجات:</strong> أسماء وأوصاف ومنتجات المنتجات التي تختار تحسينها</li>
        <li><strong>معلومات الحساب:</strong> بريدك الإلكتروني (للتواصل وإرسال الإشعارات)</li>
        <li><strong>بيانات الاستخدام:</strong> سجلات التحسينات والأرصدة المستخدمة</li>
      </ul>

      <h2>كيف نستخدم معلوماتك</h2>
      <p>نستخدم معلوماتك للأغراض التالية فقط:</p>
      <ul>
        <li>تقديم خدمات تحسين المحتوى عبر الذكاء الاصطناعي</li>
        <li>إدارة اشتراكاتك وتتبع استهلاكك</li>
        <li>إرسال إشعارات مهمة (انتهاء الاشتراك، تحديثات، إلخ)</li>
        <li>تحسين منصتنا وخدماتنا</li>
        <li>الامتثال للمتطلبات القانونية</li>
      </ul>

      <h2>مشاركة البيانات</h2>
      <p><strong>لا نبيع بياناتك أبدًا.</strong> لا نشارك معلوماتك مع أطراف ثالثة إلا في الحالات التالية:</p>
      <ul>
        <li><strong>مع سلة:</strong> لتحديث المنتجات التي تحسّنها في متجرك</li>
        <li><strong>مع OpenAI:</strong> لمعالجة النصوص عبر الذكاء الاصطناعي (بموجب شروطهم)</li>
        <li><strong>عند الطلب القانوني:</strong> إذا طلب ذلك قانونًا أو حكوميًا</li>
      </ul>

      <h2>حماية البيانات</h2>
      <p>نتخذ إجراءات أمان صارمة لحماية بياناتك:</p>
      <ul>
        <li>تشفير البيانات أثناء النقل والتخزين</li>
        <li>وصول محدود للموظفين المعتمدين</li>
        <li>مراجعات أمان دورية</li>
        <li>نسخ احتياطية منتظمة</li>
      </ul>

      <h2>حقوقك</h2>
      <p>لديك الحقوق التالية:</p>
      <ul>
        <li><strong>الوصول:</strong> طلب نسخة من بياناتك</li>
        <li><strong>التصحيح:</strong> طلب تعديل أي بيانات غير دقيقة</li>
        <li><strong>الحذف:</strong> طلب حذف بياناتك (مع مراعاة المتطلبات القانونية)</li>
        <li><strong>الاعتراض:</strong> الاعتراض على معالجة معينة لبياناتك</li>
      </ul>

      <h2>التخزين والحفظ</h2>
      <p>نحتفظ ببياناتك:</p>
      <ul>
        <li>طالما حسابك نشط</li>
        <li>لمدة سنة بعد إلغاء الاشتراك (للامتثال القانوني)</li>
        <li>سجلات الاستخدام لمدة لا تقل عن المطلوب قانونيًا</li>
      </ul>

      <h2>ملفات تعريف الارتباط (Cookies)</h2>
      <p>نستخدم ملفات تعريف الارتباط لـ:</p>
      <ul>
        <li>الحفاظ على جلسة تسجيل الدخول</li>
        <li>تذكر تفضيلاتك</li>
        <li>تحليل استخدام المنصة</li>
      </ul>

      <h2>التغييرات على السياسة</h2>
      <p>قد نحدث هذه السياسة من حين لآخر. سنعلمك بأي تغييرات جوهرية عبر:</p>
      <ul>
        <li>إشعار في لوحة التحكم</li>
        <li>بريد إلكتروني</li>
      </ul>

      <h2>اتصل بنا</h2>
      <p>لأي استفسار حول سياسة الخصوصية:</p>
      <p><strong>البريد الإلكتروني:</strong> seo@rankxseo.com</p>
    </div>

    <div class="footer">
      <p>
        <a href="{$safeAppUrl}/">الرئيسية</a> · 
        <a href="{$safeAppUrl}/about">من نحن</a> · 
        <a href="{$safeAppUrl}/faq">الأسئلة الشائعة</a> · 
        <a href="{$safeAppUrl}/terms">الشروط والأحكام</a>
      </p>
      <p>© 2024 RankX SEO - جميع الحقوق محفوظة</p>
    </div>
  </div>
</body>
</html>
HTML;

        Response::html($html);
    }

    public function terms(): void
    {
        $appUrl = (string) Config::get('APP_URL', 'http://localhost:8000');
        $safeAppUrl = htmlspecialchars(rtrim($appUrl, '/'), ENT_QUOTES, 'UTF-8');
        $logoSrc = $safeAppUrl . '/assets/rankxseo-logo.svg';

        $html = <<<HTML
<!doctype html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>الشروط والأحكام | RankX SEO</title>
  <meta name="description" content="الشروط والأحكام الخاصة باستخدام منصة RankX SEO.">
  <link rel="canonical" href="{$safeAppUrl}/terms">
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
      --danger:#EF4444;
      --glow-primary:0 0 20px rgba(99, 102, 241, 0.35);
      --shadow:0 22px 60px rgba(15,23,42,.08);
    }
    *{box-sizing:border-box}
    body{margin:0;font-family:"Tajawal","Segoe UI",sans-serif;color:var(--ink);background:var(--bg);min-height:100vh}
    .wrap{width:min(900px,100% - 28px);margin:22px auto;padding:0 14px 42px}
    .surface{background:var(--surface);border:1px solid var(--border);border-radius:16px;box-shadow:var(--shadow);padding:32px;margin-top:22px}
    .brand{display:flex;align-items:center;gap:14px;margin-bottom:32px}
    .brand img{width:min(200px,50vw);height:auto;display:block;filter:drop-shadow(0 0 20px rgba(99, 102, 241, 0.3))}
    h1{font-size:clamp(24px,4vw,36px);margin:0 0 24px;background:var(--gradient-main);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
    h2{font-size:clamp(18px,3vw,24px);margin:32px 0 14px;color:var(--ink);padding-top:16px;border-top:1px solid var(--border)}
    h2:first-of-type{border-top:none;padding-top:0}
    p{line-height:2;font-size:16px;color:#475569;margin:0 0 14px}
    ul,ol{margin:12px 0;padding-right:24px;color:#475569;line-height:2}
    li{margin-bottom:8px}
    .updated{background:#EEF2FF;color:var(--primary-2);padding:12px 16px;border-radius:10px;font-size:14px;margin-bottom:24px;display:inline-block}
    .highlight{background:#FEE2E2;border:1px solid #FECACA;border-radius:10px;padding:16px;margin:16px 0;font-size:15px}
    .highlight strong{color:var(--danger)}
    .footer{padding:24px 0;text-align:center;color:var(--muted);font-size:14px;border-top:1px solid var(--border);margin-top:42px}
    .footer a{color:var(--primary-2);text-decoration:none}
    .footer a:hover{text-decoration:underline}
    @media(max-width:640px){
      .surface{padding:20px;border-radius:12px}
    }
  </style>
</head>
<body>
  <div class="wrap">
    <div class="surface">
      <div class="brand">
        <img src="{$logoSrc}" alt="RankX SEO">
      </div>

      <h1>الشروط والأحكام</h1>
      <span class="updated">آخر تحديث: يناير 2024</span>

      <p>مرحبًا بك في RankX SEO! باستخدامك لمنصتنا، فأنت توافق على الشروط والأحكام التالية. يرجى قراءتها بعناية.</p>

      <h2>1. قبول الشروط</h2>
      <p>بالمتابعة في استخدام RankX SEO، فأنت:</p>
      <ul>
        <li>تؤكد أنك بلغت سن الرشد قانونيًا</li>
        <li>تملك صلاحية الدخول لمتجرك على سلة</li>
        <li>توافق على الالتزام بهذه الشروط</li>
      </ul>

      <h2>2. وصف الخدمة</h2>
      <p>RankX SEO توفر:</p>
      <ul>
        <li>أدوات تحسين محتوى المنتجات باستخدام الذكاء الاصطناعي</li>
        <li>تحسين Meta Tags و ALT للصور</li>
        <li>أدوات تحليل الكلمات المفتاحية والمنافسين</li>
        <li>إدارة الاشتراكات والمستخدمين</li>
      </ul>

      <h2>3. الاشتراك والدفع</h2>
      <ul>
        <li>تحدد الباقات والأسعار من قبلنا وقد تتغير</li>
        <li>الاشتراك يتجدد تلقائيًا ما لم يُلغَ</li>
        <li>يمكن إلغاء الاشتراك في أي وقت من لوحة التحكم</li>
        <li>لا يوجد استرداد للأشهر المستهلكة</li>
      </ul>

      <h2>4. الاستخدام المسموح</h2>
      <p>يُسمح لك:</p>
      <ul>
        <li>استخدام المنصة لتحسين متاجرك الخاصة</li>
        <li>الوصول للمنصة عبر حسابك فقط</li>
        <li>مشاركة النتائج مع فريقك</li>
      </ul>

      <h2>5. الاستخدام المحظور</h2>
      <div class="highlight">
        <strong>ممنوع:</strong>
        <ul style="margin:8px 0 0">
          <li>استخدام المنصة لأغراض غير مشروعة</li>
          <li>محاولة اختراق أو إيقاف الخدمة</li>
          <li>إعادة بيع الخدمة دون إذن</li>
          <li>نشر محتوى مسيء أو مخالف</li>
          <li>استخدام خدماتنا لتوليد محتوى ضار أو مضلل</li>
        </ul>
      </div>

      <h2>6. حقوق الملكية الفكرية</h2>
      <p>نحتفظ بـ:</p>
      <ul>
        <li>جميع حقوق المنصة والبرنامج</li>
        <li>العلامات التجارية والشعارات</li>
        <li>أي محتوى أو كود مقدم من جانبنا</li>
      </ul>
      <p>أنت تحتفظ بـ:</p>
      <ul>
        <li>حقوق المحتوى الخاص بمتجرك</li>
        <li>حقوق أوصاف منتجاتك</li>
      </ul>

      <h2>7. حدود المسؤولية</h2>
      <p>RankX SEO غير مسؤولة عن:</p>
      <ul>
        <li>النتائج النهائية لتحسينات البحث (تعتمد على عوامل خارجية)</li>
        <li>أي خسارة ناتجة عن استخدام المنصة</li>
        <li>انقطاع الخدمة المؤقت</li>
        <li>تغييرات في سياسات سلة أو Google</li>
      </ul>

      <h2>8. إنهاء الخدمة</h2>
      <p>نحتفظ بالحق في:</p>
      <ul>
        <li>إنهاء أي حساب يخرق الشروط</li>
        <li>إيقاف المنصة مؤقتًا للصيانة</li>
        <li>تعديل أو إيقاف أي ميزة</li>
      </ul>

      <h2>9. إخلاء المسؤولية</h2>
      <p>الخدمات تُقدم "كما هي". لا نقدم أي ضمانات:</p>
      <ul>
        <li>بأن النتائج ستكون مثالية</li>
        <li>بالتوفر المستمر للخدمة</li>
        <li>خلو الخدمة من الأخطاء</li>
      </ul>

      <h2>10. التعديلات</h2>
      <p>نحتفظ بالحق في تعديل هذه الشروط. سيتم:</p>
      <ul>
        <li>إشعارك بأي تغييرات جوهرية</li>
        <li>نشر الشروط المحدثة على الموقع</li>
        <li>استمرارك في الاستخدام يعني قبول الشروط الجديدة</li>
      </ul>

      <h2>11. القانون الحاكم</h2>
      <p>تخضع هذه الشروط لقوانين المملكة العربية السعودية، ويتم حل أي نزاع أمام محاكمها.</p>

      <h2>12. التواصل</h2>
      <p>لأي استفسار:</p>
      <p><strong>البريد:</strong> seo@rankxseo.com</p>
    </div>

    <div class="footer">
      <p>
        <a href="{$safeAppUrl}/">الرئيسية</a> · 
        <a href="{$safeAppUrl}/about">من نحن</a> · 
        <a href="{$safeAppUrl}/faq">الأسئلة الشائعة</a> · 
        <a href="{$safeAppUrl}/privacy">سياسة الخصوصية</a>
      </p>
      <p>© 2024 RankX SEO - جميع الحقوق محفوظة</p>
    </div>
  </div>
</body>
</html>
HTML;

        Response::html($html);
    }

    public function pricing(): void
    {
        $appUrl = (string) Config::get('APP_URL', 'http://localhost:8000');
        $safeAppUrl = htmlspecialchars(rtrim($appUrl, '/'), ENT_QUOTES, 'UTF-8');
        $logoSrc = $safeAppUrl . '/assets/rankxseo-logo.svg';
        $loginHref = $safeAppUrl . '/login';

        $plans = Plans::all();
        $plansHtml = '';

        foreach ($plans as $plan) {
            $isFeatured = $plan['is_featured'];
            $featuredClass = $isFeatured ? ' featured' : '';
            $featuredBadge = $isFeatured ? '<span class="featured-badge">⭐ الأكثر شعبية</span>' : '';
            
            $colorMap = [
                'green' => ['bg' => '#D1FAE5', 'text' => '#059669', 'border' => '#A7F3D0'],
                'blue' => ['bg' => '#DBEAFE', 'text' => '#2563EB', 'border' => '#BFDBFE'],
                'purple' => ['bg' => '#EDE9FE', 'text' => '#7C3AED', 'border' => '#DDD6FE'],
                'red' => ['bg' => '#FEE2E2', 'text' => '#DC2626', 'border' => '#FECACA'],
            ];
            $colors = $colorMap[$plan['color']] ?? $colorMap['blue'];

            $quotasHtml = '';
            foreach ($plan['quotas'] as $key => $value) {
                if ($value === 0 && in_array($key, ['brand_seo', 'category_seo'], true)) {
                    $quotasHtml .= '<li>غير مفعلة - رقّي الاشتراك</li>';
                    continue;
                }
                $quotasHtml .= '<li>' . $value . ' ' . Plans::quotaLabel($key) . '</li>';
            }

            $extrasHtml = '';
            if (!empty($plan['extras'])) {
                $extrasLabels = [
                    'activity_logs' => 'سجل العمليات والتصدير',
                    'export' => 'تصدير البيانات',
                    'faster_performance' => 'أداء أسرع ونتائج أفضل',
                    'priority_support' => 'أولوية في الدعم',
                    'higher_bulk_limits' => 'تنفيذ جماعي بحدود أعلى',
                ];
                foreach ($plan['extras'] as $extra => $enabled) {
                    if ($enabled && isset($extrasLabels[$extra])) {
                        $extrasHtml .= '<li class="extra-feature">' . $extrasLabels[$extra] . '</li>';
                    }
                }
            }

            $plansHtml .= <<<HTML
        <div class="plan-card{$featuredClass}" style="--plan-bg:{$colors['bg']};--plan-text:{$colors['text']};--plan-border:{$colors['border']};">
          {$featuredBadge}
          <div class="plan-header">
            <span class="plan-icon">{$plan['icon']}</span>
            <h3 class="plan-name">{$plan['name_ar']}</h3>
            <p class="plan-description">{$plan['description_ar']}</p>
          </div>
          <div class="plan-price">
            <span class="price-number">{$plan['price_sar']}</span>
            <span class="price-currency">ر.س / شهر</span>
          </div>
          <p class="price-usd">\${$plan['price_usd']} USD / month</p>
          <ul class="plan-features">
            {$quotasHtml}
            {$extrasHtml}
          </ul>
          <a href="{$loginHref}" class="plan-cta">ابدأ الآن</a>
        </div>
HTML;
        }

        $html = <<<HTML
<!doctype html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>الباقات والأسعار | RankX SEO</title>
  <meta name="description" content="اختر الباقة المناسبة لمتجرك - باقات مرنة تبدأ من 5 ر.س فقط لتحسين محتوى متجرك وزيادة المبيعات.">
  <link rel="canonical" href="{$safeAppUrl}/pricing">
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
      --glow-primary:0 0 20px rgba(99, 102, 241, 0.35);
      --shadow:0 22px 60px rgba(15,23,42,.08);
    }
    *{box-sizing:border-box}
    body{margin:0;font-family:"Tajawal","Segoe UI",sans-serif;color:var(--ink);background:var(--bg);min-height:100vh}
    .wrap{width:min(1280px,100% - 28px);margin:22px auto;padding:0 14px 42px}
    .surface{background:var(--surface);border:1px solid var(--border);border-radius:16px;box-shadow:var(--shadow);padding:32px;margin-top:22px}
    .brand{display:flex;align-items:center;gap:14px;margin-bottom:32px}
    .brand img{width:min(200px,50vw);height:auto;display:block;filter:drop-shadow(0 0 20px rgba(99, 102, 241, 0.3))}
    .hero{text-align:center;margin-bottom:48px}
    h1{font-size:clamp(28px,4vw,42px);margin:0 0 16px;background:var(--gradient-main);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
    .subtitle{font-size:20px;color:var(--muted);margin:0;line-height:1.8}
    .plans-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:24px;margin-top:32px}
    .plan-card{background:var(--surface);border:2px solid var(--border);border-radius:20px;padding:28px;text-align:center;position:relative;transition:transform .3s,box-shadow .3s}
    .plan-card:hover{transform:translateY(-4px);box-shadow:0 20px 40px rgba(15,23,42,.1)}
    .plan-card.featured{border-color:var(--primary-2);box-shadow:0 0 30px rgba(99,102,241,.2)}
    .featured-badge{position:absolute;top:-14px;right:50%;transform:translateX(50%);background:var(--gradient-main);color:#fff;padding:6px 16px;border-radius:999px;font-size:13px;font-weight:700}
    .plan-icon{font-size:48px;display:block;margin-bottom:12px}
    .plan-header{margin-bottom:20px}
    .plan-name{font-size:24px;font-weight:800;margin:0 0 8px;color:var(--ink)}
    .plan-description{font-size:15px;color:var(--muted);margin:0;line-height:1.6}
    .plan-price{margin:24px 0 4px}
    .price-number{font-size:48px;font-weight:900;color:var(--primary-2)}
    .price-currency{font-size:16px;color:var(--muted);margin-right:4px}
    .price-usd{font-size:14px;color:var(--muted);margin:0 0 24px}
    .plan-features{list-style:none;padding:0;margin:0 0 24px;text-align:right}
    .plan-features li{padding:10px 0;border-bottom:1px solid var(--border);font-size:15px;color:#475569}
    .plan-features li:last-child{border-bottom:none}
    .plan-features li::before{content:"✓";color:var(--success);margin-left:8px;font-weight:700}
    .extra-feature{color:var(--primary-2)!important;font-weight:600}
    .plan-cta{display:inline-block;width:100%;padding:14px 24px;background:var(--gradient-main);color:#fff;border-radius:12px;text-decoration:none;font-weight:700;font-size:16px;box-shadow:var(--glow-primary);transition:transform .2s,box-shadow .2s}
    .plan-cta:hover{transform:translateY(-2px);box-shadow:0 0 35px rgba(99,102,241,.4)}
    .plan-card:not(.featured) .plan-cta{background:#F1F5F9;color:var(--ink);box-shadow:none}
    .plan-card:not(.featured) .plan-cta:hover{background:#E2E8F0;box-shadow:none}
    .compare-section{margin-top:48px;padding-top:48px;border-top:1px solid var(--border)}
    .compare-section h2{text-align:center;font-size:28px;margin:0 0 32px}
    .compare-table{width:100%;border-collapse:collapse;overflow:hidden;border-radius:16px;border:1px solid var(--border)}
    .compare-table th,.compare-table td{padding:14px 16px;text-align:center;border-bottom:1px solid var(--border)}
    .compare-table th{background:#EEF2FF;font-weight:700;font-size:14px}
    .compare-table th:first-child,.compare-table td:first-child{text-align:right}
    .compare-table tr:last-child td{border-bottom:none}
    .compare-table .check{color:var(--success);font-weight:700;font-size:18px}
    .compare-table .cross{color:#DC2626;font-size:16px}
    .compare-table .plan-highlight{background:rgba(99,102,241,.05)}
    .cta-box{background:var(--gradient-main);border-radius:16px;padding:40px;text-align:center;color:#fff;margin-top:48px}
    .cta-box h3{font-size:28px;margin:0 0 12px}
    .cta-box p{opacity:0.9;margin:0 0 24px;font-size:18px}
    .cta-box a{display:inline-block;background:#fff;color:var(--primary-2);padding:14px 32px;border-radius:12px;text-decoration:none;font-weight:700;font-size:18px;box-shadow:0 4px 15px rgba(0,0,0,.1)}
    .footer{padding:24px 0;text-align:center;color:var(--muted);font-size:14px;border-top:1px solid var(--border);margin-top:42px}
    .footer a{color:var(--primary-2);text-decoration:none}
    .footer a:hover{text-decoration:underline}
    @media(max-width:768px){
      .plans-grid{grid-template-columns:1fr}
      .plan-card{max-width:400px;margin:0 auto}
      .compare-table{font-size:13px}
      .compare-table th,.compare-table td{padding:10px 8px}
    }
  </style>
</head>
<body>
  <div class="wrap">
    <div class="surface">
      <div class="brand">
        <img src="{$logoSrc}" alt="RankX SEO">
      </div>

      <div class="hero">
        <h1>باقات وأسعار RankX SEO</h1>
        <p class="subtitle">اختر الباقة المناسبة لمتجرك وابدأ في تحسين محتواك اليوم</p>
      </div>

      <div class="plans-grid">
        {$plansHtml}
      </div>

      <div class="compare-section">
        <h2>مقارنة الباقات</h2>
        <table class="compare-table">
          <thead>
            <tr>
              <th>الميزة</th>
              <th>تجربة اقتصادية</th>
              <th>الأساسية</th>
              <th class="plan-highlight">المتقدمة ⭐</th>
              <th>الاحترافية</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>تحسين وصف المنتج</td>
              <td>10</td>
              <td>80</td>
              <td class="plan-highlight">260</td>
              <td>700</td>
            </tr>
            <tr>
              <td>تحسين SEO المنتج</td>
              <td>10</td>
              <td>80</td>
              <td class="plan-highlight">140</td>
              <td>700</td>
            </tr>
            <tr>
              <td>تحسين ALT الصور</td>
              <td>10</td>
              <td>30</td>
              <td class="plan-highlight">260</td>
              <td>700</td>
            </tr>
            <tr>
              <td>عمليات الكلمات المفتاحية</td>
              <td>5</td>
              <td>10</td>
              <td class="plan-highlight">40</td>
              <td>120</td>
            </tr>
            <tr>
              <td>تحليل سيو الدومين</td>
              <td>1</td>
              <td>3</td>
              <td class="plan-highlight">12</td>
              <td>35</td>
            </tr>
            <tr>
              <td>تحسين SEO الماركات</td>
              <td>5</td>
              <td>غير مفعلة (رقّي)</td>
              <td class="plan-highlight">50</td>
              <td>150</td>
            </tr>
            <tr>
              <td>تحسين SEO الأقسام</td>
              <td>5</td>
              <td>غير مفعلة (رقّي)</td>
              <td class="plan-highlight">50</td>
              <td>100</td>
            </tr>
            <tr>
              <td>سجل العمليات</td>
              <td class="cross">—</td>
              <td class="cross">—</td>
              <td class="plan-highlight check">✓</td>
              <td class="check">✓</td>
            </tr>
            <tr>
              <td>أولوية الدعم</td>
              <td class="cross">—</td>
              <td class="cross">—</td>
              <td class="plan-highlight cross">—</td>
              <td class="check">✓</td>
            </tr>
            <tr>
              <td>حدود تنفيذ أعلى</td>
              <td class="cross">—</td>
              <td class="cross">—</td>
              <td class="plan-highlight cross">—</td>
              <td class="check">✓</td>
            </tr>
            <tr>
              <td><strong>السعر / شهر</strong></td>
              <td><strong>5 ر.س</strong></td>
              <td><strong>29 ر.س</strong></td>
              <td class="plan-highlight"><strong>79 ر.س</strong></td>
              <td><strong>149 ر.س</strong></td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="cta-box">
        <h3>هل تحتاج مساعدة في اختيار الباقة؟</h3>
        <p>فريقنا جاهز لمساعدتك واختيار الأنسب لمتجرك</p>
        <a href="mailto:seo@rankxseo.com">تواصل معنا</a>
      </div>
    </div>

    <div class="footer">
      <p>
        <a href="{$safeAppUrl}/">الرئيسية</a> · 
        <a href="{$safeAppUrl}/about">من نحن</a> · 
        <a href="{$safeAppUrl}/faq">الأسئلة الشائعة</a> · 
        <a href="{$safeAppUrl}/privacy">الخصوصية</a> · 
        <a href="{$safeAppUrl}/terms">الشروط</a>
      </p>
      <p>© 2024 RankX SEO - جميع الحقوق محفوظة</p>
    </div>
  </div>
</body>
</html>
HTML;

        Response::html($html);
    }
}
