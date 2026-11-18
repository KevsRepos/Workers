import type { Role } from '$lib/api/types';

export interface SessionUser {
	id: string;
	email: string;
	name: string;
	roles: Role[];
	isAdmin: boolean;
}
