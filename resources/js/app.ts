import '../css/app.css';
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';

createInertiaApp({
    title: (title) => title ? `${title} - Doyen Auto Services` : 'Doyen Auto Services',
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue')
        ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });
        app.use(plugin);

        // Global $route helper to prefix URLs with the app base path
        // Handles XAMPP subdirectory deployments (/garage/garage/public)
        const basePath: string = (props.initialPage.props.appBasePath as string) || '';
        const routeFn = (path: string): string => {
            if (!path || !path.startsWith('/')) return path;
            return basePath + path;
        };
        app.config.globalProperties.$route = routeFn;
        app.provide('route', routeFn);

        app.mount(el);
    },
    progress: {
        color: '#3b82f6',
        showSpinner: true,
    },
});
