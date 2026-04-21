<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Config;
use App\Services\TokenRefreshService;
use App\Support\Request;
use App\Support\Response;

final class MaintenanceController
{
    public function refreshStoreTokens(): void
    {
        $configuredKey = trim((string) Config::get('CRON_TOKEN_REFRESH_KEY', ''));
        $providedKey = trim((string) (Request::header('X-Cron-Key') ?? Request::query('key', '')));

        if ($configuredKey === '' || !hash_equals($configuredKey, $providedKey)) {
            Response::json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 401);
            return;
        }

        $force = Request::query('force', '0') === '1';
        $result = (new TokenRefreshService())->refreshDueTokens($force);

        Response::json($result, $result['success'] ? 200 : 423);
    }
}
