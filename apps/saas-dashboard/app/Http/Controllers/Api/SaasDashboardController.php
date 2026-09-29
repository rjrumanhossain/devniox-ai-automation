<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class SaasDashboardController extends Controller
{
    public function overview(): JsonResponse
    {
        return response()->json([
            'metrics' => [
                ['label' => 'Clients', 'value' => '128', 'trend' => '+14'],
                ['label' => 'AI Replies', 'value' => '48.2k', 'trend' => '+31%'],
                ['label' => 'Channels', 'value' => '149', 'trend' => 'live'],
                ['label' => 'Token Cost', 'value' => '$184', 'trend' => '-12%'],
            ],
            'plans' => [
                ['name' => 'Starter', 'price' => 1490, 'cycle' => 'monthly', 'messages' => 3000, 'channels' => 1],
                ['name' => 'Growth', 'price' => 3990, 'cycle' => 'monthly', 'messages' => 12000, 'channels' => 3],
                ['name' => 'Business', 'price' => 8990, 'cycle' => 'monthly', 'messages' => 40000, 'channels' => 8],
                ['name' => 'Enterprise', 'price' => 89900, 'cycle' => 'yearly', 'messages' => 600000, 'channels' => 25],
            ],
            'clients' => [
                ['name' => 'BoneekBD', 'plan' => 'Business', 'channels' => ['Messenger', 'Website'], 'provider' => 'OpenAI', 'status' => 'Active', 'usage' => 82],
                ['name' => 'Style Hut', 'plan' => 'Starter', 'channels' => ['Website'], 'provider' => 'Claude', 'status' => 'Trial', 'usage' => 24],
                ['name' => 'Gadget Zone', 'plan' => 'Growth', 'channels' => ['WhatsApp', 'Instagram'], 'provider' => 'OpenAI', 'status' => 'Active', 'usage' => 61],
                ['name' => 'Home Craft', 'plan' => 'Business', 'channels' => ['Messenger', 'WhatsApp'], 'provider' => 'Claude', 'status' => 'Past due', 'usage' => 93],
            ],
            'channels' => [
                ['name' => 'Messenger', 'connected' => 34, 'live' => 31],
                ['name' => 'Instagram', 'connected' => 11, 'live' => 8],
                ['name' => 'WhatsApp', 'connected' => 18, 'live' => 15],
                ['name' => 'Website Chat', 'connected' => 96, 'live' => 91],
            ],
            'providers' => [
                ['name' => 'OpenAI', 'mode' => 'client_key', 'status' => 'Ready'],
                ['name' => 'Claude', 'mode' => 'client_key', 'status' => 'Ready'],
                ['name' => 'Devniox Managed', 'mode' => 'platform_key', 'status' => 'Default'],
            ],
            'automation' => [
                ['name' => 'Messenger OAuth', 'state' => 'Build'],
                ['name' => 'Instagram DM Webhook', 'state' => 'Build'],
                ['name' => 'WhatsApp Cloud API', 'state' => 'Build'],
                ['name' => 'Package Installer', 'state' => 'Ready'],
                ['name' => 'Live Handoff', 'state' => 'Next'],
                ['name' => 'Usage Billing', 'state' => 'Next'],
            ],
        ]);
    }

    public function documentation(): JsonResponse
    {
        return response()->json([
            'sections' => [
                ['title' => 'Client Setup', 'items' => ['Create business', 'Select plan', 'Generate API key', 'Install package']],
                ['title' => 'Channel Setup', 'items' => ['Connect Facebook page', 'Connect Instagram account', 'Connect WhatsApp number', 'Verify webhook']],
                ['title' => 'AI Setup', 'items' => ['Choose managed AI', 'Add OpenAI key', 'Add Claude key', 'Set token limits']],
                ['title' => 'Support Flow', 'items' => ['AI first reply', 'Product answer', 'Order question', 'Human handoff']],
            ],
        ]);
    }
}
