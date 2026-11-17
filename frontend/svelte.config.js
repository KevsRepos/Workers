import adapter from '@sveltejs/adapter-node';
import { vitePreprocess } from '@sveltejs/vite-plugin-svelte';

/** @type {import('@sveltejs/kit').Config} */
const config = {
	preprocess: vitePreprocess(),
	kit: {
		adapter: adapter({ precompress: true }),
		// Strict Content Security Policy; SvelteKit adds nonces/hashes for its own inline scripts.
		csp: {
			mode: 'auto',
			directives: {
				'default-src': ['self'],
				'script-src': ['self'],
				'style-src': ['self', 'unsafe-inline'],
				'img-src': ['self', 'data:', 'blob:'],
				'font-src': ['self'],
				'connect-src': ['self'],
				'frame-src': ['none'],
				'frame-ancestors': ['none'],
				'form-action': ['self'],
				'base-uri': ['self'],
				'object-src': ['none'],
				'manifest-src': ['self'],
				'worker-src': ['self']
			}
		},
		csrf: {
			trustedOrigins: []
		},
		version: {
			pollInterval: 300000
		}
	}
};

export default config;
