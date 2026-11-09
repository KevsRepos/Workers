import { fetchApi } from "$lib/api/fetchApi";

export const changePassword = (payload: {
    oldPassword: string;
    newPassword: string;
    confirmNewPassword: string;
}) => fetchApi("/account/change-password", "POST", payload);
