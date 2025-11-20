<template>
  <div class="dashboard-sidebar" :class="{ collapsed: collapsed, 'mobile-open': mobileOpen, 'mobile-closed': !mobileOpen }">
    <!-- Logo - Sticky -->
    <div class="sidebar-logo sticky-logo">
      <div class="logo-icon">E</div>
      <span v-if="!collapsed" class="logo-text">EventHub</span>
    </div>

    <!-- Menu -->
    <div class="menu-container">
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
        <a-menu-item key="event-tags-all">Event Tags</a-menu-item>
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

      <a-menu-item key="speakers">
        <template #icon><UserOutlined /></template>
        <span>Speakers</span>
      </a-menu-item>

      <!-- Divider -->
      <a-menu-divider />

      <!-- Group 3: Resources -->
      <a-menu-item key="categories">
        <template #icon><FolderOutlined /></template>
        <span>Categories</span>
      </a-menu-item>

      <a-sub-menu key="teams">
        <template #icon><TeamOutlined /></template>
        <template #title>Teams</template>
        <a-menu-item key="teams-all">My Teams</a-menu-item>
        <a-menu-item key="teams-members">Team Members</a-menu-item>
        <a-menu-item key="teams-invitations">Team Invitations</a-menu-item>
      </a-sub-menu>

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
        <a-menu-item key="promo-codes-all">All Promo Codes</a-menu-item>
        <a-menu-item key="promo-codes-create">Create Promo Code</a-menu-item>
        <a-menu-item key="promo-codes-active">Active Promo Codes</a-menu-item>
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

      <a-menu-item key="reviews">
        <template #icon><StarOutlined /></template>
        <span>Reviews & Ratings</span>
      </a-menu-item>

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
  mobileOpen: {
    type: Boolean,
    default: true,
  },
});

const emit = defineEmits(['toggle']);

const page = usePage();

const selectedKeys = ref(['dashboard']);
const openKeys = ref([]);

// Map URL to menu key
const getMenuKeyFromUrl = (url) => {
  // Remove query parameters
  const path = url.split('?')[0];
  
  // Create reverse route map
  const urlToKeyMap = {
    '/dashboard': 'dashboard',
    '/dashboard/events': 'events-all',
    '/dashboard/events/create': 'events-create',
    '/dashboard/event-tags': 'event-tags-all',
    '/dashboard/promo-codes': 'promo-codes-all',
    '/dashboard/promo-codes/create': 'promo-codes-create',
    '/dashboard/categories': 'categories',
    '/dashboard/teams': 'teams-all',
    '/dashboard/teams/create': 'teams-create',
    '/dashboard/team-members': 'teams-members',
    '/dashboard/team-invitations': 'teams-invitations',
    '/dashboard/roles': 'roles-all',
    '/dashboard/roles/create': 'roles-create',
    '/dashboard/permissions': 'permissions-all',
    '/dashboard/permissions/assign': 'permissions-assign',
    '/dashboard/roles/assign-users': 'role-user-assign',
    '/dashboard/settings/general': 'settings-general',
    '/dashboard/settings/payment': 'settings-payment',
    '/dashboard/settings/email': 'settings-email',
    '/dashboard/settings/system': 'settings-system',
    '/dashboard/settings/recycle-bin': 'settings-recycle-bin',
    '/dashboard/settings/profile': 'settings-profile',
    '/dashboard/speakers': 'speakers',
    '/dashboard/vendors': 'vendors-all',
    '/dashboard/vendors/create': 'vendors-create',
    '/dashboard/venues': 'venues-all',
    '/dashboard/venues/create': 'venues-create',
    '/dashboard/sponsors': 'sponsors-all',
    '/dashboard/sponsors/create': 'sponsors-create',
    '/dashboard/surveys': 'surveys-all',
    '/dashboard/surveys/create': 'surveys-create',
  };

  // Check exact match first
  if (urlToKeyMap[path]) {
    return urlToKeyMap[path];
  }

  // Check for events routes
  if (path.startsWith('/dashboard/events')) {
    if (path === '/dashboard/events' || path === '/dashboard/events/search') {
      const urlParams = new URLSearchParams(url.split('?')[1] || '');
      const status = urlParams.get('status');
      if (status) {
        return `events-${status}`;
      }
      return 'events-all';
    }
    if (path === '/dashboard/events/create') {
      return 'events-create';
    }
    // For edit/show pages, still show as events-all
    return 'events-all';
  }

  // Check for team invitations routes
  if (path.startsWith('/dashboard/team-invitations')) {
    return 'teams-invitations';
  }

  // Check for team members routes
  if (path.startsWith('/dashboard/team-members')) {
    return 'teams-members';
  }

  // Check for teams routes
  if (path.startsWith('/dashboard/teams')) {
    if (path === '/dashboard/teams' || path === '/dashboard/teams/search') {
      return 'teams-all';
    }
    if (path === '/dashboard/teams/create') {
      return 'teams-create';
    }
    return 'teams-all';
  }

  // Check for categories routes
  if (path.startsWith('/dashboard/categories')) {
    return 'categories';
  }

  // Check for event tags routes
  if (path.startsWith('/dashboard/event-tags')) {
    return 'event-tags-all';
  }

  // Check for promo codes routes
  if (path.startsWith('/dashboard/promo-codes')) {
    if (path === '/dashboard/promo-codes' || path === '/dashboard/promo-codes/search') {
      const urlParams = new URLSearchParams(url.split('?')[1] || '');
      const isActive = urlParams.get('is_active');
      if (isActive === '1') {
        return 'promo-codes-active';
      }
      return 'promo-codes-all';
    }
    if (path === '/dashboard/promo-codes/create') {
      return 'promo-codes-create';
    }
    return 'promo-codes-all';
  }

  // Check for speakers routes
  if (path.startsWith('/dashboard/speakers')) {
    return 'speakers';
  }

  // Check for vendors routes
  if (path.startsWith('/dashboard/vendors')) {
    if (path === '/dashboard/vendors' || path === '/dashboard/vendors/search') {
      return 'vendors-all';
    }
    if (path === '/dashboard/vendors/create') {
      return 'vendors-create';
    }
    return 'vendors-all';
  }

  // Check for venues routes
  if (path.startsWith('/dashboard/venues')) {
    if (path === '/dashboard/venues' || path === '/dashboard/venues/search') {
      return 'venues-all';
    }
    if (path === '/dashboard/venues/create') {
      return 'venues-create';
    }
    return 'venues-all';
  }

  // Check for sponsors routes
  if (path.startsWith('/dashboard/sponsors')) {
    if (path === '/dashboard/sponsors' || path === '/dashboard/sponsors/search') {
      return 'sponsors-all';
    }
    if (path === '/dashboard/sponsors/create') {
      return 'sponsors-create';
    }
    return 'sponsors-all';
  }

  // Check for surveys routes
  if (path.startsWith('/dashboard/surveys')) {
    if (path === '/dashboard/surveys' || path === '/dashboard/surveys/search') {
      return 'surveys-all';
    }
    if (path === '/dashboard/surveys/create') {
      return 'surveys-create';
    }
    return 'surveys-all';
  }

  // Fallback: try to extract from path
  const parts = path.split('/').filter(p => p && p !== 'dashboard');
  if (parts.length > 0) {
    return parts.join('-');
  }

  return 'dashboard';
};

