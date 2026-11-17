<script lang="ts">
	import { MapPin, Phone, Plus, UserRound } from '@lucide/svelte';
	import { api, query } from '$lib/api/client';
	import { ApiError, userMessage } from '$lib/api/errors';
	import type { Customer, CustomerInput, CustomerSummary, Page } from '$lib/api/types';
	import Button from '$lib/components/ui/Button.svelte';
	import Combobox from '$lib/components/ui/Combobox.svelte';
	import Dialog from '$lib/components/ui/Dialog.svelte';
	import CustomerForm from '$lib/components/customers/CustomerForm.svelte';
	import { toast } from '$lib/stores/toast.svelte';
	import { withoutEmptyAddresses } from '$lib/domain/customer';

	interface Props {
		customer: Customer | null;
		onchange: (customer: Customer | null) => void;
		error?: string | null;
	}

	let { customer, onchange, error = null }: Props = $props();

	let createOpen = $state(false);
	let creating = $state(false);
	let createErrors = $state<Record<string, string>>({});
	let newCustomer = $state<CustomerInput>({
		company: false,
		firstName: '',
		surname: '',
		companyName: '',
		email: '',
		phone: ''
	});
	let searchTerm = $state('');

	const search = async (q: string) =>
		(await api.get<Page<CustomerSummary>>(`customers${query({ q, limit: 10 })}`)).items;

	const select = async (summary: CustomerSummary) => {
		try {
			onchange(await api.get<Customer>(`customers/${summary.id}`));
		} catch (e) {
			toast.error(userMessage(e));
		}
	};

	const openCreate = () => {
		const parts = searchTerm.trim().split(/\s+/);
		newCustomer = {
			company: false,
			firstName: parts.length > 1 ? parts.slice(0, -1).join(' ') : (parts[0] ?? ''),
			surname: parts.length > 1 ? parts.at(-1)! : '',
			companyName: searchTerm.trim(),
			email: '',
			phone: ''
		};
		createErrors = {};
		createOpen = true;
	};

	const create = async () => {
		creating = true;
		createErrors = {};
		try {
			const created = await api.post<Customer>('customers', withoutEmptyAddresses(newCustomer));
			toast.success(`Kunde „${created.displayName}“ angelegt.`);
			createOpen = false;
			onchange(created);
		} catch (e) {
			if (e instanceof ApiError && e.status === 422) createErrors = e.fieldErrors;
			else toast.error(userMessage(e));
		} finally {
			creating = false;
		}
	};
</script>

{#if customer}
	<div class="flex items-start justify-between gap-4 rounded-lg border border-brand-200 bg-brand-50/60 p-4">
		<div class="flex gap-3">
			<div class="rounded-full bg-white p-2 text-brand-700"><UserRound class="size-5" aria-hidden="true" /></div>
			<div>
				<p class="font-semibold">{customer.displayName}</p>
				{#if customer.company && (customer.firstName || customer.surname)}<p class="text-sm text-stone-600">
						{customer.firstName}
						{customer.surname}
					</p>{/if}
				<div class="mt-1 flex flex-wrap gap-x-4 text-sm text-stone-600">
					{#if customer.phone}<span class="flex items-center gap-1"><Phone class="size-3.5" />{customer.phone}</span
						>{/if}
					{#if customer.addresses[0]}<span class="flex items-center gap-1"
							><MapPin class="size-3.5" />{customer.addresses.length} Adresse(n)</span
						>{/if}
				</div>
			</div>
		</div>
		<Button variant="ghost" size="sm" onclick={() => onchange(null)}>Ändern</Button>
	</div>
{:else}
	<div class="flex flex-col gap-2 sm:flex-row sm:items-end">
		<div class="flex-1">
			<Combobox
				id="customer-search"
				label="Kunde"
				placeholder="Name, Firma, Telefon oder E-Mail"
				{search}
				itemKey={(c) => c.id}
				onselect={select}
				bind:value={searchTerm}
				clearOnSelect={false}
				{error}
			>
				{#snippet item(c)}
					<div class="flex flex-col">
						<span class="font-medium">{c.displayName}</span>
						<span class="text-xs text-stone-500">{[c.defaultAddress, c.phone].filter(Boolean).join(' · ')}</span>
					</div>
				{/snippet}
				{#snippet empty(term)}
					Kein Kunde gefunden. <button
						type="button"
						class="font-medium text-brand-700 underline"
						onmousedown={(e) => {
							e.preventDefault();
							openCreate();
						}}>„{term}“ neu anlegen</button
					>
				{/snippet}
			</Combobox>
		</div>
		<Button variant="secondary" onclick={openCreate}><Plus class="size-4" />Neuer Kunde</Button>
	</div>
{/if}

<Dialog bind:open={createOpen} title="Neuen Kunden anlegen" size="lg">
	<form
		id="create-customer-form"
		onsubmit={(e) => {
			e.preventDefault();
			create();
		}}
	>
		<CustomerForm bind:value={newCustomer} errors={createErrors} withAddress prefix="new-customer" />
	</form>
	{#snippet footer()}
		<Button variant="secondary" onclick={() => (createOpen = false)}>Abbrechen</Button>
		<Button type="submit" form="create-customer-form" loading={creating}>Kunde anlegen</Button>
	{/snippet}
</Dialog>
