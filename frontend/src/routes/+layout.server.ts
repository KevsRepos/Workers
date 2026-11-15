export const load = async ({ locals, cookies }) => {
    return { 
        loggedIn: locals.loggedIn
    };
};