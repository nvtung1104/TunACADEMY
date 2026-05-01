import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue";
import tailwindcss from "@tailwindcss/vite";
import { resolve } from "path";

export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/css/app.css", "resources/js/app.js"],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
                compilerOptions: {
                    isCustomElement: (tag) => tag.startsWith("ion-"),
                },
            },
        }),
        tailwindcss(),
    ],
    resolve: {
        alias: {
            "@": resolve(__dirname, "resources/js"),
            "@components": resolve(__dirname, "resources/js/components"),
            "@pages": resolve(__dirname, "resources/js/pages"),
            "@layouts": resolve(__dirname, "resources/js/layouts"),
            "@stores": resolve(__dirname, "resources/js/stores"),
            "@utils": resolve(__dirname, "resources/js/utils"),
            "@services": resolve(__dirname, "resources/js/services"),
            "@api": resolve(__dirname, "resources/js/api"),
            "@assets": resolve(__dirname, "resources/assets"),
        },
    },
    server: {
        host: "0.0.0.0",
        port: 5175,
        hmr: {
            host: "tunacademy.test",
            port: 5175,
        },
        watch: {
            ignored: [
                "**/storage/framework/views/**",
                "**/storage/logs/**",
                "**/.git/**",
                "**/node_modules/**",
            ],
        },
    },
    build: {
        outDir: "public/build",
        emptyOutDir: true,
        target: "esnext",
        minify: true,
        rollupOptions: {
            output: {
                manualChunks(id) {
                    if (id.includes('node_modules/vue')) return 'vue'
                    if (id.includes('node_modules/axios')) return 'vendor'
                },
            },
        },
        sourcemap: false,
    },
    optimizeDeps: {
        include: ["vue"],
    },
});
