<template>
  <header class="header-transparent sticky top-0 z-50 transition-all duration-300" :class="[
    isScrolled
      ? (isDark ? 'header-scrolled-dark' : 'header-scrolled-light')
      : ''
  ]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-20 gap-4">
        <!-- Logo -->
        <Link href="/" class="flex items-center gap-3 group">
          <div
            class="w-12 h-12 bg-gradient-to-br from-blue-600 to-purple-600 rounded-xl flex items-center justify-center text-white font-bold text-xl shadow-lg group-hover:scale-105 transition-transform duration-200">
            E
          </div>
          <span class="text-xl font-bold text-gray-900 hidden sm:block"
            :class="{ 'text-white': isDark }">EventHub</span>
        </Link>

        <!-- Navigation -->
        <nav class="hidden lg:flex items-center gap-1 flex-1 justify-center">
          <Link v-for="item in navItems" :key="item.key" :href="item.href" :class="[
            'px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200',
            selectedKeys.includes(item.key)
              ? (isDark ? 'bg-blue-900/30 text-blue-400' : 'bg-blue-100 text-blue-600')
              : (isDark ? 'text-gray-300 hover:bg-[#262626]' : 'text-gray-700 hover:bg-gray-100')
          ]">
            {{ item.label }}
          </Link>
        </nav>

        <!-- Actions -->
        <div class="flex items-center gap-3">
          <ThemeToggle />

          <!-- User Profile Dropdown (if logged in) -->
          <a-dropdown v-if="isAuthenticated" :trigger="['click']" class="user-dropdown">
            <div class="user-profile">
              <a-avatar :src="userAvatar" :size="32" class="user-avatar">
                {{ userInitials }}
              </a-avatar>
              <span class="user-name hidden sm:inline">{{ userName }}</span>
              <DownOutlined class="dropdown-icon" />
            </div>
            <template #overlay>
              <a-menu @click="handleMenuClick">
                <a-menu-item key="dashboard">
                  <DashboardOutlined /> Dashboard
                </a-menu-item>
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

          <!-- Login Button (if not logged in) -->
          <a-button v-else type="primary" class="hidden sm:block account-btn" @click="handleLogin">
            <template #icon>
              <UserOutlined />
            </template>
            <span>My Account</span>
          </a-button>

          <!-- Hamburger Menu Button - Only visible on mobile/tablet (below lg breakpoint) -->
          <a-button type="text" class="hamburger-btn" :class="[
            isDark ? 'hamburger-dark' : 'hamburger-light'
          ]" @click="mobileMenuOpen = !mobileMenuOpen">
            <MenuOutlined v-if="!mobileMenuOpen" class="text-lg hamburger-icon" />
            <CloseOutlined v-else class="text-lg hamburger-icon" />
          </a-button>
        </div>
      </div>
    </div>

    <!-- Mobile Menu -->
    <transition name="mobile-menu" enter-active-class="mobile-menu-enter-active"
      leave-active-class="mobile-menu-leave-active">
      <div v-if="mobileMenuOpen" class="mobile-menu lg:hidden border-t overflow-hidden mobile-menu-wrapper"
        :class="isDark ? 'mobile-menu-dark' : 'mobile-menu-light'">
        <div class="max-w-7xl mx-auto px-4 py-4 space-y-2">
          <Link v-for="item in navItems" :key="item.key" :href="item.href" :class="[
            'block px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200',
            selectedKeys.includes(item.key)
              ? (isDark ? 'bg-blue-900/30 text-blue-400' : 'bg-blue-100 text-blue-600')
              : (isDark ? 'text-gray-300 hover:bg-[#262626]' : 'text-gray-700 hover:bg-gray-100')
          ]" @click="mobileMenuOpen = false">
            {{ item.label }}
          </Link>
        </div>
      </div>
    </transition>
  </header>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import ThemeToggle from '../../Components/ThemeToggle.vue';
import { useTheme } from '../../Composables/useTheme';
import {
  UserOutlined,
  MenuOutlined,
  CloseOutlined,
  DownOutlined,
  DashboardOutlined,
  SettingOutlined,
  LogoutOutlined,
} from '@ant-design/icons-vue';

const page = usePage();
const { isDark } = useTheme();
const mobileMenuOpen = ref(false);
const isScrolled = ref(false);

// User data from Inertia
const isAuthenticated = computed(() => {
  return page.props.auth?.user !== null && page.props.auth?.user !== undefined;
});

const user = computed(() => {
  return page.props.auth?.user || null;
});

const userName = computed(() => {
  if (!user.value) return '';
  return user.value.name || `${user.value.first_name || ''} ${user.value.last_name || ''}`.trim() || 'User';
});

const userAvatar = computed(() => {
  if (!user.value) return null;
  return user.value.avatar || null;
});

const userInitials = computed(() => {
  if (!user.value) return 'U';
  const firstName = user.value.first_name || '';
  const lastName = user.value.last_name || '';

  if (firstName && lastName) {
    return `${firstName[0]}${lastName[0]}`.toUpperCase();
  }

  if (firstName) {
    return firstName[0].toUpperCase();
  }

  if (userName.value) {
    const names = userName.value.split(' ');
    if (names.length >= 2) {
      return `${names[0][0]}${names[names.length - 1][0]}`.toUpperCase();
    }
    return names[0][0].toUpperCase();
  }

  return 'U';
});

const handleScroll = () => {
  isScrolled.value = window.scrollY > 10;
};

