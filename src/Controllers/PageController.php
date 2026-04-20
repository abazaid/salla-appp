<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Config;
use App\Support\Response;
use App\Support\Plans;

final class PageController
{
    public function sitemap(): void
    {
        $appUrl = rtrim((string) Config::get('APP_URL', 'http://localhost:8000'), '/');
        $now = gmdate('Y-m-d\TH:i:s\Z');

        $pages = [
            ['path' => '/', 'changefreq' => 'weekly', 'priority' => '1.0'],
            ['path' => '/pricing', 'changefreq' => 'weekly', 'priority' => '0.9'],
            ['path' => '/about', 'changefreq' => 'monthly', 'priority' => '0.7'],
            ['path' => '/faq', 'changefreq' => 'weekly', 'priority' => '0.8'],
            ['path' => '/privacy', 'changefreq' => 'yearly', 'priority' => '0.4'],
            ['path' => '/terms', 'changefreq' => 'yearly', 'priority' => '0.4'],
        ];

        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $xml .= "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";

        foreach ($pages as $page) {
            $loc = htmlspecialchars($appUrl . $page['path'], ENT_QUOTES | ENT_XML1, 'UTF-8');
            $changefreq = htmlspecialchars($page['changefreq'], ENT_QUOTES | ENT_XML1, 'UTF-8');
            $priority = htmlspecialchars($page['priority'], ENT_QUOTES | ENT_XML1, 'UTF-8');

            $xml .= "  <url>\n";
            $xml .= "    <loc>{$loc}</loc>\n";
            $xml .= "    <lastmod>{$now}</lastmod>\n";
            $xml .= "    <changefreq>{$changefreq}</changefreq>\n";
            $xml .= "    <priority>{$priority}</priority>\n";
            $xml .= "  </url>\n";
        }

        $xml .= "</urlset>";

        http_response_code(200);
        header('Content-Type: application/xml; charset=utf-8');
        header('X-Robots-Tag: index, follow');
        echo $xml;
    }

    public function robots(): void
    {
        $appUrl = rtrim((string) Config::get('APP_URL', 'http://localhost:8000'), '/');
        $sitemapUrl = $appUrl . '/sitemap.xml';

        $content = "User-agent: *\n";
        $content .= "Allow: /\n";
        $content .= "Disallow: /admin\n";
        $content .= "Disallow: /dashboard\n";
        $content .= "Disallow: /login\n";
        $content .= "Disallow: /logout\n";
        $content .= "Disallow: /forgot-password\n";
        $content .= "Disallow: /set-password\n";
        $content .= "Disallow: /embedded\n";
        $content .= "Disallow: /api/\n";
        $content .= "Sitemap: {$sitemapUrl}\n";

        http_response_code(200);
        header('Content-Type: text/plain; charset=utf-8');
        echo $content;
    }

