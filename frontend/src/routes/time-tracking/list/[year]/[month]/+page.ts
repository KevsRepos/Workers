import { browser } from "$app/environment";
import { backendApiUrl } from "$lib/api/backendApiUrl";
import { auth } from "$lib/state/auth.svelte";

export const load = async ({ fetch, params }) => {
    const { year, month } = params;

    const res = await fetch(backendApiUrl(`time-sheets/${year}/${month}`), {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json'
        },
        body: null
    });

    if (res.status === 401 && browser) {
        await auth.logout();
    }

    const timeSheet = res.ok ? await res.json() : null;

    return { timeSheet, year: Number(year), month: Number(month) };
};