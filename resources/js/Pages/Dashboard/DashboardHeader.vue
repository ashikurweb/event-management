<template>
  <div class="dashboard-header" :class="{ 'sidebar-collapsed': collapsed }">
    <div class="header-left">
      <a-button type="text" class="sidebar-toggle hamburger-btn" @click="$emit('toggle-sidebar')">
        <template #icon>
          <MenuOutlined v-if="!mobileOpen" class="hamburger-icon" />
          <MenuUnfoldOutlined v-else-if="collapsed" />
          <MenuFoldOutlined v-else />
        </template>
      </a-button>

      <div class="header-search-wrapper" @click="openSearchModal">
        <div class="search-trigger">
          <SearchOutlined class="search-icon" />
          <span class="search-placeholder">Search items...</span>
          <div class="search-shortcut">
            <span class="shortcut-key">⌘</span>
            <span class="shortcut-key">K</span>
          </div>
        </div>
      </div>

      <!-- Mobile Search Trigger -->
      <a-button type="text" class="mobile-search-btn" @click="openSearchModal">
        <template #icon>
          <SearchOutlined />
        </template>
      </a-button>
    </div>

    <div class="header-right">
      <!-- Language Switcher -->
      <LanguageSwitcher class="header-action" />

      <!-- Theme Toggle -->
      <ThemeToggle class="header-action" />

      <!-- Quick Actions - Hidden on Mobile -->
      <a-button type="primary" class="create-event-btn" @click="handleCreateEvent">
        <template #icon>
          <PlusOutlined />
        </template>
        <span class="create-event-text">Create Event</span>
      </a-button>

      <!-- Recycle Bin -->
      <a-button type="text" class="recycle-btn header-action" @click="handleRecycleBin" title="Recycle Bin">
        <template #icon>
          <DeleteOutlined />
        </template>
      </a-button>

      <!-- Notifications -->
      <a-badge :count="notificationCount" :overflow-count="99" class="notification-badge">
        <a-button type="text" class="header-action notification-btn" @click="showNotifications = true">
          <template #icon>
            <BellOutlined />
          </template>
        </a-button>
      </a-badge>

      <!-- User Profile Dropdown -->
      <a-dropdown :trigger="['click']" class="user-dropdown" v-if="user">
        <div class="user-profile">
          <a-avatar :src="userAvatar" :size="32">
            {{ userInitials }}
          </a-avatar>
          <span class="user-name">{{ userName }}</span>
          <DownOutlined class="dropdown-icon" />
        </div>
        <template #overlay>
          <a-menu @click="handleMenuClick">
            <a-menu-item key="profile">
              <UserOutlined /> Profile
            </a-menu-item>
            <a-menu-item key="settings">
              <SettingOutlined /> Settings
            </a-menu-item>
            <a-menu-divider />
            <a-menu-item key="logout">
              <LogoutOutlined /> Logout
            </a-menu-item>
          </a-menu>
        </template>
      </a-dropdown>
    </div>

    <!-- Notifications Drawer -->
    <a-drawer v-model:open="showNotifications" title="Notifications" placement="right" :width="400">
      <div class="notifications-list">
        <a-empty v-if="notifications.length === 0" description="No notifications" />
        <div v-else>
          <div v-for="notification in notifications" :key="notification.id" class="notification-item"
            :class="{ unread: !notification.read }">
            <div class="notification-icon">
              <BellOutlined />
            </div>
            <div class="notification-content">
              <div class="notification-title">{{ notification.title }}</div>
              <div class="notification-message">{{ notification.message }}</div>
              <div class="notification-time">{{ notification.time }}</div>
            </div>
          </div>
        </div>
      </div>
    </a-drawer>

    <!-- Ultra-Modern Search Modal (Command Palette) -->
    <a-modal v-model:open="showSearchModal" :footer="null" :closable="false" width="680px"
      wrap-class-name="modern-command-palette" @cancel="closeSearchModal">
      <div class="palette-container">
        <div class="palette-header">
          <div class="palette-search-icon-wrapper">
            <SearchOutlined class="palette-search-icon" />
          </div>
          <a-input ref="modalSearchInput" v-model:value="searchValue" placeholder="Search events, orders, categories..."
            class="palette-input" :bordered="false" @keyup.enter="handleSearch" />
          <div class="palette-esc" @click="closeSearchModal">ESC</div>
        </div>

        <div class="palette-body">
          <div v-if="!searchValue" class="palette-suggestions">
            <div class="suggestion-section">
              <span class="section-title">Quick Search</span>
              <div class="suggestion-chips">
                <div class="chip" @click="searchValue = 'Events'">Events</div>
                <div class="chip" @click="searchValue = 'Orders'">Orders</div>
                <div class="chip" @click="searchValue = 'Categories'">Categories</div>
                <div class="chip" @click="searchValue = 'Users'">Users</div>
              </div>
            </div>

            <div class="suggestion-section">
              <span class="section-title">Productivity</span>
              <div class="suggestion-item">
                <PlusOutlined />
                <span>Create New Event</span>
                <span class="item-shortcut">G E</span>
              </div>
              <div class="suggestion-item">
                <UserOutlined />
                <span>View User Analytics</span>
                <span class="item-shortcut">G U</span>
              </div>
            </div>
          </div>

          <div v-else class="palette-results">
            <div class="results-header">Search results for "{{ searchValue }}"</div>
            <div class="no-results-premium">
              <div class="pulse-icon">
                <SearchOutlined />
              </div>
              <span>Searching across everything...</span>
            </div>
          </div>
        </div>

        <div class="palette-footer">
          <div class="footer-tip">
            <span class="tip-key">↑↓</span>
            <span>to navigate</span>
          </div>
          <div class="footer-tip">
            <span class="tip-key">↵</span>
            <span>to select</span>
          </div>
        </div>
      </div>
    </a-modal>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import ThemeToggle from '../../Components/ThemeToggle.vue';
