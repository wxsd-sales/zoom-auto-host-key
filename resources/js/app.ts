import './bootstrap';

import { createApp, h, type DefineComponent } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy/dist/vue.m';

const appName = window.document.getElementsByTagName('title')[0]?.innerText ?? 'Laravel';

void createInertiaApp({
  title: (title) => `${title} - ${appName}`,
  resolve: async (name) =>
    await resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob<DefineComponent>('./Pages/**/*.vue')),
  setup({ el, App, props, plugin }) {
    createApp({ render: () => h(App, props) })
      .use(plugin)
      .use(ZiggyVue, Ziggy)
      .mount(el);
  },
  progress: {
    color: '#455cc6'
  }
});
