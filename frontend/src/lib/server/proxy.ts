/**
 * Allowlist for the browser → backend JSON proxy. Authentication endpoints are deliberately
 * excluded; they are handled by server-side form actions that manage the HttpOnly cookies.
 */
const ALLOWED_PREFIXES = [
	'auth/me',
	'customers',
	'products',
	'deposits',
	'product-unions',
	'order-presets',
	'delivery-notes',
	'time-sheets',
	'time-tracking',
	'admin'
];

const SEGMENT = /^[A-Za-z0-9_-][A-Za-z0-9._-]*$/;

/**
 * Returns the normalised backend path or null if the path is not allowed.
 */
export const allowedBackendPath = (path: string): string | null => {
	const segments = path.split('/').filter((s) => s !== '');
	if (segments.length === 0 || segments.some((s) => !SEGMENT.test(s) || s === '..' || s === '.')) {
		return null;
	}
	const normalized = segments.join('/');
	const allowed = ALLOWED_PREFIXES.some((prefix) => normalized === prefix || normalized.startsWith(prefix + '/'));
	return allowed ? '/' + normalized : null;
};

export const PROXY_METHODS = ['GET', 'POST', 'PUT', 'DELETE'] as const;
