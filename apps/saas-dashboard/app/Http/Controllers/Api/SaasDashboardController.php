<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class SaasDashboardController extends Controller
{
    public function overview(): JsonResponse
    {
        return response()->json([
            'super_admin' => $this->superAdminPayload(),
            'customer' => $this->customerPayload(),
        ]);
    }

    public function superAdminOverview(): JsonResponse
    {
        return response()->json($this->superAdminPayload());
    }

    public function customerOverview(): JsonResponse
    {
        return response()->json($this->customerPayload());
    }

    public function documentation(): JsonResponse
    {
        return response()->json([
            'sections' => [
                ['title' => 'Role Setup', 'items' => ['Super admin account', 'Customer account', 'Business workspace', 'Plan access']],
                ['title' => 'Customer Setup', 'items' => ['Create business', 'Select plan', 'Generate API key', 'Install website package']],
                ['title' => 'Channel Setup', 'items' => ['Connect Facebook page', 'Connect Instagram account', 'Connect WhatsApp number', 'Verify webhook']],
                ['title' => 'AI Setup', 'items' => ['Use Devniox managed AI', 'Add OpenAI key', 'Add Claude key', 'Set token limits']],
                ['title' => 'Support Flow', 'items' => ['Customer message', 'Knowledge lookup', 'AI auto reply', 'Human handoff']],
            ],
        ]);
    }

    private function superAdminPayload(): array
    {
        return [
            'role' => 'super_admin',
            'metrics' => [
                ['label' => 'Customers', 'value' => '128', 'trend' => '+14'],
                ['label' => 'AI Replies', 'value' => '48.2k', 'trend' => '+31%'],
                ['label' => 'Connected Channels', 'value' => '149', 'trend' => 'live'],
                ['label' => 'Token Cost', 'value' => '$184', 'trend' => '-12%'],
            ],
            'plans' => $this->plans(),
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
                ['name' => 'OpenAI', 'mode' => 'customer_key', 'status' => 'Ready'],
                ['name' => 'Claude', 'mode' => 'customer_key', 'status' => 'Ready'],
                ['name' => 'Devniox Managed', 'mode' => 'platform_key', 'status' => 'Default'],
            ],
            'automation' => [
                ['name' => 'Messenger OAuth', 'state' => 'Build'],
                ['name' => 'Instagram DM Webhook', 'state' => 'Build'],
                ['name' => 'WhatsApp Cloud API', 'state' => 'Build'],
                ['name' => 'Website Package API', 'state' => 'Ready'],
                ['name' => 'Live Handoff', 'state' => 'Next'],
                ['name' => 'Usage Billing', 'state' => 'Next'],
            ],
        ];
    }

    private function customerPayload(): array
    {
        return [
            'role' => 'customer',
            'business' => [
                'name' => 'BoneekBD',
                'plan' => 'Business',
                'status' => 'Active',
                'api_key_prefix' => 'dnx_live_boneekbd',
            ],
            'metrics' => [
                ['label' => 'Auto Replies', 'value' => '8.4k', 'trend' => '+18%'],
                ['label' => 'Conversations', 'value' => '1.2k', 'trend' => 'live'],
                ['label' => 'Handoffs', 'value' => '19', 'trend' => '-8%'],
                ['label' => 'Token Usage', 'value' => '62%', 'trend' => 'safe'],
            ],
            'channels' => [
                ['name' => 'Messenger', 'connected' => 1, 'live' => 1, 'status' => 'Connected', 'target' => 'BoneekBD Page'],
                ['name' => 'Instagram', 'connected' => 0, 'live' => 0, 'status' => 'Not connected', 'target' => 'Business DM'],
                ['name' => 'WhatsApp', 'connected' => 1, 'live' => 1, 'status' => 'Connected', 'target' => '+8801*********'],
                ['name' => 'Website Chat', 'connected' => 1, 'live' => 1, 'status' => 'Connected', 'target' => 'boneekbd.test'],
            ],
            'providers' => [
                ['name' => 'OpenAI', 'mode' => 'own_api_key', 'status' => 'Connected'],
                ['name' => 'Claude', 'mode' => 'own_api_key', 'status' => 'Not connected'],
                ['name' => 'Devniox Managed', 'mode' => 'fallback', 'status' => 'Available'],
            ],
            'automation' => [
                ['name' => 'AI Auto Reply', 'state' => 'On'],
                ['name' => 'Messenger Reply', 'state' => 'On'],
                ['name' => 'WhatsApp Reply', 'state' => 'On'],
                ['name' => 'Website Live Chat', 'state' => 'On'],
                ['name' => 'Instagram Reply', 'state' => 'Setup'],
                ['name' => 'Human Handoff', 'state' => 'Ready'],
            ],
            'reply_flow' => [
                ['step' => 'Receive', 'channel' => 'Messenger, WhatsApp, Instagram, Website'],
                ['step' => 'Understand', 'channel' => 'Business knowledge, product data, FAQ'],
                ['step' => 'Generate', 'channel' => 'OpenAI, Claude, Devniox managed'],
                ['step' => 'Send', 'channel' => 'Same customer channel'],
            ],
        ];
    }

    private function plans(): array
    {
        return [
            ['name' => 'Starter', 'price' => 1490, 'cycle' => 'monthly', 'messages' => 3000, 'channels' => 1],
            ['name' => 'Growth', 'price' => 3990, 'cycle' => 'monthly', 'messages' => 12000, 'channels' => 3],
            ['name' => 'Business', 'price' => 8990, 'cycle' => 'monthly', 'messages' => 40000, 'channels' => 8],
            ['name' => 'Enterprise', 'price' => 89900, 'cycle' => 'yearly', 'messages' => 600000, 'channels' => 25],
        ];
    }
}
