import { browser } from "$app/environment";
import { PUBLIC_BACKEND_URL } from "$env/static/public";
import { auth } from "$lib/auth.svelte";

export const load = async ({ parent, fetch, params }) => {
    const { token } = await parent();
    const { year, month } = params;

    const res = await fetch(`${PUBLIC_BACKEND_URL}/time-sheets/${year}/${month}`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`
        },
        body: null
    });

    if (res.status === 401 && browser) {
        await auth.logout();
    }

    const timeSheet = res.ok ? await res.json() : null;

    return { timeSheet, year: Number(year), month: Number(month) };
};