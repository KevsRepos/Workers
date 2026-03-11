/**
 * Auth state module - stores JWT in memory for client-side access.
 * Token is hydrated from server via +layout.svelte on page load.
 * This keeps the actual token secure in an httpOnly cookie while
 * still allowing client-side API calls.
 */

import { browser } from '$app/environment';

class Auth {
    token: string | null = $state(null);

    get isLoggedIn(): boolean {
        return !!this.token;
    }

    /**
     * Hydrate auth state from server data (called in +layout.svelte)
     */
    hydrate(token: string | null) {
        this.token = token;
    }

    /**
     * Login via server route (sets httpOnly cookie)
     */
    async login(username: string, password: string): Promise<{ success: boolean; error?: string }> {
        if (!browser) {
            return { success: false, error: 'Login nur client-seitig möglich' };
        }

        const response = await fetch('/api/auth/login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ username, password })
        });

        if (!response.ok) {
            const data = await response.json();
            return { success: false, error: data.error || 'Login fehlgeschlagen' };
        }

        const data = await response.json();
        this.token = data.token;

        return { success: true };
    }

    /**
     * Logout via server route (clears httpOnly cookie)
     */
    async logout(): Promise<void> {
        if (!browser) return;
        
        await fetch('/api/auth/logout', { method: 'POST' });
        this.token = null;
    }
}

export const auth = new Auth();
