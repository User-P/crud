import fs from "fs";
import os from "os";
import path from "path";
import { defineConfig, loadEnv } from "vite";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue";
import VueDevTools from "vite-plugin-vue-devtools";

const certificatesDir = path.join(os.homedir(), ".valet", "Certificates");

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), "");
    const appUrl = env.APP_URL ?? "http://localhost";
    const appHost = resolveHost(appUrl);
    const requestedHost = env.VITE_DEV_SERVER_HOST ?? appHost;
    const port = Number(env.VITE_PORT ?? 5173);
    const wantsHttps = appUrl.startsWith("https://");
    const httpsConfig = wantsHttps ? resolveHttpsConfig(requestedHost) : null;

    let serverHost = requestedHost;
    let serverHttps = httpsConfig;

    if (wantsHttps && !httpsConfig) {
        console.warn(
            `[vite] APP_URL apunta a HTTPS pero no se encontró un certificado para ${requestedHost}. ` +
            `Falling back a http://localhost:${port} hasta que ejecutes "valet secure ${requestedHost}".`
        );
        serverHost = "localhost";
        serverHttps = null;
    }

    const usingHttps = Boolean(serverHttps);

    return {
        plugins: [
            laravel({
                input: ["resources/css/app.css", "resources/js/app.ts"],
                refresh: true,
            }),
            vue({
                template: {
                    transformAssetUrls: {
                        base: null,
                        includeAbsolute: false,
                    },
                },
            }),
            VueDevTools(),
        ],
        resolve: {
            alias: {
                "@": "/resources/js",
            },
        },
        server: {
            host: serverHost,
            port,
            https: usingHttps ? serverHttps ?? undefined : undefined,
            hmr: {
                host: serverHost,
                protocol: usingHttps ? "wss" : "ws",
            },
        },
    };
});

function resolveHost(urlValue: string) {
    try {
        return new URL(urlValue).hostname || "localhost";
    } catch {
        return "localhost";
    }
}

function resolveHttpsConfig(host: string) {
    const keyPath = path.join(certificatesDir, `${host}.key`);
    const certPath = path.join(certificatesDir, `${host}.crt`);

    if (fs.existsSync(keyPath) && fs.existsSync(certPath)) {
        return {
            key: fs.readFileSync(keyPath),
            cert: fs.readFileSync(certPath),
        };
    }

    return null;
}
