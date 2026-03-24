<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Config;
use App\Repositories\SaaSRepository;
use App\Repositories\StoreRepository;
use App\Services\Mailer;
use App\Support\Database;
use App\Support\Request;
use App\Support\Response;
use App\Support\View;

final class AuthController
{
    public function loginForm(): void
    {
        if (!Database::isAvailable()) {
            Response::html(View::render('Login', '<div class="card"><h1>تسجيل الدخول</h1><p class="muted">جهّز قاعدة البيانات أولًا ثم استورد الملف <code>database/schema.sql</code>.</p></div>'));
            return;
        }

        Response::html(View::render('Login', <<<HTML
<div class="card" style="max-width:620px;margin:auto;">
  <h1>دخول العميل</h1>
  <p class="muted">ادخل إلى لوحة التحكم الخارجية الخاصة بمتجرك، ثم ابدأ تحسين الوصف وبيانات السيو وحفظها مباشرة في سلة.</p>
  <form method="post" action="/login">
    <label><strong>البريد الإلكتروني</strong></label>
    <input name="email" type="email" required>
    <label style="margin-top:10px;display:block;"><strong>كلمة المرور</strong></label>
    <input name="password" type="password" required>
    <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:18px;">
      <button class="btn" type="submit">دخول</button>
      <a class="btn btn-secondary" href="/forgot-password">نسيت كلمة المرور</a>
    </div>
  </form>
</div>
HTML));
    }

    public function loginSubmit(): void
    {
        if (!Database::isAvailable()) {
            Response::json(['success' => false, 'message' => 'Database is not available.'], 500);
            return;
        }

        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $user = (new SaaSRepository())->findUserByEmail($email);

        if (!$user || empty($user['password_hash']) || !password_verify($password, (string) $user['password_hash'])) {
            Response::html(View::render('Login Failed', '<div class="card"><h1>فشل تسجيل الدخول</h1><p class="muted">تحقق من البريد الإلكتروني وكلمة المرور.</p><p><a class="btn" href="/login">العودة</a></p></div>'), 401);
            return;
        }

        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['store_id'] = (int) $user['store_id'];
        header('Location: /dashboard');
    }

    public function setPasswordForm(): void
    {
        $token = (string) Request::query('token', '');

        if ($token === '' || !Database::isAvailable()) {
            Response::html(View::render('Set Password', '<div class="card"><h1>الرابط غير صالح</h1></div>'), 400);
            return;
        }

        $record = (new SaaSRepository())->findValidPasswordResetToken($token);

        if ($record === null) {
            Response::html(View::render('Set Password', '<div class="card"><h1>الرابط منتهي أو غير صحيح</h1></div>'), 400);
            return;
        }

        $safeToken = htmlspecialchars($token, ENT_QUOTES, 'UTF-8');
        $safeEmail = htmlspecialchars((string) ($record['email'] ?? ''), ENT_QUOTES, 'UTF-8');

        Response::html(View::render('Set Password', <<<HTML
<div class="card" style="max-width:620px;margin:auto;">
  <h1>تعيين كلمة المرور</h1>
  <p class="muted">الحساب: {$safeEmail}</p>
  <form method="post" action="/set-password">
    <input type="hidden" name="token" value="{$safeToken}">
    <label><strong>كلمة المرور الجديدة</strong></label>
    <input name="password" type="password" required>
    <div style="margin-top:18px;">
      <button class="btn" type="submit">حفظ كلمة المرور</button>
    </div>
  </form>
</div>
HTML));
    }

