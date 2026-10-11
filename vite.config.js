import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { google } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            fonts: [
                google('Spectral', {
                    weights: [400, 500, 600],
                    styles: ['normal', 'italic'],
                    preload: [{ weight: 600 }],
                    optimizedFallbacks: false,
                }),
                google('IBM Plex Sans', {
                    weights: [400, 500, 600, 700],
                    preload: [{ weight: 400 }],
                    optimizedFallbacks: false,
                }),
                google('IBM Plex Mono', {
                    weights: [400, 500],
                    preload: false,
                    optimizedFallbacks: false,
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
