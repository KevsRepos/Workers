import { browser } from '$app/environment';

class Auth {
    private loggedIn = $state(false);

    get isLoggedIn(): boolean {
        return this.loggedIn;
    }

    hydrate(loggedIn: boolean) {
        this.loggedIn = loggedIn;
    }

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

        this.loggedIn = true;

        return { success: true };
    }

    /**
     * Logout via server route (clears httpOnly cookie)
     */
    async logout(): Promise<void> {
        if (!browser) return;
        
        await fetch('/api/auth/logout', { method: 'POST' });
        this.loggedIn = false;
    }
}

export const auth = new Auth();
