<template>
  <div class="ai-assistant-wrapper">
    <button
      class="ai-assistant-btn"
      :class="{ 'pulse-animation': isActive, 'bounce-in': !isActive }"
      @click="toggleChat"
      title="AI Assistant"
    >
      <img 
        v-if="!imageError && iconSrc"
        :key="currentPathIndex"
        :src="iconSrc" 
        alt="AI Assistant" 
        class="ai-icon"
        @error="handleImageError"
        @load="handleImageLoad"
      />
      <!-- Fallback SVG if image fails to load -->
      <svg v-else class="ai-icon" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
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
      <span class="ai-glow"></span>
      <span class="ai-pulse-ring"></span>
    </button>
    
    <!-- Floating Chat Window -->
    <Teleport to="body">
      <transition name="chat-slide">
        <div v-if="isOpen" class="ai-chat-container">
          <AIChatWindow @close="closeChat" />
        </div>
      </transition>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import AIChatWindow from './AIChatWindow.vue';

const isOpen = ref(false);
const isActive = ref(false);
const imageError = ref(false);
const currentPathIndex = ref(0);

// Try multiple possible paths for the image
const possiblePaths = [
  '/assets/ai/ai-assistance.png',
  'assets/ai/ai-assistance.png',
  window.location.origin + '/assets/ai/ai-assistance.png',
];

const iconSrc = computed(() => {
  if (imageError.value && currentPathIndex.value < possiblePaths.length - 1) {
    // Try next path
    currentPathIndex.value++;
    imageError.value = false;
    return possiblePaths[currentPathIndex.value];
  }
  if (imageError.value) {
    // All paths failed, return null to show SVG
    return null;
  }
  return possiblePaths[currentPathIndex.value];
});

const handleImageError = () => {
  if (currentPathIndex.value < possiblePaths.length - 1) {
    // Try next path
    currentPathIndex.value++;
    imageError.value = false;
  } else {
    imageError.value = true;
  }
};

const handleImageLoad = () => {
  // Image loaded successfully
  imageError.value = false;
};

const toggleChat = () => {
  isOpen.value = !isOpen.value;
  isActive.value = isOpen.value;
};

const closeChat = () => {
  isOpen.value = false;
  isActive.value = false;
};

// Close chat on outside click
const handleClickOutside = (event) => {
  const chatContainer = document.querySelector('.ai-chat-container');
  const aiButton = document.querySelector('.ai-assistant-btn');
  
  if (isOpen.value && chatContainer && !chatContainer.contains(event.target) && !aiButton?.contains(event.target)) {
    closeChat();
  }
};

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>

<style scoped>
.ai-assistant-wrapper {
  position: fixed;
  bottom: 32px;
  right: 32px;
  z-index: 999;
}

.ai-assistant-btn {
  width: 72px;
  height: 72px;
  border-radius: 50%;
  border: 3px solid rgba(255, 255, 255, 0.2);
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  position: relative;
  box-shadow: 
    0 8px 32px rgba(102, 126, 234, 0.4),
    0 0 0 0 rgba(102, 126, 234, 0.4),
    inset 0 2px 4px rgba(255, 255, 255, 0.2);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  overflow: visible;
  backdrop-filter: blur(10px);
}

.ai-assistant-btn:hover {
  transform: translateY(-6px) scale(1.08);
  box-shadow: 
    0 16px 48px rgba(102, 126, 234, 0.5),
    0 0 0 4px rgba(102, 126, 234, 0.2),
    inset 0 2px 4px rgba(255, 255, 255, 0.3);
  border-color: rgba(255, 255, 255, 0.4);
}

.ai-assistant-btn:active {
  transform: translateY(-3px) scale(1.04);
}

.ai-icon {
  width: 42px;
  height: 42px;
  object-fit: contain;
  z-index: 2;
  filter: drop-shadow(0 2px 8px rgba(0, 0, 0, 0.3));
  transition: transform 0.3s ease;
  animation: float 3s ease-in-out infinite;
}

