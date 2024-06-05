import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import { resolve } from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.js',
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
    ],
    resolve: {
        alias: {
            '@': resolve(__dirname, './resources/js'),
            '@inertiajs/inertia': resolve(__dirname, './node_modules/@inertiajs/inertia'),
            '@inertiajs/inertia-vue3': resolve(__dirname, './node_modules/@inertiajs/inertia-vue3')
        }
    },
    server: {
        port: 7891,
    }
});
