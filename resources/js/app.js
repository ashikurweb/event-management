import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { createPinia } from 'pinia'
import Antd from 'ant-design-vue';
import { ConfigProvider } from 'ant-design-vue';
import { antdTheme } from './theme/antd-theme';
import { useThemeStore } from './stores/theme';
import 'ant-design-vue/dist/reset.css';

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
    
    app.mount(el);
  },
})
