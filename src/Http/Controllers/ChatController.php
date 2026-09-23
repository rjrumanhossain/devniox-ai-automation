<?php

declare(strict_types=1);

namespace Devniox\AiAutomation\Http\Controllers;

use Devniox\AiAutomation\Services\ChatbotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    public function __construct(protected ChatbotService $chatbotService)
    {
    }

    public function send(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'message' => ['required', 'string', 'min:1', 'max:1000'],
            'conversation_id' => ['nullable', 'string', 'max:120'],
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
        if (RateLimiter::tooManyAttempts($key, 30)) {
            return response()->json([
                'success' => false,
                'message' => 'Too many chat attempts. Please wait a moment and try again.',
            ], 429);
        }

        RateLimiter::hit($key, 60);

        $result = $this->chatbotService->handle($message, [
            'conversation_id' => $validated['conversation_id'] ?? null,
            'customer_name' => $validated['customer_name'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'ip' => $request->ip(),
        ]);

        $status = $result['success'] ? 200 : 500;

        return response()->json($result, $status);
    }

    public function show(Request $request, string $conversation): JsonResponse
    {
        return response()->json([
            'conversation' => $conversation,
            'messages' => [],
            'success' => true,
        ]);
    }

    public function widgetScript(): \Illuminate\Http\Response
    {
        $js = <<<'JS'
(function() {
  if (window.DevnioxAiWidget) {
    return;
  }

  const widget = {
    config: {
      title: 'Devniox AI Assistant',
      icon: '💬',
      position: 'bottom-right',
      endpoint: '/devniox-ai/chat'
    },
    createWidget: function() {
      const wrapper = document.createElement('div');
      wrapper.className = 'devniox-ai-widget';
      wrapper.innerHTML = `
        <button class="devniox-ai-toggle" type="button" aria-label="Open chat" title="Open chat">
          <span class="devniox-ai-icon">💬</span>
        </button>
        <div class="devniox-ai-window" style="display:none;">
          <div class="devniox-ai-header">
            <span>Devniox AI</span>
            <button type="button" class="devniox-ai-close" aria-label="Close chat">×</button>
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
          font-size: 24px;
          cursor: pointer;
          box-shadow: 0 12px 30px rgba(15, 23, 42, 0.26);
        }
        .devniox-ai-window {
          width: min(360px, calc(100vw - 30px));
          background: #fff;
          border: 1px solid rgba(15, 23, 42, 0.08);
          border-radius: 18px;
          overflow: hidden;
          box-shadow: 0 18px 50px rgba(15, 23, 42, 0.18);
          margin-bottom: 12px;
        }
        .devniox-ai-header {
          display: flex;
          justify-content: space-between;
          align-items: center;
          padding: 12px 16px;
          background: linear-gradient(135deg, #111827, #0f172a);
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
          border-radius: 12px;
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
        .devniox-ai-input-box {
          display: flex;
          border-top: 1px solid #e2e8f0;
          background: #fff;
          padding: 12px;
          gap: 8px;
        }
        .devniox-ai-input {
          flex: 1;
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

      const appendMessage = function(role, text) {
        const item = document.createElement('div');
        item.className = 'devniox-ai-message ' + role;
        item.textContent = text;
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

        fetch('/devniox-ai/chat', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
          },
          body: JSON.stringify({ message: text })
        }).then(function(response) {
          return response.json();
        }).then(function(data) {
          status.remove();
          if (data && data.message) {
            appendMessage('ai', data.message);
          }
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
  widget.createWidget();
})();
JS;

        return response($js, 200, ['Content-Type' => 'application/javascript']);
    }

    public function widgetCss(): \Illuminate\Http\Response
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
