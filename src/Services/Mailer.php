<?php

declare(strict_types=1);

namespace App\Services;

use App\Config;
use RuntimeException;

final class Mailer
{
    public function sendPasswordReset(string $toEmail, string $toName, string $resetUrl): bool
    {
        $subject = (string) Config::get('MAIL_RESET_SUBJECT', 'تعيين كلمة المرور');
        $safeName = htmlspecialchars($toName !== '' ? $toName : $toEmail, ENT_QUOTES, 'UTF-8');
        $safeUrl = htmlspecialchars($resetUrl, ENT_QUOTES, 'UTF-8');
        $appUrl = rtrim((string) Config::get('APP_URL', 'http://localhost:8000'), '/');
        $safeAppUrl = htmlspecialchars($appUrl, ENT_QUOTES, 'UTF-8');
        $safeLogoUrl = htmlspecialchars($appUrl . '/assets/rankxseo-logo.png', ENT_QUOTES, 'UTF-8');

        $html = <<<HTML
<!doctype html>
<html lang="ar" dir="rtl" xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تعيين كلمة المرور</title>
  </head>
  <body style="margin:0;padding:0;background:#F8FAFC;color:#0F172A;font-family:Tajawal,Tahoma,Arial,sans-serif;-webkit-text-size-adjust:100%;text-size-adjust:100%;">
    <div style="display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;">فعّل حسابك في RankX SEO وابدأ إدارة تحسينات منتجات سلة من لوحة واحدة.</div>
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%;background:#F8FAFC;border-collapse:collapse;">
      <tr>
        <td align="center" style="padding:32px 14px;">
          <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%;max-width:680px;border-collapse:collapse;text-align:right;direction:rtl;">
            <tr>
              <td style="padding:0 4px 14px;">
                <img src="{$safeLogoUrl}" width="150" alt="RankX SEO" style="display:block;width:150px;max-width:150px;height:auto;border:0;">
              </td>
            </tr>
            <tr>
              <td style="background:#FFFFFF;border:1px solid #E2E8F0;border-radius:22px;overflow:hidden;box-shadow:0 22px 55px rgba(15,23,42,0.08);">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="border-collapse:collapse;">
                  <tr>
                    <td style="height:7px;background:#3B82F6;background:linear-gradient(135deg,#3B82F6 0%,#6366F1 55%,#8B5CF6 100%);font-size:0;line-height:0;">&nbsp;</td>
                  </tr>
                  <tr>
                    <td style="padding:34px 34px 12px;">
                      <span style="display:inline-block;padding:8px 14px;border-radius:999px;background:#EEF2FF;color:#4F46E5;font-size:13px;font-weight:700;line-height:1;">منصة تحسين محركات البحث لمتاجر سلة</span>
                      <h1 style="margin:18px 0 10px;color:#0F172A;font-size:30px;line-height:1.35;font-weight:800;">مرحبًا {$safeName}</h1>
                      <p style="margin:0;color:#475569;font-size:16px;line-height:1.9;">يمكنك الآن تعيين كلمة مرور حسابك في RankX SEO والدخول إلى لوحة التحكم لإدارة تحسين وصف المنتجات، عناصر SEO، وتحسينات محتوى متجرك من مكان واحد.</p>
                    </td>
                  </tr>
                  <tr>
                    <td style="padding:18px 34px 6px;">
                      <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#F8FAFC;border:1px solid #E2E8F0;border-radius:18px;border-collapse:separate;">
                        <tr>
                          <td style="padding:22px;">
                            <p style="margin:0 0 16px;color:#0F172A;font-size:16px;line-height:1.8;font-weight:700;">اضغط الزر التالي لتفعيل حسابك وتعيين كلمة المرور:</p>
                            <a href="{$safeUrl}" style="display:inline-block;background:#3B82F6;background:linear-gradient(135deg,#3B82F6 0%,#6366F1 55%,#8B5CF6 100%);color:#FFFFFF;text-decoration:none;padding:14px 26px;border-radius:12px;font-size:16px;font-weight:800;line-height:1.3;">تعيين كلمة المرور</a>
                            <p style="margin:16px 0 0;color:#64748B;font-size:13px;line-height:1.8;">لأمان حسابك، لا تشارك هذا الرابط مع أي شخص.</p>
                          </td>
                        </tr>
                      </table>
                    </td>
                  </tr>
                  <tr>
                    <td style="padding:20px 34px 0;">
                      <p style="margin:0 0 10px;color:#475569;font-size:14px;line-height:1.8;">إذا لم يعمل الزر، انسخ الرابط التالي وافتحه في المتصفح:</p>
                      <div style="direction:ltr;text-align:left;background:#F1F5F9;border:1px solid #E2E8F0;border-radius:12px;padding:12px;color:#4F46E5;font-size:13px;line-height:1.7;word-break:break-all;">{$safeUrl}</div>
                    </td>
                  </tr>
                  <tr>
                    <td style="padding:26px 34px 34px;">
                      <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="border-top:1px solid #E2E8F0;border-collapse:collapse;">
                        <tr>
                          <td style="padding-top:18px;color:#64748B;font-size:13px;line-height:1.8;">
                            تم إرسال هذه الرسالة من منصة <strong style="color:#0F172A;">RankX SEO</strong> لإدارة وتحسين محتوى منتجات سلة.
                            <br>
                            <a href="{$safeAppUrl}" style="color:#6366F1;text-decoration:none;">{$safeAppUrl}</a>
                          </td>
                        </tr>
                      </table>
                    </td>
                  </tr>
                </table>
              </td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
  </body>
</html>
HTML;

        return $this->sendHtml($toEmail, $toName, $subject, $html);
    }

