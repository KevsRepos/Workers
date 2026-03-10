import { PUBLIC_BACKEND_URL } from '$env/static/public';

export const load = async ({ locals, fetch }) => {
    if (!locals.jwt) return { deliveryNotes: [] };

    const response = await fetch(`${PUBLIC_BACKEND_URL}/delivery-notes/1`);

    if (response.ok) {
        const deliveryNotes = await response.json();
                
        return { deliveryNotes };
    }

    return { deliveryNotes: [] };
};

export const actions = {
    login: async (event) => {
        console.log('HIER');
        
        const formData = await event.request.formData();
        const username = formData.get('username');
        const password = formData.get('password');

        const response = await event.fetch(`${PUBLIC_BACKEND_URL}/login`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ username, password })
        });

        if (!response.ok) {
            console.log(await response.text());
            
            return { success: false, error: 'Login fehlgeschlagen' };
        }

        const data = await response.json();

        console.log(data);

        event.cookies.set('jwt_token', data.token, { path: '/'});
        return { success: true, token: data.token };
    }
}