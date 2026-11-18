<script lang="ts">
	import type { AddressInput, CustomerInput } from '$lib/api/types';
	import TextInput from '$lib/components/ui/TextInput.svelte';
	import AddressFields from './AddressFields.svelte';

	interface Props {
		value: CustomerInput;
		errors?: Record<string, string>;
		withAddress?: boolean;
		prefix?: string;
	}

	let { value = $bindable(), errors = {}, withAddress = false, prefix = 'customer' }: Props = $props();

	const emptyAddress = (): AddressInput => ({
		street: '',
		houseNumber: '',
		postalCode: '',
		city: '',
		country: null,
		note: null,
		defaultShipping: true,
		defaultBilling: true
	});

	$effect(() => {
		if (withAddress && (!value.addresses || value.addresses.length === 0)) {
			value.addresses = [emptyAddress()];
		}
	});
</script>

<div class="flex flex-col gap-4">
	<fieldset class="flex gap-2" aria-label="Kundenart">
		<label
			class="flex flex-1 cursor-pointer items-center justify-center gap-2 rounded-lg border px-3 py-2 text-sm font-medium has-checked:border-brand-600 has-checked:bg-brand-50 has-checked:text-brand-900"
		>
			<input
				type="radio"
				class="sr-only"
				name="{prefix}-kind"
				checked={!value.company}
				onchange={() => (value.company = false)}
			/> Privatperson
		</label>
		<label
			class="flex flex-1 cursor-pointer items-center justify-center gap-2 rounded-lg border px-3 py-2 text-sm font-medium has-checked:border-brand-600 has-checked:bg-brand-50 has-checked:text-brand-900"
		>
			<input
				type="radio"
				class="sr-only"
				name="{prefix}-kind"
				checked={value.company}
				onchange={() => (value.company = true)}
			/> Firma / Verein
		</label>
	</fieldset>

	{#if value.company}
		<TextInput
			id="{prefix}-companyName"
			label="Firmenname"
			required
			bind:value={value.companyName}
			error={errors.companyName}
			autocomplete="organization"
		/>
	{/if}
	<div class="grid gap-4 sm:grid-cols-2">
		<TextInput
			id="{prefix}-firstName"
			label={value.company ? 'Ansprechpartner Vorname' : 'Vorname'}
			required={!value.company}
			bind:value={value.firstName}
			error={errors.firstName}
			autocomplete="given-name"
		/>
		<TextInput
			id="{prefix}-surname"
			label={value.company ? 'Ansprechpartner Nachname' : 'Nachname'}
			required={!value.company}
			bind:value={value.surname}
			error={errors.surname}
			autocomplete="family-name"
		/>
		<TextInput
			id="{prefix}-email"
			label="E-Mail"
			type="email"
			bind:value={value.email}
			error={errors.email}
			autocomplete="email"
		/>
		<TextInput
			id="{prefix}-phone"
			label="Telefon"
			type="tel"
			bind:value={value.phone}
			error={errors.phone}
			autocomplete="tel"
		/>
	</div>

	{#if withAddress && value.addresses?.[0]}
		<div class="border-t border-stone-200 pt-4">
			<p class="mb-3 text-sm font-medium text-stone-700">Adresse</p>
			<AddressFields bind:value={value.addresses[0]} prefix="{prefix}-address" {errors} errorPrefix="addresses[0]." />
		</div>
	{/if}
</div>