// Get parent menu key for sub-menus
const getParentKey = (key) => {
  const parentMap = {
    'events-all': 'events',
    'events-create': 'events',
    'events-draft': 'events',
    'events-published': 'events',
    'events-cancelled': 'events',
    'event-tags-all': 'events',
    'promo-codes-all': 'promo-codes',
    'promo-codes-create': 'promo-codes',
    'promo-codes-active': 'promo-codes',
    'teams-all': 'teams',
    'teams-members': 'teams',
    'teams-invitations': 'teams',
    'teams-create': 'teams',
    'settings-general': 'settings',
    'settings-payment': 'settings',
    'settings-email': 'settings',
    'settings-system': 'settings',
    'settings-recycle-bin': 'settings',
    'settings-profile': 'settings',
    'roles-all': 'roles-permissions',
    'roles-create': 'roles-permissions',
    'permissions-all': 'roles-permissions',
    'permissions-assign': 'roles-permissions',
    'role-user-assign': 'roles-permissions',
    'vendors-all': 'vendors',
    'vendors-create': 'vendors',
    'vendors-by-category': 'vendors',
    'vendors-event': 'vendors',
    'venues-all': 'venues',
    'venues-create': 'venues',
    'venues-verified': 'venues',
    'sponsors-all': 'sponsors',
    'sponsors-create': 'sponsors',
    'sponsors-platinum': 'sponsors',
    'sponsors-gold': 'sponsors',
    'sponsors-silver': 'sponsors',
    'sponsors-bronze': 'sponsors',
    'surveys-all': 'surveys',
    'surveys-create': 'surveys',
    'surveys-responses': 'surveys',
  };
  return parentMap[key] || null;
};

