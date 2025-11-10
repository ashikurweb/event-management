<template>
  <a-dropdown
    :trigger="['click']"
    placement="bottomRight"
    class="language-switcher"
    :overlay-style="{ minWidth: '200px' }"
    v-model:open="isOpen"
  >
    <div class="language-trigger" :class="{ active: isOpen }">
      <img
        :src="currentLanguage.flag"
        :alt="currentLanguage.name"
        class="flag-icon"
      />
      <span class="language-code">{{ currentLanguage.code.toUpperCase() }}</span>
      <DownOutlined class="dropdown-arrow" />
    </div>
    
    <template #overlay>
      <a-menu
        class="language-menu"
        :selected-keys="[currentLanguage.code]"
        @click="handleLanguageChange"
      >
        <a-menu-item
          v-for="lang in languages"
          :key="lang.code"
          class="language-menu-item"
        >
          <div class="language-option">
            <img
              :src="lang.flag"
              :alt="lang.name"
              class="flag-icon-small"
            />
            <div class="language-info">
              <span class="language-name">{{ lang.name }}</span>
              <span class="language-native">{{ lang.native }}</span>
            </div>
            <CheckOutlined
              v-if="currentLanguage.code === lang.code"
              class="check-icon"
            />
          </div>
        </a-menu-item>
      </a-menu>
    </template>
  </a-dropdown>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { DownOutlined, CheckOutlined } from '@ant-design/icons-vue';
import { useLanguage } from '../Composables/useLanguage';

const { currentLanguage, setLanguage, languages } = useLanguage();
const isOpen = ref(false);

const handleLanguageChange = ({ key }) => {
  setLanguage(key);
  // Close dropdown after a short delay for smooth UX
  setTimeout(() => {
    isOpen.value = false;
  }, 100);
};
</script>

<style scoped>
.language-switcher {
  display: inline-block;
}

.language-trigger {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 6px 12px;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
  background: transparent;
  border: 1px solid var(--border-color, #d9d9d9);
}

[data-theme="dark"] .language-trigger {
  border-color: #434343;
  background: #262626;
}

.language-trigger:hover {
  background: var(--bg-hover, #f5f5f5);
  border-color: var(--color-primary, #1890ff);
}

[data-theme="dark"] .language-trigger:hover {
  background: #404040;
  border-color: #40a9ff;
}

.language-trigger.active {
  background: var(--color-primary-50, #e6f7ff);
  border-color: var(--color-primary, #1890ff);
}

[data-theme="dark"] .language-trigger.active {
  background: #111b26;
  border-color: #40a9ff;
}

.flag-icon {
  width: 20px;
  height: 20px;
  border-radius: 4px;
  object-fit: cover;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.language-code {
  font-size: 13px;
  font-weight: 600;
  color: var(--text-primary, #262626);
  letter-spacing: 0.5px;
}

[data-theme="dark"] .language-code {
  color: rgba(255, 255, 255, 0.85);
}

.dropdown-arrow {
  font-size: 10px;
  color: var(--text-tertiary, #8c8c8c);
  transition: transform 0.2s ease;
}

.language-trigger.active .dropdown-arrow {
  transform: rotate(180deg);
}

[data-theme="dark"] .dropdown-arrow {
  color: rgba(255, 255, 255, 0.45);
}

/* Language Menu */
:deep(.language-menu) {
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  padding: 4px;
  background: var(--card-bg, #fff);
  border: 1px solid var(--card-border, #f0f0f0);
}

[data-theme="dark"] :deep(.language-menu) {
  background: #1f1f1f;
  border-color: #2d2d2d;
}

:deep(.language-menu-item) {
  margin: 2px 0 !important;
  border-radius: 6px !important;
  padding: 0 !important;
}

:deep(.language-menu-item:hover) {
  background: var(--bg-hover, #f5f5f5) !important;
}

[data-theme="dark"] :deep(.language-menu-item:hover) {
  background: #262626 !important;
}

:deep(.language-menu-item-selected) {
  background: var(--color-primary-50, #e6f7ff) !important;
}

[data-theme="dark"] :deep(.language-menu-item-selected) {
  background: #111b26 !important;
}

.language-option {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 12px;
  width: 100%;
}

.flag-icon-small {
  width: 24px;
  height: 24px;
  border-radius: 4px;
  object-fit: cover;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  flex-shrink: 0;
}

.language-info {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.language-name {
  font-size: 14px;
  font-weight: 500;
  color: var(--text-primary, #262626);
  line-height: 1.2;
}

[data-theme="dark"] .language-name {
  color: rgba(255, 255, 255, 0.85);
}

.language-native {
  font-size: 12px;
  color: var(--text-secondary, #8c8c8c);
  line-height: 1.2;
}

[data-theme="dark"] .language-native {
  color: rgba(255, 255, 255, 0.45);
}

.check-icon {
  font-size: 14px;
  color: var(--color-primary, #1890ff);
  flex-shrink: 0;
}

[data-theme="dark"] .check-icon {
  color: #40a9ff;
}

/* Responsive */
@media (max-width: 768px) {
  .language-trigger {
    padding: 4px 8px;
    gap: 6px;
  }

  .flag-icon {
    width: 18px;
    height: 18px;
  }

  .language-code {
    font-size: 12px;
  }

  .dropdown-arrow {
    font-size: 9px;
  }
}

@media (max-width: 480px) {
  .language-trigger {
    padding: 4px 6px;
    gap: 4px;
  }

  .language-code {
    display: none;
  }
}
</style>

