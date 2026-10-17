import adapter from '@sveltejs/adapter-node';

/** @type {import('@sveltejs/kit').Config} */
const config = {
	compilerOptions: {
		warningFilter: (warning) => {
			const ionButtonA11yCodes = new Set([
				'a11y_click_events_have_key_events',
				'a11y_no_static_element_interactions',
			]);

			if (ionButtonA11yCodes.has(warning.code) && /<ion-button>/i.test(warning.message)) {
				return false;
			}

			return true;
		},
	},
	kit: {
		adapter: adapter()
	}
};

export default config;
