<script lang="ts">
	import type { HTMLTextareaAttributes } from 'svelte/elements';
	import Field from './Field.svelte';

	interface Props extends Omit<HTMLTextareaAttributes, 'value'> {
		id: string;
		label: string;
		value?: string | null;
		error?: string | null;
		hint?: string;
		class?: string;
	}

	let {
		id,
		label,
		value = $bindable(),
		error = null,
		hint,
		class: className = '',
		rows = 3,
		...rest
	}: Props = $props();
</script>

<Field {id} {label} {error} {hint} class={className}>
	<textarea
		{id}
		name={id}
		class="field-input"
		{rows}
		bind:value
		aria-invalid={error ? 'true' : undefined}
		aria-describedby={error ? `${id}-error` : hint ? `${id}-hint` : undefined}
		{...rest}
	></textarea>
</Field>