import LanguageSwitcher from '../../Components/LanguageSwitcher.vue';
import {
  MenuOutlined,
  MenuUnfoldOutlined,
  MenuFoldOutlined,
  PlusOutlined,
  BellOutlined,
  UserOutlined,
  SettingOutlined,
  LogoutOutlined,
  DownOutlined,
  DeleteOutlined,
  SearchOutlined,
} from '@ant-design/icons-vue';

const props = defineProps({
  collapsed: {
    type: Boolean,
    default: false,
  },
  mobileOpen: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['toggle-sidebar']);

const page = usePage();
const searchValue = ref('');
const isSearchFocused = ref(false);
const showSearchModal = ref(false);
const modalSearchInput = ref(null);
const showNotifications = ref(false);
const notificationCount = ref(5);

// Dynamic user data from Inertia shared props
const user = computed(() => page.props.auth?.user || null);

const userName = computed(() => {
  if (!user.value) return 'Guest';
  // Use full_name if available, otherwise combine first_name and last_name
  return user.value.name || `${user.value.first_name || ''} ${user.value.last_name || ''}`.trim() || 'User';
});

const userAvatar = computed(() => {
  if (!user.value) return null;
  // Return avatar URL if available
  return user.value.avatar || null;
});

const userInitials = computed(() => {
  if (!user.value) return 'G';

  // Use full_name if available
  if (user.value.name) {
    const names = user.value.name.split(' ');
    return names.map(n => n[0]).join('').toUpperCase().slice(0, 2);
  }

  // Fallback to first_name and last_name
  const firstName = user.value.first_name || '';
  const lastName = user.value.last_name || '';
  const initials = (firstName[0] || '') + (lastName[0] || '');
  return initials.toUpperCase() || 'U';
});

// Mock notifications
const notifications = ref([
  {
    id: 1,
    title: 'New Order',
    message: 'You have received a new order for Tech Conference 2024',
    time: '2 minutes ago',
    read: false,
  },
  {
    id: 2,
    title: 'Event Reminder',
    message: 'Your event "Web Development Workshop" starts in 2 hours',
    time: '1 hour ago',
    read: false,
  },
  {
    id: 3,
    title: 'Payment Received',
    message: 'Payment of $500 has been received',
    time: '3 hours ago',
    read: true,
  },
]);

const openSearchModal = () => {
  showSearchModal.value = true;
  // Use nextTick to focus the input after it's rendered
  setTimeout(() => {
    modalSearchInput.value?.focus();
  }, 100);
};

const closeSearchModal = () => {
  showSearchModal.value = false;
};

const handleSearch = () => {
  if (!searchValue.value.trim()) return;
  console.log('Global Search:', searchValue.value);
  closeSearchModal();
  // router.get('/dashboard/search', { q: searchValue.value });
};

// Handle Keyboard Shortcuts
onMounted(() => {
  const handleKeydown = (e) => {
    if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
      e.preventDefault();
      openSearchModal();
    }
  };
  window.addEventListener('keydown', handleKeydown);
  onUnmounted(() => window.removeEventListener('keydown', handleKeydown));
});