// Throttle scroll handler for better performance
let scrollTimeout = null;
const throttledHandleScroll = () => {
  if (scrollTimeout) return;
  scrollTimeout = setTimeout(() => {
    handleScroll();
    scrollTimeout = null;
  }, 10);
};

onMounted(() => {
  window.addEventListener('scroll', throttledHandleScroll, { passive: true });
  handleScroll(); // Check initial scroll position
});

onUnmounted(() => {
  window.removeEventListener('scroll', throttledHandleScroll);
  if (scrollTimeout) {
    clearTimeout(scrollTimeout);
  }
});

const navItems = [
  { key: 'home', label: 'Home', href: '/' },
  { key: 'events', label: 'Events', href: '/events' },
  { key: 'categories', label: 'Categories', href: '/categories' },
  { key: 'about', label: 'About', href: '/about' },
  { key: 'contact', label: 'Contact', href: '/contact' },
];

const selectedKeys = computed(() => {
  const path = page.url;
  if (path === '/') return ['home'];
  if (path.startsWith('/events')) return ['events'];
  if (path.startsWith('/categories')) return ['categories'];
  if (path.startsWith('/about')) return ['about'];
  if (path.startsWith('/contact')) return ['contact'];
  return [];
});

const handleLogin = () => {
  router.visit('/login');
};

const handleMenuClick = ({ key }) => {
  switch (key) {
    case 'dashboard':
      router.visit('/dashboard');
      break;
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
/* My Account Button */
.account-btn {
  display: inline-flex !important;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.account-btn :deep(.anticon) {
  display: inline-flex !important;
  font-size: 16px;
}

/* Hamburger button - Only show on mobile/tablet (below lg breakpoint) */
.hamburger-btn {
  display: none;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
}

/* Hamburger icon colors */
.hamburger-dark .hamburger-icon {
  color: #ffffff !important;
}

.hamburger-dark:hover .hamburger-icon {
  color: #ffffff !important;
}

.hamburger-light .hamburger-icon {
  color: rgba(0, 0, 0, 0.85) !important;
}

.hamburger-light:hover .hamburger-icon {
  color: rgba(0, 0, 0, 1) !important;
}

/* Show hamburger on mobile/tablet */
@media (max-width: 1023px) {
  .hamburger-btn {
    display: flex !important;
  }
}

/* Hide hamburger on desktop (lg and above) */
@media (min-width: 1024px) {
  .hamburger-btn {
    display: none !important;
  }
}

/* Mobile Menu Dark Theme */
.mobile-menu-dark {
  background-color: #1f1f1f !important;
  border-color: #2d2d2d !important;
  color: rgba(255, 255, 255, 0.85) !important;
}

.mobile-menu-light {
  background-color: #ffffff !important;
  border-color: #e5e7eb !important;
  color: rgba(0, 0, 0, 0.85) !important;
}

/* Ensure mobile menu links have proper colors in dark theme */
.mobile-menu-dark a {
  color: rgba(255, 255, 255, 0.65) !important;
}

.mobile-menu-dark a:hover {
  color: rgba(255, 255, 255, 0.85) !important;
}

.mobile-menu-light a {
  color: rgba(0, 0, 0, 0.65) !important;
}

.mobile-menu-light a:hover {
  color: rgba(0, 0, 0, 0.85) !important;
}

/* User Profile Dropdown */
.user-dropdown {
  display: inline-block;
}

.user-profile {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 4px 12px;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
  border: 1px solid transparent;
}

.user-profile:hover {
  background-color: var(--bg-hover, rgba(0, 0, 0, 0.05));
}

[data-theme="dark"] .user-profile:hover {
  background-color: rgba(255, 255, 255, 0.1);
}

.user-avatar {
  flex-shrink: 0;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  font-weight: 600;
  font-size: 14px;
}

.user-name {
  font-size: 14px;
  font-weight: 500;
  color: var(--text-primary, #262626);
  white-space: nowrap;
  max-width: 120px;
  overflow: hidden;
  text-overflow: ellipsis;
}

[data-theme="dark"] .user-name {
  color: rgba(255, 255, 255, 0.85);
}

.dropdown-icon {
  font-size: 12px;
  color: var(--text-secondary, #8c8c8c);
  transition: transform 0.2s;
}

[data-theme="dark"] .dropdown-icon {
  color: rgba(255, 255, 255, 0.65);
}

.user-dropdown :deep(.ant-dropdown-open) .dropdown-icon {
  transform: rotate(180deg);
}

/* User Menu Styles */
.user-dropdown :deep(.ant-dropdown-menu) {
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  padding: 4px;
  min-width: 180px;
}

[data-theme="dark"] .user-dropdown :deep(.ant-dropdown-menu) {
  background-color: #1f1f1f;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.user-dropdown :deep(.ant-menu-item) {
  border-radius: 6px;
  margin: 2px 0;
  padding: 8px 12px;
  height: auto;
  line-height: 1.5;
}

.user-dropdown :deep(.ant-menu-item:hover) {
  background-color: var(--bg-hover, #f5f5f5);
}

[data-theme="dark"] .user-dropdown :deep(.ant-menu-item:hover) {
  background-color: rgba(255, 255, 255, 0.1);
}

.user-dropdown :deep(.ant-menu-item-icon) {
  margin-right: 8px;
  font-size: 16px;
}

/* Responsive */
@media (max-width: 640px) {
  .user-name {
    display: none;
  }

  .user-profile {
    padding: 4px 8px;
    gap: 6px;
  }
}
</style>
