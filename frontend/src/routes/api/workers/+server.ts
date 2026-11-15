
import { requestWorkers } from '$lib/api/server/requestWorkers';
import { json } from '@sveltejs/kit';

export const POST = async ({ request }) => {
    const { endpoint, method, body } = await request.json();
    
    const result = await requestWorkers(endpoint, method, body);

    return json(result);
};