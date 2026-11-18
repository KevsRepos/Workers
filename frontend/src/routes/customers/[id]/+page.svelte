<script lang="ts">
	import { invalidate } from '$app/navigation';
	import { History, Mail, MapPin, Pencil, Phone, Plus, ShieldOff, Star } from '@lucide/svelte';
	import { api } from '$lib/api/client';
	import { ApiError, userMessage } from '$lib/api/errors';
	import type { Address, AddressInput, Customer, CustomerInput } from '$lib/api/types';
	import DeliveryNoteCard from '$lib/components/DeliveryNoteCard.svelte';
	import AddressFields from '$lib/components/customers/AddressFields.svelte';
	import CustomerForm from '$lib/components/customers/CustomerForm.svelte';
	import PageHeader from '$lib/components/layout/PageHeader.svelte';
	import Alert from '$lib/components/ui/Alert.svelte';
	import Badge from '$lib/components/ui/Badge.svelte';
	import Button from '$lib/components/ui/Button.svelte';
	import ConfirmDialog from '$lib/components/ui/ConfirmDialog.svelte';
	import Dialog from '$lib/components/ui/Dialog.svelte';
	import EmptyState from '$lib/components/ui/EmptyState.svelte';
	import { toast } from '$lib/stores/toast.svelte';

	let { data } = $props();
	const customer = $derived(data.customer);

	let editOpen = $state(false);
	let editValue = $state<CustomerInput>({
		company: false,
		firstName: '',
		surname: '',
		companyName: '',
		email: '',
		phone: ''
	});
	let editErrors = $state<Record<string, string>>({});
	let saving = $state(false);

	let addressOpen = $state(false);
	let addressId = $state<string | null>(null);
	let addressValue = $state<AddressInput>({
		street: '',
		houseNumber: '',
		postalCode: '',
		city: '',
		country: null,
		note: null,
		defaultShipping: false,
		defaultBilling: false
	});
	let addressErrors = $state<Record<string, string>>({});

	let anonymizeOpen = $state(false);
	let anonymizing = $state(false);

	const openEdit = () => {
		editValue = {
			company: customer.company,
			firstName: customer.firstName,
			surname: customer.surname,
			companyName: customer.companyName,
			email: customer.email,
			phone: customer.phone
		};
		editErrors = {};
		editOpen = true;
	};

	const saveCustomer = async () => {
		saving = true;
		editErrors = {};
		try {
			await api.put<Customer>(`customers/${customer.id}`, editValue);
			editOpen = false;
			toast.success('Kunde gespeichert.');
			await invalidate('app:customer');
		} catch (e) {
			if (e instanceof ApiError && e.status === 422) editErrors = e.fieldErrors;
			else toast.error(userMessage(e));
		} finally {
			saving = false;
		}
	};

	const openAddress = (address: Address | null) => {
		addressId = address?.id ?? null;
		addressValue = address
			? {
					street: address.street,
					houseNumber: address.houseNumber,
					postalCode: address.postalCode,
					city: address.city,
					country: address.country,
					note: address.note,
					defaultShipping: address.defaultShipping,
					defaultBilling: address.defaultBilling
				}
			: {
					street: '',
					houseNumber: '',
					postalCode: '',
					city: '',
					country: null,
					note: null,
					defaultShipping: customer.addresses.length === 0,
					defaultBilling: customer.addresses.length === 0
				};
		addressErrors = {};
		addressOpen = true;
	};

	const saveAddress = async () => {
		saving = true;
		addressErrors = {};
		try {
			if (addressId) await api.put(`customers/${customer.id}/addresses/${addressId}`, addressValue);
			else await api.post(`customers/${customer.id}/addresses`, addressValue);
			addressOpen = false;
			toast.success('Adresse gespeichert.');
			await invalidate('app:customer');
		} catch (e) {
			if (e instanceof ApiError && e.status === 422) addressErrors = e.fieldErrors;
			else toast.error(userMessage(e));
		} finally {
			saving = false;
		}
	};

	const setDefault = async (address: Address, kind: 'shipping' | 'billing') => {
		try {
			await api.put(`customers/${customer.id}/default-addresses`, {
				shippingAddressId: kind === 'shipping' ? address.id : customer.defaultShippingAddressId,
				billingAddressId: kind === 'billing' ? address.id : customer.defaultBillingAddressId
			});
			await invalidate('app:customer');
		} catch (e) {
			toast.error(userMessage(e));
		}
	};

	const anonymize = async () => {
		anonymizing = true;
		try {
			await api.post(`customers/${customer.id}/anonymization`);
			anonymizeOpen = false;
			toast.success('Personenbezogene Daten wurden gelöscht.');
			await invalidate('app:customer');
		} catch (e) {
			toast.error(userMessage(e));
		} finally {
			anonymizing = false;
		}
	};
