<script lang="ts">
	import Button from './Button.svelte';
	import Dialog from './Dialog.svelte';

	interface Props {
		open?: boolean;
		title: string;
		message: string;
		confirmLabel?: string;
		danger?: boolean;
		loading?: boolean;
		onconfirm: () => void | Promise<void>;
	}

	let {
		open = $bindable(false),
		title,
		message,
		confirmLabel = 'Bestätigen',
		danger = false,
		loading = false,
		onconfirm
	}: Props = $props();
</script>

<Dialog bind:open {title} size="sm">
	<p class="text-sm text-stone-700">{message}</p>
	{#snippet footer()}
		<Button variant="secondary" onclick={() => (open = false)}>Abbrechen</Button>
		<Button variant={danger ? 'danger' : 'primary'} {loading} onclick={onconfirm}>{confirmLabel}</Button>
	{/snippet}
</Dialog>
