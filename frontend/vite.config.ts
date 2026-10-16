import tailwindcss from '@tailwindcss/vite';
import { sveltekit } from '@sveltejs/kit/vite';
import { defineConfig } from 'vite';

export default defineConfig({
    optimizeDeps: {
        exclude: ['@ionic/core'],
    },
    build: {
        rollupOptions: {
        output: {
            manualChunks: undefined,
        },
        external: ['/ionic.esm.js'],
        },
    },
    plugins: [
        tailwindcss(),
        sveltekit(),
    ] ,
    server: {
        host: true
    }
});
