<?php

declare(strict_types=1);

use Devniox\AiAutomation\Http\Controllers\Admin\ConversationController;
use Devniox\AiAutomation\Http\Controllers\Admin\FaqController;
use Devniox\AiAutomation\Http\Controllers\Admin\KnowledgeController;
use Devniox\AiAutomation\Http\Controllers\Admin\SettingsController;
use Devniox\AiAutomation\Http\Controllers\ChatController;
use Devniox\AiAutomation\Http\Controllers\StatusController;
use Devniox\AiAutomation\Http\Controllers\WebhookController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;

$routePrefix = config('devniox-ai.routes.prefix', 'devniox-ai');
$routeWebhookPrefix = config('devniox-ai.routes.webhook_prefix', 'webhooks');
$routeAdminPrefix = config('devniox-ai.routes.admin_prefix', 'admin');
$adminMiddleware = config('devniox-ai.routes.admin_middleware', []);

Route::prefix($routePrefix)->group(function () use ($adminMiddleware, $routeAdminPrefix, $routeWebhookPrefix) {
    Route::match(['get', 'post'], $routeWebhookPrefix.'/whatsapp', [WebhookController::class, 'whatsapp'])->name('devniox-ai.webhooks.whatsapp');
    Route::match(['get', 'post'], $routeWebhookPrefix.'/messenger', [WebhookController::class, 'messenger'])->name('devniox-ai.webhooks.messenger');
    Route::post($routeWebhookPrefix.'/website', [WebhookController::class, 'website'])->name('devniox-ai.webhooks.website');

    Route::match(['get', 'post'], 'chat', [ChatController::class, 'send'])->name('devniox-ai.chat.send');
    Route::get('widget.js', [ChatController::class, 'widgetScript'])->name('devniox-ai.widget.js');
    Route::get('widget.css', [ChatController::class, 'widgetCss'])->name('devniox-ai.widget.css');
    Route::get('chat/{conversation}', [ChatController::class, 'show'])->name('devniox-ai.chat.show');
    Route::get('status', [StatusController::class, 'index'])->name('devniox-ai.status');

    Route::prefix($routeAdminPrefix)->middleware($adminMiddleware)->group(function () {
        Route::get('/', fn (): RedirectResponse => redirect()->route('devniox-ai.admin.settings.index'))->name('devniox-ai.admin.index');
        Route::get('settings', [SettingsController::class, 'index'])->name('devniox-ai.admin.settings.index');
        Route::post('settings', [SettingsController::class, 'store'])->name('devniox-ai.admin.settings.store');
        Route::delete('settings', [SettingsController::class, 'destroy'])->name('devniox-ai.admin.settings.destroy');
        Route::post('channels', [SettingsController::class, 'channel'])->name('devniox-ai.admin.channels.store');
        Route::post('channels/test', [SettingsController::class, 'testChannel'])->name('devniox-ai.admin.channels.test');
        Route::delete('channels', [SettingsController::class, 'destroyChannel'])->name('devniox-ai.admin.channels.destroy');

        Route::apiResource('faqs', FaqController::class)->names('devniox-ai.admin.faqs');
        Route::apiResource('knowledge', KnowledgeController::class)->parameter('knowledge', 'knowledge')->names('devniox-ai.admin.knowledge');

        Route::get('conversations', [ConversationController::class, 'index'])->name('devniox-ai.admin.conversations.index');
        Route::get('conversations/{conversation}', [ConversationController::class, 'show'])->name('devniox-ai.admin.conversations.show');
        Route::post('conversations/{conversation}/close', [ConversationController::class, 'close'])->name('devniox-ai.admin.conversations.close');
    });
});
