<template>
  <div class="min-h-screen bg-gray-50 transition-colors duration-200" :class="{ 'bg-[#141414]': isDark }">
    <!-- Header -->
    <FrontendHeader />

    <!-- Main Content -->
    <main class="flex-1">
      <slot />
    </main>

    <!-- Notifications -->
    <NotificationContainer />

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
import { Link } from '@inertiajs/vue3';
import FrontendHeader from '../Pages/Frontend/Header.vue';
import NotificationContainer from '../Components/NotificationContainer.vue';
import { useTheme } from '../Composables/useTheme';
import {
  FacebookOutlined,
  TwitterOutlined,
  InstagramOutlined,
  LinkedinOutlined,
} from '@ant-design/icons-vue';

const { isDark } = useTheme();
const newsletterEmail = ref('');
const currentYear = computed(() => new Date().getFullYear());

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
</script>

<style scoped>
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
