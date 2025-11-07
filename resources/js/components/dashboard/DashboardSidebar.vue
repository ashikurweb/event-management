<template>
  <div class="dashboard-sidebar" :class="{ collapsed: collapsed }">
    <!-- Logo -->
    <div class="sidebar-logo">
      <div class="logo-icon">U</div>
      <span v-if="!collapsed" class="logo-text">EventHub</span>
    </div>

    <!-- Menu -->
    <a-menu
      v-model:selectedKeys="selectedKeys"
      v-model:openKeys="openKeys"
      mode="inline"
      theme="light"
      :inline-collapsed="collapsed"
      class="dashboard-menu"
      @click="handleMenuClick"
    >
      <!-- Group 1: Core -->
      <a-menu-item key="dashboard">
        <template #icon><DashboardOutlined /></template>
        <span>Dashboard</span>
      </a-menu-item>

      <a-sub-menu key="events">
        <template #icon><CalendarOutlined /></template>
        <template #title>Events</template>
        <a-menu-item key="events-all">All Events</a-menu-item>
        <a-menu-item key="events-create">Create Event</a-menu-item>
        <a-menu-item key="events-draft">Draft Events</a-menu-item>
        <a-menu-item key="events-published">Published Events</a-menu-item>
        <a-menu-item key="events-cancelled">Cancelled Events</a-menu-item>
        <a-menu-item key="events-tags">Event Tags</a-menu-item>
      </a-sub-menu>

      <a-sub-menu key="tickets">
        <template #icon><IdcardOutlined /></template>
        <template #title>Tickets & Orders</template>
        <a-menu-item key="orders-all">All Orders</a-menu-item>
        <a-menu-item key="orders-pending">Pending Orders</a-menu-item>
        <a-menu-item key="orders-completed">Completed Orders</a-menu-item>
        <a-menu-item key="tickets-all">Tickets</a-menu-item>
        <a-menu-item key="tickets-types">Ticket Types</a-menu-item>
        <a-menu-item key="tickets-checkin">Check-in</a-menu-item>
        <a-menu-item key="tickets-waitlist">Waitlist</a-menu-item>
      </a-sub-menu>

      <a-sub-menu key="payments">
        <template #icon><MoneyCollectOutlined /></template>
        <template #title>Payments</template>
        <a-menu-item key="payments-all">All Payments</a-menu-item>
        <a-menu-item key="payments-pending">Pending Payments</a-menu-item>
        <a-menu-item key="payments-completed">Completed Payments</a-menu-item>
        <a-menu-item key="payments-refunds">Refunds</a-menu-item>
        <a-menu-item key="payments-methods">Payment Methods</a-menu-item>
      </a-sub-menu>

      <!-- Divider -->
      <a-menu-divider />

      <!-- Group 2: People -->
      <a-sub-menu key="users">
        <template #icon><TeamOutlined /></template>
        <template #title>Users & Roles</template>
        <a-menu-item key="users-all">All Users</a-menu-item>
        <a-menu-item key="users-organizers">Organizers</a-menu-item>
        <a-menu-item key="users-attendees">Attendees</a-menu-item>
        <a-menu-item key="users-vendors">Vendors</a-menu-item>
        <a-menu-item key="users-speakers">Speakers</a-menu-item>
        <a-menu-item key="users-activity">Activity Logs</a-menu-item>
      </a-sub-menu>

      <!-- Roles & Permissions - Separate Menu Item -->
      <a-sub-menu key="roles-permissions">
        <template #icon><SafetyOutlined /></template>
        <template #title>Roles & Permissions</template>
        <a-menu-item key="roles-all">All Roles</a-menu-item>
        <a-menu-item key="roles-create">Create Role</a-menu-item>
        <a-menu-item key="permissions-all">All Permissions</a-menu-item>
        <a-menu-item key="permissions-assign">Assign Permissions</a-menu-item>
        <a-menu-item key="role-user-assign">Assign Roles to Users</a-menu-item>
      </a-sub-menu>

      <a-sub-menu key="speakers">
        <template #icon><UserOutlined /></template>
        <template #title>Speakers</template>
        <a-menu-item key="speakers-all">All Speakers</a-menu-item>
        <a-menu-item key="speakers-featured">Featured Speakers</a-menu-item>
        <a-menu-item key="speakers-create">Add Speaker</a-menu-item>
      </a-sub-menu>

      <a-sub-menu key="teams">
        <template #icon><UserAddOutlined /></template>
        <template #title>Teams</template>
        <a-menu-item key="teams-all">My Teams</a-menu-item>
        <a-menu-item key="teams-members">Team Members</a-menu-item>
        <a-menu-item key="teams-invitations">Team Invitations</a-menu-item>
        <a-menu-item key="teams-create">Create Team</a-menu-item>
      </a-sub-menu>

      <!-- Divider -->
      <a-menu-divider />

      <!-- Group 3: Resources -->
      <a-menu-item key="categories">
        <template #icon><FolderOutlined /></template>
        <span>Categories</span>
      </a-menu-item>

      <a-sub-menu key="venues">
        <template #icon><EnvironmentOutlined /></template>
        <template #title>Venues</template>
        <a-menu-item key="venues-all">All Venues</a-menu-item>
        <a-menu-item key="venues-create">Add Venue</a-menu-item>
        <a-menu-item key="venues-verified">Verified Venues</a-menu-item>
      </a-sub-menu>

      <a-sub-menu key="sponsors">
        <template #icon><StarOutlined /></template>
        <template #title>Sponsors</template>
        <a-menu-item key="sponsors-all">All Sponsors</a-menu-item>
        <a-menu-item key="sponsors-platinum">Platinum</a-menu-item>
        <a-menu-item key="sponsors-gold">Gold</a-menu-item>
        <a-menu-item key="sponsors-silver">Silver</a-menu-item>
        <a-menu-item key="sponsors-bronze">Bronze</a-menu-item>
        <a-menu-item key="sponsors-create">Add Sponsor</a-menu-item>
      </a-sub-menu>

      <a-sub-menu key="vendors">
        <template #icon><ShopOutlined /></template>
        <template #title>Vendors</template>
        <a-menu-item key="vendors-all">All Vendors</a-menu-item>
        <a-menu-item key="vendors-by-category">By Category</a-menu-item>
        <a-menu-item key="vendors-create">Add Vendor</a-menu-item>
        <a-menu-item key="vendors-event">Event Vendors</a-menu-item>
      </a-sub-menu>

      <!-- Divider -->
      <a-menu-divider />

      <!-- Group 4: Marketing -->
      <a-sub-menu key="promo-codes">
        <template #icon><TagOutlined /></template>
        <template #title>Promo Codes</template>
        <a-menu-item key="promo-all">All Promo Codes</a-menu-item>
        <a-menu-item key="promo-active">Active Promo Codes</a-menu-item>
        <a-menu-item key="promo-create">Create Promo Code</a-menu-item>
        <a-menu-item key="promo-usage">Usage History</a-menu-item>
      </a-sub-menu>

      <a-sub-menu key="email">
        <template #icon><MailOutlined /></template>
        <template #title>Email & Communication</template>
        <a-menu-item key="email-templates">Email Templates</a-menu-item>
        <a-menu-item key="email-logs">Email Logs</a-menu-item>
        <a-menu-item key="email-campaigns">Campaigns</a-menu-item>
        <a-menu-item key="email-subscribers">Newsletter Subscribers</a-menu-item>
      </a-sub-menu>

      <!-- Divider -->
      <a-menu-divider />

      <!-- Group 5: Engagement -->
      <a-sub-menu key="reviews">
        <template #icon><StarOutlined /></template>
        <template #title>Reviews & Ratings</template>
        <a-menu-item key="reviews-all">All Reviews</a-menu-item>
        <a-menu-item key="reviews-pending">Pending Reviews</a-menu-item>
        <a-menu-item key="reviews-approved">Approved Reviews</a-menu-item>
        <a-menu-item key="reviews-replies">Review Replies</a-menu-item>
      </a-sub-menu>

      <a-sub-menu key="surveys">
        <template #icon><FileTextOutlined /></template>
        <template #title>Surveys</template>
        <a-menu-item key="surveys-all">All Surveys</a-menu-item>
        <a-menu-item key="surveys-responses">Survey Responses</a-menu-item>
        <a-menu-item key="surveys-create">Create Survey</a-menu-item>
      </a-sub-menu>

      <a-sub-menu key="certificates">
        <template #icon><TrophyFilled /></template>
        <template #title>Certificates</template>
        <a-menu-item key="certificates-all">All Certificates</a-menu-item>
        <a-menu-item key="certificates-templates">Templates</a-menu-item>
        <a-menu-item key="certificates-generate">Generate</a-menu-item>
      </a-sub-menu>

      <a-sub-menu key="notifications">
        <template #icon><BellOutlined /></template>
        <template #title>Notifications</template>
        <a-menu-item key="notifications-all">All Notifications</a-menu-item>
        <a-menu-item key="notifications-preferences">Preferences</a-menu-item>
        <a-menu-item key="notifications-custom">Custom Notifications</a-menu-item>
      </a-sub-menu>

      <!-- Divider -->
      <a-menu-divider />

      <!-- Group 6: Analytics -->
      <a-sub-menu key="analytics">
        <template #icon><BarChartOutlined /></template>
        <template #title>Analytics & Reports</template>
        <a-menu-item key="analytics-events">Event Analytics</a-menu-item>
        <a-menu-item key="analytics-sales">Sales Reports</a-menu-item>
        <a-menu-item key="analytics-users">User Reports</a-menu-item>
        <a-menu-item key="analytics-revenue">Revenue Reports</a-menu-item>
        <a-menu-item key="analytics-custom">Custom Reports</a-menu-item>
      </a-sub-menu>

      <!-- Divider -->
      <a-menu-divider />

      <!-- Group 7: Content -->
      <a-sub-menu key="content">
        <template #icon><FileOutlined /></template>
        <template #title>Content Management</template>
        <a-menu-item key="content-pages">All Pages</a-menu-item>
        <a-menu-item key="content-pages-create">Create Page</a-menu-item>
        <a-menu-item key="content-posts">Event Posts</a-menu-item>
        <a-menu-item key="content-comments">Post Comments</a-menu-item>
      </a-sub-menu>

      <!-- Divider -->
      <a-menu-divider />

      <!-- Group 8: System -->
      <a-sub-menu key="reports">
        <template #icon><FlagOutlined /></template>
        <template #title>Reports & Moderation</template>
        <a-menu-item key="reports-all">All Reports</a-menu-item>
        <a-menu-item key="reports-pending">Pending Reports</a-menu-item>
        <a-menu-item key="reports-resolved">Resolved Reports</a-menu-item>
      </a-sub-menu>

      <a-sub-menu key="settings">
        <template #icon><SettingOutlined /></template>
        <template #title>Settings</template>
        <a-menu-item key="settings-general">General Settings</a-menu-item>
        <a-menu-item key="settings-payment">Payment Settings</a-menu-item>
        <a-menu-item key="settings-email">Email Settings</a-menu-item>
        <a-menu-item key="settings-system">System Settings</a-menu-item>
        <a-menu-item key="settings-profile">Profile Settings</a-menu-item>
      </a-sub-menu>
    </a-menu>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import {
  DashboardOutlined,
  CalendarOutlined,
  IdcardOutlined,
  MoneyCollectOutlined,
  TeamOutlined,
  UserOutlined,
  UserAddOutlined,
  FolderOutlined,
  EnvironmentOutlined,
  StarOutlined,
  ShopOutlined,
  TagOutlined,
  MailOutlined,
  FileTextOutlined,
  TrophyFilled,
  BellOutlined,
  BarChartOutlined,
  FileOutlined,
  FlagOutlined,
  SettingOutlined,
  SafetyOutlined,
} from '@ant-design/icons-vue';

