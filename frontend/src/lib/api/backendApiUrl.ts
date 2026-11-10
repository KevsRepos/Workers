import { browser } from '$app/environment';
import { PUBLIC_BACKEND_URL } from '$env/static/public';

export const backendApiUrl = (endpoint: string): string => {
    const path = endpoint.replace(/^\/+/, '');

    return browser
        ? `/workers/${path}`
        : `${PUBLIC_BACKEND_URL.replace(/\/+$/, '')}/${path}`;
};