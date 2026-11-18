<script lang="ts">
	import { goto } from '$app/navigation';
	import { page } from '$app/state';
	import { ShieldCheck, ShieldX } from '@lucide/svelte';
	import PageHeader from '$lib/components/layout/PageHeader.svelte';
	import Alert from '$lib/components/ui/Alert.svelte';
	import Badge from '$lib/components/ui/Badge.svelte';
	import Button from '$lib/components/ui/Button.svelte';
	import EmptyState from '$lib/components/ui/EmptyState.svelte';
	import Pagination from '$lib/components/ui/Pagination.svelte';
	import { formatDateTime } from '$lib/utils/format';

	let { data } = $props();

	const ACTIONS: Record<string, string> = {
		'entity.created': 'Angelegt',
		'entity.updated': 'Geändert',
		'entity.deleted': 'Gelöscht',
		'auth.login_succeeded': 'Anmeldung',
		'auth.login_failed': 'Anmeldung fehlgeschlagen',
		'auth.login_throttled': 'Anmeldung gesperrt',
		'auth.logout': 'Abmeldung',
		'auth.refresh_token_reuse': 'Token-Missbrauch erkannt',
		'auth.password_changed': 'Passwort geändert',
		'auth.password_reset': 'Passwort zurückgesetzt',
		'privacy.anonymized': 'Anonymisiert',
		'audit.purged': 'Aufbewahrung bereinigt'
	};
	const TONES: Record<string, 'neutral' | 'success' | 'danger' | 'warning' | 'info'> = {
		'entity.created': 'success',
		'entity.deleted': 'danger',
		'auth.login_failed': 'warning',
		'auth.login_throttled': 'danger',
		'auth.refresh_token_reuse': 'danger',
		'privacy.anonymized': 'info'
	};

	// svelte-ignore state_referenced_locally
	let filters = $state({ ...data.filters });

	const apply = () => {
		const url = new URL(page.url);
		for (const key of ['category', 'subjectType', 'subjectId', 'from', 'to'] as const) {
			const value = filters[key];
			if (value) url.searchParams.set(key, String(value));
			else url.searchParams.delete(key);
		}
		url.searchParams.delete('page');
		goto(url.pathname + url.search);
	};

	const asChange = (value: unknown): { old?: unknown; new?: unknown } | null =>
		typeof value === 'object' && value !== null && !Array.isArray(value) && ('old' in value || 'new' in value)
			? (value as { old?: unknown; new?: unknown })
			: null;

	const show = (value: unknown): string =>
		value === null || value === undefined ? '–' : typeof value === 'object' ? JSON.stringify(value) : String(value);
</script>

<svelte:head><title>Audit-Log · Workers</title></svelte:head>

<PageHeader
	title="Audit-Log"
	subtitle="Manipulationssicheres Protokoll aller Änderungen und sicherheitsrelevanten Ereignisse."
