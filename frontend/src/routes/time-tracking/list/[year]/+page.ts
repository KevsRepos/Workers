import { browser } from "$app/environment";
import { PUBLIC_BACKEND_URL } from "$env/static/public";
import { auth } from "$lib/auth.svelte";

export const load = async ({ parent, fetch, params }) => {
    const { token } = await parent();
    const { year } = params;

    const headers = {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`
    };

    const [sheetsRes, yearsRes] = await Promise.all([
        fetch(`${PUBLIC_BACKEND_URL}/time-sheets/${year}`, { method: 'GET', headers, body: null }),
        fetch(`${PUBLIC_BACKEND_URL}/time-sheets/years`, { method: 'GET', headers, body: null })
    ]);

    if ((sheetsRes.status === 401 || yearsRes.status === 401) && browser) {
        await auth.logout();
    }

    const timeSheets = sheetsRes.ok ? await sheetsRes.json() : [];
    const years = yearsRes.ok ? await yearsRes.json() : [];

    return { timeSheets, years, year };
};