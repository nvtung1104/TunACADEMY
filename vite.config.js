import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue";
import tailwindcss from "@tailwindcss/vite";
import { resolve } from "path";
import fs from "fs";

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
        https: {
            key:  fs.readFileSync("C:/laragon/etc/ssl/laragon.key"),
            cert: fs.readFileSync("C:/laragon/etc/ssl/laragon.crt"),
        },
        hmr: {
            host: "tunacademy.test",
            port: 5175,
            protocol: "wss",
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
                    if (id.includes('node_modules/firebase')) return 'firebase'
                    if (id.includes('node_modules/@tiptap') || id.includes('node_modules/prosemirror')) return 'editor'
                    if (id.includes('node_modules/chart.js') || id.includes('node_modules/vue-chartjs')) return 'charts'
                    if (id.includes('node_modules/vue') || id.includes('node_modules/@vue') || id.includes('node_modules/pinia')) return 'vue'
                    if (id.includes('node_modules/axios') || id.includes('node_modules/lodash')) return 'vendor'
                },
            },
        },
        sourcemap: false,
    },
    optimizeDeps: {
        include: ["vue", "axios", "pinia", "@vueuse/core"],
    },
});
