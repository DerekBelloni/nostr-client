import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { createPinia } from 'pinia';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import PrimeVue from 'primevue/config';
import Button from 'primevue/button';
import ProgressSpinner from 'primevue/progressspinner';
import InputText from 'primevue/inputtext';
import FloatLabel from 'primevue/floatlabel';
import Textarea from 'primevue/textarea';
import Toast from 'primevue/toast';
import ToastService from 'primevue/toastservice';
import Lodash from 'lodash';

// import 'primevue/resources/themes/aura-dark-amber/theme.css';
import 'primevue/resources/themes/aura-light-amber/theme.css';
import 'primeicons/primeicons.css';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';
const pinia = createPinia();

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(pinia)
            .use(plugin)
            .use(PrimeVue)
            .use(ZiggyVue)
            .use(Lodash)
            .use(ToastService)
            .component('Button', Button)
            .component('InputText', InputText)
            .component('FloatLabel', FloatLabel)
            .component('ProgressSpinner', ProgressSpinner)
            .component('Textarea', Textarea)
            .component('Toast', Toast)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