.ai-assistant-btn:hover .ai-icon {
  animation: none;
  transform: scale(1.1) rotate(5deg);
}

@keyframes float {
  0%, 100% {
    transform: translateY(0px) rotate(0deg);
  }
  25% {
    transform: translateY(-4px) rotate(-5deg);
  }
  50% {
    transform: translateY(-6px) rotate(0deg);
  }
  75% {
    transform: translateY(-4px) rotate(5deg);
  }
}

.ai-glow {
  position: absolute;
  width: 100%;
  height: 100%;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(255, 255, 255, 0.5) 0%, transparent 70%);
  animation: glow-pulse 2s ease-in-out infinite;
  pointer-events: none;
}

.ai-pulse-ring {
  position: absolute;
  width: 100%;
  height: 100%;
  border-radius: 50%;
  border: 2px solid rgba(102, 126, 234, 0.6);
  animation: pulse-ring 2s ease-out infinite;
  pointer-events: none;
}

@keyframes glow-pulse {
  0%, 100% {
    opacity: 0.7;
    transform: scale(1);
  }
  50% {
    opacity: 1;
    transform: scale(1.15);
  }
}

@keyframes pulse-ring {
  0% {
    transform: scale(1);
    opacity: 0.8;
  }
  100% {
    transform: scale(1.6);
    opacity: 0;
  }
}

.pulse-animation {
  animation: pulse-glow 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse-glow {
  0%, 100% {
    box-shadow: 
      0 8px 32px rgba(102, 126, 234, 0.4),
      0 0 0 0 rgba(102, 126, 234, 0.7),
      inset 0 2px 4px rgba(255, 255, 255, 0.2);
  }
  50% {
    box-shadow: 
      0 12px 40px rgba(102, 126, 234, 0.6),
      0 0 0 16px rgba(102, 126, 234, 0),
      inset 0 2px 4px rgba(255, 255, 255, 0.3);
  }
}

.bounce-in {
  animation: bounceIn 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

@keyframes bounceIn {
  0% {
    transform: scale(0);
    opacity: 0;
  }
  50% {
    transform: scale(1.1);
  }
  100% {
    transform: scale(1);
    opacity: 1;
  }
}

.ai-chat-container {
  position: fixed;
  bottom: 100px;
  right: 24px;
  width: 420px;
  max-width: calc(100vw - 48px);
  height: 600px;
  max-height: calc(100vh - 140px);
  z-index: 1000;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  border-radius: 16px;
  overflow: hidden;
  animation: chatAppear 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

@keyframes chatAppear {
  from {
    opacity: 0;
    transform: translateY(20px) scale(0.95);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

/* Chat Slide Animation */
.chat-slide-enter-active,
.chat-slide-leave-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.chat-slide-enter-from {
  opacity: 0;
  transform: translateY(20px) scale(0.95);
}

.chat-slide-leave-to {
  opacity: 0;
  transform: translateY(20px) scale(0.95);
}

/* Responsive */
@media (max-width: 768px) {
  .ai-assistant-wrapper {
    bottom: 24px;
    right: 24px;
  }
  
  .ai-assistant-btn {
    width: 64px;
    height: 64px;
    border-width: 2px;
  }
  
  .ai-icon {
    width: 36px;
    height: 36px;
  }
  
  .ai-chat-container {
    bottom: 80px;
    right: 12px;
    left: 12px;
    width: auto;
    height: calc(100vh - 100px);
    max-height: calc(100vh - 100px);
  }
}

@media (max-width: 480px) {
  .ai-assistant-wrapper {
    bottom: 20px;
    right: 20px;
  }
  
  .ai-assistant-btn {
    width: 60px;
    height: 60px;
    border-width: 2px;
  }
  
  .ai-icon {
    width: 32px;
    height: 32px;
  }
  
  .ai-chat-container {
    bottom: 70px;
    right: 8px;
    left: 8px;
    height: calc(100vh - 90px);
    max-height: calc(100vh - 90px);
  }
}
</style>


