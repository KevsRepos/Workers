export const requestWorkers = async (
    endpoint: string,
    method: string = 'GET',
    body: any = null
) => {
    const res = await fetch('/api/workers', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ endpoint, method, body })
    });

    return await res.json();
};