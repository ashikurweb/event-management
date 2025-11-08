<template>
  <Link
    :href="href"
    :class="[
      'loading-link',
      loading ? 'loading-link-active' : '',
      customClass
    ]"
    @click="handleClick"
  >
    <span v-if="loading" class="loading-link-spinner">
      <a-spin :size="spinnerSize" />
    </span>
    <span class="loading-link-content" :class="{ 'loading-link-content-hidden': loading }">
      <slot />
    </span>
  </Link>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({
  href: {
    type: String,
    required: true,
  },
  customClass: {
    type: String,
    default: '',
  },
  spinnerSize: {
    type: String,
    default: 'small',
    validator: (value) => ['small', 'default', 'large'].includes(value),
  },
});

const loading = ref(false);
let finishUnwatch = null;

// Watch for navigation start/end
const handleClick = () => {
  loading.value = true;
  
  // Clean up previous listener if exists
  if (finishUnwatch) {
    finishUnwatch();
  }
  
  // Listen for navigation completion
  finishUnwatch = router.on('finish', () => {
    loading.value = false;
    if (finishUnwatch) {
      finishUnwatch();
      finishUnwatch = null;
    }
  });
};

onUnmounted(() => {
  if (finishUnwatch) {
    finishUnwatch();
  }
});
</script>

<style scoped>
.loading-link {
  position: relative;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.loading-link-content {
  display: inline-flex;
  align-items: center;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.loading-link-content-hidden {
  opacity: 0.4;
  transform: scale(0.95);
}

.loading-link-spinner {
  display: inline-flex;
  align-items: center;
  margin-right: 4px;
  position: absolute;
  left: 50%;
  transform: translateX(-50%);
  filter: drop-shadow(0 0 8px rgba(102, 126, 234, 0.6));
}

.loading-link-spinner :deep(.ant-spin-dot) {
  font-size: 18px;
}

.loading-link-spinner :deep(.ant-spin-dot-item) {
  background: linear-gradient(135deg, #667eea, #764ba2, #f093fb, #4facfe);
  background-size: 400% 400%;
  animation: gradient-rotate 2s ease infinite;
  box-shadow: 
    0 0 10px rgba(102, 126, 234, 0.6),
    0 0 20px rgba(118, 75, 162, 0.4),
    inset 0 1px 0 rgba(255, 255, 255, 0.3);
}

.loading-link-active {
  opacity: 0.7;
  pointer-events: none;
  cursor: wait;
  filter: brightness(1.1);
}

@keyframes gradient-rotate {
  0%, 100% {
    background-position: 0% 50%;
  }
  50% {
    background-position: 100% 50%;
  }
}

/* Dark theme spinner */
[data-theme="dark"] .loading-link-spinner {
  filter: drop-shadow(0 0 12px rgba(129, 140, 248, 0.8));
}

[data-theme="dark"] .loading-link-spinner :deep(.ant-spin-dot-item) {
  background: linear-gradient(135deg, #818cf8, #a78bfa, #c084fc, #60a5fa);
  background-size: 400% 400%;
  box-shadow: 
    0 0 15px rgba(129, 140, 248, 0.7),
    0 0 30px rgba(167, 139, 250, 0.5),
    inset 0 1px 0 rgba(255, 255, 255, 0.4);
}
</style>

