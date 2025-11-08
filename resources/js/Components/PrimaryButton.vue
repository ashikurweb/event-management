<template>
  <div 
    class="rainbow relative z-0 overflow-hidden transition duration-300" 
    :class="[
      block ? 'w-full' : '',
      size === 'large' ? 'p-0.5' : size === 'small' ? 'p-0.5' : 'p-0.5',
      disabled || loading ? 'pointer-events-none opacity-60' : ''
    ]"
    style="border-radius: 9999px;"
  >
    <button
      :type="htmlType"
      :disabled="disabled || loading"
      :class="[
        'rainbow-button',
        size === 'large' ? 'px-8 py-3 text-sm' : size === 'small' ? 'px-6 py-2 text-xs' : 'px-7 py-2.5 text-sm',
        block ? 'w-full' : ''
      ]"
      style="border-radius: 9999px;"
      @click="handleClick"
    >
      <span v-if="loading" class="flex items-center justify-center gap-2">
        <span class="loading-spinner"></span>
        <span>{{ loadingText || 'Loading...' }}</span>
      </span>
      <span v-else class="flex items-center justify-center gap-2">
        <slot name="icon"></slot>
        <slot>{{ text }}</slot>
      </span>
    </button>
  </div>
</template>

<script setup>
const props = defineProps({
  text: {
    type: String,
    default: 'Click Me'
  },
  htmlType: {
    type: String,
    default: 'button'
  },
  loading: {
    type: Boolean,
    default: false
  },
  loadingText: {
    type: String,
    default: 'Loading...'
  },
  disabled: {
    type: Boolean,
    default: false
  },
  block: {
    type: Boolean,
    default: false
  },
  size: {
    type: String,
    default: 'default',
    validator: (value) => ['small', 'default', 'large'].includes(value)
  }
});

const emit = defineEmits(['click']);

const handleClick = (event) => {
  if (!props.disabled && !props.loading) {
    emit('click', event);
  }
};
</script>

<style scoped>
@keyframes rotate {
  100% {
    transform: rotate(1turn);
  }
}

.rainbow {
  position: relative;
  z-index: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  flex: 0 0 auto;
  background: transparent;
  padding: 0;
}

/* Dark theme - show glass effect */
[data-theme="dark"] .rainbow {
  background: rgba(255, 255, 255, 0.1);
  padding: 2px;
}

.rainbow::before {
  content: '';
  position: absolute;
  z-index: -2;
  left: -50%;
  top: -50%;
  width: 200%;
  height: 200%;
  background-position: 100% 50%;
  background-repeat: no-repeat;
  background-size: 50% 30%;
  filter: blur(8px);
  background-image: linear-gradient(rgba(255, 255, 255, 0.9));
  animation: rotate 4s linear infinite;
  display: none;
}

/* Dark theme - show rotating border */
[data-theme="dark"] .rainbow::before {
  display: block;
}

.rainbow-button {
  position: relative;
  z-index: 1;
  width: 100%;
  color: white;
  font-weight: 500;
  border: none;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* Dark theme button */
[data-theme="dark"] .rainbow-button {
  background: rgba(17, 24, 39, 0.85);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5);
}

.rainbow-button:hover:not(:disabled) {
  background: linear-gradient(135deg, #7c8ef0 0%, #8a5fb8 100%);
  box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

[data-theme="dark"] .rainbow-button:hover:not(:disabled) {
  background: rgba(17, 24, 39, 0.95);
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.5);
}

.rainbow-button:disabled {
  cursor: not-allowed;
  opacity: 0.6;
}

.loading-spinner {
  width: 18px;
  height: 18px;
  border-radius: 50%;
  background: conic-gradient(
    from 0deg,
    rgba(255, 255, 255, 0.4),
    rgba(255, 255, 255, 0.7),
    rgba(255, 255, 255, 1),
    rgba(255, 255, 255, 0.7),
    rgba(255, 255, 255, 0.4)
  );
  mask: radial-gradient(farthest-side, transparent calc(100% - 2.5px), #fff 0);
  -webkit-mask: radial-gradient(farthest-side, transparent calc(100% - 2.5px), #fff 0);
  animation: spin 0.8s linear infinite;
  box-shadow: 
    0 0 6px rgba(255, 255, 255, 0.6),
    0 0 12px rgba(255, 255, 255, 0.4),
    inset 0 0 4px rgba(255, 255, 255, 0.5);
  filter: drop-shadow(0 0 2px rgba(255, 255, 255, 0.8));
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}
</style>

