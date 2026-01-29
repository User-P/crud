import "./bootstrap";
import "../css/app.css";

import { createApp, h, DefineComponent } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";
import PrimeVue from "primevue/config";
import Aura from "@primeuix/themes/aura";
import "primeicons/primeicons.css";
import { createPinia } from "pinia";

interface PageModule {
    default: DefineComponent;
}

createInertiaApp({
    resolve: (name) => {
        const pages = import.meta.glob<PageModule>("./Pages/**/*.vue", {
            eager: true
        });
        return pages[`./Pages/${name}.vue`].default;
    },
    setup({ el, App, props, plugin }) {
        const pinia = createPinia();
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(pinia)
            .use(PrimeVue, {
                theme: {
                    preset: Aura,
                },
            })
            .mount(el);
    },
});
