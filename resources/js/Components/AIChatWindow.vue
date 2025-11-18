<template>
  <div class="ai-chat-window">
    <!-- Header -->
    <div class="chat-header">
      <div class="header-left">
        <div class="ai-avatar">
          <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" class="robot-icon-svg">
            <rect x="16" y="12" width="32" height="28" rx="4" fill="white" opacity="0.95"/>
            <rect x="18" y="14" width="28" height="24" rx="2" fill="white"/>
            <circle cx="24" cy="24" r="3" fill="#667eea"/>
            <circle cx="40" cy="24" r="3" fill="#667eea"/>
            <circle cx="24" cy="24" r="1.5" fill="white"/>
            <circle cx="40" cy="24" r="1.5" fill="white"/>
            <rect x="28" y="30" width="8" height="4" rx="2" fill="#667eea"/>
            <circle cx="32" cy="8" r="2" fill="white"/>
            <line x1="32" y1="8" x2="32" y2="12" stroke="white" stroke-width="2" stroke-linecap="round"/>
            <line x1="20" y1="20" x2="44" y2="20" stroke="#667eea" stroke-width="1" opacity="0.3"/>
            <line x1="20" y1="28" x2="44" y2="28" stroke="#667eea" stroke-width="1" opacity="0.3"/>
          </svg>
        </div>
        <div class="header-info">
          <div class="header-title">AI Assistant</div>
          <div class="header-status">
            <span class="status-dot" :class="{ 'online': isConnected }"></span>
            <span>{{ isConnected ? 'Online' : 'Connecting...' }}</span>
          </div>
        </div>
      </div>
      <a-button type="text" class="close-btn" @click="$emit('close')">
        <template #icon><CloseOutlined /></template>
      </a-button>
    </div>

    <!-- Messages Container -->
    <div class="chat-messages" ref="messagesContainer">
      <div v-if="messages.length === 0" class="welcome-message">
        <div class="welcome-icon">
          <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" class="robot-icon-large">
            <rect x="16" y="12" width="32" height="28" rx="4" fill="#667eea" opacity="0.1"/>
            <rect x="18" y="14" width="28" height="24" rx="2" fill="white"/>
            <circle cx="24" cy="24" r="3" fill="#667eea"/>
            <circle cx="40" cy="24" r="3" fill="#667eea"/>
            <circle cx="24" cy="24" r="1.5" fill="white"/>
            <circle cx="40" cy="24" r="1.5" fill="white"/>
            <rect x="28" y="30" width="8" height="4" rx="2" fill="#667eea"/>
            <circle cx="32" cy="8" r="2" fill="#667eea"/>
            <line x1="32" y1="8" x2="32" y2="12" stroke="#667eea" stroke-width="2" stroke-linecap="round"/>
            <line x1="20" y1="20" x2="44" y2="20" stroke="#667eea" stroke-width="1" opacity="0.3"/>
            <line x1="20" y1="28" x2="44" y2="28" stroke="#667eea" stroke-width="1" opacity="0.3"/>
          </svg>
        </div>
        <h3>Hello! I'm your AI Assistant</h3>
        <p>I can help you manage your events. Try saying:</p>
        <div class="suggestions">
          <div class="suggestion-chip" @click="sendSuggestion('Create a new event')">
            "Create a new event"
          </div>
          <div class="suggestion-chip" @click="sendSuggestion('Show me recent events')">
            "Show me recent events"
          </div>
          <div class="suggestion-chip" @click="sendSuggestion('What are my statistics?')">
            "What are my statistics?"
          </div>
        </div>
      </div>

      <div
        v-for="(message, index) in messages"
        :key="index"
        class="message"
        :class="{ 'user-message': message.role === 'user', 'ai-message': message.role === 'assistant' }"
      >
        <div class="message-avatar" v-if="message.role === 'assistant'">
          <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" class="robot-icon-small">
            <rect x="16" y="12" width="32" height="28" rx="4" fill="white" opacity="0.95"/>
            <rect x="18" y="14" width="28" height="24" rx="2" fill="white"/>
            <circle cx="24" cy="24" r="3" fill="white"/>
            <circle cx="40" cy="24" r="3" fill="white"/>
            <circle cx="24" cy="24" r="1.5" fill="#667eea"/>
            <circle cx="40" cy="24" r="1.5" fill="#667eea"/>
            <rect x="28" y="30" width="8" height="4" rx="2" fill="white"/>
            <circle cx="32" cy="8" r="2" fill="white"/>
            <line x1="32" y1="8" x2="32" y2="12" stroke="white" stroke-width="2" stroke-linecap="round"/>
          </svg>
        </div>
        <div class="message-content">
          <div class="message-text" v-html="formatMessage(message.content)"></div>
          <div class="message-time">{{ formatTime(message.timestamp) }}</div>
        </div>
        <div class="message-avatar user-avatar" v-if="message.role === 'user'">
          <UserOutlined />
        </div>
      </div>

      <div v-if="isLoading" class="message ai-message">
        <div class="message-avatar">
          <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" class="robot-icon-small">
            <rect x="16" y="12" width="32" height="28" rx="4" fill="white" opacity="0.95"/>
            <rect x="18" y="14" width="28" height="24" rx="2" fill="white"/>
            <circle cx="24" cy="24" r="3" fill="white"/>
            <circle cx="40" cy="24" r="3" fill="white"/>
            <circle cx="24" cy="24" r="1.5" fill="#667eea"/>
            <circle cx="40" cy="24" r="1.5" fill="#667eea"/>
            <rect x="28" y="30" width="8" height="4" rx="2" fill="white"/>
            <circle cx="32" cy="8" r="2" fill="white"/>
            <line x1="32" y1="8" x2="32" y2="12" stroke="white" stroke-width="2" stroke-linecap="round"/>
          </svg>
        </div>
        <div class="message-content">
          <div class="typing-indicator">
            <span></span>
            <span></span>
            <span></span>
          </div>
        </div>
      </div>
    </div>

    <!-- Input Area - ChatGPT Style -->
    <div class="chat-input-area">
      <div class="chat-input-wrapper">
        <div class="input-container">
          <textarea
            v-model="inputMessage"
            placeholder="Message AI Assistant..."
            class="chat-input-textarea"
            @keydown.enter.exact.prevent="handleEnterKey"
            @keydown.shift.enter.exact="handleShiftEnter"
            :disabled="isLoading"
            rows="1"
            ref="textareaRef"
          ></textarea>
          <button
            class="send-button"
            :class="{ 'active': inputMessage.trim() && !isLoading, 'loading': isLoading }"
            :disabled="!inputMessage.trim() || isLoading"
            @click="sendMessage"
            type="button"
          >
            <SendOutlined v-if="!isLoading" class="send-icon" />
            <span v-else class="loading-spinner"></span>
          </button>
        </div>
        <div class="input-footer">
          <span class="input-hint">AI can make mistakes. Check important info.</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, nextTick, onMounted, watch, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import {
  CloseOutlined,
  UserOutlined,
  SendOutlined,
} from '@ant-design/icons-vue';