    public function about(): void
    {
        $appUrl = (string) Config::get('APP_URL', 'http://localhost:8000');
        $safeAppUrl = htmlspecialchars(rtrim($appUrl, '/'), ENT_QUOTES, 'UTF-8');
        $logoSrc = '/assets/rankxseo-logo.png';
        $faviconSrc = 'https://rankxseo.com/favicon.png';

        $html = <<<HTML
<!doctype html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" type="image/png" href="{$faviconSrc}">
  <link rel="apple-touch-icon" href="{$faviconSrc}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&display=swap">
  <title>Ã™â€¦Ã™â€  Ã™â€ Ã˜Â­Ã™â€  | RankX SEO Ã™â€¦Ã™â€ Ã˜ÂµÃ˜Â© Ã˜ÂªÃ˜Â­Ã˜Â³Ã™Å Ã™â€  Ã™â€¦Ã˜Â­Ã˜Â±Ã™Æ’Ã˜Â§Ã˜Âª Ã˜Â§Ã™â€žÃ˜Â¨Ã˜Â­Ã˜Â« Ã™â€žÃ™â€¦Ã˜ÂªÃ˜Â§Ã˜Â¬Ã˜Â± Ã˜Â³Ã™â€žÃ˜Â©</title>
  <meta name="description" content="Ã˜ÂªÃ˜Â¹Ã˜Â±Ã™Â Ã˜Â¹Ã™â€žÃ™â€° RankX SEOÃ˜Å’ Ã™â€¦Ã™â€ Ã˜ÂµÃ˜Â© Ã™â€¦Ã˜ÂªÃ˜Â®Ã˜ÂµÃ˜ÂµÃ˜Â© Ã™ÂÃ™Å  Ã˜ÂªÃ˜Â­Ã˜Â³Ã™Å Ã™â€  Ã™â€¦Ã˜Â­Ã˜Â±Ã™Æ’Ã˜Â§Ã˜Âª Ã˜Â§Ã™â€žÃ˜Â¨Ã˜Â­Ã˜Â« Ã™â€žÃ™â€¦Ã˜ÂªÃ˜Â§Ã˜Â¬Ã˜Â± Ã˜Â³Ã™â€žÃ˜Â©: Ã˜ÂªÃ˜Â­Ã˜Â³Ã™Å Ã™â€  Ã™Ë†Ã˜ÂµÃ™Â Ã˜Â§Ã™â€žÃ™â€¦Ã™â€ Ã˜ÂªÃ˜Â¬Ã˜Â§Ã˜ÂªÃ˜Å’ Ã˜Â³Ã™Å Ã™Ë† Ã˜Â§Ã™â€žÃ™â€¦Ã˜ÂªÃ˜Â¬Ã˜Â±Ã˜Å’ ALT Ã˜Â§Ã™â€žÃ˜ÂµÃ™Ë†Ã˜Â±Ã˜Å’ Ã˜Â§Ã™â€žÃ™Æ’Ã™â€žÃ™â€¦Ã˜Â§Ã˜Âª Ã˜Â§Ã™â€žÃ™â€¦Ã™ÂÃ˜ÂªÃ˜Â§Ã˜Â­Ã™Å Ã˜Â©Ã˜Å’ Ã™Ë†Ã˜ÂªÃ˜Â­Ã™â€žÃ™Å Ã™â€ž Ã˜Â§Ã™â€žÃ˜Â¯Ã™Ë†Ã™â€¦Ã™Å Ã™â€  Ã™â€žÃ˜Â²Ã™Å Ã˜Â§Ã˜Â¯Ã˜Â© Ã˜Â§Ã™â€žÃ˜Â²Ã™Å Ã˜Â§Ã˜Â±Ã˜Â§Ã˜Âª Ã™Ë†Ã˜Â§Ã™â€žÃ˜ÂªÃ˜Â­Ã™Ë†Ã™Å Ã™â€ž.">
  <meta name="keywords" content="Ã™â€¦Ã™â€  Ã™â€ Ã˜Â­Ã™â€  RankX SEO, Ã˜ÂªÃ˜Â­Ã˜Â³Ã™Å Ã™â€  Ã™â€¦Ã˜Â­Ã˜Â±Ã™Æ’Ã˜Â§Ã˜Âª Ã˜Â§Ã™â€žÃ˜Â¨Ã˜Â­Ã˜Â« Ã˜Â³Ã™â€žÃ˜Â©, Ã˜Â³Ã™Å Ã™Ë† Ã˜Â³Ã™â€žÃ˜Â©, Ã˜ÂªÃ˜Â·Ã˜Â¨Ã™Å Ã™â€š Ã˜Â³Ã™Å Ã™Ë† Ã˜Â³Ã™â€žÃ˜Â©, Ã˜ÂªÃ˜Â­Ã˜Â³Ã™Å Ã™â€  Ã™Ë†Ã˜ÂµÃ™Â Ã˜Â§Ã™â€žÃ™â€¦Ã™â€ Ã˜ÂªÃ˜Â¬Ã˜Â§Ã˜Âª, Ã˜Â³Ã™Å Ã™Ë† Ã˜Â§Ã™â€žÃ™â€¦Ã˜ÂªÃ˜Â¬Ã˜Â±, Ã˜ÂªÃ˜Â­Ã™â€žÃ™Å Ã™â€ž Ã˜Â§Ã™â€žÃ™Æ’Ã™â€žÃ™â€¦Ã˜Â§Ã˜Âª Ã˜Â§Ã™â€žÃ™â€¦Ã™ÂÃ˜ÂªÃ˜Â§Ã˜Â­Ã™Å Ã˜Â©">
  <meta name="robots" content="index,follow,max-image-preview:large">
  <link rel="canonical" href="{$safeAppUrl}/about">
  <meta property="og:type" content="website">
  <meta property="og:locale" content="ar_SA">
  <meta property="og:title" content="Ã™â€¦Ã™â€  Ã™â€ Ã˜Â­Ã™â€  | RankX SEO Ã™â€¦Ã™â€ Ã˜ÂµÃ˜Â© SEO Ã™â€žÃ™â€¦Ã˜ÂªÃ˜Â§Ã˜Â¬Ã˜Â± Ã˜Â³Ã™â€žÃ˜Â©">
  <meta property="og:description" content="Ã™â€¦Ã™â€ Ã˜ÂµÃ˜Â© Ã™â€¦Ã˜ÂªÃ˜Â®Ã˜ÂµÃ˜ÂµÃ˜Â© Ã™â€žÃ˜ÂªÃ˜Â­Ã˜Â³Ã™Å Ã™â€  Ã™â€¦Ã˜Â­Ã˜ÂªÃ™Ë†Ã™â€° Ã™â€¦Ã˜ÂªÃ˜Â§Ã˜Â¬Ã˜Â± Ã˜Â³Ã™â€žÃ˜Â© Ã™Ë†Ã˜Â±Ã™ÂÃ˜Â¹ Ã˜Â§Ã™â€žÃ˜Â²Ã™Å Ã˜Â§Ã˜Â±Ã˜Â§Ã˜Âª Ã™â€¦Ã™â€  Ã™â€¦Ã˜Â­Ã˜Â±Ã™Æ’Ã˜Â§Ã˜Âª Ã˜Â§Ã™â€žÃ˜Â¨Ã˜Â­Ã˜Â« Ã˜Â¹Ã˜Â¨Ã˜Â± Ã˜Â£Ã˜Â¯Ã™Ë†Ã˜Â§Ã˜Âª Ã˜Â¹Ã™â€¦Ã™â€žÃ™Å Ã˜Â© Ã™â€¦Ã˜Â¯Ã˜Â¹Ã™Ë†Ã™â€¦Ã˜Â© Ã˜Â¨Ã˜Â§Ã™â€žÃ˜Â°Ã™Æ’Ã˜Â§Ã˜Â¡ Ã˜Â§Ã™â€žÃ˜Â§Ã˜ÂµÃ˜Â·Ã™â€ Ã˜Â§Ã˜Â¹Ã™Å .">
  <meta property="og:url" content="{$safeAppUrl}/about">
  <meta property="og:image" content="{$logoSrc}">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="Ã™â€¦Ã™â€  Ã™â€ Ã˜Â­Ã™â€  | RankX SEO">
  <meta name="twitter:description" content="Ã™â€ Ã˜Â³Ã˜Â§Ã˜Â¹Ã˜Â¯ Ã™â€¦Ã˜ÂªÃ˜Â§Ã˜Â¬Ã˜Â± Ã˜Â³Ã™â€žÃ˜Â© Ã˜Â¹Ã™â€žÃ™â€° Ã˜Â¨Ã™â€ Ã˜Â§Ã˜Â¡ Ã™â€ Ã™â€¦Ã™Ë† SEO Ã™ÂÃ˜Â¹Ã™â€žÃ™Å  Ã˜Â¹Ã˜Â¨Ã˜Â± Ã˜ÂªÃ˜Â­Ã˜Â³Ã™Å Ã™â€  Ã˜Â§Ã™â€žÃ™â€¦Ã˜Â­Ã˜ÂªÃ™Ë†Ã™â€° Ã™Ë†Ã˜Â§Ã™â€žÃ™Æ’Ã™â€žÃ™â€¦Ã˜Â§Ã˜Âª Ã˜Â§Ã™â€žÃ™â€¦Ã™ÂÃ˜ÂªÃ˜Â§Ã˜Â­Ã™Å Ã˜Â© Ã™Ë†Ã˜Â§Ã™â€žÃ˜Â±Ã˜Â¨Ã˜Â· Ã˜Â§Ã™â€žÃ˜Â¯Ã˜Â§Ã˜Â®Ã™â€žÃ™Å .">
  <meta name="twitter:image" content="{$logoSrc}">
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "Organization",
    "name": "RankX SEO",
    "url": "{$safeAppUrl}",
    "logo": "{$logoSrc}",
    "email": "seo@rankxseo.com",
    "description": "Ã™â€¦Ã™â€ Ã˜ÂµÃ˜Â© Ã™â€¦Ã˜ÂªÃ˜Â®Ã˜ÂµÃ˜ÂµÃ˜Â© Ã™ÂÃ™Å  Ã˜ÂªÃ˜Â­Ã˜Â³Ã™Å Ã™â€  Ã™â€¦Ã˜Â­Ã˜Â±Ã™Æ’Ã˜Â§Ã˜Âª Ã˜Â§Ã™â€žÃ˜Â¨Ã˜Â­Ã˜Â« Ã™â€žÃ™â€¦Ã˜ÂªÃ˜Â§Ã˜Â¬Ã˜Â± Ã˜Â³Ã™â€žÃ˜Â©."
  }
  </script>
  <style>
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
    .list{margin:0;padding-right:20px;line-height:2;color:#475569}
    .list li{margin-bottom:8px}
    .note-box{background:#EEF2FF;border:1px solid #C7D2FE;border-radius:14px;padding:16px;margin:16px 0}
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
        <img src="{$logoSrc}" alt="RankX SEO" width="1200" height="400" decoding="async">
      </div>

      <h1>Ã™â€¦Ã™â€  Ã™â€ Ã˜Â­Ã™â€  - RankX SEO</h1>
      <p class="lead">
        RankX SEO Ã™â€¡Ã™Å  Ã™â€¦Ã™â€ Ã˜ÂµÃ˜Â© Ã™â€¦Ã˜ÂªÃ˜Â®Ã˜ÂµÃ˜ÂµÃ˜Â© Ã˜ÂµÃ™ÂÃ™â€¦Ã™â€¦Ã˜Âª Ã˜Â®Ã˜ÂµÃ™Å Ã˜ÂµÃ™â€¹Ã˜Â§ Ã™â€žÃ˜Â£Ã˜ÂµÃ˜Â­Ã˜Â§Ã˜Â¨ Ã™â€¦Ã˜ÂªÃ˜Â§Ã˜Â¬Ã˜Â± Ã˜Â³Ã™â€žÃ˜Â©. Ã™â€ Ã˜Â³Ã˜Â§Ã˜Â¹Ã˜Â¯ Ã˜Â§Ã™â€žÃ˜ÂªÃ˜Â§Ã˜Â¬Ã˜Â± Ã˜Â¹Ã™â€žÃ™â€° Ã˜ÂªÃ˜Â·Ã˜Â¨Ã™Å Ã™â€š Ã˜ÂªÃ˜Â­Ã˜Â³Ã™Å Ã™â€  Ã™â€¦Ã˜Â­Ã˜Â±Ã™Æ’Ã˜Â§Ã˜Âª Ã˜Â§Ã™â€žÃ˜Â¨Ã˜Â­Ã˜Â« (SEO) Ã˜Â¨Ã˜Â´Ã™Æ’Ã™â€ž Ã˜Â¹Ã™â€¦Ã™â€žÃ™Å Ã˜Å’ Ã˜Â³Ã˜Â±Ã™Å Ã˜Â¹Ã˜Å’ Ã™Ë†Ã™â€šÃ˜Â§Ã˜Â¨Ã™â€ž Ã™â€žÃ™â€žÃ™â€šÃ™Å Ã˜Â§Ã˜Â³ Ã™â€¦Ã™â€  Ã™â€žÃ™Ë†Ã˜Â­Ã˜Â© Ã™Ë†Ã˜Â§Ã˜Â­Ã˜Â¯Ã˜Â©.
      </p>

      <h2>Ã˜Â±Ã˜Â¤Ã™Å Ã˜ÂªÃ™â€ Ã˜Â§ Ã™ÂÃ™Å  Ã˜ÂªÃ˜Â­Ã˜Â³Ã™Å Ã™â€  Ã™â€¦Ã˜Â­Ã˜Â±Ã™Æ’Ã˜Â§Ã˜Âª Ã˜Â§Ã™â€žÃ˜Â¨Ã˜Â­Ã˜Â« Ã™â€žÃ™â€¦Ã˜ÂªÃ˜Â§Ã˜Â¬Ã˜Â± Ã˜Â³Ã™â€žÃ˜Â©</h2>
      <p>
        Ã™â€ Ã˜Â¤Ã™â€¦Ã™â€  Ã˜Â£Ã™â€  Ã™Æ’Ã™â€ž Ã™â€¦Ã˜ÂªÃ˜Â¬Ã˜Â± Ã™Å Ã˜Â³Ã˜ÂªÃ˜Â­Ã™â€š Ã˜Â£Ã™â€  Ã™Å Ã˜Â¸Ã™â€¡Ã˜Â± Ã™ÂÃ™Å  Ã™â€ Ã˜ÂªÃ˜Â§Ã˜Â¦Ã˜Â¬ Ã˜Â§Ã™â€žÃ˜Â¨Ã˜Â­Ã˜Â« Ã˜Â§Ã™â€žÃ˜Â£Ã™Ë†Ã™â€žÃ™â€°. Ã™â€žÃ™Æ’Ã™â€  Ã˜ÂªÃ™â€ Ã™ÂÃ™Å Ã˜Â° SEO Ã˜Â¨Ã˜Â´Ã™Æ’Ã™â€ž Ã˜ÂµÃ˜Â­Ã™Å Ã˜Â­ Ã™Å Ã˜Â­Ã˜ÂªÃ˜Â§Ã˜Â¬ Ã™Ë†Ã™â€šÃ˜ÂªÃ™â€¹Ã˜Â§ Ã™Ë†Ã˜Â®Ã˜Â¨Ã˜Â±Ã˜Â© Ã™Ë†Ã˜ÂªÃ™Æ’Ã˜Â±Ã˜Â§Ã˜Â±Ã™â€¹Ã˜Â§ Ã™â€¦Ã˜Â³Ã˜ÂªÃ™â€¦Ã˜Â±Ã™â€¹Ã˜Â§. Ã™â€¡Ã˜Â¯Ã™ÂÃ™â€ Ã˜Â§ Ã™â€¡Ã™Ë† Ã˜ÂªÃ˜Â¨Ã˜Â³Ã™Å Ã˜Â· Ã™â€¡Ã˜Â°Ã™â€¡ Ã˜Â§Ã™â€žÃ˜Â¹Ã™â€¦Ã™â€žÃ™Å Ã˜Â©
        Ã™Ë†Ã˜ÂªÃ˜Â­Ã™Ë†Ã™Å Ã™â€žÃ™â€¡Ã˜Â§ Ã˜Â¥Ã™â€žÃ™â€° Ã˜Â®Ã˜Â·Ã™Ë†Ã˜Â§Ã˜Âª Ã™Ë†Ã˜Â§Ã˜Â¶Ã˜Â­Ã˜Â© Ã™Å Ã™â€¦Ã™Æ’Ã™â€  Ã˜ÂªÃ™â€ Ã™ÂÃ™Å Ã˜Â°Ã™â€¡Ã˜Â§ Ã™Å Ã™Ë†Ã™â€¦Ã™Å Ã™â€¹Ã˜Â§Ã˜Å’ Ã™â€¦Ã™â€  Ã˜Â§Ã™â€žÃ™â€¦Ã˜ÂªÃ˜Â¬Ã˜Â± Ã˜Â§Ã™â€žÃ˜ÂµÃ˜ÂºÃ™Å Ã˜Â± Ã˜Â¥Ã™â€žÃ™â€° Ã˜Â§Ã™â€žÃ˜Â¹Ã™â€žÃ˜Â§Ã™â€¦Ã˜Â§Ã˜Âª Ã˜Â§Ã™â€žÃ™Æ’Ã˜Â¨Ã™Å Ã˜Â±Ã˜Â©.
      </p>

      <h2>Ã™â€¦Ã˜Â§Ã˜Â°Ã˜Â§ Ã™â€ Ã™â€šÃ˜Â¯Ã™â€¦ Ã˜Â¯Ã˜Â§Ã˜Â®Ã™â€ž RankX SEOÃ˜Å¸</h2>
      <div class="features">
        <div class="feature">
          <div class="feature-icon">Ã°Å¸â€œÂ</div>
          <h3>Ã˜ÂªÃ˜Â­Ã˜Â³Ã™Å Ã™â€  Ã™Ë†Ã˜ÂµÃ™Â Ã˜Â§Ã™â€žÃ™â€¦Ã™â€ Ã˜ÂªÃ˜Â¬Ã˜Â§Ã˜Âª</h3>
          <p>Ã˜ÂµÃ™Å Ã˜Â§Ã˜ÂºÃ˜Â© Ã˜Â£Ã™Ë†Ã˜ÂµÃ˜Â§Ã™Â Ã˜Â§Ã˜Â­Ã˜ÂªÃ˜Â±Ã˜Â§Ã™ÂÃ™Å Ã˜Â© Ã™â€¦Ã˜ÂªÃ™Ë†Ã˜Â§Ã™ÂÃ™â€šÃ˜Â© Ã™â€¦Ã˜Â¹ SEO Ã™Ë†Ã˜ÂªÃ˜Â±Ã™Æ’Ã™â€˜Ã˜Â² Ã˜Â¹Ã™â€žÃ™â€° Ã™â€ Ã™Å Ã˜Â© Ã˜Â§Ã™â€žÃ˜Â´Ã˜Â±Ã˜Â§Ã˜Â¡</p>
        </div>
        <div class="feature">
          <div class="feature-icon">Ã°Å¸â€Â</div>
          <h3>Ã˜Â³Ã™Å Ã™Ë† Ã˜Â§Ã™â€žÃ™â€¦Ã˜ÂªÃ˜Â¬Ã˜Â± Ã™Ë†Ã˜Â§Ã™â€žÃ˜ÂµÃ™ÂÃ˜Â­Ã˜Â§Ã˜Âª Ã˜Â§Ã™â€žÃ˜Â£Ã˜Â³Ã˜Â§Ã˜Â³Ã™Å Ã˜Â©</h3>
          <p>Ã˜ÂªÃ˜Â­Ã˜Â³Ã™Å Ã™â€  Meta Title Ã™Ë† Meta Description Ã˜Â¨Ã™â€¦Ã˜Â§ Ã™Å Ã˜Â¯Ã˜Â¹Ã™â€¦ Ã˜Â§Ã™â€žÃ˜Â¸Ã™â€¡Ã™Ë†Ã˜Â± Ã™ÂÃ™Å  Ã˜Â¬Ã™Ë†Ã˜Â¬Ã™â€ž</p>
        </div>
        <div class="feature">
          <div class="feature-icon">Ã°Å¸â€“Â¼Ã¯Â¸Â</div>
          <h3>ALT Ã™â€žÃ™â€žÃ˜ÂµÃ™Ë†Ã˜Â±</h3>
          <p>Ã˜Â¥Ã™â€ Ã˜Â´Ã˜Â§Ã˜Â¡ Ã™â€ Ã˜ÂµÃ™Ë†Ã˜Âµ Ã˜Â¨Ã˜Â¯Ã™Å Ã™â€žÃ˜Â© Ã˜Â§Ã˜Â­Ã˜ÂªÃ˜Â±Ã˜Â§Ã™ÂÃ™Å Ã˜Â© Ã™â€žÃ™â€žÃ˜ÂµÃ™Ë†Ã˜Â± Ã™â€žÃ˜Â¸Ã™â€¡Ã™Ë†Ã˜Â± Ã˜Â£Ã™ÂÃ˜Â¶Ã™â€ž Ã™ÂÃ™Å  Ã˜Â§Ã™â€žÃ˜Â¨Ã˜Â­Ã˜Â« Ã˜Â§Ã™â€žÃ™â€¦Ã˜Â±Ã˜Â¦Ã™Å </p>
        </div>
        <div class="feature">
          <div class="feature-icon">Ã°Å¸â€œÅ </div>
          <h3>Ã˜ÂªÃ˜Â­Ã™â€žÃ™Å Ã™â€ž Ã˜Â§Ã™â€žÃ™Æ’Ã™â€žÃ™â€¦Ã˜Â§Ã˜Âª</h3>
          <p>Ã˜Â£Ã˜Â¯Ã™Ë†Ã˜Â§Ã˜Âª Ã™â€¦Ã˜ÂªÃ™â€šÃ˜Â¯Ã™â€¦Ã˜Â© Ã™â€žÃ˜ÂªÃ˜Â­Ã™â€žÃ™Å Ã™â€ž Ã˜Â§Ã™â€žÃ™Æ’Ã™â€žÃ™â€¦Ã˜Â§Ã˜Âª Ã˜Â§Ã™â€žÃ™â€¦Ã™ÂÃ˜ÂªÃ˜Â§Ã˜Â­Ã™Å Ã˜Â© Ã™Ë†Ã˜Â§Ã™â€žÃ™â€¦Ã™â€ Ã˜Â§Ã™ÂÃ˜Â³Ã™Å Ã™â€ </p>
        </div>
      </div>

      <h2>Ã™Æ’Ã™Å Ã™Â Ã™â€ Ã˜Â­Ã™â€šÃ™â€š Ã™â€ Ã˜ÂªÃ˜Â§Ã˜Â¦Ã˜Â¬ SEO Ã™ÂÃ˜Â¹Ã™â€žÃ™Å Ã˜Â©Ã˜Å¸</h2>
      <p>
        Ã™ÂÃ™Å  RankX SEO Ã™â€žÃ˜Â§ Ã™â€ Ã˜Â¹Ã˜ÂªÃ™â€¦Ã˜Â¯ Ã˜Â¹Ã™â€žÃ™â€° Ã¢â‚¬Å“Ã™â€ Ã˜Âµ Ã˜Â¬Ã™â€¦Ã™Å Ã™â€žÃ¢â‚¬Â Ã™ÂÃ™â€šÃ˜Â·. Ã™â€ Ã˜Â­Ã™â€  Ã™â€ Ã˜Â¨Ã™â€ Ã™Å  Ã™â€ Ã˜Â¸Ã˜Â§Ã™â€¦ Ã˜ÂªÃ˜Â­Ã˜Â³Ã™Å Ã™â€  Ã™â€¦Ã˜ÂªÃ™Æ’Ã˜Â§Ã™â€¦Ã™â€ž: Ã˜Â§Ã˜Â®Ã˜ÂªÃ™Å Ã˜Â§Ã˜Â± Ã™Æ’Ã™â€žÃ™â€¦Ã˜Â§Ã˜Âª Ã™â€¦Ã™ÂÃ˜ÂªÃ˜Â§Ã˜Â­Ã™Å Ã˜Â© Ã™â€¦Ã™â€ Ã˜Â§Ã˜Â³Ã˜Â¨Ã˜Â©Ã˜Å’ Ã™Æ’Ã˜ÂªÃ˜Â§Ã˜Â¨Ã˜Â© Ã™â€¦Ã˜Â­Ã˜ÂªÃ™Ë†Ã™â€° Ã™Å Ã˜Â®Ã˜Â¯Ã™â€¦ Ã™â€ Ã™Å Ã˜Â© Ã˜Â§Ã™â€žÃ˜Â¨Ã˜Â­Ã˜Â«Ã˜Å’
        Ã˜ÂªÃ˜Â­Ã˜Â³Ã™Å Ã™â€  Ã˜Â¹Ã™â€ Ã˜Â§Ã˜ÂµÃ˜Â± Ã˜Â§Ã™â€žÃ™â€¦Ã™Å Ã˜ÂªÃ˜Â§Ã˜Å’ Ã˜Â«Ã™â€¦ Ã˜ÂªÃ˜Â·Ã˜Â¨Ã™Å Ã™â€š Ã˜Â§Ã™â€žÃ˜Â±Ã˜Â¨Ã˜Â· Ã˜Â§Ã™â€žÃ˜Â¯Ã˜Â§Ã˜Â®Ã™â€žÃ™Å  Ã˜Â¨Ã™Å Ã™â€  Ã˜Â§Ã™â€žÃ™â€¦Ã™â€ Ã˜ÂªÃ˜Â¬Ã˜Â§Ã˜Âª Ã™Ë†Ã˜Â§Ã™â€žÃ˜Â£Ã™â€šÃ˜Â³Ã˜Â§Ã™â€¦. Ã™â€¡Ã˜Â°Ã˜Â§ Ã˜Â§Ã™â€žÃ˜ÂªÃ˜Â³Ã™â€žÃ˜Â³Ã™â€ž Ã™â€¡Ã™Ë† Ã™â€¦Ã˜Â§ Ã™Å Ã˜ÂµÃ™â€ Ã˜Â¹ Ã˜Â£Ã˜Â«Ã˜Â±Ã™â€¹Ã˜Â§ Ã˜ÂªÃ˜Â±Ã˜Â§Ã™Æ’Ã™â€¦Ã™Å Ã™â€¹Ã˜Â§ Ã™Ë†Ã˜Â§Ã˜Â¶Ã˜Â­Ã™â€¹Ã˜Â§ Ã™ÂÃ™Å  Ã˜Â§Ã™â€žÃ˜Â¸Ã™â€¡Ã™Ë†Ã˜Â±.
      </p>
      <div class="note-box">
        <h3 style="margin:0 0 8px;">Ã˜Â§Ã™â€žÃ™â€ Ã˜ÂªÃ™Å Ã˜Â¬Ã˜Â© Ã˜Â§Ã™â€žÃ˜ÂªÃ™Å  Ã™â€ Ã˜Â³Ã˜ÂªÃ™â€¡Ã˜Â¯Ã™ÂÃ™â€¡Ã˜Â§</h3>
        <ul class="list">
          <li>Ã˜Â²Ã™Å Ã˜Â§Ã˜Â¯Ã˜Â© Ã˜Â§Ã™â€žÃ˜Â²Ã™Å Ã˜Â§Ã˜Â±Ã˜Â§Ã˜Âª Ã˜Â§Ã™â€žÃ˜Â¹Ã˜Â¶Ã™Ë†Ã™Å Ã˜Â© Ã™â€¦Ã™â€  Ã˜Â§Ã™â€žÃ™Æ’Ã™â€žÃ™â€¦Ã˜Â§Ã˜Âª Ã˜Â°Ã˜Â§Ã˜Âª Ã˜Â§Ã™â€žÃ™â€ Ã™Å Ã˜Â© Ã˜Â§Ã™â€žÃ˜Â´Ã˜Â±Ã˜Â§Ã˜Â¦Ã™Å Ã˜Â©.</li>
          <li>Ã˜Â±Ã™ÂÃ˜Â¹ Ã™â€¦Ã˜Â¹Ã˜Â¯Ã™â€ž Ã˜Â§Ã™â€žÃ˜ÂªÃ˜Â­Ã™Ë†Ã™Å Ã™â€ž Ã˜Â¹Ã˜Â¨Ã˜Â± Ã™â€¦Ã˜Â­Ã˜ÂªÃ™Ë†Ã™â€° Ã˜Â£Ã™Ë†Ã˜Â¶Ã˜Â­ Ã™Ë†Ã˜Â£Ã™Æ’Ã˜Â«Ã˜Â± Ã˜Â¥Ã™â€šÃ™â€ Ã˜Â§Ã˜Â¹Ã™â€¹Ã˜Â§.</li>
          <li>Ã˜ÂªÃ™â€šÃ™â€žÃ™Å Ã™â€ž Ã™Ë†Ã™â€šÃ˜Âª Ã˜Â¥Ã™â€ Ã˜ÂªÃ˜Â§Ã˜Â¬ Ã˜Â§Ã™â€žÃ™â€¦Ã˜Â­Ã˜ÂªÃ™Ë†Ã™â€° Ã™â€¦Ã˜Â¹ Ã˜Â§Ã™â€žÃ˜Â­Ã™ÂÃ˜Â§Ã˜Â¸ Ã˜Â¹Ã™â€žÃ™â€° Ã˜Â§Ã™â€žÃ˜Â¬Ã™Ë†Ã˜Â¯Ã˜Â©.</li>
        </ul>
      </div>

      <h2>Ã™â€žÃ™â€¦Ã™â€  Ã™â€¡Ã˜Â°Ã™â€¡ Ã˜Â§Ã™â€žÃ™â€¦Ã™â€ Ã˜ÂµÃ˜Â©Ã˜Å¸</h2>
      <ul class="list">
        <li>Ã˜Â£Ã˜ÂµÃ˜Â­Ã˜Â§Ã˜Â¨ Ã™â€¦Ã˜ÂªÃ˜Â§Ã˜Â¬Ã˜Â± Ã˜Â³Ã™â€žÃ˜Â© Ã˜Â§Ã™â€žÃ˜Â°Ã™Å Ã™â€  Ã™Å Ã˜Â±Ã™Å Ã˜Â¯Ã™Ë†Ã™â€  Ã˜ÂªÃ˜Â­Ã˜Â³Ã™Å Ã™â€  Ã˜ÂªÃ˜Â±Ã˜ÂªÃ™Å Ã˜Â¨ Ã˜Â§Ã™â€žÃ™â€¦Ã˜ÂªÃ˜Â¬Ã˜Â± Ã™ÂÃ™Å  Ã˜Â§Ã™â€žÃ˜Â¨Ã˜Â­Ã˜Â«.</li>
        <li>Ã™ÂÃ˜Â±Ã™â€š Ã˜Â§Ã™â€žÃ˜ÂªÃ˜Â³Ã™Ë†Ã™Å Ã™â€š Ã™Ë†Ã˜Â§Ã™â€žÃ™â€¦Ã˜Â­Ã˜ÂªÃ™Ë†Ã™â€° Ã˜Â§Ã™â€žÃ˜ÂªÃ™Å  Ã˜ÂªÃ˜Â­Ã˜ÂªÃ˜Â§Ã˜Â¬ Ã˜Â¥Ã™â€ Ã˜ÂªÃ˜Â§Ã˜Â¬ Ã˜Â£Ã˜Â³Ã˜Â±Ã˜Â¹ Ã™Ë†Ã™â€¦Ã˜Â®Ã˜Â±Ã˜Â¬Ã˜Â§Ã˜Âª Ã™â€šÃ˜Â§Ã˜Â¨Ã™â€žÃ˜Â© Ã™â€žÃ™â€žÃ™â€¦Ã˜Â±Ã˜Â§Ã˜Â¬Ã˜Â¹Ã˜Â©.</li>
        <li>Ã˜Â§Ã™â€žÃ™Ë†Ã™Æ’Ã˜Â§Ã™â€žÃ˜Â§Ã˜Âª Ã˜Â§Ã™â€žÃ˜ÂªÃ™Å  Ã˜ÂªÃ˜Â¯Ã™Å Ã˜Â± Ã˜Â£Ã™Æ’Ã˜Â«Ã˜Â± Ã™â€¦Ã™â€  Ã™â€¦Ã˜ÂªÃ˜Â¬Ã˜Â± Ã™Ë†Ã˜ÂªÃ˜Â­Ã˜ÂªÃ˜Â§Ã˜Â¬ Ã˜ÂªÃ˜Â¯Ã™ÂÃ™â€š Ã˜Â¹Ã™â€¦Ã™â€ž Ã˜Â«Ã˜Â§Ã˜Â¨Ã˜Âª.</li>
      </ul>

      <h2>Ã˜Â£Ã˜Â±Ã™â€šÃ˜Â§Ã™â€¦ Ã˜ÂªÃ˜ÂªÃ˜Â­Ã˜Â¯Ã˜Â« Ã˜Â¹Ã™â€ Ã˜Â§</h2>
      <div class="stats">
        <div class="stat"><strong>+500</strong><span>Ã™â€¦Ã˜ÂªÃ˜Â¬Ã˜Â± Ã™â€ Ã˜Â´Ã˜Â·</span></div>
        <div class="stat"><strong>+50K</strong><span>Ã™â€¦Ã™â€ Ã˜ÂªÃ˜Â¬ Ã™â€¦Ã˜Â­Ã˜Â³Ã™â€˜Ã™â€ </span></div>
        <div class="stat"><strong>98%</strong><span>Ã™â€ Ã˜Â³Ã˜Â¨Ã˜Â© Ã˜Â±Ã˜Â¶Ã˜Â§ Ã˜Â§Ã™â€žÃ˜Â¹Ã™â€¦Ã™â€žÃ˜Â§Ã˜Â¡</span></div>
        <div class="stat"><strong>24/7</strong><span>Ã˜Â¯Ã˜Â¹Ã™â€¦ Ã™ÂÃ™â€ Ã™Å </span></div>
      </div>

      <h2>Ã™ÂÃ˜Â±Ã™Å Ã™â€šÃ™â€ Ã˜Â§</h2>
      <p>
        Ã™ÂÃ˜Â±Ã™Å Ã™â€šÃ™â€ Ã˜Â§ Ã™Å Ã˜Â¶Ã™â€¦ Ã˜Â®Ã˜Â¨Ã˜Â±Ã˜Â§Ã˜Â¡ Ã™ÂÃ™Å  Ã˜ÂªÃ˜Â­Ã˜Â³Ã™Å Ã™â€  Ã™â€¦Ã˜Â­Ã˜Â±Ã™Æ’Ã˜Â§Ã˜Âª Ã˜Â§Ã™â€žÃ˜Â¨Ã˜Â­Ã˜Â« (SEO) Ã™Ë†Ã˜ÂªÃ˜Â·Ã™Ë†Ã™Å Ã˜Â± Ã˜Â§Ã™â€žÃ˜Â¨Ã˜Â±Ã™â€¦Ã˜Â¬Ã™Å Ã˜Â§Ã˜Âª Ã™Ë†Ã˜Â§Ã™â€žÃ˜Â°Ã™Æ’Ã˜Â§Ã˜Â¡ Ã˜Â§Ã™â€žÃ˜Â§Ã˜ÂµÃ˜Â·Ã™â€ Ã˜Â§Ã˜Â¹Ã™Å . Ã™â€ Ã˜Â¹Ã™â€¦Ã™â€ž Ã™â€¦Ã˜Â¹Ã™â€¹Ã˜Â§ Ã™â€žÃ˜ÂªÃ˜Â­Ã™â€šÃ™Å Ã™â€š Ã™â€¡Ã˜Â¯Ã™Â Ã™Ë†Ã˜Â§Ã˜Â­Ã˜Â¯:
        Ã˜ÂªÃ™â€¦Ã™Æ’Ã™Å Ã™â€ Ã™Æ’ Ã™â€¦Ã™â€  Ã˜Â¨Ã™â€ Ã˜Â§Ã˜Â¡ Ã™â€ Ã™â€¦Ã™Ë† Ã™â€šÃ˜Â§Ã˜Â¨Ã™â€ž Ã™â€žÃ™â€žÃ˜Â§Ã˜Â³Ã˜ÂªÃ™â€¦Ã˜Â±Ã˜Â§Ã˜Â± Ã™ÂÃ™Å  Ã™â€¦Ã˜ÂªÃ˜Â¬Ã˜Â±Ã™Æ’Ã˜Å’ Ã™Ë†Ã™â€žÃ™Å Ã˜Â³ Ã™â€¦Ã˜Â¬Ã˜Â±Ã˜Â¯ Ã˜ÂªÃ˜Â­Ã˜Â³Ã™Å Ã™â€ Ã˜Â§Ã˜Âª Ã™â€¦Ã˜Â¤Ã™â€šÃ˜ÂªÃ˜Â©.
      </p>
      <div class="team">
        <div class="team-member">
          <div class="avatar">R</div>
          <h3>Ã™ÂÃ˜Â±Ã™Å Ã™â€š Ã˜Â§Ã™â€žÃ˜ÂªÃ˜Â·Ã™Ë†Ã™Å Ã˜Â±</h3>
          <p>Ã˜Â®Ã˜Â¨Ã˜Â±Ã˜Â§Ã˜Â¡ Ã™ÂÃ™Å  Ã˜Â¨Ã™â€ Ã˜Â§Ã˜Â¡ Ã˜Â£Ã™â€ Ã˜Â¸Ã™â€¦Ã˜Â© Ã˜Â°Ã™Æ’Ã™Å Ã˜Â©</p>
        </div>
        <div class="team-member">
          <div class="avatar">S</div>
          <h3>Ã™ÂÃ˜Â±Ã™Å Ã™â€š SEO</h3>
          <p>Ã™â€¦Ã˜ÂªÃ˜Â®Ã˜ÂµÃ˜ÂµÃ™Ë†Ã™â€  Ã™ÂÃ™Å  Ã˜ÂªÃ˜Â­Ã˜Â³Ã™Å Ã™â€  Ã˜Â§Ã™â€žÃ˜Â¨Ã˜Â­Ã˜Â«</p>
        </div>
        <div class="team-member">
          <div class="avatar">D</div>
          <h3>Ã™ÂÃ˜Â±Ã™Å Ã™â€š Ã˜Â§Ã™â€žÃ˜ÂªÃ˜ÂµÃ™â€¦Ã™Å Ã™â€¦</h3>
          <p>Ã™â€¦Ã˜ÂµÃ™â€¦Ã™â€¦Ã™Ë†Ã™â€  Ã™â€žÃ™Ë†Ã˜Â§Ã˜Â¬Ã™â€¡Ã˜Â§Ã˜Âª Ã˜Â³Ã™â€¡Ã™â€žÃ˜Â©</p>
        </div>
        <div class="team-member">
          <div class="avatar">S</div>
          <h3>Ã˜Â§Ã™â€žÃ˜Â¯Ã˜Â¹Ã™â€¦ Ã˜Â§Ã™â€žÃ™ÂÃ™â€ Ã™Å </h3>
          <p>Ã™â€¦Ã˜ÂªÃ˜Â§Ã˜Â­Ã™Ë†Ã™â€  Ã™â€žÃ™â€¦Ã˜Â³Ã˜Â§Ã˜Â¹Ã˜Â¯Ã˜ÂªÃ™Æ’ Ã˜Â¯Ã˜Â§Ã˜Â¦Ã™â€¦Ã™â€¹Ã˜Â§</p>
        </div>
      </div>

      <h2>Ã™â€žÃ™â€¦Ã˜Â§Ã˜Â°Ã˜Â§ Ã™Å Ã˜Â«Ã™â€š Ã˜Â¨Ã™â€ Ã˜Â§ Ã˜Â£Ã˜ÂµÃ˜Â­Ã˜Â§Ã˜Â¨ Ã˜Â§Ã™â€žÃ™â€¦Ã˜ÂªÃ˜Â§Ã˜Â¬Ã˜Â±Ã˜Å¸</h2>
      <ul class="list">
        <li>Ã˜Â­Ã™â€ž Ã™â€¦Ã˜ÂªÃ˜Â®Ã˜ÂµÃ˜Âµ Ã™â€žÃ™â€¦Ã˜ÂªÃ˜Â§Ã˜Â¬Ã˜Â± Ã˜Â³Ã™â€žÃ˜Â© Ã˜Â¨Ã˜Â¯Ã™â€ž Ã˜Â£Ã˜Â¯Ã™Ë†Ã˜Â§Ã˜Âª Ã˜Â¹Ã˜Â§Ã™â€¦Ã˜Â© Ã™â€žÃ˜Â§ Ã˜ÂªÃ˜Â±Ã˜Â§Ã˜Â¹Ã™Å  Ã˜Â·Ã˜Â¨Ã™Å Ã˜Â¹Ã˜Â© Ã˜Â§Ã™â€žÃ™â€¦Ã˜ÂªÃ˜Â¬Ã˜Â±.</li>
        <li>Ã™â€¦Ã˜Â±Ã˜Â§Ã˜Â¬Ã˜Â¹Ã˜Â© Ã™Æ’Ã˜Â§Ã™â€¦Ã™â€žÃ˜Â© Ã™â€šÃ˜Â¨Ã™â€ž Ã˜Â§Ã™â€žÃ˜Â­Ã™ÂÃ˜Â¸: AI + Ã˜ÂªÃ˜Â¹Ã˜Â¯Ã™Å Ã™â€ž Ã™Å Ã˜Â¯Ã™Ë†Ã™Å  + Ã˜Â­Ã™ÂÃ˜Â¸ Ã™â€¦Ã˜Â¨Ã˜Â§Ã˜Â´Ã˜Â±.</li>
        <li>Ã˜ÂªÃ˜Â·Ã™Ë†Ã™Å Ã˜Â± Ã™â€¦Ã˜Â³Ã˜ÂªÃ™â€¦Ã˜Â± Ã™â€žÃ™â€žÃ˜Â®Ã˜ÂµÃ˜Â§Ã˜Â¦Ã˜Âµ Ã˜Â¨Ã™â€ Ã˜Â§Ã˜Â¡Ã™â€¹ Ã˜Â¹Ã™â€žÃ™â€° Ã˜Â§Ã˜Â³Ã˜ÂªÃ˜Â®Ã˜Â¯Ã˜Â§Ã™â€¦ Ã˜Â§Ã™â€žÃ˜Â¹Ã™â€¦Ã™â€žÃ˜Â§Ã˜Â¡ Ã˜Â§Ã™â€žÃ™ÂÃ˜Â¹Ã™â€žÃ™Å .</li>
      </ul>

      <h2>Ã˜ÂªÃ™Ë†Ã˜Â§Ã˜ÂµÃ™â€ž Ã™â€¦Ã˜Â¹Ã™â€ Ã˜Â§</h2>
      <div class="contact-box">
        <p>Ã™â€ Ã˜Â³Ã˜Â¹Ã˜Â¯ Ã˜Â¨Ã˜ÂªÃ™Ë†Ã˜Â§Ã˜ÂµÃ™â€žÃ™Æ’ Ã™â€¦Ã˜Â¹Ã™â€ Ã˜Â§ Ã™â€žÃ˜Â£Ã™Å  Ã˜Â§Ã˜Â³Ã˜ÂªÃ™ÂÃ˜Â³Ã˜Â§Ã˜Â±Ã˜Å’ Ã˜Â´Ã˜Â±Ã˜Â§Ã™Æ’Ã˜Â©Ã˜Å’ Ã˜Â£Ã™Ë† Ã˜Â·Ã™â€žÃ˜Â¨ Ã˜ÂªÃ˜Â·Ã™Ë†Ã™Å Ã˜Â± Ã˜Â®Ã˜Â§Ã˜Âµ Ã˜Â¨Ã™â€¦Ã˜ÂªÃ˜Â¬Ã˜Â±Ã™Æ’.</p>
        <a class="contact-email" href="mailto:seo@rankxseo.com">
          <span>Ã°Å¸â€œÂ§</span> seo@rankxseo.com
        </a>
      </div>
    </div>

    <div class="footer">
      <p>
        <a href="{$safeAppUrl}/">Ã˜Â§Ã™â€žÃ˜Â±Ã˜Â¦Ã™Å Ã˜Â³Ã™Å Ã˜Â©</a> Ã‚Â· 
        <a href="{$safeAppUrl}/faq">Ã˜Â§Ã™â€žÃ˜Â£Ã˜Â³Ã˜Â¦Ã™â€žÃ˜Â© Ã˜Â§Ã™â€žÃ˜Â´Ã˜Â§Ã˜Â¦Ã˜Â¹Ã˜Â©</a> Ã‚Â· 
        <a href="{$safeAppUrl}/privacy">Ã˜Â³Ã™Å Ã˜Â§Ã˜Â³Ã˜Â© Ã˜Â§Ã™â€žÃ˜Â®Ã˜ÂµÃ™Ë†Ã˜ÂµÃ™Å Ã˜Â©</a> Ã‚Â· 
        <a href="{$safeAppUrl}/terms">Ã˜Â§Ã™â€žÃ˜Â´Ã˜Â±Ã™Ë†Ã˜Â· Ã™Ë†Ã˜Â§Ã™â€žÃ˜Â£Ã˜Â­Ã™Æ’Ã˜Â§Ã™â€¦</a>
      </p>
      <p>Ã‚Â© 2024 RankX SEO - Ã˜Â¬Ã™â€¦Ã™Å Ã˜Â¹ Ã˜Â§Ã™â€žÃ˜Â­Ã™â€šÃ™Ë†Ã™â€š Ã™â€¦Ã˜Â­Ã™ÂÃ™Ë†Ã˜Â¸Ã˜Â©</p>
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
        $logoSrc = '/assets/rankxseo-logo.png';
        $faviconSrc = 'https://rankxseo.com/favicon.png';
        $loginHref = $safeAppUrl . '/login';

        $html = <<<HTML
<!doctype html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" type="image/png" href="{$faviconSrc}">
  <link rel="apple-touch-icon" href="{$faviconSrc}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&display=swap">
  <title>Ã˜Â§Ã™â€žÃ˜Â£Ã˜Â³Ã˜Â¦Ã™â€žÃ˜Â© Ã˜Â§Ã™â€žÃ˜Â´Ã˜Â§Ã˜Â¦Ã˜Â¹Ã˜Â© | RankX SEO</title>
  <meta name="description" content="Ã˜Â¥Ã˜Â¬Ã˜Â§Ã˜Â¨Ã˜Â§Ã˜Âª Ã˜Â´Ã˜Â§Ã™â€¦Ã™â€žÃ˜Â© Ã˜Â¹Ã™â€žÃ™â€° Ã˜Â£Ã™â€¡Ã™â€¦ Ã˜Â§Ã™â€žÃ˜Â£Ã˜Â³Ã˜Â¦Ã™â€žÃ˜Â© Ã˜Â­Ã™Ë†Ã™â€ž RankX SEO - Ã˜ÂªÃ˜Â­Ã˜Â³Ã™Å Ã™â€  Ã™â€¦Ã˜Â­Ã˜ÂªÃ™Ë†Ã™â€° Ã™â€¦Ã˜ÂªÃ˜Â§Ã˜Â¬Ã˜Â± Ã˜Â³Ã™â€žÃ˜Â© Ã˜Â¨Ã˜Â§Ã˜Â³Ã˜ÂªÃ˜Â®Ã˜Â¯Ã˜Â§Ã™â€¦ Ã˜Â§Ã™â€žÃ˜Â°Ã™Æ’Ã˜Â§Ã˜Â¡ Ã˜Â§Ã™â€žÃ˜Â§Ã˜ÂµÃ˜Â·Ã™â€ Ã˜Â§Ã˜Â¹Ã™Å .">
  <link rel="canonical" href="{$safeAppUrl}/faq">
  <style>
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
        <img src="{$logoSrc}" alt="RankX SEO" width="1200" height="400" decoding="async">
      </div>

      <h1>Ã˜Â§Ã™â€žÃ˜Â£Ã˜Â³Ã˜Â¦Ã™â€žÃ˜Â© Ã˜Â§Ã™â€žÃ˜Â´Ã˜Â§Ã˜Â¦Ã˜Â¹Ã˜Â©</h1>
      <p class="intro">Ã˜Â¥Ã˜Â¬Ã˜Â§Ã˜Â¨Ã˜Â§Ã˜Âª Ã˜Â´Ã˜Â§Ã™â€¦Ã™â€žÃ˜Â© Ã˜Â¹Ã™â€žÃ™â€° Ã˜Â£Ã™Æ’Ã˜Â«Ã˜Â± Ã˜Â§Ã™â€žÃ˜Â£Ã˜Â³Ã˜Â¦Ã™â€žÃ˜Â© Ã˜Â´Ã™Å Ã™Ë†Ã˜Â¹Ã™â€¹Ã˜Â§ Ã˜Â­Ã™Ë†Ã™â€ž RankX SEO Ã™Ë†Ã™Æ’Ã™Å Ã™ÂÃ™Å Ã˜Â© Ã˜Â§Ã˜Â³Ã˜ÂªÃ˜Â®Ã˜Â¯Ã˜Â§Ã™â€¦Ã™â€¡Ã˜Â§.</p>

      <div class="search-box">
        <span class="icon">Ã°Å¸â€Â</span>
        <input type="text" id="faqSearch" placeholder="Ã˜Â§Ã˜Â¨Ã˜Â­Ã˜Â« Ã™ÂÃ™Å  Ã˜Â§Ã™â€žÃ˜Â£Ã˜Â³Ã˜Â¦Ã™â€žÃ˜Â©..." onkeyup="filterFAQs()">
      </div>

      <div class="category" data-category="general">
        <div class="category-title">
          <div class="icon">Ã°Å¸ÂÂ </div>
          <h2>Ã™â€¦Ã˜Â¹Ã™â€žÃ™Ë†Ã™â€¦Ã˜Â§Ã˜Âª Ã˜Â¹Ã˜Â§Ã™â€¦Ã˜Â©</h2>
        </div>
        <div class="faq-list">
          <details>
            <summary>Ã™â€¦Ã˜Â§ Ã™â€¡Ã™Å  RankX SEOÃ˜Å¸</summary>
            <div class="answer">
              RankX SEO Ã™â€¡Ã™Å  Ã™â€¦Ã™â€ Ã˜ÂµÃ˜Â© Ã™â€¦Ã˜ÂªÃ˜Â®Ã˜ÂµÃ˜ÂµÃ˜Â© Ã™ÂÃ™Å  Ã˜ÂªÃ˜Â­Ã˜Â³Ã™Å Ã™â€  Ã™â€¦Ã˜Â­Ã˜ÂªÃ™Ë†Ã™â€° Ã™â€¦Ã˜ÂªÃ˜Â§Ã˜Â¬Ã˜Â± Ã˜Â³Ã™â€žÃ˜Â© Ã˜Â¨Ã˜Â§Ã˜Â³Ã˜ÂªÃ˜Â®Ã˜Â¯Ã˜Â§Ã™â€¦ Ã˜Â§Ã™â€žÃ˜Â°Ã™Æ’Ã˜Â§Ã˜Â¡ Ã˜Â§Ã™â€žÃ˜Â§Ã˜ÂµÃ˜Â·Ã™â€ Ã˜Â§Ã˜Â¹Ã™Å . Ã™â€ Ã™â€šÃ˜Â¯Ã™â€¦ Ã˜Â£Ã˜Â¯Ã™Ë†Ã˜Â§Ã˜Âª Ã™â€¦Ã˜ÂªÃ™Æ’Ã˜Â§Ã™â€¦Ã™â€žÃ˜Â© Ã™â€žÃ˜ÂªÃ˜Â­Ã˜Â³Ã™Å Ã™â€  Ã™Ë†Ã˜ÂµÃ™Â Ã˜Â§Ã™â€žÃ™â€¦Ã™â€ Ã˜ÂªÃ˜Â¬Ã˜Â§Ã˜ÂªÃ˜Å’ Meta TagsÃ˜Å’ ALT Ã˜Â§Ã™â€žÃ˜ÂµÃ™Ë†Ã˜Â±Ã˜Å’ Ã™Ë†Ã˜ÂªÃ˜Â­Ã™â€žÃ™Å Ã™â€ž Ã˜Â§Ã™â€žÃ™Æ’Ã™â€žÃ™â€¦Ã˜Â§Ã˜Âª Ã˜Â§Ã™â€žÃ™â€¦Ã™ÂÃ˜ÂªÃ˜Â§Ã˜Â­Ã™Å Ã˜Â© Ã™Ë†Ã˜Â§Ã™â€žÃ™â€¦Ã™â€ Ã˜Â§Ã™ÂÃ˜Â³Ã™Å Ã™â€  - Ã™Æ’Ã™â€ž Ã˜Â°Ã™â€žÃ™Æ’ Ã™â€¦Ã™â€  Ã™â€žÃ™Ë†Ã˜Â­Ã˜Â© Ã™Ë†Ã˜Â§Ã˜Â­Ã˜Â¯Ã˜Â© Ã˜Â³Ã™â€¡Ã™â€žÃ˜Â© Ã˜Â§Ã™â€žÃ˜Â§Ã˜Â³Ã˜ÂªÃ˜Â®Ã˜Â¯Ã˜Â§Ã™â€¦.
            </div>
          </details>
          <details>
            <summary>Ã™â€žÃ™â€¦Ã™â€  Ã˜ÂµÃ™ÂÃ™â€¦Ã™â€¦Ã˜Âª Ã™â€¡Ã˜Â°Ã™â€¡ Ã˜Â§Ã™â€žÃ™â€¦Ã™â€ Ã˜ÂµÃ˜Â©Ã˜Å¸</summary>
            <div class="answer">
              Ã˜ÂµÃ™ÂÃ™â€¦Ã™â€¦Ã˜Âª RankX SEO Ã˜Â®Ã˜ÂµÃ™Å Ã˜ÂµÃ™â€¹Ã˜Â§ Ã™â€žÃ˜Â£Ã˜ÂµÃ˜Â­Ã˜Â§Ã˜Â¨ Ã™â€¦Ã˜ÂªÃ˜Â§Ã˜Â¬Ã˜Â± Ã˜Â³Ã™â€žÃ˜Â© (Salla) Ã˜Â³Ã™Ë†Ã˜Â§Ã˜Â¡ Ã™Æ’Ã˜Â§Ã™â€ Ã™Ë†Ã˜Â§:<br>
              <ul>
                <li>Ã˜ÂªÃ˜Â¬Ã˜Â§Ã˜Â± Ã™Å Ã˜Â¯Ã™Å Ã˜Â±Ã™Ë†Ã™â€  Ã™â€¦Ã˜ÂªÃ˜Â¬Ã˜Â±Ã™â€¹Ã˜Â§ Ã™Ë†Ã˜Â§Ã˜Â­Ã˜Â¯Ã™â€¹Ã˜Â§ Ã˜Â£Ã™Ë† Ã˜Â¹Ã˜Â¯Ã˜Â© Ã™â€¦Ã˜ÂªÃ˜Â§Ã˜Â¬Ã˜Â±</li>
                <li>Ã˜Â´Ã˜Â±Ã™Æ’Ã˜Â§Ã˜Âª Ã˜ÂªÃ™â€šÃ˜Â¯Ã™â€¦ Ã˜Â®Ã˜Â¯Ã™â€¦Ã˜Â§Ã˜Âª SEO Ã™â€žÃ˜Â¹Ã™â€¦Ã™â€žÃ˜Â§Ã˜Â¦Ã™â€¡Ã˜Â§</li>
                <li>Ã™â€¦Ã˜ÂªÃ˜Â§Ã˜Â¬Ã˜Â± Ã™Æ’Ã˜Â¨Ã™Å Ã˜Â±Ã˜Â© Ã˜ÂªÃ˜Â­Ã˜ÂªÃ˜Â§Ã˜Â¬ Ã™â€žÃ˜ÂªÃ˜Â­Ã˜Â³Ã™Å Ã™â€  Ã™â€¦Ã˜Â¦Ã˜Â§Ã˜Âª Ã˜Â£Ã™Ë† Ã˜Â¢Ã™â€žÃ˜Â§Ã™Â Ã˜Â§Ã™â€žÃ™â€¦Ã™â€ Ã˜ÂªÃ˜Â¬Ã˜Â§Ã˜Âª</li>
              </ul>
            </div>
          </details>
          <details>
            <summary>Ã™â€¡Ã™â€ž Ã˜Â£Ã˜Â­Ã˜ÂªÃ˜Â§Ã˜Â¬ Ã˜Â®Ã˜Â¨Ã˜Â±Ã˜Â© Ã˜ÂªÃ™â€šÃ™â€ Ã™Å Ã˜Â© Ã™â€žÃ˜Â§Ã˜Â³Ã˜ÂªÃ˜Â®Ã˜Â¯Ã˜Â§Ã™â€¦ Ã˜Â§Ã™â€žÃ™â€¦Ã™â€ Ã˜ÂµÃ˜Â©Ã˜Å¸</summary>
            <div class="answer">
              <strong>Ã™â€žÃ˜Â§!</strong> Ã˜ÂµÃ™ÂÃ™â€¦Ã™â€¦Ã˜Âª Ã˜Â§Ã™â€žÃ™â€¦Ã™â€ Ã˜ÂµÃ˜Â© Ã™â€žÃ˜ÂªÃ™Æ’Ã™Ë†Ã™â€  Ã˜Â³Ã™â€¡Ã™â€žÃ˜Â© Ã˜Â§Ã™â€žÃ˜Â§Ã˜Â³Ã˜ÂªÃ˜Â®Ã˜Â¯Ã˜Â§Ã™â€¦ Ã˜Â­Ã˜ÂªÃ™â€° Ã™â€žÃ™â€žÃ™â€¦Ã˜Â¨Ã˜ÂªÃ˜Â¯Ã˜Â¦Ã™Å Ã™â€ . Ã™â€žÃ˜Â§ Ã˜ÂªÃ˜Â­Ã˜ÂªÃ˜Â§Ã˜Â¬ Ã˜Â£Ã™Å  Ã˜Â®Ã™â€žÃ™ÂÃ™Å Ã˜Â© Ã˜ÂªÃ™â€šÃ™â€ Ã™Å Ã˜Â© Ã˜Â£Ã™Ë† Ã˜Â®Ã˜Â¨Ã˜Â±Ã˜Â© Ã™ÂÃ™Å  SEO. Ã˜Â§Ã™â€žÃ™Ë†Ã˜Â§Ã˜Â¬Ã™â€¡Ã˜Â© Ã˜Â¹Ã˜Â±Ã˜Â¨Ã™Å Ã˜Â© Ã˜Â¨Ã˜Â§Ã™â€žÃ™Æ’Ã˜Â§Ã™â€¦Ã™â€ž Ã™â€¦Ã˜Â¹ Ã˜Â´Ã˜Â±Ã˜Â­ Ã™Ë†Ã˜Â§Ã˜Â¶Ã˜Â­ Ã™â€žÃ™Æ’Ã™â€ž Ã˜Â®Ã˜Â·Ã™Ë†Ã˜Â©.
            </div>
          </details>
        </div>
      </div>

      <div class="category" data-category="services">
        <div class="category-title">
          <div class="icon">Ã¢Å¡â„¢Ã¯Â¸Â</div>
          <h2>Ã˜Â§Ã™â€žÃ˜Â®Ã˜Â¯Ã™â€¦Ã˜Â§Ã˜Âª Ã™Ë†Ã˜Â§Ã™â€žÃ™â€¦Ã™Å Ã˜Â²Ã˜Â§Ã˜Âª</h2>
        </div>
        <div class="faq-list">
          <details>
            <summary>Ã™â€¦Ã˜Â§ Ã˜Â§Ã™â€žÃ˜Â®Ã˜Â¯Ã™â€¦Ã˜Â§Ã˜Âª Ã˜Â§Ã™â€žÃ˜ÂªÃ™Å  Ã˜ÂªÃ™â€šÃ˜Â¯Ã™â€¦Ã™â€¡Ã˜Â§ Ã˜Â§Ã™â€žÃ™â€¦Ã™â€ Ã˜ÂµÃ˜Â©Ã˜Å¸</summary>
            <div class="answer">
              Ã™â€ Ã™â€šÃ˜Â¯Ã™â€¦ 8 Ã˜Â®Ã˜Â¯Ã™â€¦Ã˜Â§Ã˜Âª Ã˜Â£Ã˜Â³Ã˜Â§Ã˜Â³Ã™Å Ã˜Â©:
              <ul>
                <li><strong>Ã˜ÂªÃ˜Â­Ã˜Â³Ã™Å Ã™â€  Ã™Ë†Ã˜ÂµÃ™Â Ã˜Â§Ã™â€žÃ™â€¦Ã™â€ Ã˜ÂªÃ˜Â¬Ã˜Â§Ã˜Âª:</strong> Ã˜ÂµÃ™Å Ã˜Â§Ã˜ÂºÃ˜Â© Ã˜Â£Ã™Ë†Ã˜ÂµÃ˜Â§Ã™Â Ã˜Â§Ã˜Â­Ã˜ÂªÃ˜Â±Ã˜Â§Ã™ÂÃ™Å Ã˜Â© Ã™â€¦Ã˜ÂªÃ™Ë†Ã˜Â§Ã™ÂÃ™â€š Ã™â€¦Ã˜Â¹ SEO</li>
                <li><strong>Ã˜ÂªÃ˜Â­Ã˜Â³Ã™Å Ã™â€  SEO Ã˜Â§Ã™â€žÃ™â€¦Ã™â€ Ã˜ÂªÃ˜Â¬:</strong> Meta Title Ã™Ë† Meta Description Ã™â€¦Ã˜Â­Ã˜Â³Ã™â€˜Ã™â€ Ã˜Â©</li>
                <li><strong>Ã˜ÂªÃ˜Â­Ã˜Â³Ã™Å Ã™â€  SEO Ã˜Â§Ã™â€žÃ™â€¦Ã˜Â§Ã˜Â±Ã™Æ’Ã˜Â§Ã˜Âª:</strong> Ã™Ë†Ã˜ÂµÃ™Â Ã™Ë† Meta Tags Ã™â€žÃ™â€žÃ™â€¦Ã˜Â§Ã˜Â±Ã™Æ’Ã˜Â§Ã˜Âª Ã˜Â§Ã™â€žÃ˜ÂªÃ˜Â¬Ã˜Â§Ã˜Â±Ã™Å Ã˜Â©</li>
                <li><strong>ALT Ã™â€žÃ™â€žÃ˜ÂµÃ™Ë†Ã˜Â±:</strong> Ã˜Â¥Ã™â€ Ã˜Â´Ã˜Â§Ã˜Â¡ Ã™â€ Ã˜ÂµÃ™Ë†Ã˜Âµ Ã˜Â¨Ã˜Â¯Ã™Å Ã™â€žÃ˜Â© Ã˜Â§Ã˜Â­Ã˜ÂªÃ˜Â±Ã˜Â§Ã™ÂÃ™Å Ã˜Â© Ã™â€žÃ™â€žÃ˜ÂµÃ™Ë†Ã˜Â±</li>
                <li><strong>Ã˜ÂªÃ˜Â­Ã˜Â³Ã™Å Ã™â€  ALT Ã˜Â¬Ã™â€¦Ã˜Â§Ã˜Â¹Ã™Å :</strong> Ã˜ÂªÃ˜Â­Ã˜Â³Ã™Å Ã™â€  Ã˜Â¬Ã™â€¦Ã™Å Ã˜Â¹ Ã˜ÂµÃ™Ë†Ã˜Â± Ã™â€¦Ã™â€ Ã˜ÂªÃ˜Â¬ Ã™Ë†Ã˜Â§Ã˜Â­Ã˜Â¯ Ã˜Â¯Ã™ÂÃ˜Â¹Ã˜Â© Ã™Ë†Ã˜Â§Ã˜Â­Ã˜Â¯Ã˜Â©</li>
                <li><strong>Ã˜ÂªÃ˜Â­Ã™â€žÃ™Å Ã™â€ž Ã˜Â§Ã™â€žÃ™Æ’Ã™â€žÃ™â€¦Ã˜Â§Ã˜Âª Ã˜Â§Ã™â€žÃ™â€¦Ã™ÂÃ˜ÂªÃ˜Â§Ã˜Â­Ã™Å Ã˜Â©:</strong> Ã˜Â¨Ã˜Â­Ã˜Â« Ã˜Â´Ã˜Â§Ã™â€¦Ã™â€ž Ã˜Â¹Ã™â€  Ã˜Â£Ã™ÂÃ˜Â¶Ã™â€ž Ã˜Â§Ã™â€žÃ™Æ’Ã™â€žÃ™â€¦Ã˜Â§Ã˜Âª Ã™â€žÃ™ÂÃ˜Â§Ã˜Â¦Ã˜Â¯Ã˜ÂªÃ™Æ’</li>
                <li><strong>Ã˜ÂªÃ˜Â­Ã™â€žÃ™Å Ã™â€ž Ã˜Â³Ã™Å Ã™Ë† Ã˜Â§Ã™â€žÃ˜Â¯Ã™Ë†Ã™â€¦Ã™Å Ã™â€ :</strong> Ã˜ÂªÃ˜Â­Ã™â€žÃ™Å Ã™â€ž Ã˜Â´Ã˜Â§Ã™â€¦Ã™â€ž Ã™â€žÃ™â€¦Ã™Ë†Ã™â€šÃ˜Â¹Ã™Æ’ Ã™Ë†Ã™â€¦Ã™â€ Ã˜Â§Ã™ÂÃ˜Â³Ã™Å Ã™Æ’</li>
                <li><strong>Ã˜Â³Ã™Å Ã™Ë† Ã˜Â§Ã™â€žÃ™â€¦Ã˜ÂªÃ˜Â¬Ã˜Â±:</strong> Ã˜ÂªÃ˜Â­Ã˜Â³Ã™Å Ã™â€  Ã˜Â¥Ã˜Â¹Ã˜Â¯Ã˜Â§Ã˜Â¯Ã˜Â§Ã˜Âª SEO Ã˜Â§Ã™â€žÃ˜Â¹Ã˜Â§Ã™â€¦Ã˜Â© Ã™â€žÃ™â€žÃ™â€¦Ã˜ÂªÃ˜Â¬Ã˜Â±</li>
              </ul>
            </div>
          </details>
          <details>
            <summary>Ã™Æ’Ã™Å Ã™Â Ã™Å Ã˜Â¹Ã™â€¦Ã™â€ž Ã˜ÂªÃ˜Â­Ã˜Â³Ã™Å Ã™â€  Ã˜Â§Ã™â€žÃ™Ë†Ã˜ÂµÃ™ÂÃ˜Å¸</summary>
            <div class="answer">
              Ã˜Â§Ã™â€žÃ˜Â¹Ã™â€¦Ã™â€žÃ™Å Ã˜Â© Ã˜Â¨Ã˜Â³Ã™Å Ã˜Â·Ã˜Â©:<br>
              <ol style="margin:12px 0;padding-right:24px">
                <li>Ã˜Â§Ã˜Â®Ã˜ÂªÃ˜Â± Ã˜Â§Ã™â€žÃ™â€¦Ã™â€ Ã˜ÂªÃ˜Â¬ Ã˜Â§Ã™â€žÃ˜Â°Ã™Å  Ã˜ÂªÃ˜Â±Ã™Å Ã˜Â¯ Ã˜ÂªÃ˜Â­Ã˜Â³Ã™Å Ã™â€ Ã™â€¡</li>
                <li>Ã˜Â§Ã˜Â¶Ã˜ÂºÃ˜Â· "Ã˜ÂªÃ˜Â­Ã˜Â³Ã™Å Ã™â€  Ã˜Â§Ã™â€žÃ™â€¦Ã˜Â­Ã˜ÂªÃ™Ë†Ã™â€°"</li>
                <li>Ã˜Â³Ã™Å Ã™â€šÃ™Ë†Ã™â€¦ Ã˜Â§Ã™â€žÃ˜Â°Ã™Æ’Ã˜Â§Ã˜Â¡ Ã˜Â§Ã™â€žÃ˜Â§Ã˜ÂµÃ˜Â·Ã™â€ Ã˜Â§Ã˜Â¹Ã™Å  Ã˜Â¨Ã˜ÂªÃ˜Â­Ã™â€žÃ™Å Ã™â€ž Ã˜Â§Ã™â€žÃ™â€¦Ã™â€ Ã˜ÂªÃ˜Â¬ Ã™Ë†Ã˜Â¥Ã™â€ Ã˜Â´Ã˜Â§Ã˜Â¡ Ã™Ë†Ã˜ÂµÃ™Â Ã™â€¦Ã˜Â­Ã˜Â³Ã™â€˜Ã™â€ </li>
                <li>Ã˜Â±Ã˜Â§Ã˜Â¬Ã˜Â¹ Ã˜Â§Ã™â€žÃ™Ë†Ã˜ÂµÃ™Â Ã˜Â§Ã™â€žÃ™â€¦Ã˜Â¹Ã˜Â¯Ã™â€ž Ã™Ë†Ã˜Â¹Ã˜Â¯Ã™â€˜Ã™â€žÃ™â€¡ Ã™Å Ã˜Â¯Ã™Ë†Ã™Å Ã™â€¹Ã˜Â§ Ã˜Â¥Ã˜Â°Ã˜Â§ Ã˜Â±Ã˜ÂºÃ˜Â¨Ã˜Âª</li>
                <li>Ã˜Â§Ã˜Â­Ã™ÂÃ˜Â¸ Ã˜Â§Ã™â€žÃ˜ÂªÃ˜ÂºÃ™Å Ã™Å Ã˜Â±Ã˜Â§Ã˜Âª Ã™Ë†Ã˜Â³Ã™Å Ã˜ÂªÃ™â€¦ Ã˜ÂªÃ˜Â­Ã˜Â¯Ã™Å Ã˜Â« Ã˜Â§Ã™â€žÃ™â€¦Ã™â€ Ã˜ÂªÃ˜Â¬ Ã™ÂÃ™Å  Ã™â€¦Ã˜ÂªÃ˜Â¬Ã˜Â±Ã™Æ’ Ã˜ÂªÃ™â€žÃ™â€šÃ˜Â§Ã˜Â¦Ã™Å Ã™â€¹Ã˜Â§</li>
              </ol>
            </div>
          </details>
          <details>
            <summary>Ã™â€¡Ã™â€ž Ã™Å Ã™â€¦Ã™Æ’Ã™â€ Ã™â€ Ã™Å  Ã˜ÂªÃ˜Â¹Ã˜Â¯Ã™Å Ã™â€ž Ã˜Â§Ã™â€žÃ™â€ Ã˜Âµ Ã™â€šÃ˜Â¨Ã™â€ž Ã˜Â§Ã™â€žÃ˜Â­Ã™ÂÃ˜Â¸Ã˜Å¸</summary>
            <div class="answer">
              <strong>Ã™â€ Ã˜Â¹Ã™â€¦!</strong> Ã™Æ’Ã™â€ž Ã™â€ Ã˜Â§Ã˜ÂªÃ˜Â¬ Ã™â€¦Ã™â€  Ã˜Â§Ã™â€žÃ˜Â°Ã™Æ’Ã˜Â§Ã˜Â¡ Ã˜Â§Ã™â€žÃ˜Â§Ã˜ÂµÃ˜Â·Ã™â€ Ã˜Â§Ã˜Â¹Ã™Å  Ã™â€šÃ˜Â§Ã˜Â¨Ã™â€ž Ã™â€žÃ™â€žÃ˜ÂªÃ˜Â¹Ã˜Â¯Ã™Å Ã™â€ž Ã™â€šÃ˜Â¨Ã™â€ž Ã˜Â§Ã™â€žÃ˜Â­Ã™ÂÃ˜Â¸. Ã™Å Ã™â€¦Ã™Æ’Ã™â€ Ã™Æ’:
              <ul>
                <li>Ã˜ÂªÃ˜Â¹Ã˜Â¯Ã™Å Ã™â€ž Ã˜Â§Ã™â€žÃ™Ë†Ã˜ÂµÃ™Â Ã™â€¦Ã˜Â¨Ã˜Â§Ã˜Â´Ã˜Â±Ã˜Â© Ã™ÂÃ™Å  Ã˜Â§Ã™â€žÃ™â€¦Ã˜Â­Ã˜Â±Ã˜Â±</li>
                <li>Ã˜Â¥Ã˜Â¶Ã˜Â§Ã™ÂÃ˜Â© Ã˜Â£Ã™Ë† Ã˜Â­Ã˜Â°Ã™Â Ã˜Â£Ã™Å  Ã˜Â¬Ã˜Â²Ã˜Â¡</li>
                <li>Ã˜ÂªÃ˜ÂºÃ™Å Ã™Å Ã˜Â± Ã˜Â§Ã™â€žÃ™â€ Ã˜Â¨Ã˜Â±Ã˜Â© Ã˜Â¨Ã™Å Ã™â€  Ã˜Â§Ã˜Â­Ã˜ÂªÃ˜Â±Ã˜Â§Ã™ÂÃ™Å Ã˜Â©Ã˜Å’ Ã™ÂÃ˜Â®Ã˜Â§Ã™â€¦Ã˜Â©Ã˜Å’ Ã˜Â¨Ã˜Â³Ã˜Â§Ã˜Â·Ã˜Â©Ã˜Å’ Ã˜Â£Ã™Ë† Ã™â€¦Ã˜Â¨Ã˜Â§Ã˜Â´Ã˜Â±Ã˜Â©</li>
                <li>Ã˜Â§Ã˜Â®Ã˜ÂªÃ™Å Ã˜Â§Ã˜Â± Ã™â€žÃ˜ÂºÃ˜Â© Ã˜Â§Ã™â€žÃ˜Â¥Ã˜Â®Ã˜Â±Ã˜Â§Ã˜Â¬ (Ã˜Â¹Ã˜Â±Ã˜Â¨Ã™Å  Ã˜Â£Ã™Ë† Ã˜Â¥Ã™â€ Ã˜Â¬Ã™â€žÃ™Å Ã˜Â²Ã™Å )</li>
              </ul>
            </div>
          </details>
          <details>
            <summary>Ã™â€¦Ã˜Â§ Ã™â€¦Ã˜Â¹Ã™â€ Ã™â€° "Ã˜ÂªÃ˜Â¹Ã™â€žÃ™Å Ã™â€¦Ã˜Â§Ã˜Âª Ã˜Â§Ã™â€žÃ™â€¦Ã˜ÂªÃ˜Â¬Ã˜Â±"Ã˜Å¸</summary>
            <div class="answer">
              Ã™â€¡Ã™Å  Ã˜Â¥Ã˜Â±Ã˜Â´Ã˜Â§Ã˜Â¯Ã˜Â§Ã˜Âª Ã™â€¦Ã˜Â®Ã˜ÂµÃ˜ÂµÃ˜Â© Ã˜ÂªÃ˜Â¶Ã™Å Ã™ÂÃ™â€¡Ã˜Â§ Ã™â€žÃ™â€¦Ã˜ÂªÃ˜Â¬Ã˜Â±Ã™Æ’ Ã™Å Ã˜Â®Ã˜Â¨Ã˜Â± Ã˜Â¨Ã™â€¡Ã˜Â§ Ã˜Â§Ã™â€žÃ˜Â°Ã™Æ’Ã˜Â§Ã˜Â¡ Ã˜Â§Ã™â€žÃ˜Â§Ã˜ÂµÃ˜Â·Ã™â€ Ã˜Â§Ã˜Â¹Ã™Å  Ã˜Â¹Ã™â€  Ã˜Â´Ã˜Â®Ã˜ÂµÃ™Å Ã˜Â© Ã™â€¦Ã˜ÂªÃ˜Â¬Ã˜Â±Ã™Æ’ Ã™Ë†Ã˜Â£Ã˜Â³Ã™â€žÃ™Ë†Ã˜Â¨Ã™â€¡. Ã™â€¦Ã˜Â«Ã™â€žÃ˜Â§Ã™â€¹:
              <ul>
                <li>"Ã™â€ Ã˜Â­Ã™â€  Ã™â€¦Ã˜ÂªÃ˜Â¬Ã˜Â± Ã™Å Ã˜Â¨Ã™Å Ã˜Â¹ Ã™â€¦Ã™â€ Ã˜ÂªÃ˜Â¬Ã˜Â§Ã˜Âª Ã™ÂÃ˜Â§Ã˜Â®Ã˜Â±Ã˜Â© Ã˜Â¨Ã˜Â£Ã˜Â³Ã˜Â¹Ã˜Â§Ã˜Â± Ã™â€¦Ã™â€ Ã˜Â§Ã˜Â³Ã˜Â¨Ã˜Â©"</li>
                <li>"Ã™â€ Ã˜Â±Ã™Æ’Ã˜Â² Ã˜Â¹Ã™â€žÃ™â€° Ã˜Â§Ã™â€žÃ˜Â¬Ã™Ë†Ã˜Â¯Ã˜Â© Ã™Ë†Ã˜Â§Ã™â€žÃ˜Â®Ã˜Â¯Ã™â€¦Ã˜Â©"</li>
                <li>"Ã˜Â£Ã˜Â³Ã™â€žÃ™Ë†Ã˜Â¨Ã™â€ Ã˜Â§ Ã™Ë†Ã˜Â¯Ã™Ë†Ã˜Â¯ Ã™Ë†Ã™â€¦Ã˜Â¨Ã˜Â§Ã˜Â´Ã˜Â±"</li>
              </ul>
              Ã™â€¡Ã˜Â°Ã™â€¡ Ã˜Â§Ã™â€žÃ˜ÂªÃ˜Â¹Ã™â€žÃ™Å Ã™â€¦Ã˜Â§Ã˜Âª Ã˜ÂªÃ˜Â³Ã˜Â§Ã˜Â¹Ã˜Â¯ AI Ã˜Â¹Ã™â€žÃ™â€° Ã˜Â¥Ã™â€ Ã˜ÂªÃ˜Â§Ã˜Â¬ Ã™â€¦Ã˜Â­Ã˜ÂªÃ™Ë†Ã™â€° Ã˜Â£Ã™Æ’Ã˜Â«Ã˜Â± Ã˜ÂªÃ™â€¦Ã˜Â§Ã˜Â´Ã™Å Ã™â€¹Ã˜Â§ Ã™â€¦Ã˜Â¹ Ã™â€¡Ã™Ë†Ã™Å Ã˜Â© Ã™â€¦Ã˜ÂªÃ˜Â¬Ã˜Â±Ã™Æ’.
            </div>
          </details>
        </div>
      </div>

      <div class="category" data-category="billing">
        <div class="category-title">
          <div class="icon">Ã°Å¸â€™Â³</div>
          <h2>Ã˜Â§Ã™â€žÃ˜Â§Ã˜Â´Ã˜ÂªÃ˜Â±Ã˜Â§Ã™Æ’Ã˜Â§Ã˜Âª Ã™Ë†Ã˜Â§Ã™â€žÃ˜Â£Ã˜Â³Ã˜Â¹Ã˜Â§Ã˜Â±</h2>
        </div>
        <div class="faq-list">
          <details>
            <summary>Ã™Æ’Ã™Å Ã™Â Ã˜Â£Ã˜Â­Ã˜ÂµÃ™â€ž Ã˜Â¹Ã™â€žÃ™â€° Ã˜Â§Ã˜Â´Ã˜ÂªÃ˜Â±Ã˜Â§Ã™Æ’Ã˜Å¸</summary>
            <div class="answer">
              Ã˜Â¹Ã™â€ Ã˜Â¯ Ã˜Â±Ã˜Â¨Ã˜Â· Ã™â€¦Ã˜ÂªÃ˜Â¬Ã˜Â±Ã™Æ’ Ã˜Â§Ã™â€žÃ˜Â£Ã™Ë†Ã™â€ž Ã™â€¦Ã˜Â¹ RankX SEOÃ˜Å’ Ã˜Â³Ã˜ÂªÃ˜Â­Ã˜ÂµÃ™â€ž Ã˜ÂªÃ™â€žÃ™â€šÃ˜Â§Ã˜Â¦Ã™Å Ã™â€¹Ã˜Â§ Ã˜Â¹Ã™â€žÃ™â€° <strong>Ã™ÂÃ˜ÂªÃ˜Â±Ã˜Â© Ã˜ÂªÃ˜Â¬Ã˜Â±Ã™Å Ã˜Â¨Ã™Å Ã˜Â© Ã™â€¦Ã˜Â¬Ã˜Â§Ã™â€ Ã™Å Ã˜Â©</strong> Ã™â€žÃ˜ÂªÃ˜Â¬Ã˜Â±Ã˜Â¨Ã˜Â© Ã˜Â§Ã™â€žÃ™â€¦Ã™â€ Ã˜ÂµÃ˜Â©. Ã˜Â¨Ã˜Â¹Ã˜Â¯Ã™â€¡Ã˜Â§ Ã™Å Ã™â€¦Ã™Æ’Ã™â€ Ã™Æ’ Ã˜Â§Ã˜Â®Ã˜ÂªÃ™Å Ã˜Â§Ã˜Â± Ã˜Â¨Ã˜Â§Ã™â€šÃ˜Â© Ã˜Â§Ã˜Â´Ã˜ÂªÃ˜Â±Ã˜Â§Ã™Æ’ Ã˜ÂªÃ™â€ Ã˜Â§Ã˜Â³Ã˜Â¨ Ã˜Â§Ã˜Â­Ã˜ÂªÃ™Å Ã˜Â§Ã˜Â¬Ã˜Â§Ã˜ÂªÃ™Æ’.
            </div>
          </details>
          <details>
            <summary>Ã™â€¦Ã˜Â§ Ã˜Â§Ã™â€žÃ™ÂÃ˜Â±Ã™â€š Ã˜Â¨Ã™Å Ã™â€  Ã˜Â§Ã™â€žÃ˜Â¨Ã˜Â§Ã™â€šÃ˜Â§Ã˜Âª Ã˜Â§Ã™â€žÃ™â€¦Ã˜ÂªÃ™Ë†Ã™ÂÃ˜Â±Ã˜Â©Ã˜Å¸</summary>
            <div class="answer">
              Ã™â€ Ã™â€šÃ˜Â¯Ã™â€¦ 4 Ã˜Â¨Ã˜Â§Ã™â€šÃ˜Â§Ã˜Âª Ã˜ÂªÃ™â€ Ã˜Â§Ã˜Â³Ã˜Â¨ Ã˜Â§Ã˜Â­Ã˜ÂªÃ™Å Ã˜Â§Ã˜Â¬Ã˜Â§Ã˜Âª Ã™â€¦Ã˜Â®Ã˜ÂªÃ™â€žÃ™ÂÃ˜Â©:
              <ul>
                <li><strong>Ã°Å¸Å¸Â¢ Ã˜Â§Ã™â€žÃ˜ÂªÃ˜Â¬Ã˜Â±Ã˜Â¨Ã˜Â© Ã˜Â§Ã™â€žÃ˜Â§Ã™â€šÃ˜ÂªÃ˜ÂµÃ˜Â§Ã˜Â¯Ã™Å Ã˜Â© (5 Ã˜Â±.Ã˜Â³/Ã˜Â´Ã™â€¡Ã˜Â±):</strong> Ã™â€žÃ™â€žÃ™â€¦ Ã˜ÂªÃ˜Â¬Ã˜Â±Ã˜Â¨Ã˜Â© Ã˜Â§Ã™â€žÃ™â€¦Ã™â€ Ã˜ÂµÃ˜Â©
                  <br>10 Ã˜ÂªÃ˜Â­Ã˜Â³Ã™Å Ã™â€  Ã™Ë†Ã˜ÂµÃ™Â | 10 Ã˜ÂªÃ˜Â­Ã˜Â³Ã™Å Ã™â€  SEO | 10 ALT Ã˜ÂµÃ™Ë†Ã˜Â± | 5 Ã™Æ’Ã™â€žÃ™â€¦Ã˜Â§Ã˜Âª Ã™â€¦Ã™ÂÃ˜ÂªÃ˜Â§Ã˜Â­Ã™Å Ã˜Â© | 1 Ã˜ÂªÃ˜Â­Ã™â€žÃ™Å Ã™â€ž Ã˜Â¯Ã™Ë†Ã™â€¦Ã™Å Ã™â€  | 5 Ã˜ÂªÃ˜Â­Ã˜Â³Ã™Å Ã™â€  Ã™â€¦Ã˜Â§Ã˜Â±Ã™Æ’Ã˜Â§Ã˜Âª | 5 Ã˜ÂªÃ˜Â­Ã˜Â³Ã™Å Ã™â€  Ã˜Â£Ã™â€šÃ˜Â³Ã˜Â§Ã™â€¦</li>
                <li><strong>Ã°Å¸â€Âµ Ã˜Â§Ã™â€žÃ˜Â®Ã˜Â·Ã˜Â© Ã˜Â§Ã™â€žÃ˜Â£Ã˜Â³Ã˜Â§Ã˜Â³Ã™Å Ã˜Â© (29 Ã˜Â±.Ã˜Â³/Ã˜Â´Ã™â€¡Ã˜Â±):</strong> Ã™â€žÃ™â€žÃ™â€¦Ã˜ÂªÃ˜Â§Ã˜Â¬Ã˜Â± Ã˜Â§Ã™â€žÃ˜ÂµÃ˜ÂºÃ™Å Ã˜Â±Ã˜Â©
                  <br>80 Ã˜ÂªÃ˜Â­Ã˜Â³Ã™Å Ã™â€  Ã™Ë†Ã˜ÂµÃ™Â | 80 Ã˜ÂªÃ˜Â­Ã˜Â³Ã™Å Ã™â€  SEO | 30 ALT Ã˜ÂµÃ™Ë†Ã˜Â± | 10 Ã™Æ’Ã™â€žÃ™â€¦Ã˜Â§Ã˜Âª Ã™â€¦Ã™ÂÃ˜ÂªÃ˜Â§Ã˜Â­Ã™Å Ã˜Â© | 3 Ã˜ÂªÃ˜Â­Ã™â€žÃ™Å Ã™â€ž Ã˜Â¯Ã™Ë†Ã™â€¦Ã™Å Ã™â€  | Ã˜ÂªÃ˜Â­Ã˜Â³Ã™Å Ã™â€  Ã˜Â§Ã™â€žÃ™â€¦Ã˜Â§Ã˜Â±Ã™Æ’Ã˜Â§Ã˜Âª Ã™Ë†Ã˜Â§Ã™â€žÃ˜Â£Ã™â€šÃ˜Â³Ã˜Â§Ã™â€¦ Ã˜ÂºÃ™Å Ã˜Â± Ã™â€¦Ã™ÂÃ˜Â¹Ã™â€˜Ã™â€ž (Ã˜Â±Ã™â€šÃ™â€˜Ã™Å  Ã˜Â§Ã™â€žÃ˜Â§Ã˜Â´Ã˜ÂªÃ˜Â±Ã˜Â§Ã™Æ’)</li>
                <li><strong>Ã°Å¸Å¸Â£ Ã˜Â§Ã™â€žÃ˜Â®Ã˜Â·Ã˜Â© Ã˜Â§Ã™â€žÃ™â€¦Ã˜ÂªÃ™â€šÃ˜Â¯Ã™â€¦Ã˜Â© (79 Ã˜Â±.Ã˜Â³/Ã˜Â´Ã™â€¡Ã˜Â±):</strong> Ã™â€žÃ™â€žÃ™â€¦Ã˜ÂªÃ˜Â§Ã˜Â¬Ã˜Â± Ã˜Â§Ã™â€žÃ™â€¦Ã˜ÂªÃ™â€ Ã˜Â§Ã™â€¦Ã™Å Ã˜Â© Ã¢Â­Â
                  <br>260 Ã˜ÂªÃ˜Â­Ã˜Â³Ã™Å Ã™â€  Ã™Ë†Ã˜ÂµÃ™Â | 140 Ã˜ÂªÃ˜Â­Ã˜Â³Ã™Å Ã™â€  SEO | 260 ALT Ã˜ÂµÃ™Ë†Ã˜Â± | 40 Ã™Æ’Ã™â€žÃ™â€¦Ã˜Â§Ã˜Âª Ã™â€¦Ã™ÂÃ˜ÂªÃ˜Â§Ã˜Â­Ã™Å Ã˜Â© | 12 Ã˜ÂªÃ˜Â­Ã™â€žÃ™Å Ã™â€ž Ã˜Â¯Ã™Ë†Ã™â€¦Ã™Å Ã™â€  | 50 Ã˜ÂªÃ˜Â­Ã˜Â³Ã™Å Ã™â€  Ã™â€¦Ã˜Â§Ã˜Â±Ã™Æ’Ã˜Â§Ã˜Âª | 50 Ã˜ÂªÃ˜Â­Ã˜Â³Ã™Å Ã™â€  Ã˜Â£Ã™â€šÃ˜Â³Ã˜Â§Ã™â€¦
                  <br>+ Ã˜Â³Ã˜Â¬Ã™â€ž Ã˜Â§Ã™â€žÃ™â€ Ã˜Â´Ã˜Â§Ã˜Â·Ã˜Â§Ã˜Âª | Ã˜ÂªÃ˜ÂµÃ˜Â¯Ã™Å Ã˜Â± Ã˜Â§Ã™â€žÃ˜Â¨Ã™Å Ã˜Â§Ã™â€ Ã˜Â§Ã˜Âª | Ã˜Â£Ã˜Â¯Ã˜Â§Ã˜Â¡ Ã˜Â£Ã˜Â³Ã˜Â±Ã˜Â¹</li>
                <li><strong>Ã°Å¸â€Â´ Ã˜Â§Ã™â€žÃ˜Â®Ã˜Â·Ã˜Â© Ã˜Â§Ã™â€žÃ˜Â§Ã˜Â­Ã˜ÂªÃ˜Â±Ã˜Â§Ã™ÂÃ™Å Ã˜Â© (149 Ã˜Â±.Ã˜Â³/Ã˜Â´Ã™â€¡Ã˜Â±):</strong> Ã™â€žÃ™â€žÃ™â€¦Ã˜ÂªÃ˜Â§Ã˜Â¬Ã˜Â± Ã˜Â§Ã™â€žÃ™Æ’Ã˜Â¨Ã™Å Ã˜Â±Ã˜Â©
                  <br>700 Ã˜ÂªÃ˜Â­Ã˜Â³Ã™Å Ã™â€  Ã™Ë†Ã˜ÂµÃ™Â | 700 Ã˜ÂªÃ˜Â­Ã˜Â³Ã™Å Ã™â€  SEO | 700 ALT Ã˜ÂµÃ™Ë†Ã˜Â± | 120 Ã™Æ’Ã™â€žÃ™â€¦Ã˜Â© Ã™â€¦Ã™ÂÃ˜ÂªÃ˜Â§Ã˜Â­Ã™Å Ã˜Â© | 35 Ã˜ÂªÃ˜Â­Ã™â€žÃ™Å Ã™â€ž Ã˜Â¯Ã™Ë†Ã™â€¦Ã™Å Ã™â€  | 150 Ã˜ÂªÃ˜Â­Ã˜Â³Ã™Å Ã™â€  Ã™â€¦Ã˜Â§Ã˜Â±Ã™Æ’Ã˜Â§Ã˜Âª | 100 Ã˜ÂªÃ˜Â­Ã˜Â³Ã™Å Ã™â€  Ã˜Â£Ã™â€šÃ˜Â³Ã˜Â§Ã™â€¦
                  <br>+ Ã˜Â¯Ã˜Â¹Ã™â€¦ Ã˜Â£Ã™Ë†Ã™â€žÃ™Ë†Ã™Å  | Ã˜Â­Ã˜Â¯Ã™Ë†Ã˜Â¯ Ã˜Â£Ã˜Â¹Ã™â€žÃ™â€° | Ã˜Â³Ã˜Â¬Ã™â€ž Ã˜Â§Ã™â€žÃ™â€ Ã˜Â´Ã˜Â§Ã˜Â·Ã˜Â§Ã˜Âª | Ã˜ÂªÃ˜ÂµÃ˜Â¯Ã™Å Ã˜Â± | Ã˜Â£Ã˜Â¯Ã˜Â§Ã˜Â¡ Ã˜Â£Ã˜Â³Ã˜Â±Ã˜Â¹</li>
              </ul>
            </div>
          </details>
          <details>
            <summary>Ã™â€¡Ã™â€ž Ã˜Â£Ã˜Â­Ã˜ÂªÃ˜Â§Ã˜Â¬ Ã™â€žÃ˜Â´Ã˜Â±Ã˜Â§Ã˜Â¡ Ã˜Â±Ã˜ÂµÃ™Å Ã˜Â¯ OpenAI separatelyÃ˜Å¸</summary>
            <div class="answer">
              <strong>Ã™â€žÃ˜Â§!</strong> Ã˜Â§Ã™â€žÃ˜ÂªÃ™Æ’Ã™â€žÃ™ÂÃ˜Â© Ã™â€¦Ã˜Â´Ã™â€¦Ã™Ë†Ã™â€žÃ˜Â© Ã™ÂÃ™Å  Ã˜Â§Ã˜Â´Ã˜ÂªÃ˜Â±Ã˜Â§Ã™Æ’Ã™Æ’. Ã™â€žÃ˜Â§ Ã˜Â­Ã˜Â§Ã˜Â¬Ã˜Â© Ã™â€žÃ˜Â¥Ã™â€ Ã˜Â´Ã˜Â§Ã˜Â¡ Ã˜Â­Ã˜Â³Ã˜Â§Ã˜Â¨ OpenAI Ã˜Â£Ã™Ë† Ã˜Â´Ã˜Â±Ã˜Â§Ã˜Â¡ Ã˜Â±Ã˜ÂµÃ™Å Ã˜Â¯ separately. Ã™â€ Ã˜Â­Ã™â€  Ã™â€ Ã˜ÂªÃ™Ë†Ã™â€žÃ™â€° Ã™Æ’Ã™â€ž Ã˜Â´Ã™Å Ã˜Â¡.
            </div>
          </details>
          <details>
            <summary>Ã™â€¦Ã˜Â§Ã˜Â°Ã˜Â§ Ã™â€žÃ™Ë† Ã˜Â§Ã˜Â³Ã˜ÂªÃ™â€¡Ã™â€žÃ™Æ’Ã˜Âª Ã™Æ’Ã™â€ž Ã˜Â§Ã™â€žÃ˜ÂªÃ˜Â­Ã˜Â³Ã™Å Ã™â€ Ã˜Â§Ã˜Âª Ã˜Â§Ã™â€žÃ™â€¦Ã˜ÂªÃ˜Â§Ã˜Â­Ã˜Â©Ã˜Å¸</summary>
            <div class="answer">
              Ã˜Â¹Ã™â€ Ã˜Â¯ Ã˜Â§Ã™â€ Ã˜ÂªÃ™â€¡Ã˜Â§Ã˜Â¡ Ã˜Â±Ã˜ÂµÃ™Å Ã˜Â¯Ã™Æ’:
              <ul>
                <li>Ã˜Â³Ã˜ÂªÃ˜Â¸Ã™â€¡Ã˜Â± Ã™â€žÃ™Æ’ Ã˜Â±Ã˜Â³Ã˜Â§Ã™â€žÃ˜Â© Ã˜ÂªÃ™â€ Ã˜Â¨Ã™Å Ã™â€¡ Ã™ÂÃ™Å  Ã™â€žÃ™Ë†Ã˜Â­Ã˜Â© Ã˜Â§Ã™â€žÃ˜ÂªÃ˜Â­Ã™Æ’Ã™â€¦</li>
                <li>Ã™Å Ã™â€¦Ã™Æ’Ã™â€ Ã™Æ’ Ã˜ÂªÃ˜Â±Ã™â€šÃ™Å Ã˜Â© Ã˜Â§Ã˜Â´Ã˜ÂªÃ˜Â±Ã˜Â§Ã™Æ’Ã™Æ’ Ã™â€žÃ™â€žÃ˜Â­Ã˜ÂµÃ™Ë†Ã™â€ž Ã˜Â¹Ã™â€žÃ™â€° Ã˜Â±Ã˜ÂµÃ™Å Ã˜Â¯ Ã˜Â£Ã™Æ’Ã˜Â¨Ã˜Â±</li>
                <li>Ã˜Â£Ã™Ë† Ã˜Â§Ã™â€žÃ˜Â§Ã™â€ Ã˜ÂªÃ˜Â¸Ã˜Â§Ã˜Â± Ã˜Â­Ã˜ÂªÃ™â€° Ã˜ÂªÃ˜Â¬Ã˜Â¯Ã™Å Ã˜Â¯ Ã˜Â§Ã™â€žÃ™ÂÃ˜ÂªÃ˜Â±Ã˜Â© Ã˜Â§Ã™â€žÃ˜ÂªÃ˜Â§Ã™â€žÃ™Å Ã˜Â©</li>
              </ul>
              <strong>Ã™â€¦Ã™â€žÃ˜Â§Ã˜Â­Ã˜Â¸Ã˜Â©:</strong> Ã™Æ’Ã™â€ž Ã˜Â¹Ã™â€¦Ã™â€žÃ™Å Ã˜Â© Ã˜ÂªÃ˜Â­Ã˜Â³Ã™Å Ã™â€  Ã˜ÂªÃ˜Â³Ã˜ÂªÃ™â€¡Ã™â€žÃ™Æ’ Ã™â€¦Ã™â€  Ã˜Â±Ã˜ÂµÃ™Å Ã˜Â¯Ã™Æ’ Ã˜Â­Ã˜Â³Ã˜Â¨ Ã™â€ Ã™Ë†Ã˜Â¹Ã™â€¡Ã˜Â§.
            </div>
          </details>
          <details>
            <summary>Ã™â€¦Ã˜Â§ Ã™â€¦Ã˜Â¹Ã™â€ Ã™â€° Ã™Æ’Ã™â€ž Ã™â€ Ã™Ë†Ã˜Â¹ Ã™â€¦Ã™â€  Ã˜Â§Ã™â€žÃ˜ÂªÃ˜Â­Ã˜Â³Ã™Å Ã™â€ Ã˜Â§Ã˜ÂªÃ˜Å¸</summary>
            <div class="answer">
              <ul>
                <li><strong>Ã˜ÂªÃ˜Â­Ã˜Â³Ã™Å Ã™â€  Ã™Ë†Ã˜ÂµÃ™Â Ã™â€¦Ã™â€ Ã˜ÂªÃ˜Â¬:</strong> Ã™Æ’Ã˜ÂªÃ˜Â§Ã˜Â¨Ã˜Â© Ã™Ë†Ã˜ÂµÃ™Â Ã˜Â§Ã˜Â­Ã˜ÂªÃ˜Â±Ã˜Â§Ã™ÂÃ™Å  Ã™â€¦Ã˜ÂªÃ™Ë†Ã˜Â§Ã™ÂÃ™â€š Ã™â€¦Ã˜Â¹ SEO</li>
                <li><strong>Ã˜ÂªÃ˜Â­Ã˜Â³Ã™Å Ã™â€  SEO Ã™â€¦Ã™â€ Ã˜ÂªÃ˜Â¬:</strong> Meta Title Ã™Ë† Meta Description</li>
                <li><strong>Ã˜ÂªÃ˜Â­Ã˜Â³Ã™Å Ã™â€  ALT Ã˜ÂµÃ™Ë†Ã˜Â±:</strong> Ã™Æ’Ã˜ÂªÃ˜Â§Ã˜Â¨Ã˜Â© Ã™â€ Ã˜Âµ Ã˜Â¨Ã˜Â¯Ã™Å Ã™â€ž Ã™â€žÃ™Æ’Ã™â€ž Ã˜ÂµÃ™Ë†Ã˜Â±Ã˜Â©</li>
                <li><strong>Ã™Æ’Ã™â€žÃ™â€¦Ã˜Â§Ã˜Âª Ã™â€¦Ã™ÂÃ˜ÂªÃ˜Â§Ã˜Â­Ã™Å Ã˜Â©:</strong> Ã˜Â§Ã™â€žÃ˜Â¨Ã˜Â­Ã˜Â« Ã˜Â¹Ã™â€  Ã˜Â£Ã™ÂÃ˜Â¶Ã™â€ž Ã˜Â§Ã™â€žÃ™Æ’Ã™â€žÃ™â€¦Ã˜Â§Ã˜Âª Ã˜Â§Ã™â€žÃ™â€¦Ã™ÂÃ˜ÂªÃ˜Â§Ã˜Â­Ã™Å Ã˜Â©</li>
                <li><strong>Ã˜ÂªÃ˜Â­Ã™â€žÃ™Å Ã™â€ž Ã˜Â³Ã™Å Ã™Ë† Ã˜Â§Ã™â€žÃ˜Â¯Ã™Ë†Ã™â€¦Ã™Å Ã™â€ :</strong> Ã˜ÂªÃ˜Â­Ã™â€žÃ™Å Ã™â€ž Ã˜Â´Ã˜Â§Ã™â€¦Ã™â€ž Ã™â€žÃ™â€¦Ã™Ë†Ã™â€šÃ˜Â¹Ã™Æ’</li>
                <li><strong>Ã˜ÂªÃ˜Â­Ã˜Â³Ã™Å Ã™â€  SEO Ã™â€¦Ã˜Â§Ã˜Â±Ã™Æ’Ã˜Â©:</strong> Ã™Ë†Ã˜ÂµÃ™Â Ã™Ë† Meta Tags Ã™â€žÃ™â€žÃ™â€¦Ã˜Â§Ã˜Â±Ã™Æ’Ã˜Â§Ã˜Âª Ã˜Â§Ã™â€žÃ˜ÂªÃ˜Â¬Ã˜Â§Ã˜Â±Ã™Å Ã˜Â©</li>
              </ul>
            </div>
          </details>
        </div>
      </div>

      <div class="category" data-category="technical">
        <div class="category-title">
          <div class="icon">Ã°Å¸â€Â§</div>
          <h2>Ã˜Â§Ã™â€žÃ˜ÂªÃ™â€šÃ™â€ Ã™Å Ã˜Â© Ã™Ë†Ã˜Â§Ã™â€žÃ˜Â±Ã˜Â¨Ã˜Â·</h2>
        </div>
        <div class="faq-list">
          <details>
            <summary>Ã™Æ’Ã™Å Ã™Â Ã˜Â£Ã˜Â±Ã˜Â¨Ã˜Â· Ã™â€¦Ã˜ÂªÃ˜Â¬Ã˜Â±Ã™Å  Ã˜Â³Ã™â€žÃ˜Â© Ã˜Â¨Ã˜Â§Ã™â€žÃ™â€¦Ã™â€ Ã˜ÂµÃ˜Â©Ã˜Å¸</summary>
            <div class="answer">
              Ã˜Â¹Ã™â€¦Ã™â€žÃ™Å Ã˜Â© Ã˜Â§Ã™â€žÃ˜Â±Ã˜Â¨Ã˜Â· Ã˜Â¨Ã˜Â³Ã™Å Ã˜Â·Ã˜Â© Ã˜Â¬Ã˜Â¯Ã™â€¹Ã˜Â§:<br>
              <ol style="margin:12px 0;padding-right:24px">
                <li>Ã˜Â³Ã˜Â¬Ã™â€˜Ã™â€ž Ã˜Â§Ã™â€žÃ˜Â¯Ã˜Â®Ã™Ë†Ã™â€ž Ã˜Â¥Ã™â€žÃ™â€° Ã˜Â­Ã˜Â³Ã˜Â§Ã˜Â¨Ã™Æ’ Ã™ÂÃ™Å  RankX SEO</li>
                <li>Ã˜Â§Ã˜Â¶Ã˜ÂºÃ˜Â· Ã˜Â¹Ã™â€žÃ™â€° "Ã˜Â±Ã˜Â¨Ã˜Â· Ã™â€¦Ã˜ÂªÃ˜Â¬Ã˜Â± Ã˜Â¬Ã˜Â¯Ã™Å Ã˜Â¯"</li>
                <li>Ã˜Â£Ã˜Â¯Ã˜Â®Ã™â€ž Ã˜Â±Ã˜Â§Ã˜Â¨Ã˜Â· Ã™â€¦Ã˜ÂªÃ˜Â¬Ã˜Â±Ã™Æ’ Ã™ÂÃ™Å  Ã˜Â³Ã™â€žÃ˜Â©</li>
                <li>Ã˜Â³Ã™Å Ã˜ÂªÃ™â€¦ Ã˜ÂªÃ™Ë†Ã˜Â¬Ã™Å Ã™â€¡Ã™Æ’ Ã™â€žÃ˜ÂªÃ˜Â£Ã™Æ’Ã™Å Ã˜Â¯ Ã˜Â§Ã™â€žÃ˜Â±Ã˜Â¨Ã˜Â· Ã™â€¦Ã™â€  Ã˜Â¯Ã˜Â§Ã˜Â®Ã™â€ž Ã˜Â³Ã™â€žÃ˜Â©</li>
                <li>Ã˜Â¨Ã˜Â¹Ã˜Â¯ Ã˜Â§Ã™â€žÃ˜ÂªÃ˜Â£Ã™Æ’Ã™Å Ã˜Â¯Ã˜Å’ Ã˜Â³Ã™Å Ã˜ÂªÃ™â€¦ Ã˜Â±Ã˜Â¨Ã˜Â· Ã˜Â§Ã™â€žÃ™â€¦Ã˜ÂªÃ˜Â¬Ã˜Â± Ã˜ÂªÃ™â€žÃ™â€šÃ˜Â§Ã˜Â¦Ã™Å Ã™â€¹Ã˜Â§!</li>
              </ol>
            </div>
          </details>
          <details>
            <summary>Ã™â€¡Ã™â€ž Ã˜Â£Ã˜Â­Ã˜ÂªÃ˜Â§Ã˜Â¬ Ã˜ÂµÃ™â€žÃ˜Â§Ã˜Â­Ã™Å Ã˜Â§Ã˜Âª Ã™â€¦Ã˜Â¹Ã™Å Ã™â€ Ã˜Â© Ã™ÂÃ™Å  Ã˜Â³Ã™â€žÃ˜Â©Ã˜Å¸</summary>
            <div class="answer">
              <strong>Ã™â€ Ã˜Â¹Ã™â€¦.</strong> Ã˜ÂªÃ˜Â­Ã˜ÂªÃ˜Â§Ã˜Â¬ Ã˜Â£Ã™â€  Ã˜ÂªÃ™Æ’Ã™Ë†Ã™â€ :<br>
              <ul>
                <li>Ã™â€¦Ã˜Â§Ã™â€žÃ™Æ’ Ã˜Â§Ã™â€žÃ™â€¦Ã˜ÂªÃ˜Â¬Ã˜Â± Ã˜Â£Ã™Ë† Ã™â€žÃ˜Â¯Ã™Å Ã™â€¡ Ã˜ÂµÃ™â€žÃ˜Â§Ã˜Â­Ã™Å Ã˜Â§Ã˜Âª Ã˜Â¥Ã˜Â¯Ã˜Â§Ã˜Â±Ã™Å Ã˜Â©</li>
                <li>Ã˜Â£Ã™â€  Ã˜ÂªÃ™Æ’Ã™Ë†Ã™â€  Ã™â€šÃ˜Â§Ã˜Â¯Ã˜Â±Ã™â€¹Ã˜Â§ Ã˜Â¹Ã™â€žÃ™â€° Ã˜ÂªÃ˜Â«Ã˜Â¨Ã™Å Ã˜Âª Ã˜Â§Ã™â€žÃ˜ÂªÃ˜Â·Ã˜Â¨Ã™Å Ã™â€šÃ˜Â§Ã˜Âª Ã™â€¦Ã™â€  Ã™â€¦Ã˜ÂªÃ˜Â¬Ã˜Â± Ã˜Â³Ã™â€žÃ˜Â©</li>
              </ul>
            </div>
          </details>
          <details>
            <summary>Ã™â€¡Ã™â€ž Ã˜Â§Ã™â€žÃ˜ÂªÃ˜ÂºÃ™Å Ã™Å Ã˜Â±Ã˜Â§Ã˜Âª Ã˜ÂªÃ™ÂÃ˜Â­Ã˜Â¯Ã˜Â« Ã™â€¦Ã˜Â¨Ã˜Â§Ã˜Â´Ã˜Â±Ã˜Â© Ã™ÂÃ™Å  Ã˜Â³Ã™â€žÃ˜Â©Ã˜Å¸</summary>
            <div class="answer">
              <strong>Ã™â€ Ã˜Â¹Ã™â€¦!</strong> Ã˜Â£Ã™Å  Ã˜ÂªÃ˜Â­Ã˜Â³Ã™Å Ã™â€  Ã˜ÂªÃ˜Â­Ã™ÂÃ˜Â¸Ã™â€¡ Ã™ÂÃ™Å  RankX SEO Ã™Å Ã˜ÂªÃ™â€¦ Ã˜ÂªÃ˜Â­Ã˜Â¯Ã™Å Ã˜Â«Ã™â€¡ Ã™â€¦Ã˜Â¨Ã˜Â§Ã˜Â´Ã˜Â±Ã˜Â© Ã™ÂÃ™Å  Ã™â€¦Ã˜ÂªÃ˜Â¬Ã˜Â±Ã™Æ’ Ã˜Â¹Ã™â€žÃ™â€° Ã˜Â³Ã™â€žÃ˜Â©. Ã™â€žÃ˜Â§ Ã˜ÂªÃ˜Â­Ã˜ÂªÃ˜Â§Ã˜Â¬ Ã™â€žÃ™â€ Ã˜Â³Ã˜Â® Ã™Ë†Ã™â€žÃ˜ÂµÃ™â€š Ã˜Â£Ã™Ë† Ã˜Â£Ã™Å  Ã˜Â¹Ã™â€¦Ã™â€žÃ™Å Ã˜Â© Ã™Å Ã˜Â¯Ã™Ë†Ã™Å Ã˜Â©.
            </div>
          </details>
          <details>
            <summary>Ã™â€¡Ã™â€ž Ã™Å Ã™â€¦Ã™Æ’Ã™â€ Ã™â€ Ã™Å  Ã™ÂÃ™Æ’ Ã˜Â±Ã˜Â¨Ã˜Â· Ã˜Â§Ã™â€žÃ™â€¦Ã˜ÂªÃ˜Â¬Ã˜Â±Ã˜Å¸</summary>
            <div class="answer">
              <strong>Ã™â€ Ã˜Â¹Ã™â€¦.</strong> Ã™Å Ã™â€¦Ã™Æ’Ã™â€ Ã™Æ’ Ã˜Â¥Ã™â€žÃ˜ÂºÃ˜Â§Ã˜Â¡ Ã˜ÂªÃ˜Â«Ã˜Â¨Ã™Å Ã˜Âª Ã˜Â§Ã™â€žÃ˜ÂªÃ˜Â·Ã˜Â¨Ã™Å Ã™â€š Ã™â€¦Ã™â€  Ã˜Â¥Ã˜Â¹Ã˜Â¯Ã˜Â§Ã˜Â¯Ã˜Â§Ã˜Âª Ã˜Â³Ã™â€žÃ˜Â© Ã™ÂÃ™Å  Ã˜Â£Ã™Å  Ã™Ë†Ã™â€šÃ˜Âª. Ã˜Â³Ã™Å Ã˜ÂªÃ™â€¦ Ã˜Â§Ã™â€žÃ˜Â§Ã˜Â­Ã˜ÂªÃ™ÂÃ˜Â§Ã˜Â¸ Ã˜Â¨Ã™â€ Ã˜Â³Ã˜Â®Ã˜Â© Ã™â€¦Ã™â€  Ã˜Â¨Ã™Å Ã˜Â§Ã™â€ Ã˜Â§Ã˜ÂªÃ™Æ’Ã˜Å’ Ã™â€žÃ™Æ’Ã™â€  Ã˜Â§Ã™â€žÃ˜ÂªÃ˜Â­Ã˜Â¯Ã™Å Ã˜Â«Ã˜Â§Ã˜Âª Ã™â€žÃ™â€  Ã˜ÂªÃ˜ÂªÃ™â€¦ Ã˜Â¨Ã˜Â¹Ã˜Â¯ Ã˜Â°Ã™â€žÃ™Æ’.
            </div>
          </details>
        </div>
      </div>

      <div class="category" data-category="results">
        <div class="category-title">
          <div class="icon">Ã°Å¸â€œË†</div>
          <h2>Ã˜Â§Ã™â€žÃ™â€ Ã˜ÂªÃ˜Â§Ã˜Â¦Ã˜Â¬ Ã™Ë†Ã˜Â§Ã™â€žÃ™â€¦Ã˜ÂªÃ˜Â§Ã˜Â¨Ã˜Â¹Ã˜Â©</h2>
        </div>
        <div class="faq-list">
          <details>
            <summary>Ã™â€¦Ã˜ÂªÃ™â€° Ã˜ÂªÃ˜Â¸Ã™â€¡Ã˜Â± Ã™â€ Ã˜ÂªÃ˜Â§Ã˜Â¦Ã˜Â¬ Ã˜Â§Ã™â€žÃ˜ÂªÃ˜Â­Ã˜Â³Ã™Å Ã™â€  Ã™ÂÃ™Å  Ã˜Â§Ã™â€žÃ˜Â¨Ã˜Â­Ã˜Â«Ã˜Å¸</summary>
            <div class="answer">
              Ã™Å Ã˜Â¹Ã˜ÂªÃ™â€¦Ã˜Â¯ Ã˜Â°Ã™â€žÃ™Æ’ Ã˜Â¹Ã™â€žÃ™â€° Ã˜Â¹Ã˜Â¯Ã˜Â© Ã˜Â¹Ã™Ë†Ã˜Â§Ã™â€¦Ã™â€ž:
              <ul>
                <li><strong>Google:</strong> Ã˜Â¹Ã˜Â§Ã˜Â¯Ã˜Â©Ã™â€¹ Ã™â€¦Ã™â€  Ã˜Â£Ã˜Â³Ã˜Â¨Ã™Ë†Ã˜Â¹ Ã˜Â¥Ã™â€žÃ™â€° 4 Ã˜Â£Ã˜Â³Ã˜Â§Ã˜Â¨Ã™Å Ã˜Â¹</li>
                <li><strong>Ã˜ÂªÃ˜Â­Ã˜Â¯Ã™Å Ã˜Â«Ã˜Â§Ã˜Âª Ã˜Â§Ã™â€žÃ™â€¦Ã™â€ Ã˜ÂªÃ˜Â¬Ã˜Â§Ã˜Âª:</strong> Ã˜ÂªÃ˜Â¸Ã™â€¡Ã˜Â± Ã˜Â£Ã˜Â³Ã˜Â±Ã˜Â¹ Ã™â€¦Ã™â€  Ã˜ÂªÃ˜Â­Ã˜Â¯Ã™Å Ã˜Â«Ã˜Â§Ã˜Âª Ã˜Â§Ã™â€žÃ™â€¦Ã˜ÂªÃ˜Â¬Ã˜Â± Ã˜Â§Ã™â€žÃ˜Â¹Ã˜Â§Ã™â€¦</li>
                <li><strong>Ã˜Â§Ã™â€žÃ™â€¦Ã™â€ Ã˜Â§Ã™ÂÃ˜Â³Ã˜Â©:</strong> Ã™ÂÃ™Å  Ã™â€¦Ã˜Â¬Ã˜Â§Ã™â€žÃ˜Â§Ã˜Âª Ã˜ÂªÃ™â€ Ã˜Â§Ã™ÂÃ˜Â³Ã™Å Ã˜Â© Ã™â€šÃ˜Â¯ Ã™Å Ã˜Â³Ã˜ÂªÃ˜ÂºÃ˜Â±Ã™â€š Ã˜Â§Ã™â€žÃ˜Â£Ã™â€¦Ã˜Â± Ã™Ë†Ã™â€šÃ˜ÂªÃ™â€¹Ã˜Â§ Ã˜Â£Ã˜Â·Ã™Ë†Ã™â€ž</li>
              </ul>
              Ã™â€ Ã˜ÂµÃ™Å Ã˜Â­Ã˜ÂªÃ™â€ Ã˜Â§: Ã˜ÂªÃ˜Â­Ã™â€žÃ™â€° Ã˜Â¨Ã˜Â§Ã™â€žÃ˜ÂµÃ˜Â¨Ã˜Â± Ã™Ë†Ã˜Â§Ã˜Â³Ã˜ÂªÃ™â€¦Ã˜Â± Ã™ÂÃ™Å  Ã˜ÂªÃ˜Â­Ã˜Â³Ã™Å Ã™â€  Ã˜Â§Ã™â€žÃ™â€¦Ã˜Â­Ã˜ÂªÃ™Ë†Ã™â€° Ã˜Â¨Ã˜Â´Ã™Æ’Ã™â€ž Ã™â€¦Ã™â€ Ã˜ÂªÃ˜Â¸Ã™â€¦.
            </div>
          </details>
          <details>
            <summary>Ã™â€¡Ã™â€ž Ã™Å Ã™â€¦Ã™Æ’Ã™â€ Ã™â€ Ã™Å  Ã˜Â§Ã™â€žÃ˜ÂªÃ˜Â¹Ã˜Â¯Ã™Å Ã™â€ž Ã™Å Ã˜Â¯Ã™Ë†Ã™Å Ã™â€¹Ã˜Â§ Ã˜Â¨Ã˜Â¯Ã™Ë†Ã™â€  AIÃ˜Å¸</summary>
            <div class="answer">
              <strong>Ã™â€ Ã˜Â¹Ã™â€¦!</strong> Ã™â€ Ã™â€šÃ˜Â¯Ã™â€¦ Ã™â€¦Ã™Å Ã˜Â²Ã˜Â© Ã˜Â§Ã™â€žÃ˜ÂªÃ˜Â­Ã˜Â±Ã™Å Ã˜Â± Ã˜Â§Ã™â€žÃ™Å Ã˜Â¯Ã™Ë†Ã™Å  Ã˜Â§Ã™â€žÃ˜ÂªÃ™Å  Ã˜ÂªÃ˜ÂªÃ™Å Ã˜Â­ Ã™â€žÃ™Æ’:
              <ul>
                <li>Ã˜Â±Ã˜Â¤Ã™Å Ã˜Â© Ã˜Â§Ã™â€žÃ™Ë†Ã˜ÂµÃ™Â Ã˜Â§Ã™â€žÃ˜Â­Ã˜Â§Ã™â€žÃ™Å  Ã™Ë†Ã˜Â§Ã™â€žÃ™Ë†Ã˜ÂµÃ™Â Ã˜Â§Ã™â€žÃ˜Â¬Ã˜Â¯Ã™Å Ã˜Â¯</li>
                <li>Ã˜ÂªÃ˜Â­Ã˜Â±Ã™Å Ã˜Â± Ã˜Â§Ã™â€žÃ™Ë†Ã˜ÂµÃ™Â Ã™Å Ã˜Â¯Ã™Ë†Ã™Å Ã™â€¹Ã˜Â§ Ã™â€šÃ˜Â¨Ã™â€ž Ã˜Â§Ã™â€žÃ˜Â­Ã™ÂÃ˜Â¸</li>
                <li>Ã˜ÂªÃ˜Â¹Ã˜Â¯Ã™Å Ã™â€ž Meta Title Ã™Ë† Meta Description</li>
                <li>Ã˜Â­Ã™ÂÃ˜Â¸ Ã˜Â§Ã™â€žÃ˜ÂªÃ˜ÂºÃ™Å Ã™Å Ã˜Â±Ã˜Â§Ã˜Âª Ã™â€¦Ã˜Â¨Ã˜Â§Ã˜Â´Ã˜Â±Ã˜Â© Ã™ÂÃ™Å  Ã™â€¦Ã˜ÂªÃ˜Â¬Ã˜Â±Ã™Æ’</li>
              </ul>
              Ã™â€¡Ã˜Â°Ã™â€¡ Ã˜Â§Ã™â€žÃ™â€¦Ã™Å Ã˜Â²Ã˜Â© Ã™â€žÃ˜Â§ Ã˜ÂªÃ˜Â³Ã˜ÂªÃ™â€¡Ã™â€žÃ™Æ’ Ã™â€¦Ã™â€  Ã˜Â±Ã˜ÂµÃ™Å Ã˜Â¯Ã™Æ’ Ã™â€žÃ˜Â£Ã™â€ Ã™â€¡Ã˜Â§ Ã™â€žÃ˜Â§ Ã˜ÂªÃ˜Â³Ã˜ÂªÃ˜Â®Ã˜Â¯Ã™â€¦ Ã˜Â§Ã™â€žÃ˜Â°Ã™Æ’Ã˜Â§Ã˜Â¡ Ã˜Â§Ã™â€žÃ˜Â§Ã˜ÂµÃ˜Â·Ã™â€ Ã˜Â§Ã˜Â¹Ã™Å .
            </div>
          </details>
          <details>
            <summary>Ã™Æ’Ã™Å Ã™Â Ã˜Â£Ã˜ÂªÃ˜Â§Ã˜Â¨Ã˜Â¹ Ã˜Â£Ã˜Â¯Ã˜Â§Ã˜Â¡ Ã˜Â§Ã™â€žÃ˜ÂªÃ˜Â­Ã˜Â³Ã™Å Ã™â€ Ã˜Â§Ã˜ÂªÃ˜Å¸</summary>
            <div class="answer">
              Ã˜ÂªÃ™Ë†Ã™ÂÃ˜Â± Ã™â€žÃ™Æ’ RankX SEO Ã˜Â³Ã˜Â¬Ã™â€ž Ã˜Â¹Ã™â€¦Ã™â€žÃ™Å Ã˜Â§Ã˜Âª Ã™Æ’Ã˜Â§Ã™â€¦Ã™â€ž Ã™Å Ã™â€¦Ã™Æ’Ã™â€ Ã™Æ’ Ã™â€¦Ã™â€ :
              <ul>
                <li>Ã™â€¦Ã˜Â¹Ã˜Â±Ã™ÂÃ˜Â© Ã˜Â§Ã™â€žÃ™â€¦Ã™â€ Ã˜ÂªÃ˜Â¬Ã˜Â§Ã˜Âª Ã˜Â§Ã™â€žÃ˜ÂªÃ™Å  Ã˜ÂªÃ™â€¦ Ã˜ÂªÃ˜Â­Ã˜Â³Ã™Å Ã™â€ Ã™â€¡Ã˜Â§ Ã™Ë†Ã˜ÂªÃ˜Â§Ã˜Â±Ã™Å Ã˜Â® Ã˜Â§Ã™â€žÃ˜ÂªÃ˜Â­Ã˜Â³Ã™Å Ã™â€ </li>
                <li>Ã™â€¦Ã˜ÂªÃ˜Â§Ã˜Â¨Ã˜Â¹Ã˜Â© Ã˜Â§Ã˜Â³Ã˜ÂªÃ™â€¡Ã™â€žÃ˜Â§Ã™Æ’ Ã™Æ’Ã™â€ž Ã™â€ Ã™Ë†Ã˜Â¹ Ã™â€¦Ã™â€  Ã˜Â§Ã™â€žÃ˜ÂªÃ˜Â­Ã˜Â³Ã™Å Ã™â€ Ã˜Â§Ã˜Âª</li>
                <li>Ã˜Â±Ã˜Â¤Ã™Å Ã˜Â© Ã˜ÂªÃ™ÂÃ˜Â§Ã˜ÂµÃ™Å Ã™â€ž Ã˜Â§Ã™â€žÃ˜Â§Ã˜Â³Ã˜ÂªÃ™â€¡Ã™â€žÃ˜Â§Ã™Æ’ Ã˜Â­Ã˜Â³Ã˜Â¨ Ã˜Â§Ã™â€žÃ˜Â¹Ã™â€¦Ã™â€žÃ™Å Ã˜Â©</li>
                <li>Ã˜Â±Ã˜Â¤Ã™Å Ã˜Â© Ã˜ÂªÃ™Æ’Ã™â€žÃ™ÂÃ˜Â© Ã™Æ’Ã™â€ž Ã˜Â¹Ã™â€¦Ã™â€žÃ™Å Ã˜Â© AI</li>
              </ul>
            </div>
          </details>
          <details>
            <summary>Ã™â€¡Ã™â€ž Ã™Å Ã˜Â¶Ã™â€¦Ã™â€  Ã˜ÂªÃ˜Â­Ã˜Â³Ã™Å Ã™â€  Ã˜Â§Ã™â€žÃ™â€¦Ã˜Â­Ã˜ÂªÃ™Ë†Ã™â€° Ã˜Â¸Ã™â€¡Ã™Ë†Ã˜Â± Ã˜Â£Ã™ÂÃ˜Â¶Ã™â€žÃ˜Å¸</summary>
            <div class="answer">
              Ã˜ÂªÃ˜Â­Ã˜Â³Ã™Å Ã™â€  Ã˜Â§Ã™â€žÃ™â€¦Ã˜Â­Ã˜ÂªÃ™Ë†Ã™â€° <strong>Ã˜Â¹Ã˜Â§Ã™â€¦Ã™â€ž Ã™â€¦Ã™â€¡Ã™â€¦</strong> Ã™â€žÃ™Æ’Ã™â€ Ã™â€¡ Ã™â€žÃ™Å Ã˜Â³ Ã˜Â§Ã™â€žÃ˜Â¹Ã˜Â§Ã™â€¦Ã™â€ž Ã˜Â§Ã™â€žÃ™Ë†Ã˜Â­Ã™Å Ã˜Â¯. Ã˜ÂªÃ˜Â¹Ã˜ÂªÃ™â€¦Ã˜Â¯ Ã™â€ Ã˜ÂªÃ˜Â§Ã˜Â¦Ã˜Â¬ Ã˜Â§Ã™â€žÃ˜Â¨Ã˜Â­Ã˜Â« Ã˜Â¹Ã™â€žÃ™â€°:
              <ul>
                <li>Ã˜Â¬Ã™Ë†Ã˜Â¯Ã˜Â© Ã˜Â§Ã™â€žÃ™â€¦Ã˜Â­Ã˜ÂªÃ™Ë†Ã™â€° Ã™Ë†Ã˜ÂªÃ™â€ Ã˜Â§Ã˜Â³Ã™â€žÃ™â€¡ Ã™â€¦Ã˜Â¹ Ã˜Â§Ã™â€žÃ˜Â¨Ã˜Â­Ã˜Â«</li>
                <li>Ã˜Â³Ã™â€žÃ˜Â·Ã˜Â© Ã˜Â§Ã™â€žÃ˜Â¯Ã™Ë†Ã™â€¦Ã™Å Ã™â€  Ã™Ë†Ã˜Â§Ã™â€žÃ™â€¦Ã™â€ Ã˜ÂªÃ˜Â¬Ã˜Â§Ã˜Âª</li>
                <li>Ã˜Â§Ã™â€žÃ˜Â¨Ã˜Â§Ã™Æ’ Ã™â€žÃ™Å Ã™â€ Ã™Æ’Ã˜Â³ (Ã˜Â±Ã™Ë†Ã˜Â§Ã˜Â¨Ã˜Â· Ã˜Â®Ã˜Â§Ã˜Â±Ã˜Â¬Ã™Å Ã˜Â©)</li>
                <li>Ã˜ÂªÃ˜Â¬Ã˜Â±Ã˜Â¨Ã˜Â© Ã˜Â§Ã™â€žÃ™â€¦Ã˜Â³Ã˜ÂªÃ˜Â®Ã˜Â¯Ã™â€¦ Ã™ÂÃ™Å  Ã˜Â§Ã™â€žÃ™â€¦Ã™Ë†Ã™â€šÃ˜Â¹</li>
                <li>Ã˜Â§Ã™â€žÃ™â€¦Ã™â€ Ã˜Â§Ã™ÂÃ˜Â³Ã˜Â© Ã™ÂÃ™Å  Ã˜Â§Ã™â€žÃ™â€¦Ã˜Â¬Ã˜Â§Ã™â€ž</li>
              </ul>
              RankX SEO Ã˜ÂªÃ˜Â¶Ã™â€¦Ã™â€  Ã™â€žÃ™Æ’ <strong>Ã˜Â£Ã™ÂÃ˜Â¶Ã™â€ž Ã™â€¦Ã˜Â­Ã˜ÂªÃ™Ë†Ã™â€° Ã™â€¦Ã™â€¦Ã™Æ’Ã™â€ </strong>Ã˜Å’ Ã™â€žÃ™Æ’Ã™â€  Ã˜Â§Ã™â€žÃ™â€ Ã˜ÂªÃ˜Â§Ã˜Â¦Ã˜Â¬ Ã˜Â§Ã™â€žÃ™â€ Ã™â€¡Ã˜Â§Ã˜Â¦Ã™Å Ã˜Â© Ã˜ÂªÃ˜Â¹Ã˜ÂªÃ™â€¦Ã˜Â¯ Ã˜Â¹Ã™â€žÃ™â€° Ã˜Â¹Ã™Ë†Ã˜Â§Ã™â€¦Ã™â€ž Ã˜Â®Ã˜Â§Ã˜Â±Ã˜Â¬Ã™Å Ã˜Â©.
            </div>
          </details>
        </div>
      </div>

      <div class="category" data-category="support">
        <div class="category-title">
          <div class="icon">Ã°Å¸â€™Â¬</div>
          <h2>Ã˜Â§Ã™â€žÃ˜Â¯Ã˜Â¹Ã™â€¦ Ã˜Â§Ã™â€žÃ™ÂÃ™â€ Ã™Å </h2>
        </div>
        <div class="faq-list">
          <details>
            <summary>Ã™Æ’Ã™Å Ã™Â Ã˜Â£Ã˜ÂªÃ™Ë†Ã˜Â§Ã˜ÂµÃ™â€ž Ã™â€¦Ã˜Â¹ Ã˜Â§Ã™â€žÃ˜Â¯Ã˜Â¹Ã™â€¦ Ã˜Â§Ã™â€žÃ™ÂÃ™â€ Ã™Å Ã˜Å¸</summary>
            <div class="answer">
              Ã™Å Ã™â€¦Ã™Æ’Ã™â€ Ã™Æ’ Ã˜Â§Ã™â€žÃ˜ÂªÃ™Ë†Ã˜Â§Ã˜ÂµÃ™â€ž Ã™â€¦Ã˜Â¹Ã™â€ Ã˜Â§ Ã˜Â¹Ã˜Â¨Ã˜Â±:<br>
              <ul>
                <li><strong>Ã˜Â§Ã™â€žÃ˜Â¨Ã˜Â±Ã™Å Ã˜Â¯:</strong> seo@rankxseo.com</li>
                <li><strong>Ã˜ÂµÃ™â€ Ã˜Â¯Ã™Ë†Ã™â€š Ã˜Â§Ã™â€žÃ˜Â±Ã˜Â³Ã˜Â§Ã˜Â¦Ã™â€ž:</strong> Ã˜Â¯Ã˜Â§Ã˜Â®Ã™â€ž Ã™â€žÃ™Ë†Ã˜Â­Ã˜Â© Ã˜Â§Ã™â€žÃ˜ÂªÃ˜Â­Ã™Æ’Ã™â€¦</li>
              </ul>
              Ã™â€ Ã˜Â³Ã˜Â¹Ã™â€° Ã™â€žÃ™â€žÃ˜Â±Ã˜Â¯ Ã˜Â®Ã™â€žÃ˜Â§Ã™â€ž 24 Ã˜Â³Ã˜Â§Ã˜Â¹Ã˜Â© Ã˜Â¹Ã™â€¦Ã™â€ž.
            </div>
          </details>
          <details>
            <summary>Ã™â€¡Ã™â€ž Ã˜ÂªÃ™â€šÃ˜Â¯Ã™â€¦Ã™Ë†Ã™â€  Ã˜Â®Ã˜Â¯Ã™â€¦Ã˜Â© Ã˜ÂªÃ˜Â¯Ã˜Â±Ã™Å Ã˜Â¨ Ã˜Â¹Ã™â€žÃ™â€° Ã˜Â§Ã™â€žÃ™â€¦Ã™â€ Ã˜ÂµÃ˜Â©Ã˜Å¸</summary>
            <div class="answer">
              <strong>Ã™â€ Ã˜Â¹Ã™â€¦!</strong> Ã™â€ Ã™â€šÃ˜Â¯Ã™â€¦ Ã˜ÂªÃ˜Â¯Ã˜Â±Ã™Å Ã˜Â¨Ã™â€¹Ã˜Â§ Ã™â€¦Ã˜Â¬Ã˜Â§Ã™â€ Ã™Å Ã™â€¹Ã˜Â§ Ã™â€žÃ™â€žÃ™â€¦Ã˜Â´Ã˜ÂªÃ˜Â±Ã™Æ’Ã™Å Ã™â€ . Ã™Æ’Ã™â€¦Ã˜Â§ Ã™â€ Ã™Ë†Ã™ÂÃ˜Â±:
              <ul>
                <li>Ã™Ë†Ã˜Â«Ã˜Â§Ã˜Â¦Ã™â€š Ã˜ÂªÃ™ÂÃ˜ÂµÃ™Å Ã™â€žÃ™Å Ã˜Â© Ã™â€žÃ™Æ’Ã™â€ž Ã™â€¦Ã™Å Ã˜Â²Ã˜Â©</li>
                <li>Ã™ÂÃ™Å Ã˜Â¯Ã™Å Ã™Ë†Ã™â€¡Ã˜Â§Ã˜Âª Ã˜ÂªÃ˜Â¹Ã™â€žÃ™Å Ã™â€¦Ã™Å Ã˜Â©</li>
                <li>Ã˜Â¯Ã˜Â¹Ã™â€¦ Ã™â€¦Ã˜Â¨Ã˜Â§Ã˜Â´Ã˜Â± Ã˜Â¹Ã™â€ Ã˜Â¯ Ã˜Â§Ã™â€žÃ˜Â­Ã˜Â§Ã˜Â¬Ã˜Â©</li>
              </ul>
            </div>
          </details>
        </div>
      </div>

      <div class="cta-box">
        <h3>Ã™â€žÃ™â€¦ Ã˜ÂªÃ˜Â¬Ã˜Â¯ Ã˜Â¥Ã˜Â¬Ã˜Â§Ã˜Â¨Ã˜Â© Ã˜Â³Ã˜Â¤Ã˜Â§Ã™â€žÃ™Æ’Ã˜Å¸</h3>
        <p>Ã™ÂÃ˜Â±Ã™Å Ã™â€šÃ™â€ Ã˜Â§ Ã˜Â¬Ã˜Â§Ã™â€¡Ã˜Â² Ã™â€žÃ™â€¦Ã˜Â³Ã˜Â§Ã˜Â¹Ã˜Â¯Ã˜ÂªÃ™Æ’ Ã™Ë†Ã˜Â§Ã™â€žÃ˜Â¥Ã˜Â¬Ã˜Â§Ã˜Â¨Ã˜Â© Ã˜Â¹Ã™â€žÃ™â€° Ã˜Â¬Ã™â€¦Ã™Å Ã˜Â¹ Ã˜Â§Ã˜Â³Ã˜ÂªÃ™ÂÃ˜Â³Ã˜Â§Ã˜Â±Ã˜Â§Ã˜ÂªÃ™Æ’</p>
        <a href="mailto:seo@rankxseo.com">
          <span>Ã°Å¸â€œÂ§</span> Ã˜ÂªÃ™Ë†Ã˜Â§Ã˜ÂµÃ™â€ž Ã™â€¦Ã˜Â¹Ã™â€ Ã˜Â§ Ã˜Â§Ã™â€žÃ˜Â¢Ã™â€ 
        </a>
      </div>
    </div>

    <div class="footer">
      <p>
        <a href="{$safeAppUrl}/">Ã˜Â§Ã™â€žÃ˜Â±Ã˜Â¦Ã™Å Ã˜Â³Ã™Å Ã˜Â©</a> Ã‚Â· 
        <a href="{$safeAppUrl}/about">Ã™â€¦Ã™â€  Ã™â€ Ã˜Â­Ã™â€ </a> Ã‚Â· 
        <a href="{$safeAppUrl}/privacy">Ã˜Â³Ã™Å Ã˜Â§Ã˜Â³Ã˜Â© Ã˜Â§Ã™â€žÃ˜Â®Ã˜ÂµÃ™Ë†Ã˜ÂµÃ™Å Ã˜Â©</a> Ã‚Â· 
        <a href="{$safeAppUrl}/terms">Ã˜Â§Ã™â€žÃ˜Â´Ã˜Â±Ã™Ë†Ã˜Â· Ã™Ë†Ã˜Â§Ã™â€žÃ˜Â£Ã˜Â­Ã™Æ’Ã˜Â§Ã™â€¦</a>
      </p>
      <p>Ã‚Â© 2024 RankX SEO - Ã˜Â¬Ã™â€¦Ã™Å Ã˜Â¹ Ã˜Â§Ã™â€žÃ˜Â­Ã™â€šÃ™Ë†Ã™â€š Ã™â€¦Ã˜Â­Ã™ÂÃ™Ë†Ã˜Â¸Ã˜Â©</p>
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
        $logoSrc = '/assets/rankxseo-logo.png';
        $faviconSrc = 'https://rankxseo.com/favicon.png';

        $html = <<<HTML
<!doctype html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" type="image/png" href="{$faviconSrc}">
  <link rel="apple-touch-icon" href="{$faviconSrc}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&display=swap">
  <title>Ã˜Â³Ã™Å Ã˜Â§Ã˜Â³Ã˜Â© Ã˜Â§Ã™â€žÃ˜Â®Ã˜ÂµÃ™Ë†Ã˜ÂµÃ™Å Ã˜Â© | RankX SEO</title>
  <meta name="description" content="Ã˜Â³Ã™Å Ã˜Â§Ã˜Â³Ã˜Â© Ã˜Â§Ã™â€žÃ˜Â®Ã˜ÂµÃ™Ë†Ã˜ÂµÃ™Å Ã˜Â© Ã˜Â§Ã™â€žÃ˜Â®Ã˜Â§Ã˜ÂµÃ˜Â© Ã˜Â¨Ã™â€¦Ã™â€ Ã˜ÂµÃ˜Â© RankX SEO - Ã™Æ’Ã™Å Ã™Â Ã™â€ Ã˜Â­Ã™â€¦Ã™Å  Ã˜Â¨Ã™Å Ã˜Â§Ã™â€ Ã˜Â§Ã˜ÂªÃ™Æ’ Ã™Ë†Ã™â€ Ã˜Â³Ã˜ÂªÃ˜Â®Ã˜Â¯Ã™â€¦Ã™â€¡Ã˜Â§.">
  <link rel="canonical" href="{$safeAppUrl}/privacy">
  <style>
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
        <img src="{$logoSrc}" alt="RankX SEO" width="1200" height="400" decoding="async">
      </div>

      <h1>Ã˜Â³Ã™Å Ã˜Â§Ã˜Â³Ã˜Â© Ã˜Â§Ã™â€žÃ˜Â®Ã˜ÂµÃ™Ë†Ã˜ÂµÃ™Å Ã˜Â©</h1>
      <span class="updated">Ã˜Â¢Ã˜Â®Ã˜Â± Ã˜ÂªÃ˜Â­Ã˜Â¯Ã™Å Ã˜Â«: Ã™Å Ã™â€ Ã˜Â§Ã™Å Ã˜Â± 2024</span>

      <p>Ã™â€ Ã˜Â­Ã™â€  Ã™ÂÃ™Å  RankX SEO ("Ã™â€ Ã˜Â­Ã™â€ "Ã˜Å’ "Ã™â€žÃ™â€ Ã˜Â§"Ã˜Å’ Ã˜Â£Ã™Ë† "Ã˜Â§Ã™â€žÃ™â€¦Ã™â€ Ã˜ÂµÃ˜Â©") Ã™â€ Ã™â€šÃ˜Â¯Ã˜Â± Ã˜Â®Ã˜ÂµÃ™Ë†Ã˜ÂµÃ™Å Ã˜ÂªÃ™Æ’ Ã™Ë†Ã™â€ Ã™â€žÃ˜ÂªÃ˜Â²Ã™â€¦ Ã˜Â¨Ã˜Â­Ã™â€¦Ã˜Â§Ã™Å Ã˜Â© Ã˜Â¨Ã™Å Ã˜Â§Ã™â€ Ã˜Â§Ã˜ÂªÃ™Æ’ Ã˜Â§Ã™â€žÃ˜Â´Ã˜Â®Ã˜ÂµÃ™Å Ã˜Â©. Ã˜ÂªÃ™Ë†Ã˜Â¶Ã˜Â­ Ã™â€¡Ã˜Â°Ã™â€¡ Ã˜Â§Ã™â€žÃ˜Â³Ã™Å Ã˜Â§Ã˜Â³Ã˜Â© Ã™Æ’Ã™Å Ã™Â Ã™â€ Ã˜Â¬Ã™â€¦Ã˜Â¹ Ã™Ë†Ã™â€ Ã˜Â³Ã˜ÂªÃ˜Â®Ã˜Â¯Ã™â€¦ Ã™Ë†Ã™â€ Ã˜Â­Ã™â€¦Ã™Å  Ã™â€¦Ã˜Â¹Ã™â€žÃ™Ë†Ã™â€¦Ã˜Â§Ã˜ÂªÃ™Æ’.</p>

      <h2>Ã˜Â§Ã™â€žÃ™â€¦Ã˜Â¹Ã™â€žÃ™Ë†Ã™â€¦Ã˜Â§Ã˜Âª Ã˜Â§Ã™â€žÃ˜ÂªÃ™Å  Ã™â€ Ã˜Â¬Ã™â€¦Ã˜Â¹Ã™â€¡Ã˜Â§</h2>
      <p>Ã™â€ Ã˜Â¬Ã™â€¦Ã˜Â¹ Ã™ÂÃ™â€šÃ˜Â· Ã˜Â§Ã™â€žÃ™â€¦Ã˜Â¹Ã™â€žÃ™Ë†Ã™â€¦Ã˜Â§Ã˜Âª Ã˜Â§Ã™â€žÃ˜Â¶Ã˜Â±Ã™Ë†Ã˜Â±Ã™Å Ã˜Â© Ã™â€žÃ˜ÂªÃ™â€šÃ˜Â¯Ã™Å Ã™â€¦ Ã˜Â®Ã˜Â¯Ã™â€¦Ã˜Â§Ã˜ÂªÃ™â€ Ã˜Â§:</p>
      <ul>
        <li><strong>Ã™â€¦Ã˜Â¹Ã™â€žÃ™Ë†Ã™â€¦Ã˜Â§Ã˜Âª Ã˜Â§Ã™â€žÃ™â€¦Ã˜ÂªÃ˜Â¬Ã˜Â±:</strong> Ã˜Â±Ã˜Â§Ã˜Â¨Ã˜Â· Ã™â€¦Ã˜ÂªÃ˜Â¬Ã˜Â±Ã™Æ’ Ã™Ë†Ã˜Â§Ã˜Â³Ã™â€¦Ã™â€¡ (Ã™â€¦Ã™â€  Ã˜Â³Ã™â€žÃ˜Â©)</li>
        <li><strong>Ã™â€¦Ã˜Â¹Ã™â€žÃ™Ë†Ã™â€¦Ã˜Â§Ã˜Âª Ã˜Â§Ã™â€žÃ™â€¦Ã™â€ Ã˜ÂªÃ˜Â¬Ã˜Â§Ã˜Âª:</strong> Ã˜Â£Ã˜Â³Ã™â€¦Ã˜Â§Ã˜Â¡ Ã™Ë†Ã˜Â£Ã™Ë†Ã˜ÂµÃ˜Â§Ã™Â Ã™Ë†Ã™â€¦Ã™â€ Ã˜ÂªÃ˜Â¬Ã˜Â§Ã˜Âª Ã˜Â§Ã™â€žÃ™â€¦Ã™â€ Ã˜ÂªÃ˜Â¬Ã˜Â§Ã˜Âª Ã˜Â§Ã™â€žÃ˜ÂªÃ™Å  Ã˜ÂªÃ˜Â®Ã˜ÂªÃ˜Â§Ã˜Â± Ã˜ÂªÃ˜Â­Ã˜Â³Ã™Å Ã™â€ Ã™â€¡Ã˜Â§</li>
        <li><strong>Ã™â€¦Ã˜Â¹Ã™â€žÃ™Ë†Ã™â€¦Ã˜Â§Ã˜Âª Ã˜Â§Ã™â€žÃ˜Â­Ã˜Â³Ã˜Â§Ã˜Â¨:</strong> Ã˜Â¨Ã˜Â±Ã™Å Ã˜Â¯Ã™Æ’ Ã˜Â§Ã™â€žÃ˜Â¥Ã™â€žÃ™Æ’Ã˜ÂªÃ˜Â±Ã™Ë†Ã™â€ Ã™Å  (Ã™â€žÃ™â€žÃ˜ÂªÃ™Ë†Ã˜Â§Ã˜ÂµÃ™â€ž Ã™Ë†Ã˜Â¥Ã˜Â±Ã˜Â³Ã˜Â§Ã™â€ž Ã˜Â§Ã™â€žÃ˜Â¥Ã˜Â´Ã˜Â¹Ã˜Â§Ã˜Â±Ã˜Â§Ã˜Âª)</li>
        <li><strong>Ã˜Â¨Ã™Å Ã˜Â§Ã™â€ Ã˜Â§Ã˜Âª Ã˜Â§Ã™â€žÃ˜Â§Ã˜Â³Ã˜ÂªÃ˜Â®Ã˜Â¯Ã˜Â§Ã™â€¦:</strong> Ã˜Â³Ã˜Â¬Ã™â€žÃ˜Â§Ã˜Âª Ã˜Â§Ã™â€žÃ˜ÂªÃ˜Â­Ã˜Â³Ã™Å Ã™â€ Ã˜Â§Ã˜Âª Ã™Ë†Ã˜Â§Ã™â€žÃ˜Â£Ã˜Â±Ã˜ÂµÃ˜Â¯Ã˜Â© Ã˜Â§Ã™â€žÃ™â€¦Ã˜Â³Ã˜ÂªÃ˜Â®Ã˜Â¯Ã™â€¦Ã˜Â©</li>
      </ul>

      <h2>Ã™Æ’Ã™Å Ã™Â Ã™â€ Ã˜Â³Ã˜ÂªÃ˜Â®Ã˜Â¯Ã™â€¦ Ã™â€¦Ã˜Â¹Ã™â€žÃ™Ë†Ã™â€¦Ã˜Â§Ã˜ÂªÃ™Æ’</h2>
      <p>Ã™â€ Ã˜Â³Ã˜ÂªÃ˜Â®Ã˜Â¯Ã™â€¦ Ã™â€¦Ã˜Â¹Ã™â€žÃ™Ë†Ã™â€¦Ã˜Â§Ã˜ÂªÃ™Æ’ Ã™â€žÃ™â€žÃ˜Â£Ã˜ÂºÃ˜Â±Ã˜Â§Ã˜Â¶ Ã˜Â§Ã™â€žÃ˜ÂªÃ˜Â§Ã™â€žÃ™Å Ã˜Â© Ã™ÂÃ™â€šÃ˜Â·:</p>
      <ul>
        <li>Ã˜ÂªÃ™â€šÃ˜Â¯Ã™Å Ã™â€¦ Ã˜Â®Ã˜Â¯Ã™â€¦Ã˜Â§Ã˜Âª Ã˜ÂªÃ˜Â­Ã˜Â³Ã™Å Ã™â€  Ã˜Â§Ã™â€žÃ™â€¦Ã˜Â­Ã˜ÂªÃ™Ë†Ã™â€° Ã˜Â¹Ã˜Â¨Ã˜Â± Ã˜Â§Ã™â€žÃ˜Â°Ã™Æ’Ã˜Â§Ã˜Â¡ Ã˜Â§Ã™â€žÃ˜Â§Ã˜ÂµÃ˜Â·Ã™â€ Ã˜Â§Ã˜Â¹Ã™Å </li>
        <li>Ã˜Â¥Ã˜Â¯Ã˜Â§Ã˜Â±Ã˜Â© Ã˜Â§Ã˜Â´Ã˜ÂªÃ˜Â±Ã˜Â§Ã™Æ’Ã˜Â§Ã˜ÂªÃ™Æ’ Ã™Ë†Ã˜ÂªÃ˜ÂªÃ˜Â¨Ã˜Â¹ Ã˜Â§Ã˜Â³Ã˜ÂªÃ™â€¡Ã™â€žÃ˜Â§Ã™Æ’Ã™Æ’</li>
        <li>Ã˜Â¥Ã˜Â±Ã˜Â³Ã˜Â§Ã™â€ž Ã˜Â¥Ã˜Â´Ã˜Â¹Ã˜Â§Ã˜Â±Ã˜Â§Ã˜Âª Ã™â€¦Ã™â€¡Ã™â€¦Ã˜Â© (Ã˜Â§Ã™â€ Ã˜ÂªÃ™â€¡Ã˜Â§Ã˜Â¡ Ã˜Â§Ã™â€žÃ˜Â§Ã˜Â´Ã˜ÂªÃ˜Â±Ã˜Â§Ã™Æ’Ã˜Å’ Ã˜ÂªÃ˜Â­Ã˜Â¯Ã™Å Ã˜Â«Ã˜Â§Ã˜ÂªÃ˜Å’ Ã˜Â¥Ã™â€žÃ˜Â®)</li>
        <li>Ã˜ÂªÃ˜Â­Ã˜Â³Ã™Å Ã™â€  Ã™â€¦Ã™â€ Ã˜ÂµÃ˜ÂªÃ™â€ Ã˜Â§ Ã™Ë†Ã˜Â®Ã˜Â¯Ã™â€¦Ã˜Â§Ã˜ÂªÃ™â€ Ã˜Â§</li>
        <li>Ã˜Â§Ã™â€žÃ˜Â§Ã™â€¦Ã˜ÂªÃ˜Â«Ã˜Â§Ã™â€ž Ã™â€žÃ™â€žÃ™â€¦Ã˜ÂªÃ˜Â·Ã™â€žÃ˜Â¨Ã˜Â§Ã˜Âª Ã˜Â§Ã™â€žÃ™â€šÃ˜Â§Ã™â€ Ã™Ë†Ã™â€ Ã™Å Ã˜Â©</li>
      </ul>

      <h2>Ã™â€¦Ã˜Â´Ã˜Â§Ã˜Â±Ã™Æ’Ã˜Â© Ã˜Â§Ã™â€žÃ˜Â¨Ã™Å Ã˜Â§Ã™â€ Ã˜Â§Ã˜Âª</h2>
      <p><strong>Ã™â€žÃ˜Â§ Ã™â€ Ã˜Â¨Ã™Å Ã˜Â¹ Ã˜Â¨Ã™Å Ã˜Â§Ã™â€ Ã˜Â§Ã˜ÂªÃ™Æ’ Ã˜Â£Ã˜Â¨Ã˜Â¯Ã™â€¹Ã˜Â§.</strong> Ã™â€žÃ˜Â§ Ã™â€ Ã˜Â´Ã˜Â§Ã˜Â±Ã™Æ’ Ã™â€¦Ã˜Â¹Ã™â€žÃ™Ë†Ã™â€¦Ã˜Â§Ã˜ÂªÃ™Æ’ Ã™â€¦Ã˜Â¹ Ã˜Â£Ã˜Â·Ã˜Â±Ã˜Â§Ã™Â Ã˜Â«Ã˜Â§Ã™â€žÃ˜Â«Ã˜Â© Ã˜Â¥Ã™â€žÃ˜Â§ Ã™ÂÃ™Å  Ã˜Â§Ã™â€žÃ˜Â­Ã˜Â§Ã™â€žÃ˜Â§Ã˜Âª Ã˜Â§Ã™â€žÃ˜ÂªÃ˜Â§Ã™â€žÃ™Å Ã˜Â©:</p>
      <ul>
        <li><strong>Ã™â€¦Ã˜Â¹ Ã˜Â³Ã™â€žÃ˜Â©:</strong> Ã™â€žÃ˜ÂªÃ˜Â­Ã˜Â¯Ã™Å Ã˜Â« Ã˜Â§Ã™â€žÃ™â€¦Ã™â€ Ã˜ÂªÃ˜Â¬Ã˜Â§Ã˜Âª Ã˜Â§Ã™â€žÃ˜ÂªÃ™Å  Ã˜ÂªÃ˜Â­Ã˜Â³Ã™â€˜Ã™â€ Ã™â€¡Ã˜Â§ Ã™ÂÃ™Å  Ã™â€¦Ã˜ÂªÃ˜Â¬Ã˜Â±Ã™Æ’</li>
        <li><strong>Ã™â€¦Ã˜Â¹ OpenAI:</strong> Ã™â€žÃ™â€¦Ã˜Â¹Ã˜Â§Ã™â€žÃ˜Â¬Ã˜Â© Ã˜Â§Ã™â€žÃ™â€ Ã˜ÂµÃ™Ë†Ã˜Âµ Ã˜Â¹Ã˜Â¨Ã˜Â± Ã˜Â§Ã™â€žÃ˜Â°Ã™Æ’Ã˜Â§Ã˜Â¡ Ã˜Â§Ã™â€žÃ˜Â§Ã˜ÂµÃ˜Â·Ã™â€ Ã˜Â§Ã˜Â¹Ã™Å  (Ã˜Â¨Ã™â€¦Ã™Ë†Ã˜Â¬Ã˜Â¨ Ã˜Â´Ã˜Â±Ã™Ë†Ã˜Â·Ã™â€¡Ã™â€¦)</li>
        <li><strong>Ã˜Â¹Ã™â€ Ã˜Â¯ Ã˜Â§Ã™â€žÃ˜Â·Ã™â€žÃ˜Â¨ Ã˜Â§Ã™â€žÃ™â€šÃ˜Â§Ã™â€ Ã™Ë†Ã™â€ Ã™Å :</strong> Ã˜Â¥Ã˜Â°Ã˜Â§ Ã˜Â·Ã™â€žÃ˜Â¨ Ã˜Â°Ã™â€žÃ™Æ’ Ã™â€šÃ˜Â§Ã™â€ Ã™Ë†Ã™â€ Ã™â€¹Ã˜Â§ Ã˜Â£Ã™Ë† Ã˜Â­Ã™Æ’Ã™Ë†Ã™â€¦Ã™Å Ã™â€¹Ã˜Â§</li>
      </ul>

      <h2>Ã˜Â­Ã™â€¦Ã˜Â§Ã™Å Ã˜Â© Ã˜Â§Ã™â€žÃ˜Â¨Ã™Å Ã˜Â§Ã™â€ Ã˜Â§Ã˜Âª</h2>
      <p>Ã™â€ Ã˜ÂªÃ˜Â®Ã˜Â° Ã˜Â¥Ã˜Â¬Ã˜Â±Ã˜Â§Ã˜Â¡Ã˜Â§Ã˜Âª Ã˜Â£Ã™â€¦Ã˜Â§Ã™â€  Ã˜ÂµÃ˜Â§Ã˜Â±Ã™â€¦Ã˜Â© Ã™â€žÃ˜Â­Ã™â€¦Ã˜Â§Ã™Å Ã˜Â© Ã˜Â¨Ã™Å Ã˜Â§Ã™â€ Ã˜Â§Ã˜ÂªÃ™Æ’:</p>
      <ul>
        <li>Ã˜ÂªÃ˜Â´Ã™ÂÃ™Å Ã˜Â± Ã˜Â§Ã™â€žÃ˜Â¨Ã™Å Ã˜Â§Ã™â€ Ã˜Â§Ã˜Âª Ã˜Â£Ã˜Â«Ã™â€ Ã˜Â§Ã˜Â¡ Ã˜Â§Ã™â€žÃ™â€ Ã™â€šÃ™â€ž Ã™Ë†Ã˜Â§Ã™â€žÃ˜ÂªÃ˜Â®Ã˜Â²Ã™Å Ã™â€ </li>
        <li>Ã™Ë†Ã˜ÂµÃ™Ë†Ã™â€ž Ã™â€¦Ã˜Â­Ã˜Â¯Ã™Ë†Ã˜Â¯ Ã™â€žÃ™â€žÃ™â€¦Ã™Ë†Ã˜Â¸Ã™ÂÃ™Å Ã™â€  Ã˜Â§Ã™â€žÃ™â€¦Ã˜Â¹Ã˜ÂªÃ™â€¦Ã˜Â¯Ã™Å Ã™â€ </li>
        <li>Ã™â€¦Ã˜Â±Ã˜Â§Ã˜Â¬Ã˜Â¹Ã˜Â§Ã˜Âª Ã˜Â£Ã™â€¦Ã˜Â§Ã™â€  Ã˜Â¯Ã™Ë†Ã˜Â±Ã™Å Ã˜Â©</li>
        <li>Ã™â€ Ã˜Â³Ã˜Â® Ã˜Â§Ã˜Â­Ã˜ÂªÃ™Å Ã˜Â§Ã˜Â·Ã™Å Ã˜Â© Ã™â€¦Ã™â€ Ã˜ÂªÃ˜Â¸Ã™â€¦Ã˜Â©</li>
      </ul>

      <h2>Ã˜Â­Ã™â€šÃ™Ë†Ã™â€šÃ™Æ’</h2>
      <p>Ã™â€žÃ˜Â¯Ã™Å Ã™Æ’ Ã˜Â§Ã™â€žÃ˜Â­Ã™â€šÃ™Ë†Ã™â€š Ã˜Â§Ã™â€žÃ˜ÂªÃ˜Â§Ã™â€žÃ™Å Ã˜Â©:</p>
      <ul>
        <li><strong>Ã˜Â§Ã™â€žÃ™Ë†Ã˜ÂµÃ™Ë†Ã™â€ž:</strong> Ã˜Â·Ã™â€žÃ˜Â¨ Ã™â€ Ã˜Â³Ã˜Â®Ã˜Â© Ã™â€¦Ã™â€  Ã˜Â¨Ã™Å Ã˜Â§Ã™â€ Ã˜Â§Ã˜ÂªÃ™Æ’</li>
        <li><strong>Ã˜Â§Ã™â€žÃ˜ÂªÃ˜ÂµÃ˜Â­Ã™Å Ã˜Â­:</strong> Ã˜Â·Ã™â€žÃ˜Â¨ Ã˜ÂªÃ˜Â¹Ã˜Â¯Ã™Å Ã™â€ž Ã˜Â£Ã™Å  Ã˜Â¨Ã™Å Ã˜Â§Ã™â€ Ã˜Â§Ã˜Âª Ã˜ÂºÃ™Å Ã˜Â± Ã˜Â¯Ã™â€šÃ™Å Ã™â€šÃ˜Â©</li>
        <li><strong>Ã˜Â§Ã™â€žÃ˜Â­Ã˜Â°Ã™Â:</strong> Ã˜Â·Ã™â€žÃ˜Â¨ Ã˜Â­Ã˜Â°Ã™Â Ã˜Â¨Ã™Å Ã˜Â§Ã™â€ Ã˜Â§Ã˜ÂªÃ™Æ’ (Ã™â€¦Ã˜Â¹ Ã™â€¦Ã˜Â±Ã˜Â§Ã˜Â¹Ã˜Â§Ã˜Â© Ã˜Â§Ã™â€žÃ™â€¦Ã˜ÂªÃ˜Â·Ã™â€žÃ˜Â¨Ã˜Â§Ã˜Âª Ã˜Â§Ã™â€žÃ™â€šÃ˜Â§Ã™â€ Ã™Ë†Ã™â€ Ã™Å Ã˜Â©)</li>
        <li><strong>Ã˜Â§Ã™â€žÃ˜Â§Ã˜Â¹Ã˜ÂªÃ˜Â±Ã˜Â§Ã˜Â¶:</strong> Ã˜Â§Ã™â€žÃ˜Â§Ã˜Â¹Ã˜ÂªÃ˜Â±Ã˜Â§Ã˜Â¶ Ã˜Â¹Ã™â€žÃ™â€° Ã™â€¦Ã˜Â¹Ã˜Â§Ã™â€žÃ˜Â¬Ã˜Â© Ã™â€¦Ã˜Â¹Ã™Å Ã™â€ Ã˜Â© Ã™â€žÃ˜Â¨Ã™Å Ã˜Â§Ã™â€ Ã˜Â§Ã˜ÂªÃ™Æ’</li>
      </ul>

      <h2>Ã˜Â§Ã™â€žÃ˜ÂªÃ˜Â®Ã˜Â²Ã™Å Ã™â€  Ã™Ë†Ã˜Â§Ã™â€žÃ˜Â­Ã™ÂÃ˜Â¸</h2>
      <p>Ã™â€ Ã˜Â­Ã˜ÂªÃ™ÂÃ˜Â¸ Ã˜Â¨Ã˜Â¨Ã™Å Ã˜Â§Ã™â€ Ã˜Â§Ã˜ÂªÃ™Æ’:</p>
      <ul>
        <li>Ã˜Â·Ã˜Â§Ã™â€žÃ™â€¦Ã˜Â§ Ã˜Â­Ã˜Â³Ã˜Â§Ã˜Â¨Ã™Æ’ Ã™â€ Ã˜Â´Ã˜Â·</li>
        <li>Ã™â€žÃ™â€¦Ã˜Â¯Ã˜Â© Ã˜Â³Ã™â€ Ã˜Â© Ã˜Â¨Ã˜Â¹Ã˜Â¯ Ã˜Â¥Ã™â€žÃ˜ÂºÃ˜Â§Ã˜Â¡ Ã˜Â§Ã™â€žÃ˜Â§Ã˜Â´Ã˜ÂªÃ˜Â±Ã˜Â§Ã™Æ’ (Ã™â€žÃ™â€žÃ˜Â§Ã™â€¦Ã˜ÂªÃ˜Â«Ã˜Â§Ã™â€ž Ã˜Â§Ã™â€žÃ™â€šÃ˜Â§Ã™â€ Ã™Ë†Ã™â€ Ã™Å )</li>
        <li>Ã˜Â³Ã˜Â¬Ã™â€žÃ˜Â§Ã˜Âª Ã˜Â§Ã™â€žÃ˜Â§Ã˜Â³Ã˜ÂªÃ˜Â®Ã˜Â¯Ã˜Â§Ã™â€¦ Ã™â€žÃ™â€¦Ã˜Â¯Ã˜Â© Ã™â€žÃ˜Â§ Ã˜ÂªÃ™â€šÃ™â€ž Ã˜Â¹Ã™â€  Ã˜Â§Ã™â€žÃ™â€¦Ã˜Â·Ã™â€žÃ™Ë†Ã˜Â¨ Ã™â€šÃ˜Â§Ã™â€ Ã™Ë†Ã™â€ Ã™Å Ã™â€¹Ã˜Â§</li>
      </ul>

      <h2>Ã™â€¦Ã™â€žÃ™ÂÃ˜Â§Ã˜Âª Ã˜ÂªÃ˜Â¹Ã˜Â±Ã™Å Ã™Â Ã˜Â§Ã™â€žÃ˜Â§Ã˜Â±Ã˜ÂªÃ˜Â¨Ã˜Â§Ã˜Â· (Cookies)</h2>
      <p>Ã™â€ Ã˜Â³Ã˜ÂªÃ˜Â®Ã˜Â¯Ã™â€¦ Ã™â€¦Ã™â€žÃ™ÂÃ˜Â§Ã˜Âª Ã˜ÂªÃ˜Â¹Ã˜Â±Ã™Å Ã™Â Ã˜Â§Ã™â€žÃ˜Â§Ã˜Â±Ã˜ÂªÃ˜Â¨Ã˜Â§Ã˜Â· Ã™â€žÃ™â‚¬:</p>
      <ul>
        <li>Ã˜Â§Ã™â€žÃ˜Â­Ã™ÂÃ˜Â§Ã˜Â¸ Ã˜Â¹Ã™â€žÃ™â€° Ã˜Â¬Ã™â€žÃ˜Â³Ã˜Â© Ã˜ÂªÃ˜Â³Ã˜Â¬Ã™Å Ã™â€ž Ã˜Â§Ã™â€žÃ˜Â¯Ã˜Â®Ã™Ë†Ã™â€ž</li>
        <li>Ã˜ÂªÃ˜Â°Ã™Æ’Ã˜Â± Ã˜ÂªÃ™ÂÃ˜Â¶Ã™Å Ã™â€žÃ˜Â§Ã˜ÂªÃ™Æ’</li>
        <li>Ã˜ÂªÃ˜Â­Ã™â€žÃ™Å Ã™â€ž Ã˜Â§Ã˜Â³Ã˜ÂªÃ˜Â®Ã˜Â¯Ã˜Â§Ã™â€¦ Ã˜Â§Ã™â€žÃ™â€¦Ã™â€ Ã˜ÂµÃ˜Â©</li>
      </ul>

      <h2>Ã˜Â§Ã™â€žÃ˜ÂªÃ˜ÂºÃ™Å Ã™Å Ã˜Â±Ã˜Â§Ã˜Âª Ã˜Â¹Ã™â€žÃ™â€° Ã˜Â§Ã™â€žÃ˜Â³Ã™Å Ã˜Â§Ã˜Â³Ã˜Â©</h2>
      <p>Ã™â€šÃ˜Â¯ Ã™â€ Ã˜Â­Ã˜Â¯Ã˜Â« Ã™â€¡Ã˜Â°Ã™â€¡ Ã˜Â§Ã™â€žÃ˜Â³Ã™Å Ã˜Â§Ã˜Â³Ã˜Â© Ã™â€¦Ã™â€  Ã˜Â­Ã™Å Ã™â€  Ã™â€žÃ˜Â¢Ã˜Â®Ã˜Â±. Ã˜Â³Ã™â€ Ã˜Â¹Ã™â€žÃ™â€¦Ã™Æ’ Ã˜Â¨Ã˜Â£Ã™Å  Ã˜ÂªÃ˜ÂºÃ™Å Ã™Å Ã˜Â±Ã˜Â§Ã˜Âª Ã˜Â¬Ã™Ë†Ã™â€¡Ã˜Â±Ã™Å Ã˜Â© Ã˜Â¹Ã˜Â¨Ã˜Â±:</p>
      <ul>
        <li>Ã˜Â¥Ã˜Â´Ã˜Â¹Ã˜Â§Ã˜Â± Ã™ÂÃ™Å  Ã™â€žÃ™Ë†Ã˜Â­Ã˜Â© Ã˜Â§Ã™â€žÃ˜ÂªÃ˜Â­Ã™Æ’Ã™â€¦</li>
        <li>Ã˜Â¨Ã˜Â±Ã™Å Ã˜Â¯ Ã˜Â¥Ã™â€žÃ™Æ’Ã˜ÂªÃ˜Â±Ã™Ë†Ã™â€ Ã™Å </li>
      </ul>

      <h2>Ã˜Â§Ã˜ÂªÃ˜ÂµÃ™â€ž Ã˜Â¨Ã™â€ Ã˜Â§</h2>
      <p>Ã™â€žÃ˜Â£Ã™Å  Ã˜Â§Ã˜Â³Ã˜ÂªÃ™ÂÃ˜Â³Ã˜Â§Ã˜Â± Ã˜Â­Ã™Ë†Ã™â€ž Ã˜Â³Ã™Å Ã˜Â§Ã˜Â³Ã˜Â© Ã˜Â§Ã™â€žÃ˜Â®Ã˜ÂµÃ™Ë†Ã˜ÂµÃ™Å Ã˜Â©:</p>
      <p><strong>Ã˜Â§Ã™â€žÃ˜Â¨Ã˜Â±Ã™Å Ã˜Â¯ Ã˜Â§Ã™â€žÃ˜Â¥Ã™â€žÃ™Æ’Ã˜ÂªÃ˜Â±Ã™Ë†Ã™â€ Ã™Å :</strong> seo@rankxseo.com</p>
    </div>

    <div class="footer">
      <p>
        <a href="{$safeAppUrl}/">Ã˜Â§Ã™â€žÃ˜Â±Ã˜Â¦Ã™Å Ã˜Â³Ã™Å Ã˜Â©</a> Ã‚Â· 
        <a href="{$safeAppUrl}/about">Ã™â€¦Ã™â€  Ã™â€ Ã˜Â­Ã™â€ </a> Ã‚Â· 
        <a href="{$safeAppUrl}/faq">Ã˜Â§Ã™â€žÃ˜Â£Ã˜Â³Ã˜Â¦Ã™â€žÃ˜Â© Ã˜Â§Ã™â€žÃ˜Â´Ã˜Â§Ã˜Â¦Ã˜Â¹Ã˜Â©</a> Ã‚Â· 
        <a href="{$safeAppUrl}/terms">Ã˜Â§Ã™â€žÃ˜Â´Ã˜Â±Ã™Ë†Ã˜Â· Ã™Ë†Ã˜Â§Ã™â€žÃ˜Â£Ã˜Â­Ã™Æ’Ã˜Â§Ã™â€¦</a>
      </p>
      <p>Ã‚Â© 2024 RankX SEO - Ã˜Â¬Ã™â€¦Ã™Å Ã˜Â¹ Ã˜Â§Ã™â€žÃ˜Â­Ã™â€šÃ™Ë†Ã™â€š Ã™â€¦Ã˜Â­Ã™ÂÃ™Ë†Ã˜Â¸Ã˜Â©</p>
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
        $logoSrc = '/assets/rankxseo-logo.png';
        $faviconSrc = 'https://rankxseo.com/favicon.png';

        $html = <<<HTML
<!doctype html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" type="image/png" href="{$faviconSrc}">
  <link rel="apple-touch-icon" href="{$faviconSrc}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&display=swap">
  <title>Ã˜Â§Ã™â€žÃ˜Â´Ã˜Â±Ã™Ë†Ã˜Â· Ã™Ë†Ã˜Â§Ã™â€žÃ˜Â£Ã˜Â­Ã™Æ’Ã˜Â§Ã™â€¦ | RankX SEO</title>
  <meta name="description" content="Ã˜Â§Ã™â€žÃ˜Â´Ã˜Â±Ã™Ë†Ã˜Â· Ã™Ë†Ã˜Â§Ã™â€žÃ˜Â£Ã˜Â­Ã™Æ’Ã˜Â§Ã™â€¦ Ã˜Â§Ã™â€žÃ˜Â®Ã˜Â§Ã˜ÂµÃ˜Â© Ã˜Â¨Ã˜Â§Ã˜Â³Ã˜ÂªÃ˜Â®Ã˜Â¯Ã˜Â§Ã™â€¦ Ã™â€¦Ã™â€ Ã˜ÂµÃ˜Â© RankX SEO.">
  <link rel="canonical" href="{$safeAppUrl}/terms">
  <style>
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
        <img src="{$logoSrc}" alt="RankX SEO" width="1200" height="400" decoding="async">
      </div>

      <h1>Ã˜Â§Ã™â€žÃ˜Â´Ã˜Â±Ã™Ë†Ã˜Â· Ã™Ë†Ã˜Â§Ã™â€žÃ˜Â£Ã˜Â­Ã™Æ’Ã˜Â§Ã™â€¦</h1>
      <span class="updated">Ã˜Â¢Ã˜Â®Ã˜Â± Ã˜ÂªÃ˜Â­Ã˜Â¯Ã™Å Ã˜Â«: Ã™Å Ã™â€ Ã˜Â§Ã™Å Ã˜Â± 2024</span>

      <p>Ã™â€¦Ã˜Â±Ã˜Â­Ã˜Â¨Ã™â€¹Ã˜Â§ Ã˜Â¨Ã™Æ’ Ã™ÂÃ™Å  RankX SEO! Ã˜Â¨Ã˜Â§Ã˜Â³Ã˜ÂªÃ˜Â®Ã˜Â¯Ã˜Â§Ã™â€¦Ã™Æ’ Ã™â€žÃ™â€¦Ã™â€ Ã˜ÂµÃ˜ÂªÃ™â€ Ã˜Â§Ã˜Å’ Ã™ÂÃ˜Â£Ã™â€ Ã˜Âª Ã˜ÂªÃ™Ë†Ã˜Â§Ã™ÂÃ™â€š Ã˜Â¹Ã™â€žÃ™â€° Ã˜Â§Ã™â€žÃ˜Â´Ã˜Â±Ã™Ë†Ã˜Â· Ã™Ë†Ã˜Â§Ã™â€žÃ˜Â£Ã˜Â­Ã™Æ’Ã˜Â§Ã™â€¦ Ã˜Â§Ã™â€žÃ˜ÂªÃ˜Â§Ã™â€žÃ™Å Ã˜Â©. Ã™Å Ã˜Â±Ã˜Â¬Ã™â€° Ã™â€šÃ˜Â±Ã˜Â§Ã˜Â¡Ã˜ÂªÃ™â€¡Ã˜Â§ Ã˜Â¨Ã˜Â¹Ã™â€ Ã˜Â§Ã™Å Ã˜Â©.</p>

      <h2>1. Ã™â€šÃ˜Â¨Ã™Ë†Ã™â€ž Ã˜Â§Ã™â€žÃ˜Â´Ã˜Â±Ã™Ë†Ã˜Â·</h2>
      <p>Ã˜Â¨Ã˜Â§Ã™â€žÃ™â€¦Ã˜ÂªÃ˜Â§Ã˜Â¨Ã˜Â¹Ã˜Â© Ã™ÂÃ™Å  Ã˜Â§Ã˜Â³Ã˜ÂªÃ˜Â®Ã˜Â¯Ã˜Â§Ã™â€¦ RankX SEOÃ˜Å’ Ã™ÂÃ˜Â£Ã™â€ Ã˜Âª:</p>
      <ul>
        <li>Ã˜ÂªÃ˜Â¤Ã™Æ’Ã˜Â¯ Ã˜Â£Ã™â€ Ã™Æ’ Ã˜Â¨Ã™â€žÃ˜ÂºÃ˜Âª Ã˜Â³Ã™â€  Ã˜Â§Ã™â€žÃ˜Â±Ã˜Â´Ã˜Â¯ Ã™â€šÃ˜Â§Ã™â€ Ã™Ë†Ã™â€ Ã™Å Ã™â€¹Ã˜Â§</li>
        <li>Ã˜ÂªÃ™â€¦Ã™â€žÃ™Æ’ Ã˜ÂµÃ™â€žÃ˜Â§Ã˜Â­Ã™Å Ã˜Â© Ã˜Â§Ã™â€žÃ˜Â¯Ã˜Â®Ã™Ë†Ã™â€ž Ã™â€žÃ™â€¦Ã˜ÂªÃ˜Â¬Ã˜Â±Ã™Æ’ Ã˜Â¹Ã™â€žÃ™â€° Ã˜Â³Ã™â€žÃ˜Â©</li>
        <li>Ã˜ÂªÃ™Ë†Ã˜Â§Ã™ÂÃ™â€š Ã˜Â¹Ã™â€žÃ™â€° Ã˜Â§Ã™â€žÃ˜Â§Ã™â€žÃ˜ÂªÃ˜Â²Ã˜Â§Ã™â€¦ Ã˜Â¨Ã™â€¡Ã˜Â°Ã™â€¡ Ã˜Â§Ã™â€žÃ˜Â´Ã˜Â±Ã™Ë†Ã˜Â·</li>
      </ul>

      <h2>2. Ã™Ë†Ã˜ÂµÃ™Â Ã˜Â§Ã™â€žÃ˜Â®Ã˜Â¯Ã™â€¦Ã˜Â©</h2>
      <p>RankX SEO Ã˜ÂªÃ™Ë†Ã™ÂÃ˜Â±:</p>
      <ul>
        <li>Ã˜Â£Ã˜Â¯Ã™Ë†Ã˜Â§Ã˜Âª Ã˜ÂªÃ˜Â­Ã˜Â³Ã™Å Ã™â€  Ã™â€¦Ã˜Â­Ã˜ÂªÃ™Ë†Ã™â€° Ã˜Â§Ã™â€žÃ™â€¦Ã™â€ Ã˜ÂªÃ˜Â¬Ã˜Â§Ã˜Âª Ã˜Â¨Ã˜Â§Ã˜Â³Ã˜ÂªÃ˜Â®Ã˜Â¯Ã˜Â§Ã™â€¦ Ã˜Â§Ã™â€žÃ˜Â°Ã™Æ’Ã˜Â§Ã˜Â¡ Ã˜Â§Ã™â€žÃ˜Â§Ã˜ÂµÃ˜Â·Ã™â€ Ã˜Â§Ã˜Â¹Ã™Å </li>
        <li>Ã˜ÂªÃ˜Â­Ã˜Â³Ã™Å Ã™â€  Meta Tags Ã™Ë† ALT Ã™â€žÃ™â€žÃ˜ÂµÃ™Ë†Ã˜Â±</li>
        <li>Ã˜Â£Ã˜Â¯Ã™Ë†Ã˜Â§Ã˜Âª Ã˜ÂªÃ˜Â­Ã™â€žÃ™Å Ã™â€ž Ã˜Â§Ã™â€žÃ™Æ’Ã™â€žÃ™â€¦Ã˜Â§Ã˜Âª Ã˜Â§Ã™â€žÃ™â€¦Ã™ÂÃ˜ÂªÃ˜Â§Ã˜Â­Ã™Å Ã˜Â© Ã™Ë†Ã˜Â§Ã™â€žÃ™â€¦Ã™â€ Ã˜Â§Ã™ÂÃ˜Â³Ã™Å Ã™â€ </li>
        <li>Ã˜Â¥Ã˜Â¯Ã˜Â§Ã˜Â±Ã˜Â© Ã˜Â§Ã™â€žÃ˜Â§Ã˜Â´Ã˜ÂªÃ˜Â±Ã˜Â§Ã™Æ’Ã˜Â§Ã˜Âª Ã™Ë†Ã˜Â§Ã™â€žÃ™â€¦Ã˜Â³Ã˜ÂªÃ˜Â®Ã˜Â¯Ã™â€¦Ã™Å Ã™â€ </li>
      </ul>

      <h2>3. Ã˜Â§Ã™â€žÃ˜Â§Ã˜Â´Ã˜ÂªÃ˜Â±Ã˜Â§Ã™Æ’ Ã™Ë†Ã˜Â§Ã™â€žÃ˜Â¯Ã™ÂÃ˜Â¹</h2>
      <ul>
        <li>Ã˜ÂªÃ˜Â­Ã˜Â¯Ã˜Â¯ Ã˜Â§Ã™â€žÃ˜Â¨Ã˜Â§Ã™â€šÃ˜Â§Ã˜Âª Ã™Ë†Ã˜Â§Ã™â€žÃ˜Â£Ã˜Â³Ã˜Â¹Ã˜Â§Ã˜Â± Ã™â€¦Ã™â€  Ã™â€šÃ˜Â¨Ã™â€žÃ™â€ Ã˜Â§ Ã™Ë†Ã™â€šÃ˜Â¯ Ã˜ÂªÃ˜ÂªÃ˜ÂºÃ™Å Ã˜Â±</li>
        <li>Ã˜Â§Ã™â€žÃ˜Â§Ã˜Â´Ã˜ÂªÃ˜Â±Ã˜Â§Ã™Æ’ Ã™Å Ã˜ÂªÃ˜Â¬Ã˜Â¯Ã˜Â¯ Ã˜ÂªÃ™â€žÃ™â€šÃ˜Â§Ã˜Â¦Ã™Å Ã™â€¹Ã˜Â§ Ã™â€¦Ã˜Â§ Ã™â€žÃ™â€¦ Ã™Å Ã™ÂÃ™â€žÃ˜ÂºÃ™Å½</li>
        <li>Ã™Å Ã™â€¦Ã™Æ’Ã™â€  Ã˜Â¥Ã™â€žÃ˜ÂºÃ˜Â§Ã˜Â¡ Ã˜Â§Ã™â€žÃ˜Â§Ã˜Â´Ã˜ÂªÃ˜Â±Ã˜Â§Ã™Æ’ Ã™ÂÃ™Å  Ã˜Â£Ã™Å  Ã™Ë†Ã™â€šÃ˜Âª Ã™â€¦Ã™â€  Ã™â€žÃ™Ë†Ã˜Â­Ã˜Â© Ã˜Â§Ã™â€žÃ˜ÂªÃ˜Â­Ã™Æ’Ã™â€¦</li>
        <li>Ã™â€žÃ˜Â§ Ã™Å Ã™Ë†Ã˜Â¬Ã˜Â¯ Ã˜Â§Ã˜Â³Ã˜ÂªÃ˜Â±Ã˜Â¯Ã˜Â§Ã˜Â¯ Ã™â€žÃ™â€žÃ˜Â£Ã˜Â´Ã™â€¡Ã˜Â± Ã˜Â§Ã™â€žÃ™â€¦Ã˜Â³Ã˜ÂªÃ™â€¡Ã™â€žÃ™Æ’Ã˜Â©</li>
      </ul>

      <h2>4. Ã˜Â§Ã™â€žÃ˜Â§Ã˜Â³Ã˜ÂªÃ˜Â®Ã˜Â¯Ã˜Â§Ã™â€¦ Ã˜Â§Ã™â€žÃ™â€¦Ã˜Â³Ã™â€¦Ã™Ë†Ã˜Â­</h2>
      <p>Ã™Å Ã™ÂÃ˜Â³Ã™â€¦Ã˜Â­ Ã™â€žÃ™Æ’:</p>
      <ul>
        <li>Ã˜Â§Ã˜Â³Ã˜ÂªÃ˜Â®Ã˜Â¯Ã˜Â§Ã™â€¦ Ã˜Â§Ã™â€žÃ™â€¦Ã™â€ Ã˜ÂµÃ˜Â© Ã™â€žÃ˜ÂªÃ˜Â­Ã˜Â³Ã™Å Ã™â€  Ã™â€¦Ã˜ÂªÃ˜Â§Ã˜Â¬Ã˜Â±Ã™Æ’ Ã˜Â§Ã™â€žÃ˜Â®Ã˜Â§Ã˜ÂµÃ˜Â©</li>
        <li>Ã˜Â§Ã™â€žÃ™Ë†Ã˜ÂµÃ™Ë†Ã™â€ž Ã™â€žÃ™â€žÃ™â€¦Ã™â€ Ã˜ÂµÃ˜Â© Ã˜Â¹Ã˜Â¨Ã˜Â± Ã˜Â­Ã˜Â³Ã˜Â§Ã˜Â¨Ã™Æ’ Ã™ÂÃ™â€šÃ˜Â·</li>
        <li>Ã™â€¦Ã˜Â´Ã˜Â§Ã˜Â±Ã™Æ’Ã˜Â© Ã˜Â§Ã™â€žÃ™â€ Ã˜ÂªÃ˜Â§Ã˜Â¦Ã˜Â¬ Ã™â€¦Ã˜Â¹ Ã™ÂÃ˜Â±Ã™Å Ã™â€šÃ™Æ’</li>
      </ul>

      <h2>5. Ã˜Â§Ã™â€žÃ˜Â§Ã˜Â³Ã˜ÂªÃ˜Â®Ã˜Â¯Ã˜Â§Ã™â€¦ Ã˜Â§Ã™â€žÃ™â€¦Ã˜Â­Ã˜Â¸Ã™Ë†Ã˜Â±</h2>
      <div class="highlight">
        <strong>Ã™â€¦Ã™â€¦Ã™â€ Ã™Ë†Ã˜Â¹:</strong>
        <ul style="margin:8px 0 0">
          <li>Ã˜Â§Ã˜Â³Ã˜ÂªÃ˜Â®Ã˜Â¯Ã˜Â§Ã™â€¦ Ã˜Â§Ã™â€žÃ™â€¦Ã™â€ Ã˜ÂµÃ˜Â© Ã™â€žÃ˜Â£Ã˜ÂºÃ˜Â±Ã˜Â§Ã˜Â¶ Ã˜ÂºÃ™Å Ã˜Â± Ã™â€¦Ã˜Â´Ã˜Â±Ã™Ë†Ã˜Â¹Ã˜Â©</li>
          <li>Ã™â€¦Ã˜Â­Ã˜Â§Ã™Ë†Ã™â€žÃ˜Â© Ã˜Â§Ã˜Â®Ã˜ÂªÃ˜Â±Ã˜Â§Ã™â€š Ã˜Â£Ã™Ë† Ã˜Â¥Ã™Å Ã™â€šÃ˜Â§Ã™Â Ã˜Â§Ã™â€žÃ˜Â®Ã˜Â¯Ã™â€¦Ã˜Â©</li>
          <li>Ã˜Â¥Ã˜Â¹Ã˜Â§Ã˜Â¯Ã˜Â© Ã˜Â¨Ã™Å Ã˜Â¹ Ã˜Â§Ã™â€žÃ˜Â®Ã˜Â¯Ã™â€¦Ã˜Â© Ã˜Â¯Ã™Ë†Ã™â€  Ã˜Â¥Ã˜Â°Ã™â€ </li>
          <li>Ã™â€ Ã˜Â´Ã˜Â± Ã™â€¦Ã˜Â­Ã˜ÂªÃ™Ë†Ã™â€° Ã™â€¦Ã˜Â³Ã™Å Ã˜Â¡ Ã˜Â£Ã™Ë† Ã™â€¦Ã˜Â®Ã˜Â§Ã™â€žÃ™Â</li>
          <li>Ã˜Â§Ã˜Â³Ã˜ÂªÃ˜Â®Ã˜Â¯Ã˜Â§Ã™â€¦ Ã˜Â®Ã˜Â¯Ã™â€¦Ã˜Â§Ã˜ÂªÃ™â€ Ã˜Â§ Ã™â€žÃ˜ÂªÃ™Ë†Ã™â€žÃ™Å Ã˜Â¯ Ã™â€¦Ã˜Â­Ã˜ÂªÃ™Ë†Ã™â€° Ã˜Â¶Ã˜Â§Ã˜Â± Ã˜Â£Ã™Ë† Ã™â€¦Ã˜Â¶Ã™â€žÃ™â€ž</li>
        </ul>
      </div>

      <h2>6. Ã˜Â­Ã™â€šÃ™Ë†Ã™â€š Ã˜Â§Ã™â€žÃ™â€¦Ã™â€žÃ™Æ’Ã™Å Ã˜Â© Ã˜Â§Ã™â€žÃ™ÂÃ™Æ’Ã˜Â±Ã™Å Ã˜Â©</h2>
      <p>Ã™â€ Ã˜Â­Ã˜ÂªÃ™ÂÃ˜Â¸ Ã˜Â¨Ã™â‚¬:</p>
      <ul>
        <li>Ã˜Â¬Ã™â€¦Ã™Å Ã˜Â¹ Ã˜Â­Ã™â€šÃ™Ë†Ã™â€š Ã˜Â§Ã™â€žÃ™â€¦Ã™â€ Ã˜ÂµÃ˜Â© Ã™Ë†Ã˜Â§Ã™â€žÃ˜Â¨Ã˜Â±Ã™â€ Ã˜Â§Ã™â€¦Ã˜Â¬</li>
        <li>Ã˜Â§Ã™â€žÃ˜Â¹Ã™â€žÃ˜Â§Ã™â€¦Ã˜Â§Ã˜Âª Ã˜Â§Ã™â€žÃ˜ÂªÃ˜Â¬Ã˜Â§Ã˜Â±Ã™Å Ã˜Â© Ã™Ë†Ã˜Â§Ã™â€žÃ˜Â´Ã˜Â¹Ã˜Â§Ã˜Â±Ã˜Â§Ã˜Âª</li>
        <li>Ã˜Â£Ã™Å  Ã™â€¦Ã˜Â­Ã˜ÂªÃ™Ë†Ã™â€° Ã˜Â£Ã™Ë† Ã™Æ’Ã™Ë†Ã˜Â¯ Ã™â€¦Ã™â€šÃ˜Â¯Ã™â€¦ Ã™â€¦Ã™â€  Ã˜Â¬Ã˜Â§Ã™â€ Ã˜Â¨Ã™â€ Ã˜Â§</li>
      </ul>
      <p>Ã˜Â£Ã™â€ Ã˜Âª Ã˜ÂªÃ˜Â­Ã˜ÂªÃ™ÂÃ˜Â¸ Ã˜Â¨Ã™â‚¬:</p>
      <ul>
        <li>Ã˜Â­Ã™â€šÃ™Ë†Ã™â€š Ã˜Â§Ã™â€žÃ™â€¦Ã˜Â­Ã˜ÂªÃ™Ë†Ã™â€° Ã˜Â§Ã™â€žÃ˜Â®Ã˜Â§Ã˜Âµ Ã˜Â¨Ã™â€¦Ã˜ÂªÃ˜Â¬Ã˜Â±Ã™Æ’</li>
        <li>Ã˜Â­Ã™â€šÃ™Ë†Ã™â€š Ã˜Â£Ã™Ë†Ã˜ÂµÃ˜Â§Ã™Â Ã™â€¦Ã™â€ Ã˜ÂªÃ˜Â¬Ã˜Â§Ã˜ÂªÃ™Æ’</li>
      </ul>

      <h2>7. Ã˜Â­Ã˜Â¯Ã™Ë†Ã˜Â¯ Ã˜Â§Ã™â€žÃ™â€¦Ã˜Â³Ã˜Â¤Ã™Ë†Ã™â€žÃ™Å Ã˜Â©</h2>
      <p>RankX SEO Ã˜ÂºÃ™Å Ã˜Â± Ã™â€¦Ã˜Â³Ã˜Â¤Ã™Ë†Ã™â€žÃ˜Â© Ã˜Â¹Ã™â€ :</p>
      <ul>
        <li>Ã˜Â§Ã™â€žÃ™â€ Ã˜ÂªÃ˜Â§Ã˜Â¦Ã˜Â¬ Ã˜Â§Ã™â€žÃ™â€ Ã™â€¡Ã˜Â§Ã˜Â¦Ã™Å Ã˜Â© Ã™â€žÃ˜ÂªÃ˜Â­Ã˜Â³Ã™Å Ã™â€ Ã˜Â§Ã˜Âª Ã˜Â§Ã™â€žÃ˜Â¨Ã˜Â­Ã˜Â« (Ã˜ÂªÃ˜Â¹Ã˜ÂªÃ™â€¦Ã˜Â¯ Ã˜Â¹Ã™â€žÃ™â€° Ã˜Â¹Ã™Ë†Ã˜Â§Ã™â€¦Ã™â€ž Ã˜Â®Ã˜Â§Ã˜Â±Ã˜Â¬Ã™Å Ã˜Â©)</li>
        <li>Ã˜Â£Ã™Å  Ã˜Â®Ã˜Â³Ã˜Â§Ã˜Â±Ã˜Â© Ã™â€ Ã˜Â§Ã˜ÂªÃ˜Â¬Ã˜Â© Ã˜Â¹Ã™â€  Ã˜Â§Ã˜Â³Ã˜ÂªÃ˜Â®Ã˜Â¯Ã˜Â§Ã™â€¦ Ã˜Â§Ã™â€žÃ™â€¦Ã™â€ Ã˜ÂµÃ˜Â©</li>
        <li>Ã˜Â§Ã™â€ Ã™â€šÃ˜Â·Ã˜Â§Ã˜Â¹ Ã˜Â§Ã™â€žÃ˜Â®Ã˜Â¯Ã™â€¦Ã˜Â© Ã˜Â§Ã™â€žÃ™â€¦Ã˜Â¤Ã™â€šÃ˜Âª</li>
        <li>Ã˜ÂªÃ˜ÂºÃ™Å Ã™Å Ã˜Â±Ã˜Â§Ã˜Âª Ã™ÂÃ™Å  Ã˜Â³Ã™Å Ã˜Â§Ã˜Â³Ã˜Â§Ã˜Âª Ã˜Â³Ã™â€žÃ˜Â© Ã˜Â£Ã™Ë† Google</li>
      </ul>

      <h2>8. Ã˜Â¥Ã™â€ Ã™â€¡Ã˜Â§Ã˜Â¡ Ã˜Â§Ã™â€žÃ˜Â®Ã˜Â¯Ã™â€¦Ã˜Â©</h2>
      <p>Ã™â€ Ã˜Â­Ã˜ÂªÃ™ÂÃ˜Â¸ Ã˜Â¨Ã˜Â§Ã™â€žÃ˜Â­Ã™â€š Ã™ÂÃ™Å :</p>
      <ul>
        <li>Ã˜Â¥Ã™â€ Ã™â€¡Ã˜Â§Ã˜Â¡ Ã˜Â£Ã™Å  Ã˜Â­Ã˜Â³Ã˜Â§Ã˜Â¨ Ã™Å Ã˜Â®Ã˜Â±Ã™â€š Ã˜Â§Ã™â€žÃ˜Â´Ã˜Â±Ã™Ë†Ã˜Â·</li>
        <li>Ã˜Â¥Ã™Å Ã™â€šÃ˜Â§Ã™Â Ã˜Â§Ã™â€žÃ™â€¦Ã™â€ Ã˜ÂµÃ˜Â© Ã™â€¦Ã˜Â¤Ã™â€šÃ˜ÂªÃ™â€¹Ã˜Â§ Ã™â€žÃ™â€žÃ˜ÂµÃ™Å Ã˜Â§Ã™â€ Ã˜Â©</li>
        <li>Ã˜ÂªÃ˜Â¹Ã˜Â¯Ã™Å Ã™â€ž Ã˜Â£Ã™Ë† Ã˜Â¥Ã™Å Ã™â€šÃ˜Â§Ã™Â Ã˜Â£Ã™Å  Ã™â€¦Ã™Å Ã˜Â²Ã˜Â©</li>
      </ul>

      <h2>9. Ã˜Â¥Ã˜Â®Ã™â€žÃ˜Â§Ã˜Â¡ Ã˜Â§Ã™â€žÃ™â€¦Ã˜Â³Ã˜Â¤Ã™Ë†Ã™â€žÃ™Å Ã˜Â©</h2>
      <p>Ã˜Â§Ã™â€žÃ˜Â®Ã˜Â¯Ã™â€¦Ã˜Â§Ã˜Âª Ã˜ÂªÃ™ÂÃ™â€šÃ˜Â¯Ã™â€¦ "Ã™Æ’Ã™â€¦Ã˜Â§ Ã™â€¡Ã™Å ". Ã™â€žÃ˜Â§ Ã™â€ Ã™â€šÃ˜Â¯Ã™â€¦ Ã˜Â£Ã™Å  Ã˜Â¶Ã™â€¦Ã˜Â§Ã™â€ Ã˜Â§Ã˜Âª:</p>
      <ul>
        <li>Ã˜Â¨Ã˜Â£Ã™â€  Ã˜Â§Ã™â€žÃ™â€ Ã˜ÂªÃ˜Â§Ã˜Â¦Ã˜Â¬ Ã˜Â³Ã˜ÂªÃ™Æ’Ã™Ë†Ã™â€  Ã™â€¦Ã˜Â«Ã˜Â§Ã™â€žÃ™Å Ã˜Â©</li>
        <li>Ã˜Â¨Ã˜Â§Ã™â€žÃ˜ÂªÃ™Ë†Ã™ÂÃ˜Â± Ã˜Â§Ã™â€žÃ™â€¦Ã˜Â³Ã˜ÂªÃ™â€¦Ã˜Â± Ã™â€žÃ™â€žÃ˜Â®Ã˜Â¯Ã™â€¦Ã˜Â©</li>
        <li>Ã˜Â®Ã™â€žÃ™Ë† Ã˜Â§Ã™â€žÃ˜Â®Ã˜Â¯Ã™â€¦Ã˜Â© Ã™â€¦Ã™â€  Ã˜Â§Ã™â€žÃ˜Â£Ã˜Â®Ã˜Â·Ã˜Â§Ã˜Â¡</li>
      </ul>

      <h2>10. Ã˜Â§Ã™â€žÃ˜ÂªÃ˜Â¹Ã˜Â¯Ã™Å Ã™â€žÃ˜Â§Ã˜Âª</h2>
      <p>Ã™â€ Ã˜Â­Ã˜ÂªÃ™ÂÃ˜Â¸ Ã˜Â¨Ã˜Â§Ã™â€žÃ˜Â­Ã™â€š Ã™ÂÃ™Å  Ã˜ÂªÃ˜Â¹Ã˜Â¯Ã™Å Ã™â€ž Ã™â€¡Ã˜Â°Ã™â€¡ Ã˜Â§Ã™â€žÃ˜Â´Ã˜Â±Ã™Ë†Ã˜Â·. Ã˜Â³Ã™Å Ã˜ÂªÃ™â€¦:</p>
      <ul>
        <li>Ã˜Â¥Ã˜Â´Ã˜Â¹Ã˜Â§Ã˜Â±Ã™Æ’ Ã˜Â¨Ã˜Â£Ã™Å  Ã˜ÂªÃ˜ÂºÃ™Å Ã™Å Ã˜Â±Ã˜Â§Ã˜Âª Ã˜Â¬Ã™Ë†Ã™â€¡Ã˜Â±Ã™Å Ã˜Â©</li>
        <li>Ã™â€ Ã˜Â´Ã˜Â± Ã˜Â§Ã™â€žÃ˜Â´Ã˜Â±Ã™Ë†Ã˜Â· Ã˜Â§Ã™â€žÃ™â€¦Ã˜Â­Ã˜Â¯Ã˜Â«Ã˜Â© Ã˜Â¹Ã™â€žÃ™â€° Ã˜Â§Ã™â€žÃ™â€¦Ã™Ë†Ã™â€šÃ˜Â¹</li>
        <li>Ã˜Â§Ã˜Â³Ã˜ÂªÃ™â€¦Ã˜Â±Ã˜Â§Ã˜Â±Ã™Æ’ Ã™ÂÃ™Å  Ã˜Â§Ã™â€žÃ˜Â§Ã˜Â³Ã˜ÂªÃ˜Â®Ã˜Â¯Ã˜Â§Ã™â€¦ Ã™Å Ã˜Â¹Ã™â€ Ã™Å  Ã™â€šÃ˜Â¨Ã™Ë†Ã™â€ž Ã˜Â§Ã™â€žÃ˜Â´Ã˜Â±Ã™Ë†Ã˜Â· Ã˜Â§Ã™â€žÃ˜Â¬Ã˜Â¯Ã™Å Ã˜Â¯Ã˜Â©</li>
      </ul>

      <h2>11. Ã˜Â§Ã™â€žÃ™â€šÃ˜Â§Ã™â€ Ã™Ë†Ã™â€  Ã˜Â§Ã™â€žÃ˜Â­Ã˜Â§Ã™Æ’Ã™â€¦</h2>
      <p>Ã˜ÂªÃ˜Â®Ã˜Â¶Ã˜Â¹ Ã™â€¡Ã˜Â°Ã™â€¡ Ã˜Â§Ã™â€žÃ˜Â´Ã˜Â±Ã™Ë†Ã˜Â· Ã™â€žÃ™â€šÃ™Ë†Ã˜Â§Ã™â€ Ã™Å Ã™â€  Ã˜Â§Ã™â€žÃ™â€¦Ã™â€¦Ã™â€žÃ™Æ’Ã˜Â© Ã˜Â§Ã™â€žÃ˜Â¹Ã˜Â±Ã˜Â¨Ã™Å Ã˜Â© Ã˜Â§Ã™â€žÃ˜Â³Ã˜Â¹Ã™Ë†Ã˜Â¯Ã™Å Ã˜Â©Ã˜Å’ Ã™Ë†Ã™Å Ã˜ÂªÃ™â€¦ Ã˜Â­Ã™â€ž Ã˜Â£Ã™Å  Ã™â€ Ã˜Â²Ã˜Â§Ã˜Â¹ Ã˜Â£Ã™â€¦Ã˜Â§Ã™â€¦ Ã™â€¦Ã˜Â­Ã˜Â§Ã™Æ’Ã™â€¦Ã™â€¡Ã˜Â§.</p>

      <h2>12. Ã˜Â§Ã™â€žÃ˜ÂªÃ™Ë†Ã˜Â§Ã˜ÂµÃ™â€ž</h2>
      <p>Ã™â€žÃ˜Â£Ã™Å  Ã˜Â§Ã˜Â³Ã˜ÂªÃ™ÂÃ˜Â³Ã˜Â§Ã˜Â±:</p>
      <p><strong>Ã˜Â§Ã™â€žÃ˜Â¨Ã˜Â±Ã™Å Ã˜Â¯:</strong> seo@rankxseo.com</p>
    </div>

    <div class="footer">
      <p>
        <a href="{$safeAppUrl}/">Ã˜Â§Ã™â€žÃ˜Â±Ã˜Â¦Ã™Å Ã˜Â³Ã™Å Ã˜Â©</a> Ã‚Â· 
        <a href="{$safeAppUrl}/about">Ã™â€¦Ã™â€  Ã™â€ Ã˜Â­Ã™â€ </a> Ã‚Â· 
        <a href="{$safeAppUrl}/faq">Ã˜Â§Ã™â€žÃ˜Â£Ã˜Â³Ã˜Â¦Ã™â€žÃ˜Â© Ã˜Â§Ã™â€žÃ˜Â´Ã˜Â§Ã˜Â¦Ã˜Â¹Ã˜Â©</a> Ã‚Â· 
        <a href="{$safeAppUrl}/privacy">Ã˜Â³Ã™Å Ã˜Â§Ã˜Â³Ã˜Â© Ã˜Â§Ã™â€žÃ˜Â®Ã˜ÂµÃ™Ë†Ã˜ÂµÃ™Å Ã˜Â©</a>
      </p>
      <p>Ã‚Â© 2024 RankX SEO - Ã˜Â¬Ã™â€¦Ã™Å Ã˜Â¹ Ã˜Â§Ã™â€žÃ˜Â­Ã™â€šÃ™Ë†Ã™â€š Ã™â€¦Ã˜Â­Ã™ÂÃ™Ë†Ã˜Â¸Ã˜Â©</p>
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
        $logoSrc = '/assets/rankxseo-logo.png';
        $faviconSrc = 'https://rankxseo.com/favicon.png';
        $loginHref = $safeAppUrl . '/login';

        $plans = Plans::all();
        $plansHtml = '';

        foreach ($plans as $plan) {
            $isFeatured = $plan['is_featured'];
            $featuredClass = $isFeatured ? ' featured' : '';
            $featuredBadge = $isFeatured ? '<span class="featured-badge">Ã¢Â­Â Ã˜Â§Ã™â€žÃ˜Â£Ã™Æ’Ã˜Â«Ã˜Â± Ã˜Â´Ã˜Â¹Ã˜Â¨Ã™Å Ã˜Â©</span>' : '';
            
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
                    $quotasHtml .= '<li>Ã˜ÂºÃ™Å Ã˜Â± Ã™â€¦Ã™ÂÃ˜Â¹Ã™â€žÃ˜Â© - Ã˜Â±Ã™â€šÃ™â€˜Ã™Å  Ã˜Â§Ã™â€žÃ˜Â§Ã˜Â´Ã˜ÂªÃ˜Â±Ã˜Â§Ã™Æ’</li>';
                    continue;
                }
                $quotasHtml .= '<li>' . $value . ' ' . Plans::quotaLabel($key) . '</li>';
            }

            $extrasHtml = '';
            if (!empty($plan['extras'])) {
                $extrasLabels = [
                    'activity_logs' => 'Ã˜Â³Ã˜Â¬Ã™â€ž Ã˜Â§Ã™â€žÃ˜Â¹Ã™â€¦Ã™â€žÃ™Å Ã˜Â§Ã˜Âª Ã™Ë†Ã˜Â§Ã™â€žÃ˜ÂªÃ˜ÂµÃ˜Â¯Ã™Å Ã˜Â±',
                    'export' => 'Ã˜ÂªÃ˜ÂµÃ˜Â¯Ã™Å Ã˜Â± Ã˜Â§Ã™â€žÃ˜Â¨Ã™Å Ã˜Â§Ã™â€ Ã˜Â§Ã˜Âª',
                    'faster_performance' => 'Ã˜Â£Ã˜Â¯Ã˜Â§Ã˜Â¡ Ã˜Â£Ã˜Â³Ã˜Â±Ã˜Â¹ Ã™Ë†Ã™â€ Ã˜ÂªÃ˜Â§Ã˜Â¦Ã˜Â¬ Ã˜Â£Ã™ÂÃ˜Â¶Ã™â€ž',
                    'priority_support' => 'Ã˜Â£Ã™Ë†Ã™â€žÃ™Ë†Ã™Å Ã˜Â© Ã™ÂÃ™Å  Ã˜Â§Ã™â€žÃ˜Â¯Ã˜Â¹Ã™â€¦',
                    'higher_bulk_limits' => 'Ã˜ÂªÃ™â€ Ã™ÂÃ™Å Ã˜Â° Ã˜Â¬Ã™â€¦Ã˜Â§Ã˜Â¹Ã™Å  Ã˜Â¨Ã˜Â­Ã˜Â¯Ã™Ë†Ã˜Â¯ Ã˜Â£Ã˜Â¹Ã™â€žÃ™â€°',
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
            <div class="plan-title-row">
              <h3 class="plan-name">{$plan['name_ar']}</h3>
              <span class="plan-icon" aria-hidden="true">{$plan['icon']}</span>
            </div>
            <p class="plan-description">{$plan['description_ar']}</p>
          </div>
          <div class="plan-price">
            <span class="price-number">{$plan['price_sar']}</span>
            <span class="price-currency">Ã˜Â±.Ã˜Â³ / Ã˜Â´Ã™â€¡Ã˜Â±</span>
          </div>
          <p class="price-usd">\${$plan['price_usd']} USD / month</p>
          <ul class="plan-features">
            {$quotasHtml}
            {$extrasHtml}
          </ul>
          <a href="{$loginHref}" class="plan-cta">Ã˜Â§Ã˜Â¨Ã˜Â¯Ã˜Â£ Ã˜Â§Ã™â€žÃ˜Â¢Ã™â€ </a>
        </div>
HTML;
        }

        $html = <<<HTML
<!doctype html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" type="image/png" href="{$faviconSrc}">
  <link rel="apple-touch-icon" href="{$faviconSrc}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&display=swap">
  <title>Ã˜Â§Ã™â€žÃ˜Â¨Ã˜Â§Ã™â€šÃ˜Â§Ã˜Âª Ã™Ë†Ã˜Â§Ã™â€žÃ˜Â£Ã˜Â³Ã˜Â¹Ã˜Â§Ã˜Â± | RankX SEO</title>
  <meta name="description" content="Ã˜Â§Ã˜Â®Ã˜ÂªÃ˜Â± Ã˜Â§Ã™â€žÃ˜Â¨Ã˜Â§Ã™â€šÃ˜Â© Ã˜Â§Ã™â€žÃ™â€¦Ã™â€ Ã˜Â§Ã˜Â³Ã˜Â¨Ã˜Â© Ã™â€žÃ™â€¦Ã˜ÂªÃ˜Â¬Ã˜Â±Ã™Æ’ - Ã˜Â¨Ã˜Â§Ã™â€šÃ˜Â§Ã˜Âª Ã™â€¦Ã˜Â±Ã™â€ Ã˜Â© Ã˜ÂªÃ˜Â¨Ã˜Â¯Ã˜Â£ Ã™â€¦Ã™â€  5 Ã˜Â±.Ã˜Â³ Ã™ÂÃ™â€šÃ˜Â· Ã™â€žÃ˜ÂªÃ˜Â­Ã˜Â³Ã™Å Ã™â€  Ã™â€¦Ã˜Â­Ã˜ÂªÃ™Ë†Ã™â€° Ã™â€¦Ã˜ÂªÃ˜Â¬Ã˜Â±Ã™Æ’ Ã™Ë†Ã˜Â²Ã™Å Ã˜Â§Ã˜Â¯Ã˜Â© Ã˜Â§Ã™â€žÃ™â€¦Ã˜Â¨Ã™Å Ã˜Â¹Ã˜Â§Ã˜Âª.">
  <link rel="canonical" href="{$safeAppUrl}/pricing">
  <style>
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
    .plan-card{background:var(--surface);border:2px solid var(--border);border-radius:20px;padding:28px;text-align:center;position:relative;transition:transform .3s,box-shadow .3s;display:flex;flex-direction:column}
    .plan-card:hover{transform:translateY(-4px);box-shadow:0 20px 40px rgba(15,23,42,.1)}
    .plan-card.featured{border-color:var(--primary-2);box-shadow:0 0 30px rgba(99,102,241,.2)}
    .featured-badge{position:absolute;top:-14px;right:50%;transform:translateX(50%);background:var(--gradient-main);color:#fff;padding:6px 16px;border-radius:999px;font-size:13px;font-weight:700}
    .plan-header{margin-bottom:20px}
    .plan-title-row{display:flex;align-items:center;justify-content:center;gap:10px;margin:0 0 8px}
    .plan-icon{
      font-size:28px;
      line-height:1;
      width:30px;
      height:30px;
      display:inline-flex;
      align-items:center;
      justify-content:center;
      transform:translateY(1px);
      flex:0 0 auto;
    }
    .plan-name{font-size:24px;font-weight:800;margin:0;color:var(--ink);line-height:1.2}
    .plan-description{font-size:15px;color:var(--muted);margin:0;line-height:1.6}
    .plan-price{margin:24px 0 4px}
    .price-number{font-size:48px;font-weight:900;color:var(--primary-2)}
    .price-currency{font-size:16px;color:var(--muted);margin-right:4px}
    .price-usd{font-size:14px;color:var(--muted);margin:0 0 24px}
    .plan-features{list-style:none;padding:0;margin:0 0 24px;text-align:right;flex:1 1 auto}
    .plan-features li{padding:10px 0;border-bottom:1px solid var(--border);font-size:15px;color:#475569}
    .plan-features li:last-child{border-bottom:none}
    .plan-features li::before{content:"Ã¢Å“â€œ";color:var(--success);margin-left:8px;font-weight:700}
    .extra-feature{color:var(--primary-2)!important;font-weight:600}
    .plan-cta{display:inline-block;width:100%;padding:14px 24px;background:var(--gradient-main);color:#fff;border-radius:12px;text-decoration:none;font-weight:700;font-size:16px;box-shadow:var(--glow-primary);transition:transform .2s,box-shadow .2s;margin-top:auto}
    .plan-cta:hover{transform:translateY(-2px);box-shadow:0 0 35px rgba(99,102,241,.4)}
    .plan-card:not(.featured) .plan-cta{background:#F1F5F9;color:var(--ink);box-shadow:none}
    .plan-card:not(.featured) .plan-cta:hover{background:#E2E8F0;box-shadow:none}
    .compare-section{margin-top:48px;padding-top:48px;border-top:1px solid var(--border)}
    .compare-section h2{text-align:center;font-size:28px;margin:0 0 32px}
    .compare-table-wrap{width:100%;overflow-x:auto;-webkit-overflow-scrolling:touch;border-radius:16px}
    .compare-table{width:100%;min-width:760px;border-collapse:collapse;overflow:hidden;border-radius:16px;border:1px solid var(--border)}
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
      .plan-card{max-width:420px;margin:0 auto;padding:22px}
      .plan-title-row{gap:8px}
      .plan-icon{font-size:24px;width:26px;height:26px}
      .plan-name{font-size:22px}
      .price-number{font-size:42px}
      .plan-features li{font-size:14px;padding:8px 0}
      .compare-table{font-size:13px}
      .compare-table th,.compare-table td{padding:10px 8px}
    }
  </style>
</head>
<body>
  <div class="wrap">
    <div class="surface">
      <div class="brand">
        <img src="{$logoSrc}" alt="RankX SEO" width="1200" height="400" decoding="async">
      </div>

      <div class="hero">
        <h1>Ã˜Â¨Ã˜Â§Ã™â€šÃ˜Â§Ã˜Âª Ã™Ë†Ã˜Â£Ã˜Â³Ã˜Â¹Ã˜Â§Ã˜Â± RankX SEO</h1>
        <p class="subtitle">Ã˜Â§Ã˜Â®Ã˜ÂªÃ˜Â± Ã˜Â§Ã™â€žÃ˜Â¨Ã˜Â§Ã™â€šÃ˜Â© Ã˜Â§Ã™â€žÃ™â€¦Ã™â€ Ã˜Â§Ã˜Â³Ã˜Â¨Ã˜Â© Ã™â€žÃ™â€¦Ã˜ÂªÃ˜Â¬Ã˜Â±Ã™Æ’ Ã™Ë†Ã˜Â§Ã˜Â¨Ã˜Â¯Ã˜Â£ Ã™ÂÃ™Å  Ã˜ÂªÃ˜Â­Ã˜Â³Ã™Å Ã™â€  Ã™â€¦Ã˜Â­Ã˜ÂªÃ™Ë†Ã˜Â§Ã™Æ’ Ã˜Â§Ã™â€žÃ™Å Ã™Ë†Ã™â€¦</p>
      </div>

      <div class="plans-grid">
        {$plansHtml}
      </div>

      <div class="compare-section">
        <h2>Ã™â€¦Ã™â€šÃ˜Â§Ã˜Â±Ã™â€ Ã˜Â© Ã˜Â§Ã™â€žÃ˜Â¨Ã˜Â§Ã™â€šÃ˜Â§Ã˜Âª</h2>
        <div class="compare-table-wrap">
        <table class="compare-table">
          <thead>
            <tr>
              <th>Ã˜Â§Ã™â€žÃ™â€¦Ã™Å Ã˜Â²Ã˜Â©</th>
              <th>Ã˜ÂªÃ˜Â¬Ã˜Â±Ã˜Â¨Ã˜Â© Ã˜Â§Ã™â€šÃ˜ÂªÃ˜ÂµÃ˜Â§Ã˜Â¯Ã™Å Ã˜Â©</th>
              <th>Ã˜Â§Ã™â€žÃ˜Â£Ã˜Â³Ã˜Â§Ã˜Â³Ã™Å Ã˜Â©</th>
              <th class="plan-highlight">Ã˜Â§Ã™â€žÃ™â€¦Ã˜ÂªÃ™â€šÃ˜Â¯Ã™â€¦Ã˜Â© Ã¢Â­Â</th>
              <th>Ã˜Â§Ã™â€žÃ˜Â§Ã˜Â­Ã˜ÂªÃ˜Â±Ã˜Â§Ã™ÂÃ™Å Ã˜Â©</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>Ã˜ÂªÃ˜Â­Ã˜Â³Ã™Å Ã™â€  Ã™Ë†Ã˜ÂµÃ™Â Ã˜Â§Ã™â€žÃ™â€¦Ã™â€ Ã˜ÂªÃ˜Â¬</td>
              <td>10</td>
              <td>80</td>
              <td class="plan-highlight">260</td>
              <td>700</td>
            </tr>
            <tr>
              <td>Ã˜ÂªÃ˜Â­Ã˜Â³Ã™Å Ã™â€  SEO Ã˜Â§Ã™â€žÃ™â€¦Ã™â€ Ã˜ÂªÃ˜Â¬</td>
              <td>10</td>
              <td>80</td>
              <td class="plan-highlight">140</td>
              <td>700</td>
            </tr>
            <tr>
              <td>Ã˜ÂªÃ˜Â­Ã˜Â³Ã™Å Ã™â€  ALT Ã˜Â§Ã™â€žÃ˜ÂµÃ™Ë†Ã˜Â±</td>
              <td>10</td>
              <td>30</td>
              <td class="plan-highlight">260</td>
              <td>700</td>
            </tr>
            <tr>
              <td>Ã˜Â¹Ã™â€¦Ã™â€žÃ™Å Ã˜Â§Ã˜Âª Ã˜Â§Ã™â€žÃ™Æ’Ã™â€žÃ™â€¦Ã˜Â§Ã˜Âª Ã˜Â§Ã™â€žÃ™â€¦Ã™ÂÃ˜ÂªÃ˜Â§Ã˜Â­Ã™Å Ã˜Â©</td>
              <td>5</td>
              <td>10</td>
              <td class="plan-highlight">40</td>
              <td>120</td>
            </tr>
            <tr>
              <td>Ã˜ÂªÃ˜Â­Ã™â€žÃ™Å Ã™â€ž Ã˜Â³Ã™Å Ã™Ë† Ã˜Â§Ã™â€žÃ˜Â¯Ã™Ë†Ã™â€¦Ã™Å Ã™â€ </td>
              <td>1</td>
              <td>3</td>
              <td class="plan-highlight">12</td>
              <td>35</td>
            </tr>
            <tr>
              <td>Ã˜ÂªÃ˜Â­Ã˜Â³Ã™Å Ã™â€  SEO Ã˜Â§Ã™â€žÃ™â€¦Ã˜Â§Ã˜Â±Ã™Æ’Ã˜Â§Ã˜Âª</td>
              <td>5</td>
              <td>Ã˜ÂºÃ™Å Ã˜Â± Ã™â€¦Ã™ÂÃ˜Â¹Ã™â€žÃ˜Â© (Ã˜Â±Ã™â€šÃ™â€˜Ã™Å )</td>
              <td class="plan-highlight">50</td>
              <td>150</td>
            </tr>
            <tr>
              <td>Ã˜ÂªÃ˜Â­Ã˜Â³Ã™Å Ã™â€  SEO Ã˜Â§Ã™â€žÃ˜Â£Ã™â€šÃ˜Â³Ã˜Â§Ã™â€¦</td>
              <td>5</td>
              <td>Ã˜ÂºÃ™Å Ã˜Â± Ã™â€¦Ã™ÂÃ˜Â¹Ã™â€žÃ˜Â© (Ã˜Â±Ã™â€šÃ™â€˜Ã™Å )</td>
              <td class="plan-highlight">50</td>
              <td>100</td>
            </tr>
            <tr>
              <td>Ã˜Â³Ã˜Â¬Ã™â€ž Ã˜Â§Ã™â€žÃ˜Â¹Ã™â€¦Ã™â€žÃ™Å Ã˜Â§Ã˜Âª</td>
              <td class="cross">Ã¢â‚¬â€</td>
              <td class="cross">Ã¢â‚¬â€</td>
              <td class="plan-highlight check">Ã¢Å“â€œ</td>
              <td class="check">Ã¢Å“â€œ</td>
            </tr>
            <tr>
              <td>Ã˜Â£Ã™Ë†Ã™â€žÃ™Ë†Ã™Å Ã˜Â© Ã˜Â§Ã™â€žÃ˜Â¯Ã˜Â¹Ã™â€¦</td>
              <td class="cross">Ã¢â‚¬â€</td>
              <td class="cross">Ã¢â‚¬â€</td>
              <td class="plan-highlight cross">Ã¢â‚¬â€</td>
              <td class="check">Ã¢Å“â€œ</td>
            </tr>
            <tr>
              <td>Ã˜Â­Ã˜Â¯Ã™Ë†Ã˜Â¯ Ã˜ÂªÃ™â€ Ã™ÂÃ™Å Ã˜Â° Ã˜Â£Ã˜Â¹Ã™â€žÃ™â€°</td>
              <td class="cross">Ã¢â‚¬â€</td>
              <td class="cross">Ã¢â‚¬â€</td>
              <td class="plan-highlight cross">Ã¢â‚¬â€</td>
              <td class="check">Ã¢Å“â€œ</td>
            </tr>
            <tr>
              <td><strong>Ã˜Â§Ã™â€žÃ˜Â³Ã˜Â¹Ã˜Â± / Ã˜Â´Ã™â€¡Ã˜Â±</strong></td>
              <td><strong>5 Ã˜Â±.Ã˜Â³</strong></td>
              <td><strong>29 Ã˜Â±.Ã˜Â³</strong></td>
              <td class="plan-highlight"><strong>79 Ã˜Â±.Ã˜Â³</strong></td>
              <td><strong>149 Ã˜Â±.Ã˜Â³</strong></td>
            </tr>
          </tbody>
        </table>
        </div>
      </div>

      <div class="cta-box">
        <h3>Ã™â€¡Ã™â€ž Ã˜ÂªÃ˜Â­Ã˜ÂªÃ˜Â§Ã˜Â¬ Ã™â€¦Ã˜Â³Ã˜Â§Ã˜Â¹Ã˜Â¯Ã˜Â© Ã™ÂÃ™Å  Ã˜Â§Ã˜Â®Ã˜ÂªÃ™Å Ã˜Â§Ã˜Â± Ã˜Â§Ã™â€žÃ˜Â¨Ã˜Â§Ã™â€šÃ˜Â©Ã˜Å¸</h3>
        <p>Ã™ÂÃ˜Â±Ã™Å Ã™â€šÃ™â€ Ã˜Â§ Ã˜Â¬Ã˜Â§Ã™â€¡Ã˜Â² Ã™â€žÃ™â€¦Ã˜Â³Ã˜Â§Ã˜Â¹Ã˜Â¯Ã˜ÂªÃ™Æ’ Ã™Ë†Ã˜Â§Ã˜Â®Ã˜ÂªÃ™Å Ã˜Â§Ã˜Â± Ã˜Â§Ã™â€žÃ˜Â£Ã™â€ Ã˜Â³Ã˜Â¨ Ã™â€žÃ™â€¦Ã˜ÂªÃ˜Â¬Ã˜Â±Ã™Æ’</p>
        <a href="mailto:seo@rankxseo.com">Ã˜ÂªÃ™Ë†Ã˜Â§Ã˜ÂµÃ™â€ž Ã™â€¦Ã˜Â¹Ã™â€ Ã˜Â§</a>
      </div>
    </div>

    <div class="footer">
      <p>
        <a href="{$safeAppUrl}/">Ã˜Â§Ã™â€žÃ˜Â±Ã˜Â¦Ã™Å Ã˜Â³Ã™Å Ã˜Â©</a> Ã‚Â· 
        <a href="{$safeAppUrl}/about">Ã™â€¦Ã™â€  Ã™â€ Ã˜Â­Ã™â€ </a> Ã‚Â· 
        <a href="{$safeAppUrl}/faq">Ã˜Â§Ã™â€žÃ˜Â£Ã˜Â³Ã˜Â¦Ã™â€žÃ˜Â© Ã˜Â§Ã™â€žÃ˜Â´Ã˜Â§Ã˜Â¦Ã˜Â¹Ã˜Â©</a> Ã‚Â· 
        <a href="{$safeAppUrl}/privacy">Ã˜Â§Ã™â€žÃ˜Â®Ã˜ÂµÃ™Ë†Ã˜ÂµÃ™Å Ã˜Â©</a> Ã‚Â· 
        <a href="{$safeAppUrl}/terms">Ã˜Â§Ã™â€žÃ˜Â´Ã˜Â±Ã™Ë†Ã˜Â·</a>
      </p>
      <p>Ã‚Â© 2024 RankX SEO - Ã˜Â¬Ã™â€¦Ã™Å Ã˜Â¹ Ã˜Â§Ã™â€žÃ˜Â­Ã™â€šÃ™Ë†Ã™â€š Ã™â€¦Ã˜Â­Ã™ÂÃ™Ë†Ã˜Â¸Ã˜Â©</p>
    </div>
  </div>
</body>
</html>
HTML;

        Response::html($html);
    }
}
