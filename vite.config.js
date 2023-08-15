import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue2 from '@vitejs/plugin-vue2';

export default defineConfig({
    server: {
        hmr: {
              host: 'localhost',
        },
    },
    plugins: [
        laravel([
            'resources/js/curated-collections-addon.js',
        ]),
        vue2(),
    ],
});
