import { browser } from "$app/environment";
import { PUBLIC_BACKEND_URL } from "$env/static/public";
import { auth } from "$lib/auth.svelte";

export const load = async ({ parent, fetch }) => {
    const { token } = await parent();
    const response = await fetch(`${PUBLIC_BACKEND_URL}/order-presets`, {
        headers: {
            "Content-Type": "application/json",
            Authorization: `Bearer ${token}`
        }
    });

    if (response.status === 401 && browser) {
        await auth.logout();
    }

    return {
        orderPresets: response.ok ? await response.json() : []
    };
};