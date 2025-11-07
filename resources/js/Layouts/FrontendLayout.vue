<template>
  <div class="min-h-screen bg-gray-50 transition-colors duration-200" :class="{ 'bg-[#141414]': isDark }">
    <!-- Header -->
    <header class="header-glass sticky top-0 z-50 shadow-sm">
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
              class="hidden sm:block !flex items-center"
              @click="handleLogin"
            >
              <span>Login</span>
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

    <!-- Main Content -->
    <main class="flex-1">
      <slot />
    </main>

    <!-- Footer -->
    <footer class="border-t mt-20" :class="isDark ? 'bg-[#1f1f1f] border-[#2d2d2d]' : 'bg-white border-gray-200'">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-8">
          <!-- About -->
          <div>
            <h3 class="text-lg font-semibold mb-4" :class="isDark ? 'text-white' : 'text-gray-900'">About EventHub</h3>
            <p class="text-sm mb-4 leading-relaxed" :class="isDark ? 'text-gray-400' : 'text-gray-600'">
              Your premier destination for discovering and managing events.
              Connect, learn, and celebrate with thousands of events worldwide.
            </p>
            <div class="flex gap-3">
              <a
                v-for="social in socialLinks"
                :key="social.name"
                :href="social.href"
                :class="[
                  'w-10 h-10 rounded-lg flex items-center justify-center transition-all duration-200',
                  isDark 
                    ? 'bg-[#262626] text-gray-400 hover:bg-blue-900/30 hover:text-blue-400'
                    : 'bg-gray-100 text-gray-600 hover:bg-blue-100 hover:text-blue-600'
                ]"
              >
                <component :is="social.icon" />
              </a>
            </div>
          </div>

          <!-- Quick Links -->
          <div>
            <h3 class="text-lg font-semibold mb-4" :class="isDark ? 'text-white' : 'text-gray-900'">Quick Links</h3>
            <ul class="space-y-2">
              <li v-for="link in quickLinks" :key="link.href">
                <Link
                  :href="link.href"
                  :class="[
                    'text-sm transition-colors duration-200',
                    isDark 
                      ? 'text-gray-400 hover:text-blue-400'
                      : 'text-gray-600 hover:text-blue-600'
                  ]"
                >
                  {{ link.label }}
                </Link>
              </li>
            </ul>
          </div>

          <!-- Support -->
          <div>
            <h3 class="text-lg font-semibold mb-4" :class="isDark ? 'text-white' : 'text-gray-900'">Support</h3>
            <ul class="space-y-2">
              <li v-for="link in supportLinks" :key="link.href">
                <a
                  :href="link.href"
                  :class="[
                    'text-sm transition-colors duration-200',
                    isDark 
                      ? 'text-gray-400 hover:text-blue-400'
                      : 'text-gray-600 hover:text-blue-600'
                  ]"
                >
                  {{ link.label }}
                </a>
              </li>
            </ul>
          </div>

          <!-- Newsletter -->
          <div>
            <h3 class="text-lg font-semibold mb-4" :class="isDark ? 'text-white' : 'text-gray-900'">Newsletter</h3>
            <p class="text-sm mb-4 leading-relaxed" :class="isDark ? 'text-gray-400' : 'text-gray-600'">
              Subscribe to get updates on upcoming events and exclusive offers.
            </p>
            <div class="newsletter-form">
              <a-input-group compact class="newsletter-input-group">
                <a-input
                  v-model:value="newsletterEmail"
                  placeholder="Your email"
                  class="newsletter-input"
                  :class="isDark ? 'newsletter-input-dark' : 'newsletter-input-light'"
                  size="large"
                />
                <a-button type="primary" size="large" class="newsletter-button">
                  Subscribe
                </a-button>
              </a-input-group>
            </div>
          </div>
        </div>

        <div class="pt-8 border-t text-center" :class="isDark ? 'border-[#2d2d2d]' : 'border-gray-200'">
          <p class="text-sm" :class="isDark ? 'text-gray-400' : 'text-gray-600'">
            &copy; {{ currentYear }} EventHub. All rights reserved.
          </p>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import ThemeToggle from '../components/ThemeToggle.vue';
