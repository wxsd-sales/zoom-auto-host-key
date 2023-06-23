import { createSSRApp, h, type DefineComponent } from 'vue';
import { renderToString } from '@vue/server-renderer';
import { createInertiaApp } from '@inertiajs/vue3';
import createServer from '@inertiajs/vue3/server';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy/dist/vue.m';

const appName = 'Laravel';

createServer(
  async (page) =>
    await createInertiaApp({
      page,
      render: renderToString,
      id: 'main',
      title: (title) => `${title} - ${appName}`,
      resolve: async (name) =>
        await resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob<DefineComponent>('./Pages/**/*.vue')),
      setup({ App, props, plugin }) {
        return createSSRApp({ render: () => h(App, props) })
          .use(plugin)
          .use(ZiggyVue, {
            // eslint-disable-next-line @typescript-eslint/ban-ts-comment
            // @ts-expect-error
            ...page.props.ziggy,
            // eslint-disable-next-line @typescript-eslint/ban-ts-comment
            // @ts-expect-error
            location: new URL(page.props.ziggy.location)
          });
      }
    })
);
