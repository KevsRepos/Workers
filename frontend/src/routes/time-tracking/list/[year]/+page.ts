import { browser } from "$app/environment";
import { backendApiUrl } from "$lib/api/backendApiUrl";
import { auth } from "$lib/state/auth.svelte";

export const load = async ({ fetch, params }) => {
    const { year } = params;

    const headers = { 'Content-Type': 'application/json' };

    const [sheetsRes, yearsRes] = await Promise.all([
        fetch(backendApiUrl(`time-sheets/${year}`), { method: 'GET', headers, body: null }),
        fetch(backendApiUrl('time-sheets/years'), { method: 'GET', headers, body: null })
    ]);

    if ((sheetsRes.status === 401 || yearsRes.status === 401) && browser) {
        await auth.logout();
    }

    const timeSheets = sheetsRes.ok ? await sheetsRes.json() : [];
    const years = yearsRes.ok ? await yearsRes.json() : [];

    return { timeSheets, years, year };
};