</script>

<svelte:head><title>{customer.displayName} · Kunden · Workers</title></svelte:head>

<PageHeader
	title={customer.displayName}
	subtitle={customer.company && (customer.firstName || customer.surname)
		? `Ansprechpartner: ${customer.firstName ?? ''} ${customer.surname ?? ''}`
		: undefined}
	back="/customers"
>
	{#snippet meta()}
		<Badge>{customer.company ? 'Firma / Verein' : 'Privatperson'}</Badge>
		{#if customer.anonymized}<Badge tone="danger">Anonymisiert</Badge>{/if}
	{/snippet}
	{#snippet actions()}
		{#if !customer.anonymized}
			<Button variant="secondary" onclick={openEdit}><Pencil class="size-4" />Bearbeiten</Button>
			<Button href="/delivery-notes/new?customerId={customer.id}"><Plus class="size-4" />Lieferschein</Button>
		{/if}
	{/snippet}
</PageHeader>

<div class="grid gap-6 lg:grid-cols-3">
	<div class="flex flex-col gap-6">
		<section class="card p-4 text-sm" aria-labelledby="contact-title">
			<h2 id="contact-title" class="mb-3 text-base">Kontakt</h2>
			<dl class="flex flex-col gap-2">
				<div class="flex items-center gap-3">
					<dt class="text-stone-400"><Phone class="size-4" aria-label="Telefon" /></dt>
					<dd>
						{#if customer.phone}<a class="text-brand-700 underline" href="tel:{customer.phone}">{customer.phone}</a
							>{:else}–{/if}
					</dd>
				</div>
				<div class="flex items-center gap-3">
					<dt class="text-stone-400"><Mail class="size-4" aria-label="E-Mail" /></dt>
					<dd>
						{#if customer.email}<a class="text-brand-700 underline" href="mailto:{customer.email}">{customer.email}</a
							>{:else}–{/if}
					</dd>
				</div>
			</dl>
		</section>

		<section class="card p-4" aria-labelledby="addresses-title">
			<div class="mb-3 flex items-center justify-between">
				<h2 id="addresses-title" class="text-base">Adressen</h2>
				{#if !customer.anonymized}<Button size="sm" variant="ghost" onclick={() => openAddress(null)}
						><Plus class="size-4" />Neu</Button
					>{/if}
			</div>
			<ul class="flex flex-col gap-3">
				{#each customer.addresses as address (address.id)}
					<li class="rounded-lg border border-stone-200 p-3 text-sm">
						<div class="flex justify-between gap-2">
							<p class="flex gap-2">
								<MapPin class="mt-0.5 size-4 shrink-0 text-stone-400" aria-hidden="true" /><span
									>{address.street}
									{address.houseNumber}<br />{address.postalCode}
									{address.city}{#if address.note}<span class="block text-xs text-stone-500">{address.note}</span
										>{/if}</span
								>
							</p>
							{#if !customer.anonymized}<button
									class="self-start rounded p-1 text-stone-400 hover:bg-stone-100 hover:text-stone-700"
									aria-label="Adresse bearbeiten"
									onclick={() => openAddress(address)}><Pencil class="size-4" /></button
								>{/if}
						</div>
						<div class="mt-2 flex flex-wrap gap-2">
							{#if address.defaultShipping}<Badge tone="brand"><Star class="size-3" />Lieferadresse</Badge
								>{:else if !customer.anonymized}<button
									class="text-xs text-brand-700 hover:underline"
									onclick={() => setDefault(address, 'shipping')}>Als Lieferadresse</button
								>{/if}
							{#if address.defaultBilling}<Badge tone="brand"><Star class="size-3" />Rechnungsadresse</Badge
								>{:else if !customer.anonymized}<button
									class="text-xs text-brand-700 hover:underline"
									onclick={() => setDefault(address, 'billing')}>Als Rechnungsadresse</button
								>{/if}
						</div>
					</li>
				{:else}
					<li class="text-sm text-stone-500">Keine Adresse hinterlegt.</li>
				{/each}
			</ul>
		</section>

		{#if data.user?.isAdmin}
			<section class="card p-4 text-sm" aria-labelledby="privacy-title">
				<h2 id="privacy-title" class="mb-2 text-base">Datenschutz</h2>
				<p class="mb-3 text-stone-600">
					Löschung personenbezogener Daten auf Anfrage (Art. 17 DSGVO). Kunden mit Lieferscheinen unterliegen der
					gesetzlichen Aufbewahrungspflicht.
				</p>
				<div class="flex flex-wrap gap-2">
					<Button size="sm" variant="secondary" href="/admin/audit-log?subjectType=Customer&subjectId={customer.id}"
						><History class="size-4" />Verlauf</Button
					>
					{#if !customer.anonymized}<Button size="sm" variant="danger" onclick={() => (anonymizeOpen = true)}
							><ShieldOff class="size-4" />Anonymisieren</Button
						>{/if}
				</div>
			</section>
		{/if}
	</div>

	<section class="lg:col-span-2" aria-labelledby="notes-title">
		<div class="mb-3 flex items-center justify-between">
			<h2 id="notes-title" class="text-lg">Lieferscheine</h2>
			{#if data.notes.total > data.notes.items.length}<span class="text-sm text-stone-500"
					>{data.notes.items.length} von {data.notes.total}</span
				>{/if}
		</div>
		<div class="card divide-y divide-stone-100 overflow-hidden">
			{#each data.notes.items as note (note.id)}
				<DeliveryNoteCard {note} />
			{:else}
				<EmptyState title="Noch keine Lieferscheine" />
			{/each}
		</div>
	</section>
</div>

<Dialog bind:open={editOpen} title="Kunde bearbeiten" size="lg">
	<form
		id="edit-customer"
		onsubmit={(e) => {
			e.preventDefault();
			saveCustomer();
		}}
		novalidate
	>
		<CustomerForm bind:value={editValue} errors={editErrors} prefix="edit" />
	</form>
	{#snippet footer()}
		<Button variant="secondary" onclick={() => (editOpen = false)}>Abbrechen</Button>
		<Button type="submit" form="edit-customer" loading={saving}>Speichern</Button>
	{/snippet}
</Dialog>

<Dialog bind:open={addressOpen} title={addressId ? 'Adresse bearbeiten' : 'Adresse hinzufügen'} size="lg">
	<form
		id="address-form"
		onsubmit={(e) => {
			e.preventDefault();
			saveAddress();
		}}
		novalidate
	>
		{#if addressErrors && Object.keys(addressErrors).length > 0}<div class="mb-3">
				<Alert tone="danger">Bitte prüfe die markierten Felder.</Alert>
			</div>{/if}
		<AddressFields bind:value={addressValue} errors={addressErrors} prefix="addr" />
	</form>
	{#snippet footer()}
		<Button variant="secondary" onclick={() => (addressOpen = false)}>Abbrechen</Button>
		<Button type="submit" form="address-form" loading={saving}>Speichern</Button>
	{/snippet}
</Dialog>

<ConfirmDialog
	bind:open={anonymizeOpen}
	title="Kunde anonymisieren?"
	message="Name, Kontaktdaten und Adressen werden unwiderruflich entfernt. Dieser Vorgang wird im Audit-Log protokolliert."
	confirmLabel="Endgültig anonymisieren"
	danger
	loading={anonymizing}
	onconfirm={anonymize}
/>
