import "./bootstrap";
import "../css/app.css";

import { createApp, h, DefineComponent } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";
import PrimeVue from "primevue/config";
import CosmosPreset from "./primevue-preset";
import "primeicons/primeicons.css";
import { createPinia } from "pinia";

/* Aplicar tema al cargar para que PrimeVue y todos los componentes usen light/dark desde el primer paint */
function initTheme() {
    const saved = (typeof localStorage !== "undefined" && localStorage.getItem("admin-theme")) || "system";
    const resolved =
        saved === "system"
            ? (typeof window !== "undefined" && window.matchMedia("(prefers-color-scheme: dark)").matches
                ? "dark"
                : "light")
            : saved;
    if (typeof document !== "undefined" && document.documentElement) {
        document.documentElement.setAttribute("data-theme", resolved);
    }
}
initTheme();

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
                    preset: CosmosPreset,
                    options: {
                        cssLayer: {
                            name: 'primevue',
                            order: 'tailwind-base, primevue, tailwind-utilities',
                        },
                        darkModeSelector: '[data-theme="dark"]',
                    },
                },
            })
            .mount(el);
    },
});