>
	{#snippet actions()}<Button variant="secondary" href="/admin/audit-log?verify=1"
			><ShieldCheck class="size-4" />Integrität prüfen</Button
		>{/snippet}
</PageHeader>

{#if data.verification}
	<div class="mb-4">
		{#if data.verification.valid}
			<Alert tone="success" title="Integrität bestätigt"
				>{data.verification.checked} Einträge (Nr. {data.verification.firstSeq ?? '–'} bis {data.verification.lastSeq ??
					'–'}) geprüft. Die Hash-Kette ist lückenlos und unverändert.</Alert
			>
		{:else}
			<Alert tone="danger" title="Integrität verletzt"
				><ShieldX class="inline size-4" />
				{data.verification.reason}
				{#if data.verification.brokenAtSeq}(bei Eintrag {data.verification.brokenAtSeq}){/if}</Alert
			>
		{/if}
	</div>
{/if}

<form
	class="card mb-4 grid gap-3 p-4 sm:grid-cols-5"
	onsubmit={(e) => {
		e.preventDefault();
		apply();
	}}
>
	<label class="flex flex-col gap-1 text-sm"
		><span class="font-medium text-stone-700">Kategorie</span>
		<select class="field-input" bind:value={filters.category}>
			<option value={null}>Alle</option><option value="entity">Daten</option><option value="auth">Anmeldung</option
			><option value="privacy">Datenschutz</option><option value="audit">Audit</option>
		</select>
	</label>
	<label class="flex flex-col gap-1 text-sm"
		><span class="font-medium text-stone-700">Objekttyp</span><input
			class="field-input"
			bind:value={filters.subjectType}
			placeholder="z. B. DeliveryNote"
		/></label
	>
	<label class="flex flex-col gap-1 text-sm"
		><span class="font-medium text-stone-700">von</span><input
			type="date"
			class="field-input"
			bind:value={filters.from}
		/></label
	>
	<label class="flex flex-col gap-1 text-sm"
		><span class="font-medium text-stone-700">bis</span><input
			type="date"
			class="field-input"
			bind:value={filters.to}
		/></label
	>
	<div class="flex items-end"><Button type="submit" class="w-full">Filtern</Button></div>
	{#if filters.subjectId}<p class="text-xs text-stone-500 sm:col-span-5">
			Gefiltert auf Objekt {filters.subjectId} ·
			<button
				type="button"
				class="underline"
				onclick={() => {
					filters.subjectId = null;
					apply();
				}}>Filter entfernen</button
			>
		</p>{/if}
</form>

<div class="card divide-y divide-stone-100 overflow-hidden">
	{#each data.entries.items as entry (entry.seq)}
		<details class="group">
			<summary
				class="flex cursor-pointer list-none flex-wrap items-center gap-x-3 gap-y-1 px-4 py-2.5 text-sm hover:bg-stone-50"
			>
				<span class="w-14 shrink-0 font-mono text-xs text-stone-400">#{entry.seq}</span>
				<span class="w-40 shrink-0 text-stone-600">{formatDateTime(entry.occurredAt)}</span>
				<Badge tone={TONES[entry.action] ?? 'neutral'}>{ACTIONS[entry.action] ?? entry.action}</Badge>
				<span class="font-medium">{entry.subjectType ?? ''}</span>
				<span class="ml-auto truncate text-stone-500">{entry.actorLabel ?? 'unbekannt'}</span>
			</summary>
			<div class="bg-stone-50 px-4 py-3 text-xs">
				{#if entry.changes}
					<table class="mb-3 w-full">
						<thead class="text-left text-stone-500"
							><tr
								><th class="py-1 pr-4 font-medium">Feld</th><th class="py-1 pr-4 font-medium">vorher</th><th
									class="py-1 font-medium">nachher</th
								></tr
							></thead
						>
						<tbody>
							{#each Object.entries(entry.changes) as [field, change] (field)}
								{@const diff = asChange(change)}
								<tr class="align-top">
									<td class="py-0.5 pr-4 font-medium">{field}</td>
									{#if diff}
										<td class="py-0.5 pr-4 break-all text-red-700">{'old' in diff ? show(diff.old) : ''}</td>
										<td class="py-0.5 break-all text-emerald-700">{'new' in diff ? show(diff.new) : ''}</td>
									{:else}
										<td colspan="2" class="py-0.5 break-all">{show(change)}</td>
									{/if}
								</tr>
							{/each}
						</tbody>
					</table>
				{/if}
				<dl class="grid gap-x-4 gap-y-1 text-stone-600 sm:grid-cols-[auto_1fr]">
					{#if entry.subjectId}<dt>Objekt-ID</dt>
						<dd class="font-mono">
							<a class="underline" href="/admin/audit-log?subjectType={entry.subjectType}&subjectId={entry.subjectId}"
								>{entry.subjectId}</a
							>
						</dd>{/if}
					{#each Object.entries(entry.context ?? {}) as [key, value] (key)}<dt>{key}</dt>
						<dd class="font-mono break-all">{value}</dd>{/each}
					<dt>Hash</dt>
					<dd class="font-mono break-all">{entry.hash}</dd>
				</dl>
			</div>
		</details>
	{:else}
		<EmptyState title="Keine Einträge" />
	{/each}
</div>
<Pagination page={data.entries.page} pages={data.entries.pages} total={data.entries.total} />
