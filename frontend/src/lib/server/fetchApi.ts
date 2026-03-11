import { PUBLIC_BACKEND_URL } from "$env/static/public"

export const fetchApi = async (endpoint: string, method: string = 'GET', body: object = {}) => {
    const res = await fetch(`${PUBLIC_BACKEND_URL}${endpoint}`, {
        method,
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(body)
    });
    
    return res.json();

}