const props = defineProps({
  collapsed: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['toggle']);

const page = usePage();

const selectedKeys = ref(['dashboard']);
const openKeys = ref([]);

// Watch route changes
watch(() => page.url, (newUrl) => {
  // Extract key from URL
  const path = newUrl.split('/').pop() || 'dashboard';
  selectedKeys.value = [path];
}, { immediate: true });

const handleMenuClick = ({ key }) => {
  // Map menu keys to routes
  const routeMap = {
    'dashboard': '/dashboard',
    'events-all': '/dashboard/events',
    'events-create': '/dashboard/events/create',
    'events-draft': '/dashboard/events?status=draft',
    'events-published': '/dashboard/events?status=published',
    'events-cancelled': '/dashboard/events?status=cancelled',
    'events-tags': '/dashboard/events/tags',
    'roles-all': '/dashboard/roles',
    'roles-create': '/dashboard/roles/create',
    'permissions-all': '/dashboard/permissions',
    'permissions-assign': '/dashboard/permissions/assign',
    'role-user-assign': '/dashboard/roles/assign-users',
    // Add more route mappings as needed
  };

  const route = routeMap[key] || `/dashboard/${key.replace(/-/g, '/')}`;
  router.visit(route);
};
</script>

<style scoped>
.dashboard-sidebar {
  position: fixed;
  left: 0;
  top: 0;
  height: 100vh;
  width: 256px;
  background: var(--sidebar-bg, #141414);
  box-shadow: var(--shadow-base, 2px 0 8px rgba(0, 0, 0, 0.1));
  transition: width 0.2s, background-color 0.3s, box-shadow 0.3s;
  z-index: 1000;
  overflow-y: auto;
  overflow-x: hidden;
  border-right: 1px solid var(--sidebar-border, #2d2d2d);
}

[data-theme="light"] .dashboard-sidebar {
  background: #fff;
  border-right-color: #f0f0f0;
}

[data-theme="dark"] .dashboard-sidebar {
  background: #141414;
  border-right-color: #2d2d2d;
}

.dashboard-sidebar.collapsed {
  width: 80px;
}

.sidebar-logo {
  height: 64px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 16px;
  border-bottom: 1px solid var(--border-color-light, #f0f0f0);
  gap: 12px;
  transition: border-color 0.3s;
}

.logo-icon {
  width: 40px;
  height: 40px;
  background: linear-gradient(135deg, #1890ff 0%, #40a9ff 100%);
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 20px;
  font-weight: bold;
  flex-shrink: 0;
}

.logo-text {
  font-size: 18px;
  font-weight: 600;
  color: var(--text-primary, #262626);
  white-space: nowrap;
  transition: opacity 0.2s, color 0.3s;
}

[data-theme="dark"] .logo-text {
  color: rgba(255, 255, 255, 0.85);
}

.dashboard-sidebar.collapsed .logo-text {
  opacity: 0;
  width: 0;
  overflow: hidden;
}

.dashboard-menu {
  border-right: none;
  padding: 8px 0;
}

:deep(.ant-menu-item),
:deep(.ant-menu-submenu-title) {
  margin: 4px 8px !important;
  border-radius: 6px;
  height: 40px;
  line-height: 40px;
}

:deep(.ant-menu-item-selected) {
  background-color: #e6f7ff !important;
  color: #1890ff !important;
}

:deep(.ant-menu-item:hover),
:deep(.ant-menu-submenu-title:hover) {
  background-color: var(--bg-hover, #f5f5f5);
}

:deep(.ant-menu-submenu-title) {
  font-weight: 500;
}

:deep(.ant-menu-item-icon) {
  font-size: 16px;
}

/* Scrollbar styling */
.dashboard-sidebar::-webkit-scrollbar {
  width: 6px;
}

.dashboard-sidebar::-webkit-scrollbar-track {
  background: #f1f1f1;
}

[data-theme="dark"] .dashboard-sidebar::-webkit-scrollbar-track {
  background: #1a1a1a;
}

.dashboard-sidebar::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

[data-theme="dark"] .dashboard-sidebar::-webkit-scrollbar-thumb {
  background: #434343;
}

.dashboard-sidebar::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}

[data-theme="dark"] .dashboard-sidebar::-webkit-scrollbar-thumb:hover {
  background: #595959;
}
</style>

