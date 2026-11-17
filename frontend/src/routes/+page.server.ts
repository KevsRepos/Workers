import type { PageServerLoad } from './$types';
import type { DeliveryNoteSummary, Page, TimeSheetMonth } from '$lib/api/types';
import { api } from '$lib/server/backend';
import { addDaysIso, todayIso } from '$lib/utils/format';

export const load: PageServerLoad = async (event) => {
	const client = api(event);
	const today = todayIso();
	const [year, month, day] = today.split('-').map(Number);

	const [upcoming, counts, timeSheet] = await Promise.all([
		client.get<Page<DeliveryNoteSummary>>(`/delivery-notes?status=1&from=${today}&to=${addDaysIso(today, 7)}&limit=20`),
		client.get<Record<string, number>>('/delivery-notes/status-counts'),
		client.get<TimeSheetMonth>(`/time-sheets/${year}/${month}`)
	]);

	return {
		today,
		upcoming,
		counts,
		todayEntry: timeSheet.days.find((d) => d.day === day)?.entry ?? null,
		monthHours: timeSheet.totalHours
	};
};