// Watch route changes
watch(() => page.url, (newUrl) => {
  const menuKey = getMenuKeyFromUrl(newUrl);
  selectedKeys.value = [menuKey];
  
  // Open parent sub-menu if child is selected
  const parentKey = getParentKey(menuKey);
  if (parentKey && !openKeys.value.includes(parentKey)) {
    openKeys.value = [...openKeys.value, parentKey];
  }
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
    'event-tags-all': '/dashboard/event-tags',
    'promo-codes-all': '/dashboard/promo-codes',
    'promo-codes-create': '/dashboard/promo-codes/create',
    'promo-codes-active': '/dashboard/promo-codes?is_active=1',
    'categories': '/dashboard/categories',
    'teams-all': '/dashboard/teams',
    'teams-members': '/dashboard/team-members',
    'teams-invitations': '/dashboard/team-invitations',
    'teams-create': '/dashboard/teams/create',
    'roles-all': '/dashboard/roles',
    'roles-create': '/dashboard/roles/create',
    'permissions-all': '/dashboard/permissions',
    'permissions-assign': '/dashboard/permissions/assign',
    'role-user-assign': '/dashboard/roles/assign-users',
    'settings-general': '/dashboard/settings/general',
    'settings-system': '/dashboard/settings/system',
    'settings-recycle-bin': '/dashboard/settings/recycle-bin',
    'speakers': '/dashboard/speakers',
    'vendors-all': '/dashboard/vendors',
    'vendors-create': '/dashboard/vendors/create',
    'vendors-by-category': '/dashboard/vendors?filter=category',
    'vendors-event': '/dashboard/vendors?filter=event',
    'venues-all': '/dashboard/venues',
    'venues-create': '/dashboard/venues/create',
    'venues-verified': '/dashboard/venues?is_verified=1',
    'sponsors-all': '/dashboard/sponsors',
    'sponsors-create': '/dashboard/sponsors/create',
    'sponsors-platinum': '/dashboard/sponsors?tier=platinum',
    'sponsors-gold': '/dashboard/sponsors?tier=gold',
    'sponsors-silver': '/dashboard/sponsors?tier=silver',
    'sponsors-bronze': '/dashboard/sponsors?tier=bronze',
    'surveys-all': '/dashboard/surveys',
    'surveys-create': '/dashboard/surveys/create',
    'surveys-responses': '/dashboard/surveys?filter=responses',
    'reviews': '/dashboard/reviews',
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
  transition: width 0.2s ease-in-out, background-color 0.1s ease-out, box-shadow 0.1s ease-out;
  z-index: 1000;
  overflow: hidden;
  border-right: 1px solid var(--sidebar-border, #2d2d2d);
  display: flex;
  flex-direction: column;
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

/* Tablet Styles */
@media (max-width: 1024px) {
  .dashboard-sidebar {
    width: 240px;
  }

  .dashboard-sidebar.collapsed {
    width: 80px;
  }
}

/* Mobile Styles */
@media (max-width: 768px) {
  .dashboard-sidebar {
    transform: translateX(-100%);
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    z-index: 1000;
    width: 280px;
  }

  .dashboard-sidebar.mobile-open {
    transform: translateX(0);
  }

  .dashboard-sidebar.mobile-closed {
    transform: translateX(-100%);
  }

  .dashboard-sidebar.collapsed {
    width: 280px; /* Full width on mobile when open */
  }

  .sidebar-logo {
    height: 56px;
    padding: 12px;
  }

  .logo-icon {
    width: 36px;
    height: 36px;
    font-size: 18px;
  }

  .logo-text {
    font-size: 16px;
  }

  .dashboard-menu {
    padding: 4px 0;
  }

  :deep(.ant-menu-item),
  :deep(.ant-menu-submenu-title) {
    margin: 2px 4px !important;
    height: 36px;
    line-height: 36px;
    font-size: 14px;
  }

  :deep(.ant-menu-item-icon) {
    font-size: 14px;
  }
}

@media (max-width: 480px) {
  .dashboard-sidebar {
    width: 260px;
  }

  .dashboard-sidebar.collapsed {
    width: 260px;
  }

  .sidebar-logo {
    height: 52px;
    padding: 10px;
  }

  .logo-icon {
    width: 32px;
    height: 32px;
    font-size: 16px;
  }

  .logo-text {
    font-size: 14px;
  }

  :deep(.ant-menu-item),
  :deep(.ant-menu-submenu-title) {
    height: 32px;
    line-height: 32px;
    font-size: 13px;
  }
}

.sidebar-logo {
  height: 64px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 16px;
  border-bottom: 1px solid var(--border-color-light, #f0f0f0);
  gap: 12px;
  transition: border-color 0.1s ease-out;
}

.sticky-logo {
  position: sticky;
  top: 0;
  z-index: 10;
  background: var(--sidebar-bg, #141414);
  border-bottom: 1px solid var(--border-color-light, #f0f0f0);
}

[data-theme="light"] .sticky-logo {
  background: #fff;
  border-bottom-color: #f0f0f0;
}

[data-theme="dark"] .sticky-logo {
  background: #141414;
  border-bottom-color: #2d2d2d;
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
  transition: opacity 0.1s ease-out, color 0.1s ease-out;
}

[data-theme="dark"] .logo-text {
  color: rgba(255, 255, 255, 0.85);
}

.dashboard-sidebar.collapsed .logo-text {
  opacity: 0;
  width: 0;
  overflow: hidden;
}

.menu-container {
  flex: 1;
  overflow-y: auto;
  overflow-x: hidden;
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

/* Menu styles - Using global styles from antd-theme.css */
/* Component-specific overrides only */
:deep(.ant-menu-item-selected::after) {
  display: none !important;
}

/* Light theme active state - sidebar specific */
:deep(.ant-menu-item-selected) {
  background-color: #e6f7ff !important;
  color: #1890ff !important;
}

/* Light theme hover state - sidebar specific */
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

