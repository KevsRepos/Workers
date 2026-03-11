import { getCookie, setCookie, deleteCookie } from '$lib/cookies';

class User {
    loggedIn: boolean = $state(false);

    get jwt(): string | undefined {
        return getCookie('jwt_token');
    }

    setAuth(token: string) {
        setCookie('jwt_token', token);
        this.loggedIn = true;
    }

    logout() {
        deleteCookie('jwt_token');
        this.loggedIn = false;
    }

    init() {
        this.loggedIn = !!this.jwt;
    }
}

const user = new User();

export { user };