const emit = defineEmits(['close']);

const page = usePage();
const messages = ref([]);
const inputMessage = ref('');
const isLoading = ref(false);
const isConnected = ref(true);
const messagesContainer = ref(null);
const textareaRef = ref(null);

const userId = computed(() => page.props.auth?.user?.id || null);

// Auto-resize textarea
const adjustTextareaHeight = () => {
  nextTick(() => {
    if (textareaRef.value) {
      textareaRef.value.style.height = 'auto';
      const scrollHeight = textareaRef.value.scrollHeight;
      const maxHeight = 200; // Max 200px
      textareaRef.value.style.height = Math.min(scrollHeight, maxHeight) + 'px';
    }
  });
};

const handleEnterKey = () => {
  if (!isLoading.value && inputMessage.value.trim()) {
    sendMessage();
  }
};

const handleShiftEnter = () => {
  // Allow new line with Shift+Enter
  if (textareaRef.value) {
    const start = textareaRef.value.selectionStart;
    const end = textareaRef.value.selectionEnd;
    inputMessage.value = inputMessage.value.substring(0, start) + '\n' + inputMessage.value.substring(end);
    nextTick(() => {
      textareaRef.value.selectionStart = textareaRef.value.selectionEnd = start + 1;
      adjustTextareaHeight();
    });
  }
};

watch(inputMessage, () => {
  adjustTextareaHeight();
});

// Initialize with welcome message
onMounted(() => {
  addWelcomeMessage();
  adjustTextareaHeight();
});

const addWelcomeMessage = () => {
  messages.value.push({
    role: 'assistant',
    content: "Hello! I'm your AI Assistant. I can help you manage events, view statistics, and navigate your dashboard. How can I assist you today?",
    timestamp: new Date(),
  });
  scrollToBottom();
};

