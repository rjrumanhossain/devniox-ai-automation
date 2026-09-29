<?php

declare(strict_types=1);

namespace Devniox\AiAutomation\Services;

use Devniox\AiAutomation\Models\AiSetting;
use Devniox\AiAutomation\Models\ChannelConnection;
use Devniox\AiAutomation\Support\TenantResolver;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;

class SettingsService
{
    private const AI_API_KEY = 'ai.api_key';

    public function list(array $scope): array
    {
        $settings = AiSetting::query();
        TenantResolver::applyScope($settings, $scope);

        $listedSettings = $settings->get(['key', 'value'])
            ->reject(fn (AiSetting $setting) => $setting->key === self::AI_API_KEY)
            ->mapWithKeys(fn (AiSetting $setting) => [$setting->key => $setting->value])
            ->all();

        $channels = ChannelConnection::query();
        TenantResolver::applyScope($channels, $scope);

        $ai = $this->aiConfiguration($scope);
        unset($ai['api_key']);

        return [
            'settings' => $listedSettings,
            'ai' => $ai,
            'channels' => $channels->get()->map(fn (ChannelConnection $connection) => [
                'channel' => $connection->channel,
                'connection_name' => $connection->connection_name,
                'enabled' => $connection->enabled,
                'configured' => filled($connection->credentials_encrypted),
                'created_at' => $connection->created_at,
                'updated_at' => $connection->updated_at,
            ])->values()->all(),
        ];
    }

    public function putSetting(array $scope, string $key, mixed $value): AiSetting
    {
        $query = AiSetting::query();
        TenantResolver::applyScope($query, $scope);

        if ($key === self::AI_API_KEY && filled($value)) {
            $value = Crypt::encryptString((string) $value);
        }

        return $query->updateOrCreate([
            'key' => $key,
        ], array_merge($scope, [
            'key' => $key,
            'value' => $value,
        ]));
    }

    public function deleteSetting(array $scope, string $key): bool
    {
        if ($key === self::AI_API_KEY) {
            return false;
        }

        $query = AiSetting::query()->where('key', $key);
        TenantResolver::applyScope($query, $scope);

        return $query->delete() > 0;
    }

    public function saveAiConfiguration(array $scope, array $values): void
    {
        if (filter_var($values['clear_api_key'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            $query = AiSetting::query()->where('key', self::AI_API_KEY);
            TenantResolver::applyScope($query, $scope);
            $query->delete();
        } elseif (filled($values['api_key'] ?? null)) {
            $this->putSetting($scope, self::AI_API_KEY, $values['api_key']);
        }

        foreach ([
            'model' => 'ai.model',
            'use_ai_fallback' => 'ai.use_ai_fallback',
            'local_first' => 'answering.local_first',
            'system_instructions' => 'ai.system_instructions',
        ] as $input => $key) {
            if (array_key_exists($input, $values)) {
                $this->putSetting($scope, $key, $values[$input]);
            }
        }
    }

    public function aiConfiguration(array $scope): array
    {
        $settings = collect();

        if (Schema::hasTable('devniox_ai_settings')) {
            $query = AiSetting::query();
            TenantResolver::applyScope($query, $scope);

            $settings = $query->whereIn('key', [
                self::AI_API_KEY,
                'ai.model',
                'ai.use_ai_fallback',
                'answering.local_first',
                'ai.system_instructions',
            ])->get()->mapWithKeys(fn (AiSetting $setting) => [$setting->key => $setting->value]);
        }

        $encryptedApiKey = $settings->get(self::AI_API_KEY);
        $apiKey = config('devniox-ai.ai.api_key');
        $apiKeySaved = filled($encryptedApiKey);

        if (filled($encryptedApiKey)) {
            try {
                $apiKey = Crypt::decryptString($encryptedApiKey);
            } catch (\Throwable) {
                $apiKey = $encryptedApiKey;
            }
        }

        return [
            'api_key' => $apiKey,
            'api_key_configured' => filled($apiKey),
            'api_key_saved' => $apiKeySaved,
            'model' => $settings->get('ai.model', config('devniox-ai.ai.model', 'gpt-4o-mini')),
            'use_ai_fallback' => (bool) $settings->get('ai.use_ai_fallback', config('devniox-ai.ai.use_ai_fallback', true)),
            'local_first' => (bool) $settings->get('answering.local_first', config('devniox-ai.answering.local_first', true)),
            'system_instructions' => $settings->get('ai.system_instructions', config('devniox-ai.ai.system_instructions', 'You are a helpful customer support assistant.')),
        ];
    }

    public function putChannel(array $scope, string $channel, array $credentials, ?string $name = null, bool $enabled = true): ChannelConnection
    {
        $query = ChannelConnection::query();
        TenantResolver::applyScope($query, $scope);

        return $query->updateOrCreate([
            'channel' => $channel,
        ], array_merge($scope, [
            'channel' => $channel,
            'connection_name' => $name,
            'credentials_encrypted' => Crypt::encryptString(json_encode($credentials, JSON_THROW_ON_ERROR)),
            'enabled' => $enabled,
        ]));
    }

    public function deleteChannel(array $scope, string $channel): bool
    {
        $query = ChannelConnection::query()->where('channel', $channel);
        TenantResolver::applyScope($query, $scope);

        return $query->delete() > 0;
    }

    public function credentials(array $scope, string $channel): array
    {
        if (! Schema::hasTable('devniox_channel_connections')) {
            return [];
        }

        $query = ChannelConnection::query()->where('channel', $channel)->where('enabled', true);
        TenantResolver::applyScope($query, $scope);

        $connection = $query->latest('id')->first();

        if (! $connection || blank($connection->credentials_encrypted)) {
            return [];
        }

        return json_decode(Crypt::decryptString($connection->credentials_encrypted), true, 512, JSON_THROW_ON_ERROR);
    }
}
