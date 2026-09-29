@extends(config('devniox-ai.admin.layout', 'devniox-ai::admin.layout'))

@section('title', 'AI Automation')

@section('css')
<style>
    .dn-admin {
        --dn-ink: #1d2b2b;
        --dn-muted: #71817e;
        --dn-line: #dce6e3;
        --dn-paper: #fff;
        --dn-accent: #087f70;
        color: var(--dn-ink);
        padding: 20px 22px 36px;
        max-width: 1480px;
        margin: 0 auto;
    }
    .dn-admin *, .dn-admin *::before, .dn-admin *::after { box-sizing: border-box; }
    .dn-admin .dn-page-heading { display: flex; justify-content: space-between; align-items: flex-start; gap: 20px; margin: 0 0 20px; }
    .dn-admin .dn-eyebrow { color: var(--dn-accent); font-size: 11px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; margin: 0 0 5px; }
    .dn-admin .dn-page-heading h1 { color: var(--dn-ink); font-size: 24px; line-height: 1.25; font-weight: 750; margin: 0; }
    .dn-admin .dn-page-heading p { color: var(--dn-muted); font-size: 13px; margin: 6px 0 0; }
    .dn-admin .dn-online { display: inline-flex; align-items: center; gap: 7px; padding: 7px 10px; border: 1px solid #bfe4d8; border-radius: 999px; background: #effaf5; color: #197252; font-size: 12px; font-weight: 700; white-space: nowrap; }
    .dn-admin .dn-online::before { content: ""; width: 7px; height: 7px; border-radius: 50%; background: #25a46b; }
    .dn-admin .dn-tabs { display: flex; align-items: center; gap: 4px; padding: 5px; margin: 0 0 18px; border: 1px solid var(--dn-line); border-radius: 8px; background: #f8faf9; overflow-x: auto; }
    .dn-admin .dn-tabs a { display: inline-flex; align-items: center; min-height: 36px; padding: 0 13px; border-radius: 5px; color: #50615d; font-size: 13px; font-weight: 650; text-decoration: none; white-space: nowrap; }
    .dn-admin .dn-tabs a:hover { background: #edf4f1; color: var(--dn-ink); }
    .dn-admin .dn-tabs a.active { background: var(--dn-accent); color: white; }
    .dn-admin .dn-panel { background: var(--dn-paper); border: 1px solid var(--dn-line); border-radius: 8px; box-shadow: 0 2px 8px rgba(24, 49, 42, .035); }
    .dn-admin .card { margin: 0 0 16px; border: 1px solid var(--dn-line); border-radius: 8px; background: var(--dn-paper); box-shadow: 0 2px 8px rgba(24, 49, 42, .035); }
    .dn-admin .card-body { padding: 19px 20px; }
    .dn-admin .card-title { margin: 0 0 15px; color: var(--dn-ink); font-size: 16px; font-weight: 720; }
    .dn-admin .dn-panel + .dn-panel { margin-top: 16px; }
    .dn-admin .dn-panel-body { padding: 20px; }
    .dn-admin .dn-panel-heading { display: flex; justify-content: space-between; align-items: center; gap: 12px; margin: 0 0 16px; }
    .dn-admin .dn-panel-heading h2 { color: var(--dn-ink); font-size: 16px; font-weight: 720; line-height: 1.35; margin: 0; }
    .dn-admin .dn-panel-heading p { color: var(--dn-muted); font-size: 12px; margin: 4px 0 0; }
    .dn-admin .dn-count { color: var(--dn-muted); font-size: 12px; font-weight: 600; }
    .dn-admin .dn-form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px 18px; }
    .dn-admin .dn-field { min-width: 0; }
    .dn-admin .dn-field-full { grid-column: 1 / -1; }
    .dn-admin .dn-field > label, .dn-admin label.dn-label { display: block; color: #384945; font-size: 12px; font-weight: 700; margin: 0 0 6px; }
    .dn-admin .dn-field input:not([type="checkbox"]), .dn-admin .dn-field textarea, .dn-admin .dn-field select { display: block; width: 100%; min-height: 39px; padding: 9px 11px; border: 1px solid #ccd9d5; border-radius: 5px; background: white; color: var(--dn-ink); font: inherit; font-size: 13px; box-shadow: none; }
    .dn-admin .dn-field textarea { min-height: 92px; resize: vertical; line-height: 1.5; }
    .dn-admin .dn-field input:focus, .dn-admin .dn-field textarea:focus, .dn-admin .dn-field select:focus { border-color: #3a9e8d; outline: 3px solid rgba(8, 127, 112, .12); }
    .dn-admin form > label, .dn-admin form .col-12 > label, .dn-admin form .col-md-6 > label, .dn-admin form .col-md-4 > label, .dn-admin form .col-lg-6 > label { display: block; color: #384945; font-size: 12px; font-weight: 700; margin: 12px 0 6px; }
    .dn-admin form input:not([type="checkbox"]), .dn-admin form textarea, .dn-admin form select { display: block; width: 100%; min-height: 39px; padding: 9px 11px; border: 1px solid #ccd9d5; border-radius: 5px; background: #fff; color: var(--dn-ink); font: inherit; font-size: 13px; box-shadow: none; }
    .dn-admin form textarea { min-height: 92px; resize: vertical; line-height: 1.5; }
    .dn-admin form input:focus, .dn-admin form textarea:focus, .dn-admin form select:focus { border-color: #3a9e8d; outline: 3px solid rgba(8, 127, 112, .12); }
    .dn-admin form input[type="checkbox"] { width: 16px !important; height: 16px; margin: 0 7px 0 0; vertical-align: middle; accent-color: var(--dn-accent); }
    .dn-admin form .col-12:has(input[type="checkbox"]), .dn-admin form .col-md-4:has(input[type="checkbox"]) { padding-top: 10px; }
    .dn-admin .card .table { margin: 0; }
    .dn-admin .card .table th { padding: 10px 12px; background: #f6f9f8; color: #61716d; font-size: 11px; font-weight: 750; text-transform: uppercase; white-space: nowrap; }
    .dn-admin .card .table td { padding: 11px 12px; border-color: #e8eeec; color: #354541; font-size: 13px; vertical-align: middle; }
    .dn-admin .card .table a { color: var(--dn-accent); font-weight: 650; text-decoration: none; }
    .dn-admin .card .table a:hover { text-decoration: underline; }
    .dn-admin .record { padding: 18px 0; border-top: 1px solid #e8eeec; }
    .dn-admin .record:first-of-type { padding-top: 0; border-top: 0; }
    .dn-admin .record:last-child { padding-bottom: 0; }
    .dn-admin .btn { display: inline-flex; justify-content: center; align-items: center; min-height: 36px; padding: 0 13px; border-radius: 5px; font-size: 12px; font-weight: 700; }
    .dn-admin .btn-primary { border-color: var(--dn-accent); background: var(--dn-accent); }
    .dn-admin .btn-primary:hover { border-color: #066d61; background: #066d61; }
    .dn-admin .btn-outline-primary { border-color: #b7d4cd; color: var(--dn-accent); }
    .dn-admin .btn-outline-danger { border-color: #e6c8c5; color: #a13e37; }
    .dn-admin .border.rounded { border-color: #e1e9e6 !important; border-radius: 7px !important; background: #fbfcfc; }
    .dn-admin .border.rounded p { color: #354541; font-size: 13px; line-height: 1.55; overflow-wrap: anywhere; }
    .dn-admin .dn-check { display: inline-flex; align-items: center; gap: 8px; color: #445550; font-size: 13px; font-weight: 550; cursor: pointer; }
    .dn-admin .dn-check input { width: 16px !important; height: 16px; margin: 0; accent-color: var(--dn-accent); }
    .dn-admin .dn-actions { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; margin-top: 16px; }
    .dn-admin .dn-button { display: inline-flex; justify-content: center; align-items: center; min-height: 36px; padding: 0 13px; border: 1px solid var(--dn-accent); border-radius: 5px; background: var(--dn-accent); color: white; font-size: 12px; font-weight: 700; line-height: 1.2; cursor: pointer; text-decoration: none; transition: background .15s ease, border-color .15s ease; }
    .dn-admin .dn-button:hover { background: #066d61; border-color: #066d61; color: white; }
    .dn-admin .dn-button-secondary { border-color: #cad7d3; background: white; color: #40524d; }
    .dn-admin .dn-button-secondary:hover { background: #f2f7f5; border-color: #a9bfba; color: #263c36; }
    .dn-admin .dn-button-danger { border-color: #e6c8c5; background: white; color: #a13e37; }
    .dn-admin .dn-button-danger:hover { border-color: #d9a39e; background: #fff5f4; color: #922f29; }
    .dn-admin .dn-empty { padding: 26px 14px; border: 1px dashed #d3dfdb; border-radius: 6px; color: var(--dn-muted); font-size: 13px; text-align: center; }
    .dn-admin .dn-record { padding: 18px 0; border-top: 1px solid #e8eeec; }
    .dn-admin .dn-record:first-of-type { padding-top: 0; border-top: 0; }
    .dn-admin .dn-record:last-child { padding-bottom: 0; }
    .dn-admin .dn-table-wrap { width: 100%; overflow-x: auto; }
    .dn-admin .dn-table { width: 100%; border-collapse: collapse; }
    .dn-admin .dn-table th { padding: 10px 12px; background: #f6f9f8; color: #61716d; font-size: 11px; font-weight: 750; text-align: left; text-transform: uppercase; white-space: nowrap; }
    .dn-admin .dn-table td { padding: 12px; border-top: 1px solid #e8eeec; color: #354541; font-size: 13px; vertical-align: middle; }
    .dn-admin .dn-table a { color: var(--dn-accent); font-weight: 650; text-decoration: none; }
    .dn-admin .dn-table a:hover { text-decoration: underline; }
    .dn-admin .dn-status { display: inline-flex; padding: 4px 8px; border-radius: 999px; background: #edf7f2; color: #27714f; font-size: 11px; font-weight: 700; }
    .dn-admin .dn-message { padding: 10px 13px; margin: 0 0 14px; border-radius: 5px; font-size: 13px; }
    .dn-admin .dn-message-success { border: 1px solid #bfe4d8; background: #effaf5; color: #176548; }
    .dn-admin .dn-message-error { border: 1px solid #f0c7c2; background: #fff4f2; color: #9d342c; }
    .dn-admin .dn-chat-message { padding: 13px 14px; margin: 10px 0; border: 1px solid #e1e9e6; border-radius: 7px; background: #fbfcfc; }
    .dn-admin .dn-chat-message p { margin: 8px 0 0; color: #354541; font-size: 13px; line-height: 1.55; overflow-wrap: anywhere; }
    .dn-admin .dn-chat-meta { display: flex; justify-content: space-between; gap: 12px; color: var(--dn-muted); font-size: 11px; }
    .dn-admin .dn-pagination { margin-top: 16px; }
    .dn-admin .dn-pagination nav { margin: 0; }
    @media (max-width: 767px) {
        .dn-admin { padding: 15px 12px 26px; }
        .dn-admin .dn-page-heading { gap: 10px; margin-bottom: 14px; }
        .dn-admin .dn-page-heading h1 { font-size: 20px; }
        .dn-admin .dn-page-heading p { max-width: 36ch; font-size: 12px; }
        .dn-admin .dn-online { padding: 6px 8px; font-size: 11px; }
        .dn-admin .dn-tabs { gap: 2px; margin-bottom: 13px; }
        .dn-admin .dn-tabs a { min-height: 34px; padding: 0 10px; font-size: 12px; }
        .dn-admin .dn-panel-body { padding: 15px 13px; }
        .dn-admin .card-body { padding: 15px 13px; }
        .dn-admin .dn-form-grid { grid-template-columns: minmax(0, 1fr); gap: 11px; }
        .dn-admin .dn-field-full { grid-column: auto; }
        .dn-admin .dn-table { min-width: 560px; }
    }
</style>
@endsection

@section('content')
@php
    $pages = [
        'settings' => ['Settings', route('devniox-ai.admin.settings.index')],
        'faqs' => ['FAQs', route('devniox-ai.admin.faqs.index')],
        'knowledge' => ['Knowledge', route('devniox-ai.admin.knowledge.index')],
        'conversations' => ['Conversations', route('devniox-ai.admin.conversations.index')],
    ];
    $titles = ['settings' => 'Settings', 'faqs' => 'FAQs', 'knowledge' => 'Knowledge', 'conversations' => 'Conversations', 'conversation' => 'Conversation'];
@endphp
<div class="container-fluid dn-admin">
    <div class="dn-page-heading">
        <div>
            <div class="dn-eyebrow">Customer support platform</div>
            <h1>AI Automation <span class="text-muted">/ {{ $titles[$section] }}</span></h1>
            <p>Manage customer-support automation data.</p>
        </div>
        <span class="dn-online">Online</span>
    </div>

    <nav class="dn-tabs" aria-label="AI Automation sections">
        @foreach($pages as $key => [$label, $url])
            <a class="nav-link {{ $section === $key ? 'active' : '' }}" href="{{ $url }}">{{ $label }}</a>
        @endforeach
    </nav>

    <div data-ai-message class="dn-message d-none" role="status" aria-live="polite"></div>

    @if($section === 'settings')
        <div class="row g-3">
            <div class="col-lg-6">
                <section class="card">
                    <div class="card-body">
                        <h5 class="card-title">Automation settings</h5>
                        <p class="text-muted">Configure the AI provider for this business. The API key is encrypted when saved.</p>
                        <form action="{{ route('devniox-ai.admin.settings.store') }}" method="post" data-ai-form data-kind="ai-settings">
                            @csrf
                            <label for="ai-api-key">OpenAI API key</label>
                            <input id="ai-api-key" name="api_key" type="password" maxlength="4096" autocomplete="new-password" placeholder="{{ $ai['api_key_saved'] ? 'Key saved; enter only to replace it' : ($ai['api_key_configured'] ? 'Key available from environment; enter to override' : 'Paste API key') }}">
                            @if($ai['api_key_saved'])
                                <label class="d-flex align-items-center gap-2 mt-2"><input type="checkbox" name="clear_api_key" style="width:auto"> Remove saved API key</label>
                            @endif
                            <label for="ai-model">Model</label>
                            <input id="ai-model" name="model" maxlength="120" required value="{{ $ai['model'] }}">
                            <label class="d-flex align-items-center gap-2 mt-3"><input type="checkbox" name="use_ai_fallback" {{ $ai['use_ai_fallback'] ? 'checked' : '' }} style="width:auto"> Enable AI fallback</label>
                            <label class="d-flex align-items-center gap-2 mt-2"><input type="checkbox" name="local_first" {{ $ai['local_first'] ? 'checked' : '' }} style="width:auto"> Search FAQs and knowledge first</label>
                            <label for="ai-instructions">System instructions</label>
                            <textarea id="ai-instructions" name="system_instructions" maxlength="8000">{{ $ai['system_instructions'] }}</textarea>
                            <button class="btn btn-primary mt-3" type="submit">Save AI settings</button>
                        </form>
                        <div class="table-responsive mt-4">
                            <table class="table">
                                <thead><tr><th>Key</th><th>Value</th><th>Actions</th></tr></thead>
                                <tbody>
                                @forelse($settings as $key => $value)
                                    <tr>
                                        <td>{{ $key }}</td>
                                        <td class="text-break">{{ $value }}</td>
                                        <td>
                                            <form action="{{ route('devniox-ai.admin.settings.destroy') }}" method="post" data-ai-form data-kind="delete-setting" data-method="DELETE" onsubmit="return confirm('Delete this setting?')">
                                                @csrf
                                                <input type="hidden" name="key" value="{{ $key }}">
                                                <button class="btn btn-outline-danger" type="submit">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-muted">No saved settings yet.</td></tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>
            </div>
            <div class="col-lg-6">
                <section class="card">
                    <div class="card-body">
                        <h5 class="card-title">Channel connections</h5>
                        <p class="text-muted">Connect official Meta channels. Copy the callback URL and verify token into Meta, then save the access credentials here.</p>
                        <div class="table-responsive">
                            <table class="table">
                                <thead><tr><th>Channel</th><th>Name</th><th>Status</th><th>Actions</th></tr></thead>
                                <tbody>
                                @forelse($channels as $channel)
                                    <tr>
                                        <td>{{ ucfirst($channel['channel']) }}</td>
                                        <td>{{ $channel['connection_name'] ?: '—' }}</td>
                                        <td>{{ $channel['enabled'] ? 'Enabled' : 'Disabled' }}{{ $channel['configured'] ? ' · Credentials saved' : ' · Missing credentials' }}</td>
                                        <td>
                                            <form action="{{ route('devniox-ai.admin.channels.destroy') }}" method="post" data-ai-form data-kind="delete-channel" data-method="DELETE" onsubmit="return confirm('Remove this channel connection?')">
                                                @csrf
                                                <input type="hidden" name="channel" value="{{ $channel['channel'] }}">
                                                <button class="btn btn-outline-danger" type="submit">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-muted">No channels connected yet.</td></tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="row g-3 mt-2">
                            <div class="col-12">
                                <section class="border rounded p-3">
                                    <h6 class="mb-2">Messenger connect</h6>
                                    <label>Callback URL</label>
                                    <input readonly value="{{ $channelGuides['messenger']['callback_url'] ?? '' }}" onclick="this.select()">
                                    <form action="{{ route('devniox-ai.admin.channels.store') }}" method="post" data-ai-form data-kind="channel" class="mt-2">
                                        @csrf
                                        <input type="hidden" name="channel" value="messenger">
                                        <input type="hidden" name="connection_name" value="Facebook Messenger">
                                        <div class="row g-2">
                                            <div class="col-md-6"><label>Page access token</label><input type="password" data-credential-key="page_access_token" autocomplete="new-password" required></div>
                                            <div class="col-md-6"><label>Verify token</label><input data-credential-key="verify_token" placeholder="Any secret text you choose" required></div>
                                            <div class="col-md-6"><label>App secret</label><input type="password" data-credential-key="app_secret" autocomplete="new-password"></div>
                                            <div class="col-md-6"><label>Graph API URL</label><input data-credential-key="api_url" value="{{ config('devniox-ai.messenger.api_url') }}"></div>
                                        </div>
                                        <label class="d-flex align-items-center gap-2 mt-3"><input type="checkbox" name="enabled" checked style="width:auto"> Enable Messenger auto reply</label>
                                        <button class="btn btn-primary mt-2" type="submit">Connect Messenger</button>
                                    </form>
                                    <form action="{{ route('devniox-ai.admin.channels.test') }}" method="post" data-ai-form data-kind="channel-test" class="mt-2">
                                        @csrf
                                        <input type="hidden" name="channel" value="messenger">
                                        <button class="btn btn-outline-primary" type="submit">Test Messenger connection</button>
                                    </form>
                                </section>
                            </div>
                            <div class="col-12">
                                <section class="border rounded p-3">
                                    <h6 class="mb-2">WhatsApp connect</h6>
                                    <label>Callback URL</label>
                                    <input readonly value="{{ $channelGuides['whatsapp']['callback_url'] ?? '' }}" onclick="this.select()">
                                    <form action="{{ route('devniox-ai.admin.channels.store') }}" method="post" data-ai-form data-kind="channel" class="mt-2">
                                        @csrf
                                        <input type="hidden" name="channel" value="whatsapp">
                                        <input type="hidden" name="connection_name" value="WhatsApp Business">
                                        <div class="row g-2">
                                            <div class="col-md-6"><label>Access token</label><input type="password" data-credential-key="token" autocomplete="new-password" required></div>
                                            <div class="col-md-6"><label>Phone number ID</label><input data-credential-key="phone_id" required></div>
                                            <div class="col-md-6"><label>Verify token</label><input data-credential-key="verify_token" placeholder="Any secret text you choose" required></div>
                                            <div class="col-md-6"><label>App secret</label><input type="password" data-credential-key="webhook_secret" autocomplete="new-password"></div>
                                            <div class="col-md-6"><label>Graph API URL</label><input data-credential-key="api_url" value="{{ config('devniox-ai.whatsapp.api_url') }}"></div>
                                        </div>
                                        <label class="d-flex align-items-center gap-2 mt-3"><input type="checkbox" name="enabled" checked style="width:auto"> Enable WhatsApp auto reply</label>
                                        <button class="btn btn-primary mt-2" type="submit">Connect WhatsApp</button>
                                    </form>
                                    <form action="{{ route('devniox-ai.admin.channels.test') }}" method="post" data-ai-form data-kind="channel-test" class="mt-2">
                                        @csrf
                                        <input type="hidden" name="channel" value="whatsapp">
                                        <button class="btn btn-outline-primary" type="submit">Test WhatsApp connection</button>
                                    </form>
                                </section>
                            </div>
                        </div>
                        <details class="mt-3">
                            <summary>Advanced JSON credentials</summary>
                            <form action="{{ route('devniox-ai.admin.channels.store') }}" method="post" data-ai-form data-kind="channel">
                                @csrf
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <label for="channel-type">Channel</label>
                                        <select id="channel-type" name="channel"><option value="whatsapp">WhatsApp</option><option value="messenger">Messenger</option></select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="channel-name">Connection name</label>
                                        <input id="channel-name" name="connection_name" maxlength="120" placeholder="Main support">
                                    </div>
                                </div>
                                <label for="channel-credentials">Credentials (JSON)</label>
                                <textarea id="channel-credentials" name="credentials_json" required placeholder='{"token":"...","phone_id":"...","verify_token":"..."}'></textarea>
                                <label class="d-flex align-items-center gap-2 mt-3"><input type="checkbox" name="enabled" checked style="width:auto"> Enable connection</label>
                                <button class="btn btn-primary mt-2" type="submit">Save channel</button>
                            </form>
                        </details>
                    </div>
                </section>
            </div>
        </div>
    @elseif($section === 'faqs')
        <section class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Add FAQ</h5>
                <form action="{{ route('devniox-ai.admin.faqs.store') }}" method="post" data-ai-form data-kind="faq">
                    @csrf
                    <div class="row g-3">
                        <div class="col-lg-6"><label for="faq-question">Question</label><input id="faq-question" name="question" maxlength="255" required></div>
                        <div class="col-lg-6"><label for="faq-keywords">Keywords, comma separated</label><input id="faq-keywords" name="keywords_text" placeholder="shipping, delivery"></div>
                        <div class="col-12"><label for="faq-answer">Answer</label><textarea id="faq-answer" name="answer" required></textarea></div>
                        <div class="col-12"><label><input type="checkbox" name="is_active" checked style="width:auto"> Active</label></div>
                    </div>
                    <button class="btn btn-primary mt-3" type="submit">Add FAQ</button>
                </form>
            </div>
        </section>
        <section class="card"><div class="card-body">
            <h5 class="card-title">FAQ entries <span class="text-muted">({{ $items->total() }})</span></h5>
            @forelse($items as $item)
                <div class="record">
                    <form action="{{ route('devniox-ai.admin.faqs.update', $item->id) }}" method="post" data-ai-form data-kind="faq" data-method="PUT">
                        @csrf
                        <div class="row g-3">
                            <div class="col-lg-6"><label>Question</label><input name="question" maxlength="255" required value="{{ $item->question }}"></div>
                            <div class="col-lg-6"><label>Keywords, comma separated</label><input name="keywords_text" value="{{ implode(', ', $item->keywords ?? []) }}"></div>
                            <div class="col-12"><label>Answer</label><textarea name="answer" required>{{ $item->answer }}</textarea></div>
                            <div class="col-12"><label><input type="checkbox" name="is_active" {{ $item->is_active ? 'checked' : '' }} style="width:auto"> Active</label></div>
                        </div>
                        <button class="btn btn-primary mt-2" type="submit">Save changes</button>
                    </form>
                    <form action="{{ route('devniox-ai.admin.faqs.destroy', $item->id) }}" method="post" data-ai-form data-method="DELETE" class="mt-2" onsubmit="return confirm('Delete this FAQ?')">
                        @csrf<button class="btn btn-outline-danger" type="submit">Delete</button>
                    </form>
                </div>
            @empty
                <p class="text-muted mb-0">No FAQs yet. Add the first one above.</p>
            @endforelse
            <div class="mt-3">{{ $items->links() }}</div>
        </div></section>
    @elseif($section === 'knowledge')
        <section class="card mb-4"><div class="card-body">
            <h5 class="card-title">Add knowledge</h5>
            <form action="{{ route('devniox-ai.admin.knowledge.store') }}" method="post" data-ai-form data-kind="knowledge">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4"><label>Title</label><input name="title" maxlength="255" required></div>
                    <div class="col-md-4"><label>Category</label><input name="category" maxlength="120"></div>
                    <div class="col-md-4"><label><input type="checkbox" name="is_active" checked style="width:auto"> Active</label></div>
                    <div class="col-12"><label>Content</label><textarea name="content" required></textarea></div>
                </div>
                <button class="btn btn-primary mt-3" type="submit">Add knowledge</button>
            </form>
        </div></section>
        <section class="card"><div class="card-body">
            <h5 class="card-title">Knowledge entries <span class="text-muted">({{ $items->total() }})</span></h5>
            @forelse($items as $item)
                <div class="record">
                    <form action="{{ route('devniox-ai.admin.knowledge.update', $item->id) }}" method="post" data-ai-form data-kind="knowledge" data-method="PUT">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6"><label>Title</label><input name="title" maxlength="255" required value="{{ $item->title }}"></div>
                            <div class="col-md-6"><label>Category</label><input name="category" maxlength="120" value="{{ $item->category }}"></div>
                            <div class="col-12"><label>Content</label><textarea name="content" required>{{ $item->content }}</textarea></div>
                            <div class="col-12"><label><input type="checkbox" name="is_active" {{ $item->is_active ? 'checked' : '' }} style="width:auto"> Active</label></div>
                        </div>
                        <button class="btn btn-primary mt-2" type="submit">Save changes</button>
                    </form>
                    <form action="{{ route('devniox-ai.admin.knowledge.destroy', $item->id) }}" method="post" data-ai-form data-method="DELETE" class="mt-2" onsubmit="return confirm('Delete this knowledge item?')">
                        @csrf<button class="btn btn-outline-danger" type="submit">Delete</button>
                    </form>
                </div>
            @empty
                <p class="text-muted mb-0">No knowledge entries yet. Add the first one above.</p>
            @endforelse
            <div class="mt-3">{{ $items->links() }}</div>
        </div></section>
    @elseif($section === 'conversations')
        <section class="card"><div class="card-body">
            <h5 class="card-title">Conversations <span class="text-muted">({{ $items->total() }})</span></h5>
            <div class="table-responsive"><table class="table table-hover align-middle">
                <thead><tr><th>Conversation</th><th>Channel</th><th>Status</th><th>Messages</th><th>Started</th></tr></thead>
                <tbody>
                @forelse($items as $item)
                    <tr>
                        <td><a href="{{ route('devniox-ai.admin.conversations.show', $item->uuid) }}">{{ $item->uuid }}</a></td>
                        <td>{{ ucfirst($item->channel) }}</td>
                        <td>{{ ucfirst($item->status) }}</td>
                        <td>{{ $item->messages_count }}</td>
                        <td>{{ $item->created_at?->format('Y-m-d H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-muted">No conversations yet.</td></tr>
                @endforelse
                </tbody>
            </table></div>
            {{ $items->links() }}
        </div></section>
    @elseif($section === 'conversation')
        <p><a href="{{ route('devniox-ai.admin.conversations.index') }}">&larr; All conversations</a></p>
        <section class="card mb-3"><div class="card-body d-flex flex-wrap justify-content-between gap-3">
            <div><h5 class="card-title mb-1">{{ $conversation->uuid }}</h5><p class="text-muted mb-0">{{ ucfirst($conversation->channel) }} · {{ ucfirst($conversation->status) }}</p></div>
            @if($conversation->status !== 'closed')
                <form action="{{ route('devniox-ai.admin.conversations.close', $conversation->uuid) }}" method="post" data-ai-form data-method="POST">@csrf<button class="btn btn-outline-primary" type="submit">Close conversation</button></form>
            @endif
        </div></section>
        <section class="card"><div class="card-body">
            <h5 class="card-title">Messages</h5>
            @forelse($conversation->messages as $message)
                <article class="border rounded p-3 mb-2">
                    <div class="d-flex justify-content-between text-muted small"><strong>{{ ucfirst($message->direction) }}</strong><span>{{ $message->created_at?->format('Y-m-d H:i') }}</span></div>
                    <p class="mb-0 mt-2 text-break">{{ $message->content }}</p>
                </article>
            @empty
                <p class="text-muted">No messages in this conversation.</p>
            @endforelse
        </div></section>
    @endif
</div>

<script>
(() => {
    const message = document.querySelector('[data-ai-message]');
    const csrf = document.querySelector('input[name="_token"]')?.value || document.querySelector('meta[name="csrf-token"]')?.content || '';

    const notify = (text, success) => {
        message.textContent = text;
            message.className = `dn-message dn-message-${success ? 'success' : 'error'}`;
    };

    document.querySelectorAll('[data-ai-form]').forEach((form) => {
        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            const data = Object.fromEntries(new FormData(form).entries());
            delete data._token;

            if (form.dataset.kind === 'faq') {
                data.keywords = (data.keywords_text || '').split(',').map((word) => word.trim()).filter(Boolean);
                delete data.keywords_text;
                data.is_active = form.querySelector('[name="is_active"]')?.checked ? 1 : 0;
            }
            if (form.dataset.kind === 'knowledge') {
                data.is_active = form.querySelector('[name="is_active"]')?.checked ? 1 : 0;
            }
            if (form.dataset.kind === 'channel') {
                const credentialInputs = form.querySelectorAll('[data-credential-key]');
                if (credentialInputs.length) {
                    data.credentials = {};
                    credentialInputs.forEach((input) => {
                        if (input.value.trim() !== '') {
                            data.credentials[input.dataset.credentialKey] = input.value.trim();
                        }
                    });
                } else {
                    try {
                        data.credentials = JSON.parse(data.credentials_json || '{}');
                    } catch {
                        notify('Credentials must be valid JSON.', false);
                        return;
                    }
                }
                delete data.credentials_json;
                data.enabled = form.querySelector('[name="enabled"]')?.checked ? 1 : 0;
            }
            if (form.dataset.kind === 'channel-test') {
                data.message = 'Devniox AI Automation test message.';
            }
            if (form.dataset.kind === 'ai-settings') {
                data.use_ai_fallback = form.querySelector('[name="use_ai_fallback"]')?.checked ? 1 : 0;
                data.local_first = form.querySelector('[name="local_first"]')?.checked ? 1 : 0;
                data.clear_api_key = form.querySelector('[name="clear_api_key"]')?.checked ? 1 : 0;
            }

            try {
                const response = await fetch(form.action, {
                    method: form.dataset.method || 'POST',
                    headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                    body: JSON.stringify(data),
                });
                const result = await response.json();
                if (!response.ok) throw new Error(result.message || 'Request failed. Check the submitted values.');
                notify(form.dataset.kind === 'channel-test' ? 'Connection test completed.' : 'Saved successfully.', true);
                window.location.reload();
            } catch (error) {
                notify(error.message || 'Could not save changes.', false);
            }
        });
    });
})();
</script>
@endsection
