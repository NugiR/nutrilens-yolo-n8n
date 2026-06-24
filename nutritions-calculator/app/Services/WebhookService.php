<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class WebhookService
{
    public function sendToN8n(array $payload, ?string $photoPath = null): void
    {
        $url = config('services.n8n.webhook_url');
        $secret = config('services.n8n.secret');

        if (! $url) {
            Log::warning('N8N_WEBHOOK_URL not configured — skipping webhook dispatch.');

            return;
        }

        try {
            $client = new Client(['timeout' => 30]);
            $options = ['headers' => ['X-Webhook-Secret' => $secret]];

            if ($photoPath && Storage::disk('public')->exists($photoPath)) {
                $multipart = [['name' => 'image', 'contents' => Storage::disk('public')->get($photoPath), 'filename' => basename($photoPath), 'headers' => ['Content-Type' => 'image/jpeg']]];
                foreach ($payload as $key => $value) {
                    $multipart[] = ['name' => $key, 'contents' => (string) $value];
                }
                $options['multipart'] = $multipart;
            } else {
                $options['json'] = $payload;
            }

            $client->post($url, $options);

            Log::info('N8N webhook dispatched.', ['meal_log_id' => $payload['meal_log_id'] ?? null]);
        } catch (\Exception $e) {
            Log::error('N8N webhook dispatch failed: ' . $e->getMessage(), ['url' => $url]);
        }
    }
}
