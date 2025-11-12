<template>
  <nav class="breadcrumb-container" aria-label="Breadcrumb">
    <ol class="breadcrumb-list">
      <li 
        v-for="(item, index) in items" 
        :key="index"
        class="breadcrumb-item"
        :class="{ 'breadcrumb-item-active': index === items.length - 1 }"
      >
        <template v-if="index === 0">
          <Link 
            v-if="item.href && index !== items.length - 1" 
            :href="item.href"
            class="breadcrumb-link breadcrumb-home"
          >
            <HomeOutlined class="breadcrumb-icon" />
            <span class="breadcrumb-text">{{ item.label }}</span>
          </Link>
          <span v-else class="breadcrumb-current breadcrumb-home">
            <HomeOutlined class="breadcrumb-icon" />
            <span class="breadcrumb-text">{{ item.label }}</span>
          </span>
        </template>
        <template v-else>
          <span class="breadcrumb-separator">
            <RightOutlined />
          </span>
          <Link 
            v-if="item.href && index !== items.length - 1" 
            :href="item.href"
            class="breadcrumb-link"
          >
            <span class="breadcrumb-text">{{ item.label }}</span>
          </Link>
          <span v-else class="breadcrumb-current">
            <span class="breadcrumb-text">{{ item.label }}</span>
          </span>
        </template>
      </li>
    </ol>
  </nav>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { HomeOutlined, RightOutlined } from '@ant-design/icons-vue';

const props = defineProps({
  items: {
    type: Array,
    required: true,
    default: () => [],
    validator: (items) => {
      return items.every(item => 
        item && 
        typeof item === 'object' && 
        'label' in item
      );
    }
  }
});
</script>

<style scoped>
.breadcrumb-container {
  margin-bottom: 16px;
}

.breadcrumb-list {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 4px;
  list-style: none;
  padding: 0;
  margin: 0;
}

.breadcrumb-item {
  display: flex;
  align-items: center;
  gap: 8px;
}

.breadcrumb-separator {
  display: flex;
  align-items: center;
  color: var(--text-tertiary, #8c8c8c);
  font-size: 12px;
  margin: 0 4px;
  transition: color 0.2s ease;
}

[data-theme="dark"] .breadcrumb-separator {
  color: rgba(255, 255, 255, 0.45);
}

.breadcrumb-link {
  display: flex;
  align-items: center;
  gap: 6px;
  color: var(--text-secondary, #595959);
  text-decoration: none;
  font-size: 14px;
  font-weight: 400;
  padding: 4px 8px;
  border-radius: 6px;
  transition: all 0.2s ease;
  cursor: pointer;
}

.breadcrumb-link:hover {
  color: var(--color-primary, #1890ff);
  background-color: var(--bg-hover, #f5f5f5);
}

[data-theme="dark"] .breadcrumb-link {
  color: rgba(255, 255, 255, 0.65);
}

[data-theme="dark"] .breadcrumb-link:hover {
  color: var(--color-primary, #40a9ff);
  background-color: rgba(255, 255, 255, 0.08);
}

.breadcrumb-current {
  display: flex;
  align-items: center;
  gap: 6px;
  color: var(--text-primary, #262626);
  font-size: 14px;
  font-weight: 500;
  padding: 4px 8px;
}

[data-theme="dark"] .breadcrumb-current {
  color: rgba(255, 255, 255, 0.85);
}

.breadcrumb-home {
  display: flex;
  align-items: center;
  gap: 6px;
}

.breadcrumb-icon {
  font-size: 16px;
  display: flex;
  align-items: center;
}

.breadcrumb-text {
  line-height: 1.5;
  white-space: nowrap;
}

/* Active state styling */
.breadcrumb-item-active .breadcrumb-current {
  color: var(--color-primary, #1890ff);
  font-weight: 600;
}

[data-theme="dark"] .breadcrumb-item-active .breadcrumb-current {
  color: var(--color-primary, #40a9ff);
}

/* Responsive styles */
@media (max-width: 768px) {
  .breadcrumb-container {
    margin-bottom: 12px;
  }

  .breadcrumb-list {
    gap: 2px;
  }

  .breadcrumb-item {
    gap: 4px;
  }

  .breadcrumb-separator {
    font-size: 10px;
    margin: 0 2px;
  }

  .breadcrumb-link,
  .breadcrumb-current {
    font-size: 13px;
    padding: 3px 6px;
  }

  .breadcrumb-icon {
    font-size: 14px;
  }

  /* Hide middle items on mobile, show only first and last */
  .breadcrumb-item:not(:first-child):not(:last-child) .breadcrumb-text {
    display: none;
  }

  .breadcrumb-item:not(:first-child):not(:last-child) .breadcrumb-separator {
    margin: 0;
  }
}

@media (max-width: 480px) {
  .breadcrumb-link,
  .breadcrumb-current {
    font-size: 12px;
    padding: 2px 4px;
  }

  .breadcrumb-icon {
    font-size: 13px;
  }
}

/* Animation for breadcrumb items */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-4px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.breadcrumb-item {
  animation: fadeIn 0.3s ease-out;
}

.breadcrumb-item:nth-child(1) {
  animation-delay: 0s;
}

.breadcrumb-item:nth-child(2) {
  animation-delay: 0.05s;
}

.breadcrumb-item:nth-child(3) {
  animation-delay: 0.1s;
}

.breadcrumb-item:nth-child(4) {
  animation-delay: 0.15s;
}

.breadcrumb-item:nth-child(5) {
  animation-delay: 0.2s;
}
</style>

