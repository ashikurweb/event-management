<template>
  <a-modal
    v-model:open="visible"
    :title="null"
    :footer="null"
    :width="1000"
    :centered="true"
    :mask-closable="true"
    :keyboard="true"
    :closable="false"
    class="image-gallery-modal"
    @cancel="handleClose"
  >
    <div class="gallery-container">
      <!-- Close Button -->
      <a-button
        type="text"
        class="close-button"
        @click="handleClose"
      >
        <template #icon><CloseOutlined /></template>
      </a-button>

      <!-- Image Counter -->
      <div class="image-counter">
        {{ currentIndex + 1 }} / {{ images.length }}
      </div>

      <!-- Main Image -->
      <div class="image-wrapper" v-if="currentImage">
        <img
          :src="currentImage"
          :alt="`Image ${currentIndex + 1}`"
          class="gallery-image"
          @load="handleImageLoad"
          @error="handleImageError"
        />
        <div v-if="loading" class="image-loading">
          <a-spin size="large" />
        </div>
      </div>

      <!-- Navigation Buttons -->
      <a-button
        v-if="images.length > 1"
        type="text"
        class="nav-button nav-button-prev"
        :disabled="currentIndex === 0"
        @click="goToPrevious"
      >
        <template #icon><LeftOutlined /></template>
      </a-button>

      <a-button
        v-if="images.length > 1"
        type="text"
        class="nav-button nav-button-next"
        :disabled="currentIndex === images.length - 1"
        @click="goToNext"
      >
        <template #icon><RightOutlined /></template>
      </a-button>

      <!-- Thumbnail Strip (if multiple images) -->
      <div v-if="images.length > 1" class="thumbnail-strip">
        <div
          v-for="(image, index) in images"
          :key="index"
          class="thumbnail-item"
          :class="{ active: index === currentIndex }"
          @click="goToImage(index)"
        >
          <img :src="image" :alt="`Thumbnail ${index + 1}`" />
        </div>
      </div>
    </div>
  </a-modal>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import {
  CloseOutlined,
  LeftOutlined,
  RightOutlined,
} from '@ant-design/icons-vue';

const props = defineProps({
  open: {
    type: Boolean,
    default: false,
  },
  images: {
    type: Array,
    default: () => [],
  },
  initialIndex: {
    type: Number,
    default: 0,
  },
});

const emit = defineEmits(['update:open', 'close']);

const visible = computed({
  get: () => props.open,
  set: (value) => emit('update:open', value),
});

const currentIndex = ref(props.initialIndex);
const loading = ref(true);

const currentImage = computed(() => {
  if (props.images.length === 0) return null;
  return props.images[currentIndex.value] || props.images[0];
});

const goToNext = () => {
  if (currentIndex.value < props.images.length - 1) {
    currentIndex.value++;
    loading.value = true;
  }
};

const goToPrevious = () => {
  if (currentIndex.value > 0) {
    currentIndex.value--;
    loading.value = true;
  }
};

const goToImage = (index) => {
  if (index >= 0 && index < props.images.length) {
    currentIndex.value = index;
    loading.value = true;
  }
};

const handleImageLoad = () => {
  loading.value = false;
};

const handleImageError = () => {
  loading.value = false;
};

const handleClose = () => {
  visible.value = false;
  emit('close');
};

// Keyboard navigation
const handleKeyPress = (event) => {
  if (!visible.value) return;

  switch (event.key) {
    case 'ArrowLeft':
      event.preventDefault();
      goToPrevious();
      break;
    case 'ArrowRight':
      event.preventDefault();
      goToNext();
      break;
    case 'Escape':
      event.preventDefault();
      handleClose();
      break;
  }
};

// Watch for initial index changes
watch(() => props.initialIndex, (newIndex) => {
  if (newIndex >= 0 && newIndex < props.images.length) {
    currentIndex.value = newIndex;
    loading.value = true;
  }
});

// Watch for images changes
watch(() => props.images, () => {
  if (props.images.length > 0 && currentIndex.value >= props.images.length) {
    currentIndex.value = 0;
  }
});

onMounted(() => {
  window.addEventListener('keydown', handleKeyPress);
});

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeyPress);
});
</script>

<style scoped>
.image-gallery-modal :deep(.ant-modal) {
  max-width: 1000px !important;
  width: 1000px !important;
}

