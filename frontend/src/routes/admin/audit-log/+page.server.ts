import type { PageServerLoad } from './$types';
import type { AuditEntry, AuditVerification, Page } from '$lib/api/types';
import { query } from '$lib/api/client';
import { api } from '$lib/server/backend';

export const load: PageServerLoad = async (event) => {
	const p = event.url.searchParams;
	const filters = {
		category: p.get('category'),
		action: p.get('action'),
		subjectType: p.get('subjectType'),
		subjectId: p.get('subjectId'),
		actorId: p.get('actorId'),
		from: p.get('from'),
		to: p.get('to') ? `${p.get('to')}T23:59:59` : null,
		page: p.get('page') ?? 1,
		limit: 50
	};
	const client = api(event);
	const verify = p.get('verify') === '1';
	const [entries, verification] = await Promise.all([
		client.get<Page<AuditEntry>>(`/admin/audit-log${query(filters)}`),
		verify ? client.get<AuditVerification>('/admin/audit-log/verification') : Promise.resolve(null)
	]);
	return { entries, verification, filters: { ...filters, to: p.get('to') } };
};
