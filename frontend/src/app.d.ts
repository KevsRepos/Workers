import type { SessionUser } from '$lib/session';

declare global {
	namespace App {
		interface Error {
			message: string;
			requestId?: string;
			status?: number;
		}
		interface Locals {
			user: SessionUser | null;
			accessToken: string | null;
			requestId: string;
		}
		interface PageData {
			user?: SessionUser | null;
		}
	}
}

export {};
