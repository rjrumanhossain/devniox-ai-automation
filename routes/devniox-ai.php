<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

$routePrefix = config('devniox-ai.routes.prefix', 'devniox-ai');
$routeWebhookPrefix = config('devniox-ai.routes.webhook_prefix', 'webhooks');

Route::prefix($routePrefix)->group(function () use ($routeWebhookPrefix) {
    Route::post($routeWebhookPrefix.'/whatsapp', [\Devniox\AiAutomation\Http\Controllers\WebhookController::class, 'whatsapp'])->name('devniox-ai.webhooks.whatsapp');
    Route::post($routeWebhookPrefix.'/messenger', [\Devniox\AiAutomation\Http\Controllers\WebhookController::class, 'messenger'])->name('devniox-ai.webhooks.messenger');
    Route::post($routeWebhookPrefix.'/website', [\Devniox\AiAutomation\Http\Controllers\WebhookController::class, 'website'])->name('devniox-ai.webhooks.website');

    Route::match(['get', 'post'], 'chat', [\Devniox\AiAutomation\Http\Controllers\ChatController::class, 'send'])->name('devniox-ai.chat.send');
    Route::get('widget.js', [\Devniox\AiAutomation\Http\Controllers\ChatController::class, 'widgetScript'])->name('devniox-ai.widget.js');
    Route::get('widget.css', [\Devniox\AiAutomation\Http\Controllers\ChatController::class, 'widgetCss'])->name('devniox-ai.widget.css');
    Route::get('chat/{conversation}', [\Devniox\AiAutomation\Http\Controllers\ChatController::class, 'show'])->name('devniox-ai.chat.show');
    Route::get('status', [\Devniox\AiAutomation\Http\Controllers\StatusController::class, 'index'])->name('devniox-ai.status');
});