import { useTheme } from '../composables/useTheme';
import {
  MenuOutlined,
  CloseOutlined,
  FacebookOutlined,
  TwitterOutlined,
  InstagramOutlined,
  LinkedinOutlined,
} from '@ant-design/icons-vue';

const page = usePage();
const { isDark } = useTheme();
const mobileMenuOpen = ref(false);
const newsletterEmail = ref('');
const currentYear = computed(() => new Date().getFullYear());

const navItems = [
  { key: 'home', label: 'Home', href: '/' },
  { key: 'events', label: 'Events', href: '/events' },
  { key: 'categories', label: 'Categories', href: '/categories' },
  { key: 'about', label: 'About', href: '/about' },
  { key: 'contact', label: 'Contact', href: '/contact' },
];

const socialLinks = [
  { name: 'Facebook', icon: FacebookOutlined, href: '#' },
  { name: 'Twitter', icon: TwitterOutlined, href: '#' },
  { name: 'Instagram', icon: InstagramOutlined, href: '#' },
  { name: 'LinkedIn', icon: LinkedinOutlined, href: '#' },
];

const quickLinks = [
  { label: 'Browse Events', href: '/events' },
  { label: 'Categories', href: '/categories' },
  { label: 'About Us', href: '/about' },
  { label: 'Contact', href: '/contact' },
];

const supportLinks = [
  { label: 'Help Center', href: '#' },
  { label: 'FAQs', href: '#' },
  { label: 'Terms of Service', href: '#' },
  { label: 'Privacy Policy', href: '#' },
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
/* Remove any default icon from Login button */
:deep(.ant-btn-primary) {
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

:deep(.ant-btn-primary .anticon) {
  display: none !important;
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

/* Footer Newsletter Form */
.newsletter-form {
  width: 100%;
}

.newsletter-input-group {
  display: flex;
  width: 100%;
  border-radius: 8px;
  overflow: hidden;
}

.newsletter-input-group .ant-input-group {
  display: flex;
  width: 100%;
}

.newsletter-input {
  flex: 1;
  border-radius: 8px 0 0 8px !important;
  transition: all 0.3s ease;
}

.newsletter-button {
  border-radius: 0 8px 8px 0 !important;
  min-width: 120px;
  font-weight: 500;
}

/* Newsletter Input Light Theme */
.newsletter-input-light {
  background-color: #ffffff !important;
  border-color: #d9d9d9 !important;
  color: rgba(0, 0, 0, 0.85) !important;
}

.newsletter-input-light::placeholder {
  color: rgba(0, 0, 0, 0.25) !important;
}

.newsletter-input-light:hover {
  border-color: #40a9ff !important;
}

.newsletter-input-light:focus,
.newsletter-input-light.ant-input-focused {
  border-color: #40a9ff !important;
  box-shadow: 0 0 0 2px rgba(24, 144, 255, 0.2) !important;
}

/* Newsletter Input Dark Theme */
.newsletter-input-dark {
  background-color: #262626 !important;
  border-color: #434343 !important;
  color: rgba(255, 255, 255, 0.85) !important;
}

.newsletter-input-dark::placeholder {
  color: rgba(255, 255, 255, 0.25) !important;
}

.newsletter-input-dark:hover {
  border-color: #595959 !important;
}

.newsletter-input-dark:focus,
.newsletter-input-dark.ant-input-focused {
  border-color: #40a9ff !important;
  box-shadow: 0 0 0 2px rgba(64, 169, 255, 0.2) !important;
}

/* Newsletter Input Group Dark Theme */
[data-theme="dark"] .newsletter-input-group .ant-input-group-addon {
  background-color: transparent !important;
  border-color: #434343 !important;
}

[data-theme="dark"] .newsletter-input-group .ant-input {
  border-right: none !important;
}

[data-theme="dark"] .newsletter-input-group .ant-btn {
  border-left: 1px solid #434343 !important;
}
</style>
