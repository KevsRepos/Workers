<script lang="ts">
	import type { HTMLInputAttributes } from 'svelte/elements';
	import Field from './Field.svelte';

	interface Props extends Omit<HTMLInputAttributes, 'value'> {
		id: string;
		label: string;
		value?: string | number | null;
		error?: string | null;
		hint?: string;
		class?: string;
		input?: HTMLInputElement | null;
	}

	let {
		id,
		label,
		value = $bindable(),
		error = null,
		hint,
		class: className = '',
		required = false,
		input = $bindable(null),
		...rest
	}: Props = $props();
	const isRequired = $derived(required === true);
</script>

<Field {id} {label} {error} {hint} required={isRequired} class={className}>
	<input
		bind:this={input}
		{id}
		name={id}
		class="field-input"
		bind:value
		{required}
		aria-invalid={error ? 'true' : undefined}
		aria-describedby={error ? `${id}-error` : hint ? `${id}-hint` : undefined}
		{...rest}
	/>
</Field>
