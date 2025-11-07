<template>
  <header 
    class="header-transparent sticky top-0 z-50 transition-all duration-300"
    :class="[
      isScrolled 
        ? (isDark ? 'header-scrolled-dark' : 'header-scrolled-light')
        : ''
    ]"
  >
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-20 gap-4">
        <!-- Logo -->
        <Link href="/" class="flex items-center gap-3 group">
          <div class="w-12 h-12 bg-gradient-to-br from-blue-600 to-purple-600 rounded-xl flex items-center justify-center text-white font-bold text-xl shadow-lg group-hover:scale-105 transition-transform duration-200">
            E
          </div>
          <span class="text-xl font-bold text-gray-900 hidden sm:block" :class="{ 'text-white': isDark }">EventHub</span>
        </Link>

        <!-- Navigation -->
        <nav class="hidden lg:flex items-center gap-1 flex-1 justify-center">
          <Link
            v-for="item in navItems"
            :key="item.key"
            :href="item.href"
            :class="[
              'px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200',
              selectedKeys.includes(item.key)
                ? (isDark ? 'bg-blue-900/30 text-blue-400' : 'bg-blue-100 text-blue-600')
                : (isDark ? 'text-gray-300 hover:bg-[#262626]' : 'text-gray-700 hover:bg-gray-100')
            ]"
          >
            {{ item.label }}
          </Link>
        </nav>

        <!-- Actions -->
        <div class="flex items-center gap-3">
          <ThemeToggle />
          <a-button
            type="primary"
            class="hidden sm:block account-btn"
            @click="handleLogin"
          >
            <template #icon><UserOutlined /></template>
            <span>My Account</span>
          </a-button>
          <!-- Hamburger Menu Button - Only visible on mobile/tablet (below lg breakpoint) -->
          <a-button
            type="text"
            class="hamburger-btn"
            :class="[
              isDark ? 'hamburger-dark' : 'hamburger-light'
            ]"
            @click="mobileMenuOpen = !mobileMenuOpen"
          >
            <MenuOutlined v-if="!mobileMenuOpen" class="text-lg hamburger-icon" />
            <CloseOutlined v-else class="text-lg hamburger-icon" />
          </a-button>
        </div>
      </div>
    </div>

    <!-- Mobile Menu -->
    <transition
      name="mobile-menu"
      enter-active-class="mobile-menu-enter-active"
      leave-active-class="mobile-menu-leave-active"
    >
      <div
        v-if="mobileMenuOpen"
        class="mobile-menu lg:hidden border-t overflow-hidden mobile-menu-wrapper"
        :class="isDark ? 'mobile-menu-dark' : 'mobile-menu-light'"
      >
        <div class="max-w-7xl mx-auto px-4 py-4 space-y-2">
          <Link
            v-for="item in navItems"
            :key="item.key"
            :href="item.href"
            :class="[
              'block px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200',
              selectedKeys.includes(item.key)
                ? (isDark ? 'bg-blue-900/30 text-blue-400' : 'bg-blue-100 text-blue-600')
                : (isDark ? 'text-gray-300 hover:bg-[#262626]' : 'text-gray-700 hover:bg-gray-100')
            ]"
            @click="mobileMenuOpen = false"
          >
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
} from '@ant-design/icons-vue';

const page = usePage();
const { isDark } = useTheme();
const mobileMenuOpen = ref(false);
const isScrolled = ref(false);

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
</style>

