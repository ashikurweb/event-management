import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { router } from '@inertiajs/vue3'
import { createPinia } from 'pinia'
import Antd from 'ant-design-vue';
import { ConfigProvider } from 'ant-design-vue';
import { antdTheme } from './Theme/antd-theme';
import { useThemeStore } from './Stores/theme';
import 'ant-design-vue/dist/reset.css';
import NProgress from 'nprogress';
import 'nprogress/nprogress.css';

// Initialize Pinia
const pinia = createPinia();

// Initialize theme on page load (before app creation)
const initializeTheme = () => {
    const savedTheme = localStorage.getItem('theme');
    const systemPrefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    const theme = savedTheme || (systemPrefersDark ? 'dark' : 'light');
    document.documentElement.setAttribute('data-theme', theme);
};

// Initialize theme immediately
initializeTheme();

// Configure NProgress (for progress bar)
NProgress.configure({
  showSpinner: true,
  trickleSpeed: 200,
  minimum: 0.08,
  easing: 'ease',
  speed: 500,
});

createInertiaApp({
  resolve: name => {
    const pages = import.meta.glob('./Pages/**/*.vue', { eager: true })
    return pages[`./Pages/${name}.vue`]
  },
  setup({ el, App, props, plugin }) {
    const app = createApp({
      render: () => h(ConfigProvider, { theme: antdTheme }, {
        default: () => h(App, props)
      })
    });
    
    app.use(plugin);
    app.use(pinia);
    app.use(Antd);
    
    // Initialize theme store after Pinia is set up
    const themeStore = useThemeStore();
    themeStore.initializeTheme();
    
    // Setup Inertia progress bar using router events
    router.on('start', () => {
      NProgress.start();
    });
    
    router.on('progress', (event) => {
      if (event.detail?.progress?.percentage) {
        NProgress.set(event.detail.progress.percentage / 100);
      }
    });
    
    router.on('finish', () => {
      NProgress.done();
    });
    
    app.mount(el);
  },
})
