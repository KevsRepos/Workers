<script lang="ts">
	import { goto } from '$app/navigation';
	import { api } from '$lib/api/client';
	import { ApiError, userMessage } from '$lib/api/errors';
	import type { Customer, CustomerInput } from '$lib/api/types';
	import CustomerForm from '$lib/components/customers/CustomerForm.svelte';
	import PageHeader from '$lib/components/layout/PageHeader.svelte';
	import Alert from '$lib/components/ui/Alert.svelte';
	import Button from '$lib/components/ui/Button.svelte';
	import { withoutEmptyAddresses } from '$lib/domain/customer';
	import { toast } from '$lib/stores/toast.svelte';

	let value = $state<CustomerInput>({
		company: false,
		firstName: '',
		surname: '',
		companyName: '',
		email: '',
		phone: ''
	});
	let errors = $state<Record<string, string>>({});
	let error = $state<string | null>(null);
	let saving = $state(false);

	const save = async () => {
		saving = true;
		errors = {};
		error = null;
		try {
			const customer = await api.post<Customer>('customers', withoutEmptyAddresses(value));
			toast.success(`Kunde „${customer.displayName}“ angelegt.`);
			await goto(`/customers/${customer.id}`);
		} catch (e) {
			if (e instanceof ApiError && e.status === 422) errors = e.fieldErrors;
			error = userMessage(e);
		} finally {
			saving = false;
		}
	};
</script>

<svelte:head><title>Neuer Kunde · Workers</title></svelte:head>

<PageHeader title="Neuer Kunde" back="/customers" />

<form
	class="card max-w-3xl p-4 sm:p-6"
	onsubmit={(e) => {
		e.preventDefault();
		save();
	}}
	novalidate
>
	{#if error}<div class="mb-4"><Alert tone="danger">{error}</Alert></div>{/if}
	<CustomerForm bind:value {errors} withAddress />
	<p class="mt-3 text-xs text-stone-500">Die Adresse kann leer bleiben, z. B. bei Selbstabholern.</p>
	<div class="mt-6 flex justify-end gap-2">
		<Button variant="secondary" href="/customers">Abbrechen</Button>
		<Button type="submit" loading={saving}>Kunde anlegen</Button>
	</div>
</form>
