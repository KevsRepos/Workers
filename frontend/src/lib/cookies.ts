export const getCookie = (name: string): string | undefined => {
    if (typeof document === 'undefined') return undefined;
    const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
    return match ? match[2] : undefined;
};

export const setCookie = (name: string, value: string) => {
    document.cookie = `${name}=${value}; path=/`;
};

export const deleteCookie = (name: string) => {
    document.cookie = `${name}=; path=/; expires=Thu, 01 Jan 1970 00:00:00 GMT`;
};
