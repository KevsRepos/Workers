import { requestWorkers } from "$lib/api/requestWorkers";

export const changePassword = (payload: {
    oldPassword: string;
    newPassword: string;
    confirmNewPassword: string;
}) => requestWorkers("/account/change-password", "POST", payload);
