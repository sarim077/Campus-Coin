import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { serveAndBundleOcrAssets } from './vite.ocr-assets.js';

export default defineConfig({
    plugins: [
        serveAndBundleOcrAssets(),
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/report-export.js',
                'resources/js/receipt-scanner.js',
            ],
            refresh: true,
        }),
    ],
});
