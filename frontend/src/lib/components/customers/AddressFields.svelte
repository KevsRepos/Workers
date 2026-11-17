<script lang="ts">
	import type { AddressInput } from '$lib/api/types';
	import TextInput from '$lib/components/ui/TextInput.svelte';

	let {
		value = $bindable(),
		errors = {},
		prefix = 'address',
		errorPrefix = ''
	}: { value: AddressInput; errors?: Record<string, string>; prefix?: string; errorPrefix?: string } = $props();
</script>

<div class="grid gap-4 sm:grid-cols-6">
	<TextInput
		id="{prefix}-street"
		label="Straße"
		required
		class="sm:col-span-4"
		bind:value={value.street}
		error={errors[errorPrefix + 'street']}
		autocomplete="address-line1"
	/>
	<TextInput
		id="{prefix}-houseNumber"
		label="Nr."
		required
		class="sm:col-span-2"
		bind:value={value.houseNumber}
		error={errors[errorPrefix + 'houseNumber']}
	/>
	<TextInput
		id="{prefix}-postalCode"
		label="PLZ"
		required
		class="sm:col-span-2"
		bind:value={value.postalCode}
		error={errors[errorPrefix + 'postalCode']}
		autocomplete="postal-code"
		inputmode="numeric"
	/>
	<TextInput
		id="{prefix}-city"
		label="Ort"
		required
		class="sm:col-span-4"
		bind:value={value.city}
		error={errors[errorPrefix + 'city']}
		autocomplete="address-level2"
	/>
	<TextInput
		id="{prefix}-note"
		label="Hinweis (optional)"
		class="sm:col-span-6"
		bind:value={value.note}
		error={errors[errorPrefix + 'note']}
		placeholder="z. B. Zufahrt über Hof"
	/>
</div>
