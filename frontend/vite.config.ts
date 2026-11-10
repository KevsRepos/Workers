import tailwindcss from '@tailwindcss/vite';
import { sveltekit } from '@sveltejs/kit/vite';
import { defineConfig, loadEnv } from 'vite';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, '.', '');

    return {
        plugins: [tailwindcss(), sveltekit()],
        server: {
            host: true,
            proxy: {
                '/workers': {
                    target: env.PUBLIC_BACKEND_URL,
                    changeOrigin: true,
                    rewrite: (path) => path.replace(/^\/workers/, '')
                }
            }
        }
    };
});
