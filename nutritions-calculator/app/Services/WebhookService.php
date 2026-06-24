<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebhookService
{
    public function sendToN8n(array $payload): void
    {
        $url = config('services.n8n.webhook_url');
        $secret = config('services.n8n.secret');

        if (! $url) {
            Log::warning('N8N_WEBHOOK_URL not configured — skipping webhook dispatch.');

            return;
        }

        Http::withHeader('X-Webhook-Secret', $secret)
            ->timeout(10)
            ->post($url, $payload);
    }
}
