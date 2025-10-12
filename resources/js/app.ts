import "./bootstrap";
import "../css/app.css";

import { createApp, h, DefineComponent } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";

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
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
});
