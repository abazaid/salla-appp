<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

final class HttpClient
{
    public function get(string $url, array $headers = []): array
    {
        return $this->request('GET', $url, null, $headers);
    }

    public function post(string $url, ?array $payload = null, array $headers = []): array
    {
        return $this->request('POST', $url, $payload, $headers);
    }

    public function put(string $url, ?array $payload = null, array $headers = []): array
    {
        return $this->request('PUT', $url, $payload, $headers);
    }

    public function postForm(string $url, array $payload, array $headers = []): array
    {
        return $this->request('POST', $url, $payload, $headers, false);
    }

    private function request(string $method, string $url, ?array $payload, array $headers, bool $jsonPayload = true): array
    {
        $ch = curl_init($url);

        $preparedHeaders = [];
        foreach ($headers as $name => $value) {
            $preparedHeaders[] = $name . ': ' . $value;
        }

        if ($payload !== null && $jsonPayload) {
            $preparedHeaders[] = 'Content-Type: application/json';
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $preparedHeaders,
            CURLOPT_TIMEOUT => 30,
        ]);

        if ($payload !== null) {
            curl_setopt(
                $ch,
                CURLOPT_POSTFIELDS,
                $jsonPayload
                    ? json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                    : $payload
            );
        }

        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new RuntimeException($error ?: 'HTTP request failed.');
        }

        $decoded = json_decode($response, true);

        if ($status >= 400) {
            $message = 'HTTP request failed with status ' . $status . '.';

            if (is_array($decoded)) {
                $candidate = $decoded['message']
                    ?? $decoded['error_description']
                    ?? $decoded['error']
                    ?? null;

                if (is_string($candidate) && $candidate !== '') {
                    $message = $candidate;
                } elseif (is_array($candidate)) {
                    $message = json_encode($candidate, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: $message;
                }
            }

            throw new RuntimeException($message);
        }

        return [
            'status' => $status,
            'body' => is_array($decoded) ? $decoded : ['raw' => $response],
        ];
    }
}
