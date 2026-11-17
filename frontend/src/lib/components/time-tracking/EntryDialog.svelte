<script lang="ts">
	import { api } from '$lib/api/client';
	import { ApiError, userMessage } from '$lib/api/errors';
	import type { TimeEntry, TimeSheetDay } from '$lib/api/types';
	import Alert from '$lib/components/ui/Alert.svelte';
	import Button from '$lib/components/ui/Button.svelte';
	import Dialog from '$lib/components/ui/Dialog.svelte';
	import TextInput from '$lib/components/ui/TextInput.svelte';
	import {
		ENTRY_TYPES,
		formFromEntry,
		formToInput,
		previewHours,
		previewWarnings,
		endsNextDay,
		type EntryForm
	} from '$lib/domain/timeTracking';
	import { toast } from '$lib/stores/toast.svelte';
	import { formatHours, formatWeekdayDate } from '$lib/utils/format';

	interface Props {
		open?: boolean;
		day: TimeSheetDay | null;
		year: number;
		month: number;
		accountQuery: string;
		onsaved: () => void;
	}

	let { open = $bindable(false), day, year, month, accountQuery, onsaved }: Props = $props();

	let form = $state<EntryForm>(formFromEntry(null));
	let errors = $state<Record<string, string>>({});
	let error = $state<string | null>(null);
	let saving = $state(false);
	let deleting = $state(false);

	$effect(() => {
		if (open && day) {
			form = formFromEntry(day.entry);
			errors = {};
			error = null;
		}
	});

	const hours = $derived(previewHours(form));
	const overnight = $derived(endsNextDay(form));
	const warnings = $derived(previewWarnings(form));

	const save = async () => {
		if (!day) return;
		saving = true;
		errors = {};
		error = null;
		try {
			const input = formToInput(day.day, form);
			if (day.entry) await api.put<TimeEntry>(`time-sheets/entries/${day.entry.id}`, input);
			else await api.post<TimeEntry>(`time-sheets/${year}/${month}/entries${accountQuery}`, input);
			toast.success('Eintrag gespeichert.');
			open = false;
			onsaved();
		} catch (e) {
			if (e instanceof ApiError && e.status === 422) {
				errors = e.fieldErrors;
				error = errors.day ?? null;
			} else error = userMessage(e);
		} finally {
			saving = false;
		}
	};

	const remove = async () => {
		if (!day?.entry || !confirm('Eintrag wirklich löschen?')) return;
		deleting = true;
		try {
			await api.delete(`time-sheets/entries/${day.entry.id}`);
			toast.success('Eintrag gelöscht.');
			open = false;
			onsaved();
		} catch (e) {
			error = userMessage(e);
		} finally {
			deleting = false;
		}
	};
</script>

<Dialog bind:open title={day ? formatWeekdayDate(day.date) : 'Eintrag'}>
	{#if day}
		<form
			id="entry-form"
			class="flex flex-col gap-4"
			onsubmit={(e) => {
				e.preventDefault();
				save();
			}}
			novalidate
		>
			{#if error}<Alert tone="danger">{error}</Alert>{/if}
			<fieldset>
				<legend class="mb-2 text-sm font-medium text-stone-700">Art</legend>
				<div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
					{#each ENTRY_TYPES as type (type.value)}
						<label
							class="flex cursor-pointer items-center justify-center rounded-lg border px-2 py-2 text-sm font-medium has-checked:border-brand-600 has-checked:bg-brand-50 has-checked:text-brand-900"
						>
							<input
								type="radio"
								class="sr-only"
								name="entry-type"
								value={type.value}
								bind:group={form.type}
							/>{type.label}
						</label>
					{/each}
				</div>
			</fieldset>
			{#if form.type === 'work'}
				<div class="grid grid-cols-3 gap-3">
					<TextInput
						id="entry-start"
						type="time"
						label="Beginn"
						required
						bind:value={form.start}
						error={errors.start}
					/>
					<TextInput id="entry-end" type="time" label="Ende" required bind:value={form.end} error={errors.end} />
					<TextInput
						id="entry-break"
						type="number"
						min="0"
						step="5"
						label="Pause (Min.)"
						bind:value={form.breakMinutes}
						error={errors.breakMinutes}
					/>
				</div>
				{#if hours !== null}<p class="text-sm text-stone-600">
						Arbeitszeit: <strong>{formatHours(Math.round(hours * 100) / 100)}</strong>{#if overnight}
							· endet am Folgetag{/if}
					</p>{/if}
				{#each warnings as warning (warning)}<Alert tone="warning">{warning}</Alert>{/each}
			{/if}
			<TextInput id="entry-note" label="Notiz (optional)" bind:value={form.note} error={errors.note} />
		</form>
	{/if}
	{#snippet footer()}
		{#if day?.entry}<Button variant="ghost" class="mr-auto text-red-600" loading={deleting} onclick={remove}
				>Löschen</Button
			>{/if}
		<Button variant="secondary" onclick={() => (open = false)}>Abbrechen</Button>
		<Button type="submit" form="entry-form" loading={saving}>Speichern</Button>
	{/snippet}
</Dialog>
