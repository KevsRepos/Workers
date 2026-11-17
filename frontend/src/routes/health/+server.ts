import { json } from '@sveltejs/kit';

/** Liveness probe of the frontend server (does not touch the backend). */
export const GET = () => json({ status: 'ok' }, { headers: { 'Cache-Control': 'no-store' } });
