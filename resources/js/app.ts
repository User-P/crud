import "./bootstrap";
import "../css/app.css";

import { createApp, h, DefineComponent, Fragment } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";
import PrimeVue from "primevue/config";
import CosmosPreset from "./primevue-preset";
import "primeicons/primeicons.css";
import { createPinia } from "pinia";
import * as echarts from "echarts/core";
import { cosmosLight, cosmosDark } from "./echarts/cosmosThemes";
import GlobalLoadingLayer from "./Components/GlobalLoadingLayer.vue";

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

/* Temas ECharts alineados con Cosmos (glass/bento) */
echarts.registerTheme("cosmos-light", cosmosLight);
echarts.registerTheme("cosmos-dark", cosmosDark);

interface PageModule {
    default: DefineComponent;
}

createInertiaApp({
    progress: {
        delay: 200,
        color: '#0b4261',
        includeCSS: true,
        showSpinner: false,
    },
    resolve: (name) => {
        const pages = import.meta.glob<PageModule>("./Pages/**/*.vue", {
            eager: true
        });
        return pages[`./Pages/${name}.vue`].default;
    },
    setup({ el, App, props, plugin }) {
        const pinia = createPinia();
        createApp({
            render() {
                return h(Fragment, null, [
                    h(App, props),
                    h(GlobalLoadingLayer),
                ]);
            },
        })
            .use(plugin)
            .use(pinia)
            .use(PrimeVue, {
                theme: {
                    preset: CosmosPreset,
                    options: {
                        cssLayer: {
                            name: 'primevue',
                            order: 'base, primevue, utilities',
                        },
                        darkModeSelector: '[data-theme="dark"]',
                    },
                },
            })
            .mount(el);
    },
});
