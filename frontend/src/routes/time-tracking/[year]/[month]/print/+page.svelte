<script lang="ts">
	import { onMount } from 'svelte';
	import { Printer } from '@lucide/svelte';
	import Button from '$lib/components/ui/Button.svelte';
	import { company as companyConfig } from '$lib/company';
	import { typeInfo } from '$lib/domain/timeTracking';
	import { WEEKDAYS_SHORT, formatDateTime, formatHours, monthName } from '$lib/utils/format';

	let { data } = $props();
	const sheet = $derived(data.sheet);
	const company = companyConfig();
	const generatedAt = new Date().toISOString();

	onMount(() => {
		const timer = setTimeout(() => window.print(), 300);
		return () => clearTimeout(timer);
	});
</script>

<svelte:head><title>Stundenzettel {monthName(sheet.month)} {sheet.year} · Workers</title></svelte:head>

<div class="no-print mb-6 flex items-center justify-between">
	<a href="/time-tracking/{sheet.year}/{sheet.month}" class="text-sm font-medium text-brand-700 hover:underline"
		>← Zurück</a
	>
	<Button onclick={() => window.print()}><Printer class="size-4" />Drucken</Button>
</div>

<article class="sheet">
	<header class="mb-6 flex justify-between">
		<div>
			<h1 class="text-[16pt] font-bold">Arbeitszeitnachweis {monthName(sheet.month)} {sheet.year}</h1>
			<p>{sheet.accountName}</p>
		</div>
		<p class="text-right text-[9pt]">{company.name}<br />{company.street}<br />{company.postalCode} {company.city}</p>
	</header>
	<table class="w-full border-collapse text-[9pt]">
		<thead>
			<tr class="border-b-2 border-black text-left">
				<th class="py-1">Tag</th><th class="py-1">Art</th><th class="py-1">Beginn</th><th class="py-1">Ende</th><th
					class="py-1 text-right">Pause</th
				><th class="py-1 text-right">Stunden</th><th class="py-1">Notiz</th>
			</tr>
		</thead>
		<tbody>
			{#each sheet.days as day (day.day)}
				<tr class="border-b border-stone-300 {day.weekend ? 'text-stone-500' : ''}">
					<td class="py-0.5">{day.day}. {WEEKDAYS_SHORT[day.weekday - 1]}</td>
					<td class="py-0.5">{day.entry ? typeInfo(day.entry.type).label : ''}</td>
					<td class="py-0.5">{day.entry?.start ?? ''}</td>
					<td class="py-0.5">{day.entry?.end ?? ''}{day.entry?.endsNextDay ? ' (+1)' : ''}</td>
					<td class="py-0.5 text-right">{day.entry?.breakMinutes ? `${day.entry.breakMinutes} min` : ''}</td>
					<td class="py-0.5 text-right">{day.entry ? formatHours(day.entry.hours) : ''}</td>
					<td class="py-0.5">{day.entry?.note ?? ''}</td>
				</tr>
			{/each}
		</tbody>
		<tfoot>
			<tr class="font-semibold"
				><td colspan="5" class="pt-2 text-right">Arbeit</td><td class="pt-2 text-right"
					>{formatHours(sheet.workedHours)}</td
				><td></td></tr
			>
			<tr class="font-semibold"
				><td colspan="5" class="text-right">Abwesenheit</td><td class="text-right">{formatHours(sheet.absenceHours)}</td
				><td></td></tr
			>
			<tr class="font-bold"
				><td colspan="5" class="text-right">Gesamt</td><td class="text-right">{formatHours(sheet.totalHours)}</td><td
				></td></tr
			>
		</tfoot>
	</table>
	<div class="mt-16 grid grid-cols-2 gap-12 text-[9pt]">
		<p class="border-t border-black pt-1">Datum, Unterschrift Mitarbeiter/in</p>
		<p class="border-t border-black pt-1">Datum, Unterschrift Arbeitgeber</p>
	</div>
	<p class="mt-6 text-[7pt] text-stone-500">
		Erstellt am {formatDateTime(generatedAt)} aus Workers. Änderungen an den Einträgen sind im Audit-Log nachvollziehbar.
	</p>
</article>

<style>
	.sheet {
		background: white;
		color: black;
		margin: 0 auto;
		max-width: 21cm;
		padding: 1.5cm 1.8cm;
		box-shadow: 0 1px 3px rgb(0 0 0 / 0.15);
	}
	@media print {
		@page {
			size: A4;
			margin: 0;
		}
		.sheet {
			box-shadow: none;
		}
	}
</style>
