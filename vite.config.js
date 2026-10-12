import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
import fs from 'node:fs';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.jsx'],
            refresh: true,
        }),
        react(),
        {
            name: 'create-dist-directory',
            closeBundle() {
                try {
                    fs.cpSync('public', 'dist', { recursive: true });
                    console.log('[Vite] Successfully created dist directory');
                } catch (err) {
                    console.error('[Vite] Failed to copy public to dist:', err);
                }
            },
        },
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