const handleCreateEvent = () => {
  router.visit('/dashboard/events/create');
};

const handleRecycleBin = () => {
  router.visit('/dashboard/settings/recycle-bin');
};

const handleMenuClick = ({ key }) => {
  switch (key) {
    case 'profile':
      router.visit('/dashboard/profile');
      break;
    case 'settings':
      router.visit('/dashboard/settings');
      break;
    case 'logout':
      router.post('/logout', {}, {
        onSuccess: () => {
          // Redirect handled by backend
        },
      });
      break;
  }
};
</script>

<style scoped>
.dashboard-header {
  height: 64px;
  background: var(--bg-secondary, #1f1f1f);
  padding: 0 24px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  box-shadow: var(--shadow-base, 0 2px 8px rgba(0, 0, 0, 0.06));
  position: fixed;
  top: 0;
  left: 256px;
  right: 0;
  z-index: 100;
  border-bottom: 1px solid var(--border-color-light, #2d2d2d);
  transition: left 0.2s, background-color 0.1s ease-out, border-color 0.1s ease-out, box-shadow 0.1s ease-out;
}

/* Header positioning when sidebar is collapsed */
.dashboard-header.sidebar-collapsed {
  left: 80px;
}

[data-theme="light"] .dashboard-header {
  background: #fff;
  border-bottom-color: #f0f0f0;
}

[data-theme="dark"] .dashboard-header {
  background: #1f1f1f;
  border-bottom-color: #2d2d2d;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 16px;
}

.sidebar-toggle {
  font-size: 18px;
}

.hamburger-btn {
  display: none;
}

.hamburger-icon {
  font-size: 20px;
}

@media (max-width: 768px) {
  .hamburger-btn {
    display: inline-flex !important;
  }
}

[data-theme="dark"] .sidebar-toggle :deep(.anticon) {
  color: rgba(255, 255, 255, 0.85) !important;
}

.mobile-search-btn {
  display: none;
}

/* Search Bar - Modern Design (Trigger) */
.header-search-wrapper {
  margin-left: 8px;
  display: flex;
  align-items: center;
  cursor: pointer;
}

.search-trigger {
  width: 320px;
  background: var(--bg-primary, #f5f5f5);
  border: 1px solid var(--border-color-light, #f0f0f0);
  border-radius: 10px;
  padding: 8px 12px;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: all 0.2s ease;
}

.search-trigger:hover {
  background: var(--bg-hover, #efefef);
  border-color: var(--primary-color, #1890ff);
}

.search-placeholder {
  color: var(--text-tertiary, #8c8c8c);
  flex: 1;
}

[data-theme="dark"] .search-trigger {
  background: rgba(255, 255, 255, 0.04);
  border-color: rgba(255, 255, 255, 0.08);
}

[data-theme="dark"] .search-trigger:hover {
  background: rgba(255, 255, 255, 0.08);
}

.search-icon {
  color: var(--text-tertiary, #8c8c8c);
  font-size: 16px;
}

.search-shortcut {
  display: flex;
  align-items: center;
  gap: 2px;
  background: var(--bg-secondary, #fafafa);
  padding: 2px 6px;
  border-radius: 4px;
  border: 1px solid var(--border-color, #f0f0f0);
  opacity: 0.8;
}

[data-theme="dark"] .search-shortcut {
  background: rgba(255, 255, 255, 0.08);
  border-color: rgba(255, 255, 255, 0.12);
}

.shortcut-key {
  font-size: 11px;
  font-weight: 500;
  color: var(--text-tertiary, #8c8c8c);
}

/* Modern Command Palette Design */
:global(.modern-command-palette .ant-modal-mask) {
  backdrop-filter: blur(20px) saturate(180%) !important;
  -webkit-backdrop-filter: blur(20px) saturate(180%) !important;
  background: rgba(0, 0, 0, 0.45) !important;
}

:global(.modern-command-palette .ant-modal-content) {
  background: rgba(255, 255, 255, 0.7) !important;
  backdrop-filter: blur(40px) !important;
  border: 1px solid rgba(255, 255, 255, 0.3) !important;
  border-radius: 20px !important;
  padding: 0 !important;
  overflow: hidden !important;
  box-shadow: 0 30px 60px -12px rgba(0, 0, 0, 0.25),
    0 0 0 1px rgba(255, 255, 255, 0.1) !important;
}

[data-theme="dark"] :global(.modern-command-palette .ant-modal-content) {
  background: rgba(20, 20, 20, 0.7) !important;
  border-color: rgba(255, 255, 255, 0.08) !important;
  box-shadow: 0 30px 60px -12px rgba(0, 0, 0, 0.5),
    inset 0 0 0 1px rgba(255, 255, 255, 0.05) !important;
}

.palette-container {
  display: flex;
  flex-direction: column;
}

.palette-header {
  display: flex;
  align-items: center;
  padding: 16px 20px;
  gap: 16px;
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

[data-theme="dark"] .palette-header {
  border-bottom-color: rgba(255, 255, 255, 0.05);
}

.palette-search-icon-wrapper {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  background: var(--primary-color, #1890ff);
  border-radius: 10px;
  box-shadow: 0 4px 12px rgba(24, 144, 255, 0.3);
}

.palette-search-icon {
  font-size: 18px;
  color: #fff;
}

.palette-input {
  flex: 1;
  background: transparent !important;
  font-size: 18px !important;
  color: var(--text-primary, #262626) !important;
}

[data-theme="dark"] .palette-input {
  color: #fff !important;
}

.palette-input :deep(input::placeholder) {
  color: rgba(0, 0, 0, 0.3);
}

[data-theme="dark"] .palette-input :deep(input::placeholder) {
  color: rgba(255, 255, 255, 0.2);
}

.palette-esc {
  padding: 4px 8px;
  background: rgba(0, 0, 0, 0.05);
  border-radius: 6px;
  font-size: 11px;
  font-weight: 700;
  color: var(--text-tertiary, #8c8c8c);
  cursor: pointer;
  transition: all 0.2s;
}

[data-theme="dark"] .palette-esc {
  background: rgba(255, 255, 255, 0.08);
}

.palette-body {
  padding: 24px 20px;
  min-height: 320px;
}

.suggestion-section {
  margin-bottom: 24px;
}

.section-title {
  display: block;
  font-size: 12px;
  font-weight: 600;
  color: var(--text-tertiary, #8c8c8c);
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 12px;
  padding-left: 4px;
}

.suggestion-chips {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.chip {
  padding: 6px 14px;
  background: rgba(0, 0, 0, 0.03);
  border: 1px solid rgba(0, 0, 0, 0.05);
  border-radius: 20px;
  font-size: 13px;
  cursor: pointer;
  transition: all 0.2s;
}

[data-theme="dark"] .chip {
  background: rgba(255, 255, 255, 0.04);
  border-color: rgba(255, 255, 255, 0.08);
  color: rgba(255, 255, 255, 0.65);
}

.chip:hover {
  background: var(--primary-color, #1890ff);
  color: #fff;
  border-color: var(--primary-color, #1890ff);
  transform: translateY(-1px);
}

.suggestion-item {
  display: flex;
  align-items: center;
  padding: 10px 12px;
  border-radius: 12px;
  gap: 12px;
  cursor: pointer;
  transition: all 0.2s;
  color: var(--text-secondary, #595959);
}

[data-theme="dark"] .suggestion-item {
  color: rgba(255, 255, 255, 0.65);
}

.suggestion-item:hover {
  background: rgba(0, 0, 0, 0.03);
  color: var(--primary-color, #1890ff);
}

[data-theme="dark"] .suggestion-item:hover {
  background: rgba(255, 255, 255, 0.04);
}

.item-shortcut {
  margin-left: auto;
  font-size: 11px;
  color: var(--text-tertiary, #8c8c8c);
  opacity: 0.6;
}

.palette-results {
  animation: fadeIn 0.3s ease;
}

.results-header {
  font-size: 14px;
  color: var(--text-tertiary, #8c8c8c);
  margin-bottom: 20px;
}

.no-results-premium {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  margin-top: 40px;
  gap: 16px;
  color: var(--text-tertiary, #8c8c8c);
}

.pulse-icon {
  font-size: 32px;
  color: var(--primary-color, #1890ff);
  animation: pulse 2s infinite;
}

.palette-footer {
  padding: 12px 20px;
  background: rgba(0, 0, 0, 0.02);
  border-top: 1px solid rgba(0, 0, 0, 0.05);
  display: flex;
  gap: 20px;
}

[data-theme="dark"] .palette-footer {
  background: rgba(255, 255, 255, 0.02);
  border-top-color: rgba(255, 255, 255, 0.05);
}

.footer-tip {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
  color: var(--text-tertiary, #8c8c8c);
}

.tip-key {
  padding: 1px 4px;
  background: rgba(0, 0, 0, 0.05);
  border-radius: 4px;
  font-family: monospace;
  font-weight: 600;
}

[data-theme="dark"] .tip-key {
  background: rgba(255, 255, 255, 0.1);
}

@keyframes pulse {
  0% {
    transform: scale(1);
    opacity: 0.8;
  }

  50% {
    transform: scale(1.1);
    opacity: 1;
  }

  100% {
    transform: scale(1);
    opacity: 0.8;
  }
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Animations */
.search-modal-enter-active,
.search-modal-leave-active {
  transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}

.search-modal-enter-from,
.search-modal-leave-to {
  opacity: 0;
  transform: translateY(-20px) scale(0.95);
}

/* Header Icons Dark Mode */
[data-theme="dark"] .header-right .ant-btn-text :deep(.anticon) {
  color: rgba(255, 255, 255, 0.85) !important;
}

.header-right {
  display: flex;
  align-items: center;
  gap: 16px;
}

.recycle-btn {
  margin-right: -8px;
  background: rgba(255, 77, 79, 0.1) !important;
  border-radius: 50% !important;
  width: 40px !important;
  height: 40px !important;
  padding: 0 !important;
  display: inline-flex !important;
  align-items: center !important;
  justify-content: center !important;
  transition: all 0.2s ease !important;
}

.recycle-btn:hover {
  background: rgba(255, 77, 79, 0.2) !important;
}

.recycle-btn :deep(.anticon) {
  color: #ff4d4f !important;
}

.recycle-btn:hover :deep(.anticon) {
  color: #ff7875 !important;
}

[data-theme="dark"] .recycle-btn {
  background: rgba(255, 77, 79, 0.15) !important;
}

[data-theme="dark"] .recycle-btn:hover {
  background: rgba(255, 77, 79, 0.25) !important;
}

[data-theme="dark"] .recycle-btn :deep(.anticon) {
  color: #ff7875 !important;
}

[data-theme="dark"] .recycle-btn:hover :deep(.anticon) {
  color: #ff9c9e !important;
}

.notification-badge {
  margin-left: -8px;
}

.user-profile {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  padding: 4px 8px;
  border-radius: 6px;
  transition: background-color 0.2s;
}

.user-profile:hover {
  background-color: var(--bg-hover, #f5f5f5);
}

.user-name {
  font-weight: 500;
  color: var(--text-primary, #262626);
}

[data-theme="dark"] .user-name {
  color: rgba(255, 255, 255, 0.85);
}

.dropdown-icon {
  font-size: 12px;
  color: var(--text-tertiary, #8c8c8c);
}

.notifications-list {
  padding: 8px 0;
}

.notification-item {
  display: flex;
  gap: 12px;
  padding: 12px;
  border-bottom: 1px solid var(--border-color-light, #f0f0f0);
  cursor: pointer;
  transition: background-color 0.2s;
}

.notification-item:hover {
  background-color: var(--bg-hover, #fafafa);
}

.notification-item.unread {
  background-color: var(--bg-elevated, #e6f7ff);
}

.notification-icon {
  font-size: 20px;
  color: #1890ff;
  flex-shrink: 0;
}

.notification-content {
  flex: 1;
}

.notification-title {
  font-weight: 500;
  color: var(--text-primary, #262626);
  margin-bottom: 4px;
}

.notification-message {
  color: var(--text-secondary, #595959);
  font-size: 14px;
  margin-bottom: 4px;
}

.notification-time {
  color: var(--text-tertiary, #8c8c8c);
  font-size: 12px;
}

/* Responsive Styles */
@media (max-width: 1024px) {
  .search-trigger {
    width: 200px;
  }
}

@media (max-width: 768px) {
  .search-trigger {
    display: none;
  }

  .mobile-search-btn {
    display: inline-flex !important;
    font-size: 18px;
    padding: 4px 8px !important;
    height: 40px;
    width: 40px;
    align-items: center;
    justify-content: center;
  }

  .header-right {
    gap: 8px;
    margin-left: auto;
  }

  /* Hide Create Event button and Recycle Bin on mobile */
  .create-event-btn,
  .recycle-btn {
    display: none !important;
  }

  /* Show only: Theme Toggle, Notifications, Profile Dropdown */
  .header-action {
    display: inline-flex !important;
  }

  .notification-badge {
    display: inline-block;
  }

  .user-dropdown {
    display: inline-block;
  }

  .user-name {
    display: none;
  }

  .user-profile {
    padding: 4px 8px;
    gap: 6px;
  }

  .user-profile :deep(.ant-avatar) {
    width: 32px !important;
    height: 32px !important;
  }

  .notification-btn,
  .header-action {
    padding: 4px 8px !important;
    min-width: 40px;
    height: 40px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
  }

  :deep(.ant-drawer) {
    width: 100% !important;
    max-width: 100% !important;
  }
}

@media (max-width: 480px) {
  .dashboard-header {
    padding: 0 8px;
    height: 52px;
  }

  .header-left {
    gap: 4px;
  }

  .header-right {
    gap: 4px;
  }

  .user-profile {
    padding: 2px 4px;
    gap: 4px;
  }

  .user-profile :deep(.ant-avatar) {
    width: 28px !important;
    height: 28px !important;
  }

  .notification-btn,
  .header-action {
    padding: 4px !important;
    min-width: 36px;
    height: 36px;
  }
}
</style>
