export const load = async ({ locals }) => {
    return { loggedIn: !!locals.jwt };
};