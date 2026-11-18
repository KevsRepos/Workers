<script lang="ts">
	import type { Snippet } from 'svelte';
	import { X } from '@lucide/svelte';

	interface Props {
		open?: boolean;
		title: string;
		size?: 'sm' | 'md' | 'lg';
		children: Snippet;
		footer?: Snippet;
		onclose?: () => void;
	}

	let { open = $bindable(false), title, size = 'md', children, footer, onclose }: Props = $props();
	let dialog = $state<HTMLDialogElement>();

	const widths = { sm: 'max-w-sm', md: 'max-w-lg', lg: 'max-w-3xl' };

	$effect(() => {
		if (!dialog) return;
		if (open && !dialog.open) dialog.showModal();
		if (!open && dialog.open) dialog.close();
	});

	const close = () => {
		open = false;
		onclose?.();
	};
</script>

<dialog
	bind:this={dialog}
	onclose={close}
	onclick={(e) => e.target === dialog && close()}
	class="m-auto w-[calc(100%-2rem)] {widths[size]} rounded-2xl bg-white p-0 shadow-xl backdrop:bg-stone-950/40"
	aria-labelledby="dialog-title"
>
	{#if open}
		<div class="flex items-center justify-between border-b border-stone-200 px-5 py-4">
			<h2 id="dialog-title" class="text-lg">{title}</h2>
			<button
				type="button"
				class="rounded-md p-1 text-stone-500 hover:bg-stone-100"
				onclick={close}
				aria-label="Schließen"
			>
				<X class="size-5" />
			</button>
		</div>
		<div class="max-h-[70dvh] overflow-y-auto px-5 py-4">{@render children()}</div>
		{#if footer}
			<div class="flex flex-wrap justify-end gap-2 border-t border-stone-200 px-5 py-3">{@render footer()}</div>
		{/if}
	{/if}
</dialog>
