import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    build: {
        emptyOutDir: false,
    },
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/dashboard/shared/runtime-helpers.js',
                'resources/js/dashboard/export/print-core.js',
                'resources/js/dashboard/export/print-browser.js',
                'resources/js/dashboard/export/analytics-export-bridge.js',
                'resources/js/dashboard/export/customer-service-bridge.js',
                'resources/js/dashboard/export/reporting-export-bridge.js',
                'resources/js/dashboard/export/sales-export-bridge.js',
                'resources/js/dashboard/export/analytics.js',
                'resources/js/dashboard/export/bonus.js',
                'resources/js/dashboard/export/promo.js',
                'resources/js/dashboard/export/sell-out.js',
                'resources/js/dashboard/export/unit-ditanya.js',
                'resources/js/dashboard/export/claim-garansi.js',
                'resources/js/dashboard/export/budget.js',
                'resources/js/dashboard/export/lpjk-detail.js',
                'resources/js/dashboard/export/price-comparison.js',
            ],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
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
