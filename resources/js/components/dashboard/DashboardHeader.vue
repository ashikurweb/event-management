<template>
  <div class="dashboard-header">
    <div class="header-left">
      <a-button
        type="text"
        class="sidebar-toggle"
        @click="$emit('toggle-sidebar')"
      >
        <template #icon><MenuUnfoldOutlined v-if="collapsed" /><MenuFoldOutlined v-else /></template>
      </a-button>
      
      <a-input-search
        v-model:value="searchValue"
        placeholder="Search events, users, orders..."
        style="width: 300px"
        @search="handleSearch"
      />
    </div>

    <div class="header-right">
      <!-- Quick Actions -->
      <a-button type="primary" @click="handleCreateEvent">
        <template #icon><PlusOutlined /></template>
        Create Event
      </a-button>

      <a-button type="text" @click="handleViewCalendar">
        <template #icon><CalendarOutlined /></template>
      </a-button>

      <!-- Notifications -->
      <a-badge :count="notificationCount" :overflow-count="99">
        <a-button type="text" @click="showNotifications = true">
          <template #icon><BellOutlined /></template>
        </a-button>
      </a-badge>

      <!-- User Profile Dropdown -->
      <a-dropdown :trigger="['click']">
        <div class="user-profile">
          <a-avatar :src="userAvatar" :size="32">
            {{ userInitials }}
          </a-avatar>
          <span class="user-name">{{ userName }}</span>
          <DownOutlined class="dropdown-icon" />
        </div>
        <template #overlay>
          <a-menu>
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
import { router } from '@inertiajs/vue3';
import {
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
});

const emit = defineEmits(['toggle-sidebar']);

const searchValue = ref('');
const showNotifications = ref(false);
const notificationCount = ref(5);

// Mock user data - replace with actual user data
const userName = ref('John Doe');
const userAvatar = ref(null);
const userInitials = computed(() => {
  const names = userName.value.split(' ');
  return names.map(n => n[0]).join('').toUpperCase();
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
</script>

<style scoped>
.dashboard-header {
  height: 64px;
  background: #fff;
  padding: 0 24px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  position: sticky;
  top: 0;
  z-index: 100;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 16px;
}

.sidebar-toggle {
  font-size: 18px;
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
  background-color: #f5f5f5;
}

.user-name {
  font-weight: 500;
  color: #262626;
}

.dropdown-icon {
  font-size: 12px;
  color: #8c8c8c;
}

.notifications-list {
  padding: 8px 0;
}

.notification-item {
  display: flex;
  gap: 12px;
  padding: 12px;
  border-bottom: 1px solid #f0f0f0;
  cursor: pointer;
  transition: background-color 0.2s;
}

.notification-item:hover {
  background-color: #fafafa;
}

.notification-item.unread {
  background-color: #e6f7ff;
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
  color: #262626;
  margin-bottom: 4px;
}

.notification-message {
  color: #595959;
  font-size: 14px;
  margin-bottom: 4px;
}

.notification-time {
  color: #8c8c8c;
  font-size: 12px;
}

@media (max-width: 768px) {
  .header-left :deep(.ant-input-search) {
    display: none;
  }
  
  .user-name {
    display: none;
  }
}
</style>