const sendMessage = async () => {
  if (!inputMessage.value.trim() || isLoading.value) return;

  const userMessage = inputMessage.value.trim();
  inputMessage.value = '';

  // Add user message
  messages.value.push({
    role: 'user',
    content: userMessage,
    timestamp: new Date(),
  });

  scrollToBottom();
  isLoading.value = true;

  try {
    const response = await fetch('/api/ai/chat', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
      },
      body: JSON.stringify({
        message: userMessage,
        context: {
          current_route: window.location.pathname,
          user_id: userId.value,
        },
      }),
    });

    const data = await response.json();

    if (data.success) {
      messages.value.push({
        role: 'assistant',
        content: data.response,
        timestamp: new Date(),
      });

      // Handle actions if any
      if (data.action) {
        handleAction(data.action);
      }
    } else {
      messages.value.push({
        role: 'assistant',
        content: 'Sorry, I encountered an error. Please try again.',
        timestamp: new Date(),
      });
    }
  } catch (error) {
    console.error('AI Chat Error:', error);
    messages.value.push({
      role: 'assistant',
      content: 'Sorry, I\'m having trouble connecting. Please check your connection and try again.',
      timestamp: new Date(),
    });
  } finally {
    isLoading.value = false;
    scrollToBottom();
  }
};

const sendSuggestion = (suggestion) => {
  inputMessage.value = suggestion;
  sendMessage();
};

const handleAction = (action) => {
  switch (action.type) {
    case 'navigate':
      if (action.url) {
        router.visit(action.url);
      }
      break;
    case 'create_event':
      router.visit('/dashboard/events/create');
      break;
    case 'show_events':
      router.visit('/dashboard/events');
      break;
    case 'show_statistics':
      router.visit('/dashboard');
      break;
    default:
      break;
  }
};

const formatMessage = (content) => {
  // Simple markdown-like formatting
  return content
    .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
    .replace(/\*(.*?)\*/g, '<em>$1</em>')
    .replace(/`(.*?)`/g, '<code>$1</code>')
    .replace(/\n/g, '<br>');
};

const formatTime = (timestamp) => {
  const date = new Date(timestamp);
  const now = new Date();
  const diff = now - date;

  if (diff < 60000) return 'Just now';
  if (diff < 3600000) return `${Math.floor(diff / 60000)}m ago`;
  if (diff < 86400000) return `${Math.floor(diff / 3600000)}h ago`;
  return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
};

const scrollToBottom = () => {
  nextTick(() => {
    if (messagesContainer.value) {
      messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
    }
  });
};

watch(messages, () => {
  scrollToBottom();
}, { deep: true });
</script>

<style scoped>
.ai-chat-window {
  display: flex;
  flex-direction: column;
  height: 100%;
  background: var(--bg-primary, #ffffff);
  border-radius: 16px;
  overflow: hidden;
}

[data-theme="dark"] .ai-chat-window {
  background: #1f1f1f;
}

/* Header */
.chat-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 20px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.header-left {
  display: flex;
  align-items: center;
  gap: 12px;
}

.ai-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.2);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
}

.header-info {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.header-title {
  font-weight: 600;
  font-size: 16px;
}

.header-status {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
  opacity: 0.9;
}

.status-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.5);
}

.status-dot.online {
  background: #52c41a;
  box-shadow: 0 0 8px rgba(82, 196, 26, 0.6);
}

.close-btn {
  color: white !important;
}

.close-btn:hover {
  background: rgba(255, 255, 255, 0.1) !important;
}

