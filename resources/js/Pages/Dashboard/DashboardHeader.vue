<template>
  <div class="dashboard-header" :class="{ 'sidebar-collapsed': collapsed }">
    <div class="header-left">
      <a-button
        type="text"
        class="sidebar-toggle hamburger-btn"
        @click="$emit('toggle-sidebar')"
      >
        <template #icon>
          <MenuOutlined v-if="!mobileOpen" class="hamburger-icon" />
          <MenuUnfoldOutlined v-else-if="collapsed" />
          <MenuFoldOutlined v-else />
        </template>
      </a-button>
      
      <a-input-search
        v-model:value="searchValue"
        placeholder="Search events, users, orders..."
        class="header-search"
        style="width: 300px"
        @search="handleSearch"
      />
    </div>

    <div class="header-right">
      <!-- Language Switcher -->
      <LanguageSwitcher class="header-action" />

      <!-- Theme Toggle -->
      <ThemeToggle class="header-action" />

      <!-- Quick Actions - Hidden on Mobile -->
      <a-button type="primary" class="create-event-btn" @click="handleCreateEvent">
        <template #icon><PlusOutlined /></template>
        <span class="create-event-text">Create Event</span>
      </a-button>

      <a-button type="text" class="calendar-btn" @click="handleViewCalendar">
        <template #icon><CalendarOutlined /></template>
      </a-button>

      <!-- Notifications -->
      <a-badge :count="notificationCount" :overflow-count="99" class="notification-badge">
        <a-button type="text" class="header-action notification-btn" @click="showNotifications = true">
          <template #icon><BellOutlined /></template>
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
    <a-drawer
      v-model:open="showNotifications"
      title="Notifications"
      placement="right"
      :width="400"
    >
      <div class="notifications-list">
        <a-empty v-if="notifications.length === 0" description="No notifications" />
        <div v-else>
          <div
            v-for="notification in notifications"
            :key="notification.id"
            class="notification-item"
            :class="{ unread: !notification.read }"
          >
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
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import ThemeToggle from '../../Components/ThemeToggle.vue';
import LanguageSwitcher from '../../Components/LanguageSwitcher.vue';
import {
  MenuOutlined,
  MenuUnfoldOutlined,
  MenuFoldOutlined,
  PlusOutlined,
  CalendarOutlined,
  BellOutlined,
  UserOutlined,
  SettingOutlined,
  LogoutOutlined,
  DownOutlined,
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

const handleSearch = (value) => {
  console.log('Search:', value);
  // Implement search functionality
};

const handleCreateEvent = () => {
  router.visit('/dashboard/events/create');
};

const handleViewCalendar = () => {
  router.visit('/dashboard/calendar');
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

/* Search Bar - Using global styles from antd-theme.css */

[data-theme="dark"] .header-right :deep(.anticon) {
  color: rgba(255, 255, 255, 0.85) !important;
}

[data-theme="dark"] .header-right .ant-btn-text :deep(.anticon) {
  color: rgba(255, 255, 255, 0.85) !important;
}

.header-right {
  display: flex;
  align-items: center;
  gap: 16px;
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
  .header-search {
    width: 200px !important;
  }
}

@media (max-width: 768px) {
  .dashboard-header {
    padding: 0 12px;
    height: 56px;
    position: fixed;
    top: 0;
    left: 0 !important; /* Full width on mobile */
    right: 0;
    z-index: 100;
  }

  .dashboard-header.sidebar-collapsed {
    left: 0 !important; /* Full width on mobile */
  }

  .header-left {
    gap: 8px;
  }

  .header-search {
    display: none !important;
  }

  .header-right {
    gap: 8px;
    margin-left: auto;
  }

  /* Hide Create Event button and Calendar on mobile */
  .create-event-btn,
  .calendar-btn {
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

