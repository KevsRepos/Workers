<script lang="ts">
	import { onMount } from 'svelte';
	import { beforeNavigate, goto } from '$app/navigation';
	import { Car, Plus, Truck } from '@lucide/svelte';
	import { api } from '$lib/api/client';
	import { ApiError, userMessage } from '$lib/api/errors';
	import type { AddressInput, Customer, DeliveryNote } from '$lib/api/types';
	import { draftToInput, validateDraft, type DeliveryNoteDraft } from '$lib/domain/deliveryNote';
	import { toast } from '$lib/stores/toast.svelte';
	import Alert from '$lib/components/ui/Alert.svelte';
	import Button from '$lib/components/ui/Button.svelte';
	import Checkbox from '$lib/components/ui/Checkbox.svelte';
	import Dialog from '$lib/components/ui/Dialog.svelte';
	import TextArea from '$lib/components/ui/TextArea.svelte';
	import TextInput from '$lib/components/ui/TextInput.svelte';
	import AddressFields from '$lib/components/customers/AddressFields.svelte';
	import CustomerSelect from './CustomerSelect.svelte';
	import LineEditor from './LineEditor.svelte';

	interface Props {
		draft: DeliveryNoteDraft;
		customer: Customer | null;
		noteId?: string | null;
		/** sessionStorage key for autosaving unsaved drafts (create mode only). */
		storageKey?: string | null;
	}

	let { draft = $bindable(), customer = $bindable(), noteId = null, storageKey = null }: Props = $props();

	let errors = $state<Record<string, string>>({});
	let formError = $state<string | null>(null);
	let saving = $state(false);
	let dirty = $state(false);
	let addressOpen = $state(false);
	let addressSaving = $state(false);
	let newAddress = $state<AddressInput>({
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

	let initialized = false;
	$effect(() => {
		const snapshot = JSON.stringify(draft);
		if (!initialized) {
			initialized = true;
			return;
		}
		dirty = true;
		if (storageKey) {
			try {
				sessionStorage.setItem(storageKey, snapshot);
			} catch {
				// storage unavailable (private mode) – autosave is a convenience only
			}
		}
	});

	onMount(() => {
		const handler = (event: BeforeUnloadEvent) => {
			if (dirty && !saving) event.preventDefault();
		};
		window.addEventListener('beforeunload', handler);
		return () => window.removeEventListener('beforeunload', handler);
	});

	beforeNavigate(({ cancel, type, to }) => {
		if (dirty && !saving && type !== 'leave' && to?.url.pathname !== window.location.pathname) {
			if (!confirm('Ungespeicherte Änderungen verwerfen?')) cancel();
		}
	});

	const setCustomer = (selected: Customer | null) => {
		customer = selected;
		draft.customerId = selected?.id ?? null;
		draft.customerName = selected?.displayName ?? '';
		draft.shippingAddressId = selected?.defaultShippingAddressId ?? selected?.addresses[0]?.id ?? null;
		draft.billingAddressId = selected?.defaultBillingAddressId ?? draft.shippingAddressId;
		delete errors.customerId;
	};

	const addAddress = async () => {
		if (!customer) return;
		addressSaving = true;
		addressErrors = {};
		try {
			const created = await api.post<Customer['addresses'][number]>(`customers/${customer.id}/addresses`, newAddress);
			customer = await api.get<Customer>(`customers/${customer.id}`);
			draft.shippingAddressId = created.id;
			draft.billingAddressId ??= created.id;
			addressOpen = false;
			newAddress = {
				street: '',
				houseNumber: '',
				postalCode: '',
				city: '',
				country: null,
				note: null,
				defaultShipping: false,
				defaultBilling: false
			};
			toast.success('Adresse hinzugefügt.');
		} catch (e) {
			if (e instanceof ApiError && e.status === 422) addressErrors = e.fieldErrors;
			else toast.error(userMessage(e));
		} finally {
			addressSaving = false;
		}
	};

	const save = async () => {
		formError = null;
		errors = validateDraft(draft);
		if (Object.keys(errors).length > 0) {
			formError = 'Bitte prüfe die markierten Angaben.';
			document.querySelector('[aria-invalid="true"]')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
			return;
		}

		saving = true;
		try {
			const input = draftToInput(draft);
			const note = noteId
				? await api.put<DeliveryNote>(`delivery-notes/${noteId}`, input)
				: await api.post<DeliveryNote>('delivery-notes', input);
			if (storageKey) sessionStorage.removeItem(storageKey);
			dirty = false;
			toast.success(noteId ? 'Lieferschein gespeichert.' : `Lieferschein ${note.number} angelegt.`);
			await goto(`/delivery-notes/${note.id}`);
		} catch (e) {
			if (e instanceof ApiError && (e.status === 422 || e.status === 409)) {
				errors = e.fieldErrors;
				formError = e.status === 409 ? userMessage(e) : 'Bitte prüfe die markierten Angaben.';
			} else {
				formError = userMessage(e);
			}
		} finally {
			saving = false;
		}
	};
</script>

<form
	class="flex flex-col gap-6 pb-24"
	onsubmit={(e) => {
		e.preventDefault();
		save();
	}}
	novalidate
>
	{#if formError}<Alert tone="danger">{formError}</Alert>{/if}

	<section class="card p-4 sm:p-6" aria-labelledby="section-customer">
		<h2 id="section-customer" class="mb-4 text-base">1. Kunde</h2>
		<CustomerSelect {customer} onchange={setCustomer} error={errors.customerId} />
	</section>

	<section class="card p-4 sm:p-6" aria-labelledby="section-schedule">
		<h2 id="section-schedule" class="mb-4 text-base">2. Termin</h2>
		<div class="mb-4 grid grid-cols-2 gap-2" role="radiogroup" aria-label="Art">
			<label
				class="flex cursor-pointer items-center justify-center gap-2 rounded-lg border px-3 py-3 text-sm font-medium has-checked:border-brand-600 has-checked:bg-brand-50 has-checked:text-brand-900"
			>
				<input
					type="radio"
					class="sr-only"
					name="delivery"
					checked={draft.delivery}
					onchange={() => (draft.delivery = true)}
				/><Truck class="size-4" />Wir liefern
			</label>
			<label
				class="flex cursor-pointer items-center justify-center gap-2 rounded-lg border px-3 py-3 text-sm font-medium has-checked:border-brand-600 has-checked:bg-brand-50 has-checked:text-brand-900"
			>
				<input
					type="radio"
					class="sr-only"
					name="delivery"
					checked={!draft.delivery}
					onchange={() => (draft.delivery = false)}
				/><Car class="size-4" />Kunde holt ab
			</label>
		</div>
		<div class="grid gap-4 sm:grid-cols-3">
			<TextInput
				id="deliveryDate"
				type="date"
				label={draft.delivery ? 'Liefertermin' : 'Abholtermin'}
				required
				bind:value={draft.deliveryDate}
				error={errors.deliveryDate}
			/>
			<TextInput
				id="pickupDate"
				type="date"
				label="Rückgabe / Abholung am"
				min={draft.deliveryDate}
				bind:value={draft.pickupDate}
				error={errors.pickupDate}
				hint="Optional, z. B. bei Leihware"
			/>
			<TextInput
				id="assignment"
				label="Zuständig"
				bind:value={draft.assignment}
				error={errors.assignment}
				placeholder="z. B. Fahrer"
			/>
			<TextInput
				id="adultGuests"
				type="number"
				min="0"
				label="Gäste (Erwachsene)"
				value={draft.adultGuests ?? ''}
				oninput={(e) => (draft.adultGuests = e.currentTarget.value === '' ? null : Number(e.currentTarget.value))}
				error={errors.adultGuests}
			/>
			<TextInput
				id="childGuests"
				type="number"
				min="0"
				label="Gäste (Kinder)"
				value={draft.childGuests ?? ''}
				oninput={(e) => (draft.childGuests = e.currentTarget.value === '' ? null : Number(e.currentTarget.value))}
				error={errors.childGuests}
			/>
			<div class="flex items-end pb-2">
				<Checkbox id="callBefore" label="Vorher anrufen" bind:checked={draft.callBefore} />
			</div>
		</div>
	</section>

	{#if customer}
		<section class="card p-4 sm:p-6" aria-labelledby="section-address">
			<div class="mb-4 flex items-center justify-between">
				<h2 id="section-address" class="text-base">3. Adressen</h2>
				<Button size="sm" variant="ghost" onclick={() => (addressOpen = true)}><Plus class="size-4" />Adresse</Button>
			</div>
			{#if customer.addresses.length === 0}
				<Alert tone={draft.delivery ? 'warning' : 'info'}
					>Für diesen Kunden ist keine Adresse hinterlegt.{draft.delivery
						? ' Für eine Lieferung bitte eine Adresse hinzufügen.'
						: ''}</Alert
				>
			{:else}
				<div class="grid gap-6 sm:grid-cols-2">
					{#if draft.delivery}
						<fieldset>
							<legend class="mb-2 text-sm font-medium text-stone-700">Lieferadresse</legend>
							<div class="flex flex-col gap-2">
								{#each customer.addresses as address (address.id)}
									<label
										class="flex cursor-pointer gap-2 rounded-lg border px-3 py-2 text-sm has-checked:border-brand-600 has-checked:bg-brand-50"
									>
										<input
											type="radio"
											name="shipping"
											value={address.id}
											bind:group={draft.shippingAddressId}
											class="mt-0.5 accent-brand-700"
										/>
										<span
											>{address.singleLine}{#if address.note}<span class="block text-xs text-stone-500"
													>{address.note}</span
												>{/if}</span
										>
									</label>
								{/each}
							</div>
							{#if errors.shippingAddressId}<p class="mt-1 text-sm text-red-600" role="alert">
									{errors.shippingAddressId}
								</p>{/if}
						</fieldset>
					{/if}
					<fieldset>
						<legend class="mb-2 text-sm font-medium text-stone-700">Rechnungsadresse</legend>
						<div class="flex flex-col gap-2">
							<label
								class="flex cursor-pointer gap-2 rounded-lg border px-3 py-2 text-sm has-checked:border-brand-600 has-checked:bg-brand-50"
							>
								<input
									type="radio"
									name="billing"
									value={null}
									bind:group={draft.billingAddressId}
									class="mt-0.5 accent-brand-700"
								/>
								<span class="text-stone-500">Keine</span>
							</label>
							{#each customer.addresses as address (address.id)}
								<label
									class="flex cursor-pointer gap-2 rounded-lg border px-3 py-2 text-sm has-checked:border-brand-600 has-checked:bg-brand-50"
								>
									<input
										type="radio"
										name="billing"
										value={address.id}
										bind:group={draft.billingAddressId}
										class="mt-0.5 accent-brand-700"
									/>
									<span>{address.singleLine}</span>
								</label>
							{/each}
						</div>
					</fieldset>
				</div>
			{/if}
		</section>
	{/if}

	<section class="card p-4 sm:p-6" aria-labelledby="section-lines">
		<h2 id="section-lines" class="mb-4 text-base">{customer ? '4.' : '3.'} Artikel</h2>
		<LineEditor bind:lines={draft.lines} {errors} />
	</section>

	<section class="card p-4 sm:p-6" aria-labelledby="section-notes">
		<h2 id="section-notes" class="mb-4 text-base">{customer ? '5.' : '4.'} Hinweise</h2>
		<div class="grid gap-4 sm:grid-cols-2">
			<TextArea
				id="shortDescription"
				label="Hinweis für den Kunden"
				hint="Wird auf dem Lieferschein gedruckt."
				bind:value={draft.shortDescription}
				error={errors.shortDescription}
				rows={4}
			/>
			<TextArea
				id="privateDescription"
				label="Interne Notiz"
				hint="Nur intern sichtbar, nicht auf dem Kundenexemplar."
				bind:value={draft.privateDescription}
				error={errors.privateDescription}
				rows={4}
			/>
		</div>
	</section>

	<div
		class="no-print fixed inset-x-0 bottom-0 z-20 border-t border-stone-200 bg-white/95 px-4 py-3 backdrop-blur lg:left-64"
	>
		<div class="mx-auto flex max-w-6xl items-center justify-between gap-3 sm:px-2 lg:px-4">
			<p class="hidden text-sm text-stone-500 sm:block">
				{draft.lines.length} Artikel{#if draft.customerName}
					· {draft.customerName}{/if}
			</p>
			<div class="ml-auto flex gap-2">
				<Button variant="secondary" href={noteId ? `/delivery-notes/${noteId}` : '/delivery-notes'}>Abbrechen</Button>
				<Button type="submit" loading={saving}>{noteId ? 'Änderungen speichern' : 'Lieferschein anlegen'}</Button>
			</div>
		</div>
	</div>
</form>

<Dialog bind:open={addressOpen} title="Adresse hinzufügen" size="lg">
	<form
		id="address-form"
		onsubmit={(e) => {
			e.preventDefault();
			addAddress();
		}}
	>
		<AddressFields bind:value={newAddress} errors={addressErrors} prefix="new-address" />
	</form>
	{#snippet footer()}
		<Button variant="secondary" onclick={() => (addressOpen = false)}>Abbrechen</Button>
		<Button type="submit" form="address-form" loading={addressSaving}>Hinzufügen</Button>
	{/snippet}
</Dialog>
