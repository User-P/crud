import type { Config } from "tailwindcss";

export default {
    content: [
        "./resources/views/**/*.blade.php",
        "./resources/js/**/*.vue",
        "./resources/js/**/*.js",
        "./resources/js/**/*.ts",
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
    ],
    darkMode: ["selector", '[data-theme="dark"]'],
    theme: {
        extend: {
            transitionDuration: {
                "400": "400ms",
            },
        },
    },
    plugins: [],
} satisfies Config;