    public function setPasswordSubmit(): void
    {
        if (!Database::isAvailable()) {
            Response::json(['success' => false, 'message' => 'Database is not available.'], 500);
            return;
        }

        $token = trim((string) ($_POST['token'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $repository = new SaaSRepository();
        $record = $repository->findValidPasswordResetToken($token);

        if ($record === null || strlen($password) < 8) {
            Response::html(View::render('Set Password', '<div class="card"><h1>تعذر تعيين كلمة المرور</h1><p class="muted">تأكد أن الرابط صحيح وأن كلمة المرور 8 أحرف على الأقل.</p></div>'), 422);
            return;
        }

        $repository->setUserPassword((int) $record['user_id'], password_hash($password, PASSWORD_DEFAULT));
        $repository->markResetTokenUsed((int) $record['id']);

        Response::html(View::render('Set Password', '<div class="card"><h1>تم إنشاء الحساب بنجاح</h1><p class="muted">يمكنك الآن تسجيل الدخول إلى المنصة الخارجية.</p><p><a class="btn" href="/login">الانتقال لتسجيل الدخول</a></p></div>'));
    }

    public function logout(): void
    {
        unset($_SESSION['user_id'], $_SESSION['store_id']);
        header('Location: /login');
    }

    public function forgotPasswordForm(): void
    {
        Response::html(View::render('Forgot Password', <<<HTML
<div class="card" style="max-width:620px;margin:auto;">
  <h1>استرجاع كلمة المرور</h1>
  <p class="muted">أدخل بريدك الإلكتروني وسنرسل لك رابطًا لإعادة تعيين كلمة المرور.</p>
  <form method="post" action="/forgot-password">
    <label><strong>البريد الإلكتروني</strong></label>
    <input name="email" type="email" required>
    <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:18px;">
      <button class="btn" type="submit">إرسال الرابط</button>
      <a class="btn btn-secondary" href="/login">العودة للدخول</a>
    </div>
  </form>
</div>
HTML));
    }

    public function forgotPasswordSubmit(): void
    {
        if (!Database::isAvailable()) {
            Response::html(View::render('Forgot Password', '<div class="card"><h1>قاعدة البيانات غير متاحة</h1></div>'), 500);
            return;
        }

        $email = trim((string) ($_POST['email'] ?? ''));
        $repository = new SaaSRepository();
        $user = $repository->findUserByEmail($email);

        if ($user) {
            $rawToken = bin2hex(random_bytes(32));
            $repository->createPasswordResetToken(
                (int) $user['id'],
                password_hash($rawToken, PASSWORD_DEFAULT),
                date('Y-m-d H:i:s', strtotime('+2 hours'))
            );

            $appUrl = rtrim((string) (\App\Config::get('APP_URL', 'http://localhost:8000')), '/');
            $url = $appUrl . '/set-password?token=' . urlencode($rawToken);
            (new Mailer())->sendPasswordReset((string) $user['email'], (string) ($user['full_name'] ?? ''), $url);
        }

        Response::html(View::render('Forgot Password', '<div class="card"><h1>تم إرسال الطلب</h1><p class="muted">إذا كان البريد موجودًا لدينا فستصلك رسالة إعادة التعيين.</p><p><a class="btn" href="/login">العودة للدخول</a></p></div>'));
    }

    public function dashboard(): void
    {
        if (!Database::isAvailable()) {
            Response::html(View::render('Dashboard', '<div class="card"><h1>قاعدة البيانات غير مفعلة</h1></div>'), 500);
            return;
        }

        $userId = (int) ($_SESSION['user_id'] ?? 0);
        $storeId = (int) ($_SESSION['store_id'] ?? 0);

        if ($userId <= 0 || $storeId <= 0) {
            header('Location: /login');
            return;
        }

        $store = (new SaaSRepository())->findStoreById($storeId);

        Response::html(View::renderFile('Dashboard', dirname(__DIR__) . '/Views/client-dashboard.php', [
            'storeName' => (string) ($store['store_name'] ?? 'متجرك'),
            'merchantId' => (string) ($store['merchant_id'] ?? '-'),
            'ownerEmail' => (string) ($store['owner_email'] ?? '-'),
        ]));
    }

    public function reconnect(): void
    {
        $clientId = Config::get('SALLA_CLIENT_ID', '');
        $redirectUri = Config::get('SALLA_REDIRECT_URI', '');

        if ($clientId === '' || $redirectUri === '') {
            Response::json([
                'success' => false,
                'message' => 'OAuth configuration is missing.',
            ], 500);
            return;
        }

        $scopes = 'offline access merchants.read products.read brands.read brands.read_write categories.read categories.read_write';
        $state = bin2hex(random_bytes(16));

        $authorizeUrl = 'https://accounts.salla.sa/oauth2/authorize'
            . '?client_id=' . urlencode($clientId)
            . '&redirect_uri=' . urlencode($redirectUri)
            . '&response_type=code'
            . '&scope=' . urlencode($scopes)
            . '&state=' . urlencode($state);

        Response::json([
            'success' => true,
            'redirect_url' => $authorizeUrl,
        ]);
    }
}
