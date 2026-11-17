<script lang="ts">
	import { goto, invalidate } from '$app/navigation';
	import { CalendarRange, ChevronLeft, ChevronRight, Plus, Printer, TriangleAlert } from '@lucide/svelte';
	import type { TimeSheetDay } from '$lib/api/types';
	import EntryDialog from '$lib/components/time-tracking/EntryDialog.svelte';
	import PageHeader from '$lib/components/layout/PageHeader.svelte';
	import Badge from '$lib/components/ui/Badge.svelte';
	import Button from '$lib/components/ui/Button.svelte';
	import { shiftMonth, typeInfo } from '$lib/domain/timeTracking';
	import { WEEKDAYS_SHORT, formatHours, monthName, todayIso } from '$lib/utils/format';
	import { query } from '$lib/api/client';

	let { data } = $props();
	const sheet = $derived(data.sheet);
	const accountQuery = $derived(query({ accountId: data.accountId }));
	const prev = $derived(shiftMonth(sheet.year, sheet.month, -1));
	const next = $derived(shiftMonth(sheet.year, sheet.month, 1));
	const today = todayIso();
	const isOwn = $derived(!data.accountId || data.accountId === data.user?.id);

	let dialogOpen = $state(false);
	let selected = $state<TimeSheetDay | null>(null);

	const openDay = (day: TimeSheetDay) => {
		selected = day;
		dialogOpen = true;
	};

	const todayDay = $derived(sheet.days.find((d) => d.date === today) ?? null);
</script>

<svelte:head><title>Zeiterfassung {monthName(sheet.month)} {sheet.year} · Workers</title></svelte:head>

<PageHeader
	title="Zeiterfassung"
	subtitle={isOwn
		? `${monthName(sheet.month)} ${sheet.year}`
		: `${sheet.accountName} · ${monthName(sheet.month)} ${sheet.year}`}
>
	{#snippet actions()}
		<Button variant="secondary" href="/time-tracking/{sheet.year}{accountQuery}"
			><CalendarRange class="size-4" />Jahr</Button
		>
		<Button variant="secondary" href="/time-tracking/{sheet.year}/{sheet.month}/print{accountQuery}"
			><Printer class="size-4" />Drucken</Button
		>
		{#if todayDay}<Button onclick={() => openDay(todayDay)}
				><Plus class="size-4" />{todayDay.entry ? 'Heute bearbeiten' : 'Heute erfassen'}</Button
			>{/if}
	{/snippet}
</PageHeader>

{#if data.accounts.length > 0}
	<div class="mb-4 flex items-center gap-2">
		<label for="account-select" class="text-sm text-stone-600">Mitarbeiter</label>
		<select
			id="account-select"
			class="field-input w-auto"
			value={data.accountId ?? data.user?.id}
			onchange={(e) =>
				goto(
					`/time-tracking/${sheet.year}/${sheet.month}${query({ accountId: e.currentTarget.value === data.user?.id ? null : e.currentTarget.value })}`
				)}
		>
			{#each data.accounts as account (account.id)}<option value={account.id}>{account.displayName}</option>{/each}
		</select>
	</div>
{/if}

<div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
	<div class="card p-4">
		<p class="text-xs text-stone-500">Gesamt</p>
		<p class="text-2xl font-semibold">{formatHours(sheet.totalHours)}</p>
	</div>
	<div class="card p-4">
		<p class="text-xs text-stone-500">Arbeit</p>
		<p class="text-2xl font-semibold">{formatHours(sheet.workedHours)}</p>
	</div>
	<div class="card p-4">
		<p class="text-xs text-stone-500">Abwesenheit</p>
		<p class="text-2xl font-semibold">{formatHours(sheet.absenceHours)}</p>
		<p class="text-xs text-stone-500">
			{sheet.countsByType.vacation} Urlaub · {sheet.countsByType.sick_leave} krank · {sheet.countsByType.holiday} Feiertag
		</p>
	</div>
	<div class="card p-4">
		<p class="text-xs text-stone-500">Hinweise ArbZG</p>
		<p class="text-2xl font-semibold {sheet.warningCount ? 'text-amber-700' : ''}">{sheet.warningCount}</p>
	</div>
</div>

<div class="card overflow-hidden">
	<div class="flex items-center justify-between border-b border-stone-200 px-2 py-2">
		<a
			class="rounded-lg p-2 text-stone-600 hover:bg-stone-100"
			href="/time-tracking/{prev.year}/{prev.month}{accountQuery}"
			aria-label="Vorheriger Monat"><ChevronLeft class="size-5" /></a
		>
		<h2 class="text-base">{monthName(sheet.month)} {sheet.year}</h2>
		<a
			class="rounded-lg p-2 text-stone-600 hover:bg-stone-100"
			href="/time-tracking/{next.year}/{next.month}{accountQuery}"
			aria-label="Nächster Monat"><ChevronRight class="size-5" /></a
		>
	</div>
	<ul class="divide-y divide-stone-100">
		{#each sheet.days as day (day.day)}
			{@const info = day.entry ? typeInfo(day.entry.type) : null}
			<li>
				<button
					type="button"
					class="flex w-full items-center gap-4 px-4 py-2.5 text-left hover:bg-stone-50 {day.weekend
						? 'bg-stone-50/70'
						: ''} {day.date === today ? 'ring-2 ring-brand-300 ring-inset' : ''}"
					onclick={() => openDay(day)}
				>
					<span class="w-14 shrink-0 text-sm {day.weekend ? 'text-stone-400' : 'text-stone-700'}"
						><span class="font-semibold">{day.day}.</span> {WEEKDAYS_SHORT[day.weekday - 1]}</span
					>
					<span class="flex min-w-0 flex-1 flex-wrap items-center gap-2 text-sm">
						{#if day.entry && info}
							<Badge tone={info.tone}>{info.label}</Badge>
							{#if day.entry.type === 'work'}<span class="text-stone-700"
									>{day.entry.start}–{day.entry.end}{day.entry.breakMinutes
										? ` · ${day.entry.breakMinutes} min Pause`
										: ''}</span
								>{/if}
							{#if day.entry.note}<span class="truncate text-stone-500">{day.entry.note}</span>{/if}
							{#if day.entry.warnings.length}<span
									class="flex items-center gap-1 text-amber-700"
									title={day.entry.warnings.join('\n')}
									><TriangleAlert class="size-4" /><span class="sr-only">{day.entry.warnings.join(' ')}</span></span
								>{/if}
						{:else if !day.future && !day.weekend}
							<span class="text-stone-400">kein Eintrag</span>
						{/if}
					</span>
					<span class="w-16 shrink-0 text-right text-sm font-medium"
						>{day.entry ? formatHours(day.entry.hours) : ''}</span
					>
				</button>
			</li>
		{/each}
	</ul>
</div>

<EntryDialog
	bind:open={dialogOpen}
	day={selected}
	year={sheet.year}
	month={sheet.month}
	{accountQuery}
	onsaved={() => invalidate('app:time-sheet')}
/>
