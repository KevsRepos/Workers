export const load = async ({ locals }) => {
    return { 
        loggedIn: locals.loggedIn,
        token: locals.token
    };
};