    public function sendHtml(string $toEmail, string $toName, string $subject, string $html): bool
    {
        $host = (string) Config::get('SMTP_HOST', '');
        $port = (int) Config::get('SMTP_PORT', 587);
        $username = (string) Config::get('SMTP_USERNAME', '');
        $password = (string) Config::get('SMTP_PASSWORD', '');
        $encryption = (string) Config::get('SMTP_ENCRYPTION', 'tls');
        $fromEmail = (string) Config::get('MAIL_FROM_ADDRESS', '');
        $fromName = (string) Config::get('MAIL_FROM_NAME', 'RankX SEO');

        if ($host === '' || $username === '' || $password === '' || $fromEmail === '') {
            throw new RuntimeException('SMTP settings are incomplete.');
        }

        (new SmtpClient())->send(
            $host,
            $port,
            $username,
            $password,
            $fromEmail,
            $fromName,
            $toEmail,
            $toName,
            $subject,
            $html,
            $encryption
        );

        return true;
    }

    public function sendTestEmail(string $toEmail): bool
    {
        $safeEmail = htmlspecialchars($toEmail, ENT_QUOTES, 'UTF-8');
        $html = <<<HTML
<html lang="ar" dir="rtl">
  <body style="font-family:Tajawal,Tahoma,Arial,sans-serif;background:#f6efe3;padding:28px;">
    <div style="max-width:640px;margin:0 auto;background:#ffffff;border-radius:18px;padding:28px;border:1px solid #eadccc;">
      <h2 style="margin-top:0;color:#1d1b18;">اختبار إرسال البريد</h2>
      <p style="line-height:1.8;color:#4f463d;">إذا وصلك هذا البريد على <strong>{$safeEmail}</strong> فهذا يعني أن إعدادات SMTP الخاصة بـ RankX SEO تعمل بنجاح.</p>
      <p style="line-height:1.8;color:#6c645a;">يمكنك الآن الاعتماد على نفس الإعدادات في رسائل تفعيل الحساب واسترجاع كلمة المرور.</p>
    </div>
  </body>
</html>
HTML;

        return $this->sendHtml($toEmail, $toEmail, 'اختبار بريد RankX SEO', $html);
    }
}