.image-gallery-modal :deep(.ant-modal-content) {
  background: var(--bg-primary, #fff) !important;
  border-radius: 12px;
  overflow: hidden;
  padding: 0;
  max-width: 1000px;
  width: 100%;
}

[data-theme="dark"] .image-gallery-modal :deep(.ant-modal-content) {
  background: var(--bg-primary, #141414) !important;
  color: var(--text-primary, rgba(255, 255, 255, 0.85)) !important;
}

[data-theme="dark"] .image-gallery-modal :deep(.ant-modal-close) {
  color: var(--text-primary, rgba(255, 255, 255, 0.85)) !important;
}

[data-theme="dark"] .image-gallery-modal :deep(.ant-modal-close:hover) {
  color: var(--text-primary, rgba(255, 255, 255, 1)) !important;
  background-color: var(--bg-hover, #262626) !important;
}

.image-gallery-modal :deep(.ant-modal-body) {
  padding: 0;
}

.gallery-container {
  position: relative;
  width: 100%;
  height: auto;
  min-height: 600px;
  max-height: 80vh;
  background: var(--bg-secondary, #f5f5f5) !important;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

[data-theme="dark"] .gallery-container {
  background: var(--bg-secondary, #1f1f1f) !important;
}

.close-button {
  position: absolute;
  top: 16px;
  right: 16px;
  z-index: 10;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: rgba(0, 0, 0, 0.5);
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
  backdrop-filter: blur(8px);
}

.close-button:hover {
  background: rgba(0, 0, 0, 0.7);
  transform: scale(1.1);
}

[data-theme="dark"] .close-button {
  background: rgba(255, 255, 255, 0.2);
}

[data-theme="dark"] .close-button:hover {
  background: rgba(255, 255, 255, 0.3);
}

.image-counter {
  position: absolute;
  top: 16px;
  left: 16px;
  z-index: 10;
  padding: 8px 16px;
  background: rgba(0, 0, 0, 0.5);
  color: #fff;
  border-radius: 20px;
  font-size: 14px;
  font-weight: 500;
  backdrop-filter: blur(8px);
}

[data-theme="dark"] .image-counter {
  background: rgba(255, 255, 255, 0.2);
}

.image-wrapper {
  position: relative;
  width: 100%;
  height: 600px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
  overflow: hidden;
}

.image-loading {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  z-index: 5;
}

.gallery-image {
  max-width: 900px;
  max-height: 560px;
  width: auto;
  height: auto;
  object-fit: contain;
  border-radius: 8px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
  transition: opacity 0.3s ease;
}

[data-theme="dark"] .gallery-image {
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
}

.nav-button {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  z-index: 10;
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: rgba(0, 0, 0, 0.5);
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
  backdrop-filter: blur(8px);
  border: none;
}

.nav-button:hover:not(:disabled) {
  background: rgba(0, 0, 0, 0.7);
  transform: translateY(-50%) scale(1.1);
}

.nav-button:disabled {
  opacity: 0.3;
  cursor: not-allowed;
}

[data-theme="dark"] .nav-button {
  background: rgba(255, 255, 255, 0.2);
}

[data-theme="dark"] .nav-button:hover:not(:disabled) {
  background: rgba(255, 255, 255, 0.3);
}

.nav-button-prev {
  left: 16px;
}

.nav-button-next {
  right: 16px;
}

.thumbnail-strip {
  display: flex;
  gap: 12px;
  padding: 16px;
  background: var(--bg-primary, #fff) !important;
  border-top: 1px solid var(--border-color, #f0f0f0);
  overflow-x: auto;
  justify-content: center;
  align-items: center;
}

[data-theme="dark"] .thumbnail-strip {
  background: var(--bg-primary, #141414) !important;
  border-top-color: var(--border-color, #434343) !important;
}

.thumbnail-item {
  width: 80px;
  height: 80px;
  border-radius: 8px;
  overflow: hidden;
  cursor: pointer;
  border: 2px solid transparent;
  transition: all 0.3s ease;
  flex-shrink: 0;
  opacity: 0.6;
}

.thumbnail-item:hover {
  opacity: 1;
  transform: scale(1.05);
  border-color: var(--color-primary, #1890ff);
}

.thumbnail-item.active {
  opacity: 1;
  border-color: var(--color-primary, #1890ff);
  box-shadow: 0 0 0 2px rgba(24, 144, 255, 0.2);
}

[data-theme="dark"] .thumbnail-item.active {
  box-shadow: 0 0 0 2px rgba(64, 169, 255, 0.3);
}

.thumbnail-item img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

/* Responsive */
@media (max-width: 1024px) {
  .image-gallery-modal :deep(.ant-modal) {
    width: 95% !important;
    max-width: 95% !important;
  }
  
  .gallery-container {
    max-height: 75vh;
  }
  
  .image-wrapper {
    height: 500px;
    padding: 15px;
  }
  
  .gallery-image {
    max-width: 100%;
    max-height: 470px;
  }
}

@media (max-width: 768px) {
  .image-gallery-modal :deep(.ant-modal) {
    width: 98% !important;
    max-width: 98% !important;
  }
  
  .gallery-container {
    max-height: 70vh;
  }
  
  .image-wrapper {
    height: 400px;
    padding: 10px;
  }

  .nav-button {
    width: 40px;
    height: 40px;
  }

  .nav-button-prev {
    left: 8px;
  }

  .nav-button-next {
    right: 8px;
  }

  .thumbnail-strip {
    padding: 12px;
    gap: 8px;
  }

  .thumbnail-item {
    width: 60px;
    height: 60px;
  }

  .gallery-image {
    max-width: 100%;
    max-height: 380px;
  }
}
</style>

