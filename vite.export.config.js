import { defineConfig } from 'vite';
import path from 'path';

export default defineConfig({
    build: {
        outDir: 'public/build',
        emptyOutDir: false,
        lib: {
            entry: path.resolve(__dirname, 'resources/js/dashboard/export/dashboard-exports-all.js'),
            formats: ['iife'],
            name: 'DashboardExports',
            fileName: () => 'assets/dashboard-exports.js',
        },
        rollupOptions: {
            output: {
                assetFileNames: 'assets/[name][extname]',
            },
        },
    },
});
