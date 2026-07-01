import '../css/app.css';

import { createInertiaApp } from '@inertiajs/vue3';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { Toaster } from 'vue-sonner';
import { useTheme } from '@/composables/useTheme';

useTheme().initialize();

createInertiaApp({
    title: (title) => (title ? `${title} - PeopleOps` : 'PeopleOps'),
    resolve: async (name) => {
        const pages = import.meta.glob('./Pages/**/*.vue');
        const page = pages[`./Pages/${name}.vue`];

        if (!page) {
            throw new Error(`Page not found: ${name}`);
        }

        return page() as Promise<DefineComponent>;
    },
    setup({ el, App, props, plugin }) {
        createApp({
            render: () => h('div', [h(App, props), h(Toaster, { richColors: true, position: 'top-right' })]),
        })
            .use(plugin)
            .mount(el);
    },
    progress: {
        color: '#6d28d9',
    },
});
