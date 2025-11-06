import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import Antd from 'ant-design-vue';
import { ConfigProvider } from 'ant-design-vue';
import { antdTheme } from './theme/antd-theme';
import 'ant-design-vue/dist/reset.css';

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
    app.use(Antd);
    app.mount(el);
  },
})