/* Messages */
.chat-messages {
  flex: 1;
  overflow-y: auto;
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 16px;
  background: var(--bg-secondary, #f5f5f5);
}

[data-theme="dark"] .chat-messages {
  background: #141414;
}

.welcome-message {
  text-align: center;
  padding: 40px 20px;
  color: var(--text-secondary, #8c8c8c);
}

.welcome-icon {
  margin-bottom: 16px;
}

.robot-icon-large {
  width: 64px;
  height: 64px;
}

.robot-icon-svg {
  width: 100%;
  height: 100%;
}

.robot-icon-small {
  width: 100%;
  height: 100%;
}

.welcome-message h3 {
  color: var(--text-primary, #262626);
  margin-bottom: 8px;
  font-size: 18px;
}

[data-theme="dark"] .welcome-message h3 {
  color: rgba(255, 255, 255, 0.85);
}

.welcome-message p {
  margin-bottom: 16px;
  font-size: 14px;
}

.suggestions {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-top: 20px;
}

.suggestion-chip {
  padding: 10px 16px;
  background: var(--bg-primary, #ffffff);
  border: 1px solid var(--border-color-light, #e8e8e8);
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
  font-size: 14px;
  color: var(--text-primary, #262626);
}

[data-theme="dark"] .suggestion-chip {
  background: #262626;
  border-color: #2d2d2d;
  color: rgba(255, 255, 255, 0.85);
}

.suggestion-chip:hover {
  background: #667eea;
  color: white;
  border-color: #667eea;
  transform: translateX(4px);
}

.message {
  display: flex;
  gap: 12px;
  align-items: flex-start;
  animation: messageSlide 0.3s ease-out;
}

@keyframes messageSlide {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.user-message {
  flex-direction: row-reverse;
}

.message-avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  font-size: 16px;
}

.message-avatar:not(.user-avatar) {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.user-avatar {
  background: #1890ff;
  color: white;
}

.message-content {
  max-width: 75%;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.user-message .message-content {
  align-items: flex-end;
}

.message-text {
  padding: 10px 14px;
  border-radius: 12px;
  font-size: 14px;
  line-height: 1.5;
  word-wrap: break-word;
}

.ai-message .message-text {
  background: var(--bg-primary, #ffffff);
  color: var(--text-primary, #262626);
  border: 1px solid var(--border-color-light, #e8e8e8);
}

[data-theme="dark"] .ai-message .message-text {
  background: #262626;
  color: rgba(255, 255, 255, 0.85);
  border-color: #2d2d2d;
}

.user-message .message-text {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.message-text :deep(code) {
  background: rgba(0, 0, 0, 0.1);
  padding: 2px 6px;
  border-radius: 4px;
  font-family: 'Courier New', monospace;
  font-size: 12px;
}

.message-time {
  font-size: 11px;
  color: var(--text-tertiary, #8c8c8c);
  padding: 0 4px;
}

.typing-indicator {
  display: flex;
  gap: 4px;
  padding: 10px 14px;
}

.typing-indicator span {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: var(--text-tertiary, #8c8c8c);
  animation: typing 1.4s infinite;
}

.typing-indicator span:nth-child(2) {
  animation-delay: 0.2s;
}

.typing-indicator span:nth-child(3) {
  animation-delay: 0.4s;
}

@keyframes typing {
  0%, 60%, 100% {
    transform: translateY(0);
    opacity: 0.7;
  }
  30% {
    transform: translateY(-10px);
    opacity: 1;
  }
}

/* Input Area - ChatGPT Style */
.chat-input-area {
  padding: 0;
  background: var(--bg-primary, #ffffff);
  border-top: 1px solid var(--border-color-light, #e8e8e8);
}

[data-theme="dark"] .chat-input-area {
  background: #1f1f1f;
  border-top-color: #2d2d2d;
}

.chat-input-wrapper {
  padding: 12px 16px;
}

.input-container {
  display: flex;
  align-items: flex-end;
  gap: 8px;
  background: var(--bg-secondary, #f5f5f5);
  border: 1px solid var(--border-color-light, #e8e8e8);
  border-radius: 24px;
  padding: 8px 12px;
  transition: all 0.2s;
  min-height: 52px;
}

[data-theme="dark"] .input-container {
  background: #262626;
  border-color: #2d2d2d;
}

.input-container:focus-within {
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.chat-input-textarea {
  flex: 1;
  border: none;
  outline: none;
  background: transparent;
  resize: none;
  font-size: 15px;
  line-height: 1.5;
  color: var(--text-primary, #262626);
  font-family: inherit;
  padding: 8px 4px;
  max-height: 200px;
  overflow-y: auto;
  min-height: 20px;
}

[data-theme="dark"] .chat-input-textarea {
  color: rgba(255, 255, 255, 0.85);
}

.chat-input-textarea::placeholder {
  color: var(--text-tertiary, #8c8c8c);
}

.chat-input-textarea:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.send-button {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  border: none;
  background: var(--bg-tertiary, #e8e8e8);
  color: var(--text-tertiary, #8c8c8c);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: not-allowed;
  transition: all 0.2s;
  flex-shrink: 0;
  padding: 0;
}

[data-theme="dark"] .send-button {
  background: #2d2d2d;
  color: rgba(255, 255, 255, 0.4);
}

.send-button.active {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  cursor: pointer;
  box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
}

.send-button.active:hover {
  transform: scale(1.05);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.send-button.active:active {
  transform: scale(0.95);
}

.send-button.loading {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  cursor: not-allowed;
}

.send-icon {
  font-size: 16px;
}

.loading-spinner {
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.input-footer {
  padding: 8px 12px 0;
  text-align: center;
}

.input-hint {
  font-size: 11px;
  color: var(--text-tertiary, #8c8c8c);
  line-height: 1.4;
}

[data-theme="dark"] .input-hint {
  color: rgba(255, 255, 255, 0.4);
}

/* Scrollbar */
.chat-messages::-webkit-scrollbar {
  width: 6px;
}

.chat-messages::-webkit-scrollbar-track {
  background: transparent;
}

.chat-messages::-webkit-scrollbar-thumb {
  background: var(--border-color-light, #d9d9d9);
  border-radius: 3px;
}

[data-theme="dark"] .chat-messages::-webkit-scrollbar-thumb {
  background: #2d2d2d;
}

.chat-messages::-webkit-scrollbar-thumb:hover {
  background: var(--text-tertiary, #8c8c8c);
}
</style>

