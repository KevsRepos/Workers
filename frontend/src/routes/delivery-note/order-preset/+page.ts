import { browser } from "$app/environment";
import { backendApiUrl } from "$lib/api/backendApiUrl";
import { auth } from "$lib/state/auth.svelte";

export const load = async ({ fetch }) => {
    const response = await fetch(backendApiUrl('order-presets'), {
        headers: {
            "Content-Type": "application/json"
        }
    });

    if (response.status === 401 && browser) {
        await auth.logout();
    }

    return {
        orderPresets: response.ok ? await response.json() : []
    };
};