<?php

declare(strict_types=1);

namespace Devniox\AiAutomation\Http\Controllers;

use Devniox\AiAutomation\Models\Conversation;
use Devniox\AiAutomation\Services\ChatbotService;
use Devniox\AiAutomation\Support\TenantResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    public function __construct(protected ChatbotService $chatbotService) {}

    public function send(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'message' => ['required', 'string', 'min:1', 'max:1000'],
            'conversation_id' => ['nullable', 'string', 'max:120'],
            'business_key' => ['nullable', 'string', 'max:120'],
            'customer_id' => ['nullable', 'string', 'max:120'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()->all(),
            ], 422);
        }

        $validated = $validator->validated();
        $message = trim((string) ($validated['message'] ?? ''));

        if ($message === '') {
            return response()->json([
                'success' => false,
                'message' => 'Message cannot be empty.',
            ], 422);
        }

        $key = 'devniox-ai-chat:'.($request->ip() ?? 'guest');
        $limit = (int) config('devniox-ai.website.rate_limit', 60);

        if (RateLimiter::tooManyAttempts($key, $limit)) {
            return response()->json([
                'success' => false,
                'message' => 'Too many chat attempts. Please wait a moment and try again.',
            ], 429);
        }

        RateLimiter::hit($key, 60);

        $result = $this->chatbotService->handle($message, [
            'request' => $request,
            'channel' => 'website',
            'conversation_id' => $validated['conversation_id'] ?? null,
            'business_key' => $validated['business_key'] ?? null,
            'customer_id' => $validated['customer_id'] ?? null,
            'customer_name' => $validated['customer_name'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'ip' => $request->ip(),
        ]);

        return response()->json($result, $result['success'] ? 200 : 500);
    }

    public function show(Request $request, string $conversation): JsonResponse
    {
        $scope = TenantResolver::resolve($request, $request->all());
        $query = Conversation::query()->where('uuid', $conversation);
        TenantResolver::applyScope($query, $scope);

        $record = $query->first();

        return response()->json([
            'conversation' => $conversation,
            'messages' => $record
                ? $record->messages()->oldest('id')->get(['direction', 'content', 'status', 'metadata', 'created_at'])
                : [],
            'success' => (bool) $record,
        ], $record ? 200 : 404);
    }

    public function widgetScript(): Response
    {
        $js = <<<'JS'
(function() {
  if (window.DevnioxAiWidget) {
    return;
  }

  const widget = {
    config: {
      title: 'Devniox AI Assistant',
      icon: 'AI',
      baseUrl: '/devniox-ai',
      endpoint: null,
      businessKey: null,
      customerId: null,
      conversationId: null
    },
    init: function(config) {
      this.config = Object.assign({}, this.config, config || {});
      this.config.endpoint = this.config.endpoint || (this.config.baseUrl.replace(/\/$/, '') + '/chat');
      this.createWidget();
    },
    createWidget: function() {
      if (document.querySelector('.devniox-ai-widget')) {
        return;
      }

      const wrapper = document.createElement('div');
      wrapper.className = 'devniox-ai-widget';
      wrapper.innerHTML = `
        <button class="devniox-ai-toggle" type="button" aria-label="Open chat" title="Open chat">
          <span class="devniox-ai-icon">${this.config.icon}</span>
        </button>
        <div class="devniox-ai-window" style="display:none;">
          <div class="devniox-ai-header">
            <span>${this.config.title}</span>
            <button type="button" class="devniox-ai-close" aria-label="Close chat">&times;</button>
          </div>
          <div class="devniox-ai-messages"></div>
          <div class="devniox-ai-input-box">
            <input type="text" class="devniox-ai-input" placeholder="Type your message..." maxlength="1000">
            <button type="button" class="devniox-ai-send">Send</button>
          </div>
        </div>
      `;

      const css = document.createElement('style');
      css.textContent = `
        .devniox-ai-widget {
          position: fixed;
          right: 20px;
          bottom: 20px;
          z-index: 999999;
          font-family: Arial, sans-serif;
        }
        .devniox-ai-toggle {
          width: 58px;
          height: 58px;
          border: none;
          border-radius: 50%;
          background: #0f172a;
          color: #fff;
          font-size: 18px;
          font-weight: 700;
          cursor: pointer;
          box-shadow: 0 12px 30px rgba(15, 23, 42, 0.26);
        }
        .devniox-ai-window {
          width: min(360px, calc(100vw - 30px));
          background: #fff;
          border: 1px solid rgba(15, 23, 42, 0.08);
          border-radius: 14px;
          overflow: hidden;
          box-shadow: 0 18px 50px rgba(15, 23, 42, 0.18);
          margin-bottom: 12px;
        }
        .devniox-ai-header {
          display: flex;
          justify-content: space-between;
          align-items: center;
          padding: 12px 16px;
          background: #0f172a;
          color: #fff;
          font-weight: 700;
        }
        .devniox-ai-close {
          background: transparent;
          border: none;
          color: #fff;
          font-size: 24px;
          cursor: pointer;
        }
        .devniox-ai-messages {
          padding: 12px;
          min-height: 180px;
          max-height: 340px;
          overflow-y: auto;
          background: #f8fafc;
        }
        .devniox-ai-message {
          margin-bottom: 10px;
          padding: 10px 12px;
          border-radius: 10px;
          line-height: 1.45;
          font-size: 14px;
          max-width: 85%;
          word-break: break-word;
        }
        .devniox-ai-message.user {
          margin-left: auto;
          background: #0f172a;
          color: #fff;
        }
        .devniox-ai-message.ai {
          margin-right: auto;
          background: #e2e8f0;
          color: #0f172a;
        }
        .devniox-ai-message a {
          color: inherit;
          font-weight: 700;
          text-decoration: underline;
          text-underline-offset: 2px;
        }
        .devniox-ai-input-box {
          display: flex;
          border-top: 1px solid #e2e8f0;
          background: #fff;
          padding: 12px;
          gap: 8px;
        }
        .devniox-ai-input {
          flex: 1;
          min-width: 0;
          padding: 10px 12px;
          border: 1px solid #cbd5e1;
          border-radius: 10px;
          font-size: 14px;
        }
        .devniox-ai-send {
          border: none;
          border-radius: 10px;
          padding: 10px 14px;
          background: #0f172a;
          color: #fff;
          cursor: pointer;
        }
        @media (max-width: 480px) {
          .devniox-ai-widget {
            right: 14px;
            bottom: 14px;
          }
        }
      `;

      document.head.appendChild(css);
      document.body.appendChild(wrapper);

      const toggle = wrapper.querySelector('.devniox-ai-toggle');
      const windowEl = wrapper.querySelector('.devniox-ai-window');
      const close = wrapper.querySelector('.devniox-ai-close');
      const input = wrapper.querySelector('.devniox-ai-input');
      const send = wrapper.querySelector('.devniox-ai-send');
      const messages = wrapper.querySelector('.devniox-ai-messages');

      toggle.addEventListener('click', function() {
        windowEl.style.display = windowEl.style.display === 'none' ? 'block' : 'none';
      });

      close.addEventListener('click', function() {
        windowEl.style.display = 'none';
      });

      const appendTextWithLinks = function(element, text) {
        const pattern = /(https?:\/\/[^\s<>"']+)/g;
        let lastIndex = 0;
        let match;

        while ((match = pattern.exec(text)) !== null) {
          const url = match[0];
          const cleanUrl = url.replace(/[.,!?)]$/, '');
          const trailing = url.slice(cleanUrl.length);

          if (match.index > lastIndex) {
            element.appendChild(document.createTextNode(text.slice(lastIndex, match.index)));
          }

          const anchor = document.createElement('a');
          anchor.href = cleanUrl;
          anchor.textContent = cleanUrl;
          anchor.target = '_blank';
          anchor.rel = 'noopener noreferrer';
          element.appendChild(anchor);

          if (trailing) {
            element.appendChild(document.createTextNode(trailing));
          }

          lastIndex = match.index + url.length;
        }

        if (lastIndex < text.length) {
          element.appendChild(document.createTextNode(text.slice(lastIndex)));
        }
      };

      const appendMessage = function(role, text) {
        const item = document.createElement('div');
        item.className = 'devniox-ai-message ' + role;
        appendTextWithLinks(item, text);
        messages.appendChild(item);
        messages.scrollTop = messages.scrollHeight;
      };

      const sendMessage = function() {
        const text = input.value.trim();
        if (!text) {
          return;
        }

        appendMessage('user', text);
        input.value = '';
        const status = document.createElement('div');
        status.className = 'devniox-ai-message ai';
        status.textContent = 'Thinking...';
        messages.appendChild(status);

        fetch(widget.config.endpoint, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
          },
          body: JSON.stringify({
            message: text,
            business_key: widget.config.businessKey,
            customer_id: widget.config.customerId,
            conversation_id: widget.config.conversationId
          })
        }).then(function(response) {
          return response.json();
        }).then(function(data) {
          status.remove();
          if (data && data.conversation_id) {
            widget.config.conversationId = data.conversation_id;
          }
          appendMessage('ai', data && data.message ? data.message : 'Sorry, I could not answer that right now.');
        }).catch(function() {
          status.textContent = 'Sorry, something went wrong. Please try again.';
        });
      };

      send.addEventListener('click', sendMessage);
      input.addEventListener('keydown', function(event) {
        if (event.key === 'Enter') {
          sendMessage();
        }
      });
    }
  };

  window.DevnioxAiWidget = widget;
  if (window.DevnioxAiAutoInit !== false) {
    widget.init(window.DevnioxAiConfig || {});
  }
})();
JS;

        return response($js, 200, ['Content-Type' => 'application/javascript']);
    }

    public function widgetCss(): Response
    {
        $css = <<<'CSS'
.devniox-ai-widget {
  position: fixed;
  right: 20px;
  bottom: 20px;
  z-index: 999999;
}
CSS;

        return response($css, 200, ['Content-Type' => 'text/css']);
    }
}
