<?php

declare(strict_types=1);

namespace App\Support;

final class View
{
    public static function render(string $title, string $content): string
    {
        return <<<HTML
<!doctype html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{$title}</title>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700&display=swap');
    :root {
      --bg: #f3ecdf;
      --surface: rgba(255, 251, 245, 0.82);
      --surface-strong: #fffdf8;
      --surface-soft: rgba(252, 247, 240, 0.88);
      --ink: #1c1b18;
      --muted: #6b6258;
      --accent: #0f7b66;
      --accent-strong: #0a5a4b;
      --sky: #2ea9d6;
      --sky-strong: #1d8eb6;
      --warm: #bb6b35;
      --danger: #b94136;
      --danger-soft: #fff1ef;
      --success: #0f7b66;
      --success-soft: #e9f8f2;
      --warning: #a66a1a;
      --warning-soft: #fff7e8;
      --border: rgba(202, 177, 149, 0.55);
      --shadow: 0 24px 80px rgba(67, 49, 25, 0.12);
      --shadow-soft: 0 16px 40px rgba(67, 49, 25, 0.08);
    }
    * { box-sizing: border-box; }
    body {
      margin: 0;
      font-family: "IBM Plex Sans Arabic", "Segoe UI", sans-serif;
      background:
        radial-gradient(circle at 10% 10%, rgba(15, 123, 102, 0.18), transparent 20%),
        radial-gradient(circle at 90% 15%, rgba(46, 169, 214, 0.15), transparent 22%),
        radial-gradient(circle at 50% 100%, rgba(128, 93, 52, 0.10), transparent 30%),
        var(--bg);
      color: var(--ink);
      min-height: 100vh;
    }
    .wrap {
      width: min(100%, 1380px);
      margin: 0 auto;
      padding: 28px 20px 72px;
    }
    .card {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: 28px;
      padding: 26px;
      box-shadow: var(--shadow);
      backdrop-filter: blur(14px);
    }
    .surface-soft { background: var(--surface-soft); }
    h1, h2, h3 { margin-top: 0; }
    h1 { font-size: clamp(30px, 4vw, 56px); line-height: 1.05; letter-spacing: -0.03em; }
    h2 { font-size: 24px; margin-bottom: 12px; }
    h3 { font-size: 18px; }
    p, li { line-height: 1.8; }
    .muted { color: var(--muted); }
    .grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
      gap: 18px;
      margin-top: 20px;
    }
    .pill {
      display: inline-block;
      padding: 8px 14px;
      border-radius: 999px;
      background: rgba(240, 227, 211, 0.96);
      color: #6e4721;
      font-size: 13px;
      font-weight: 600;
      margin-left: 8px;
    }
    .btn,
    button.btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      text-decoration: none;
      background: var(--accent);
      color: #fff;
      padding: 12px 18px;
      border-radius: 14px;
      border: none;
      cursor: pointer;
      font: inherit;
      transition: transform .16s ease, box-shadow .16s ease, background .16s ease, opacity .16s ease;
      box-shadow: 0 10px 24px rgba(15, 123, 102, 0.24);
    }
    .btn:hover,
    button.btn:hover {
      transform: translateY(-1px);
      background: var(--accent-strong);
    }
    .btn:disabled,
    button.btn:disabled {
      opacity: .6;
      cursor: wait;
      transform: none;
    }
    .btn-secondary {
      background: rgba(239, 229, 215, 0.95);
      color: #6e4721;
      box-shadow: none;
    }
    .btn-secondary:hover { background: rgba(230, 216, 197, 1); }
    .btn-sky {
      background: var(--sky);
      box-shadow: 0 10px 24px rgba(46, 169, 214, 0.22);
    }
    .btn-sky:hover { background: var(--sky-strong); }
    .btn-danger {
      background: var(--danger);
      box-shadow: 0 10px 24px rgba(185, 65, 54, 0.22);
    }
    .btn-danger:hover { background: #9b332a; }
    code, pre {
      font-family: Consolas, monospace;
      background: #f3eadf;
      border-radius: 10px;
    }
    code { padding: 2px 6px; }
    pre { padding: 14px; overflow: auto; }
    input, select, textarea {
      width: 100%;
      padding: 13px 14px;
      margin-top: 8px;
      border-radius: 14px;
      border: 1px solid rgba(202, 177, 149, 0.75);
      background: rgba(255,255,255,0.94);
      color: var(--ink);
      font: inherit;
      outline: none;
      transition: border-color .16s ease, box-shadow .16s ease;
    }
    input:focus, select:focus, textarea:focus {
      border-color: rgba(15, 123, 102, 0.65);
      box-shadow: 0 0 0 4px rgba(15, 123, 102, 0.12);
    }
    table {
      width: 100%;
      border-collapse: collapse;
      overflow: hidden;
      border-radius: 18px;
      background: var(--surface-strong);
    }
    th, td {
      border-bottom: 1px solid rgba(202, 177, 149, 0.32);
      padding: 12px 14px;
      vertical-align: top;
    }
    th {
      font-size: 14px;
      color: var(--muted);
      background: rgba(248, 241, 232, 0.95);
    }
    tr:last-child td { border-bottom: none; }
    .hero {
      display: grid;
      grid-template-columns: 1.5fr 1fr;
      gap: 18px;
    }
    .stat {
      display: flex;
      flex-direction: column;
      gap: 8px;
      min-height: 140px;
    }
    .stat-label {
      color: var(--muted);
      font-size: 14px;
    }
    .stat-value {
      font-size: clamp(26px, 3vw, 42px);
      font-weight: 700;
      line-height: 1.05;
    }
    .panel-stack {
      display: grid;
      gap: 18px;
    }
    .dashboard-layout {
      display: grid;
      grid-template-columns: minmax(0, 1fr) 340px;
      gap: 20px;
      align-items: start;
    }
    .section-head {
      display: flex;
      justify-content: space-between;
      gap: 16px;
      align-items: flex-start;
      flex-wrap: wrap;
      margin-bottom: 14px;
    }
    .danger-zone {
      border-color: rgba(185, 65, 54, 0.28);
      background: rgba(255, 245, 244, 0.9);
    }
    .toolbar {
      display: grid;
      gap: 14px;
    }
    .toolbar-row {
      display: flex;
      flex-wrap: wrap;
      gap: 12px;
      align-items: center;
    }
    .toolbar-row > * {
      flex: 1 1 180px;
      min-width: 160px;
    }
    .toolbar-row .inline-actions {
      flex: 0 0 auto;
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
    }
    .chips {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
    }
    .chip {
      border: 1px solid rgba(46, 169, 214, 0.24);
      background: rgba(46, 169, 214, 0.12);
      color: #155f77;
      padding: 10px 14px;
      border-radius: 999px;
      cursor: pointer;
      transition: .16s ease;
      font: inherit;
    }
    .chip.is-active {
      background: var(--sky);
      color: #fff;
      border-color: transparent;
      box-shadow: 0 10px 24px rgba(46, 169, 214, 0.22);
    }
    .products-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(290px, 1fr));
      gap: 20px;
    }
    .product-card {
      position: relative;
      display: flex;
      flex-direction: column;
      gap: 16px;
      min-height: 100%;
      background: rgba(255, 253, 248, 0.88);
      border: 1px solid rgba(202, 177, 149, 0.38);
      border-radius: 24px;
      padding: 18px;
      box-shadow: var(--shadow-soft);
    }
    .product-badges {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
    }
    .status-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 7px 12px;
      border-radius: 999px;
      font-size: 13px;
      font-weight: 700;
    }
    .status-badge.success {
      background: var(--success-soft);
      color: var(--success);
    }
    .status-badge.danger {
      background: var(--danger-soft);
      color: var(--danger);
    }
    .status-badge.warning {
      background: var(--warning-soft);
      color: var(--warning);
    }
    .product-thumb {
      width: 100%;
      aspect-ratio: 1 / 1;
      object-fit: cover;
      border-radius: 20px;
      background: linear-gradient(180deg, rgba(255,255,255,.9), rgba(241, 232, 220, .92));
      border: 1px solid rgba(202, 177, 149, 0.25);
    }
    .product-title {
      margin: 0;
      font-size: 22px;
      line-height: 1.35;
    }
    .meta-list {
      display: grid;
      gap: 8px;
      color: var(--muted);
      font-size: 14px;
    }
    .product-actions {
      display: grid;
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap: 10px;
    }
    .product-actions .btn {
      width: 100%;
      padding: 12px 14px;
      font-size: 14px;
    }
    .pagination {
      display: flex;
      gap: 8px;
      flex-wrap: wrap;
      align-items: center;
      justify-content: center;
    }
    .pagination button {
      min-width: 42px;
      padding: 10px 12px;
      border: 1px solid rgba(202, 177, 149, 0.7);
      border-radius: 12px;
      background: rgba(255,255,255,0.9);
      cursor: pointer;
      font: inherit;
    }
    .pagination button.is-active {
      background: var(--sky);
      color: #fff;
      border-color: transparent;
    }
    .modal-backdrop {
      position: fixed;
      inset: 0;
      background: rgba(28, 27, 24, 0.46);
      display: none;
      align-items: center;
      justify-content: center;
      padding: 24px;
      z-index: 999;
      backdrop-filter: blur(6px);
    }
    .modal-backdrop.is-open { display: flex; }
    .modal {
      width: min(1120px, 100%);
      max-height: 92vh;
      overflow: auto;
      border-radius: 28px;
      padding: 26px;
      background: rgba(255, 252, 247, 0.98);
      border: 1px solid rgba(202, 177, 149, 0.6);
      box-shadow: 0 36px 90px rgba(28, 27, 24, 0.26);
    }
    .modal-head {
      display: flex;
      justify-content: space-between;
      gap: 16px;
      align-items: flex-start;
      margin-bottom: 20px;
      flex-wrap: wrap;
    }
    .compare-grid {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 16px;
    }
    .compare-card {
      background: rgba(252, 247, 240, 0.9);
      border: 1px solid rgba(202, 177, 149, 0.48);
      border-radius: 22px;
      padding: 18px;
    }
    .compare-card textarea { min-height: 160px; }
    .compare-card.is-meta textarea { min-height: 104px; }
    .helper-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 12px;
      color: var(--muted);
      font-size: 13px;
      margin-top: 8px;
    }
    .empty-state {
      text-align: center;
      padding: 36px 18px;
      border: 1px dashed rgba(202, 177, 149, 0.9);
      border-radius: 24px;
      background: rgba(255,255,255,0.45);
    }
    .notice {
      margin-top: 12px;
      padding: 14px 16px;
      border-radius: 16px;
      font-size: 14px;
    }
    .notice.success {
      background: var(--success-soft);
      color: var(--success);
    }
    .notice.error {
      background: var(--danger-soft);
      color: var(--danger);
    }
    @media (max-width: 980px) {
      .hero,
      .compare-grid,
      .dashboard-layout {
        grid-template-columns: 1fr;
      }
      .product-actions {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>
  <div class="wrap">
    <div class="section-head" style="margin-bottom:20px;">
      <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
        <img src="/assets/rankxseo-logo.svg" alt="RankX SEO" style="width:min(100%,280px);height:auto;display:block;">
        <div>
          <div class="pill">RankX SEO</div>
          <div class="muted" style="margin-top:10px;">منصة تحسين محتوى منتجات سلة وربطها بلوحة خارجية احترافية.</div>
        </div>
      </div>
    </div>
    {$content}
    <div style="margin-top:26px;text-align:center;color:var(--muted);font-size:14px;">
      <span>Powered by RankX SEO</span>
    </div>
  </div>
</body>
</html>
HTML;
    }

    public static function renderFile(string $title, string $templatePath, array $data = []): string
    {
        extract($data, EXTR_SKIP);

        ob_start();
        require $templatePath;
        $content = (string) ob_get_clean();

        return self::render($title, $content);
    }
}
