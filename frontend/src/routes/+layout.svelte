<script lang="ts">
	import '../app.css';
	import AppShell from '$lib/components/layout/AppShell.svelte';
	import Toaster from '$lib/components/ui/Toaster.svelte';

	let { data, children } = $props();
</script>

{#if data.user}
	<AppShell user={data.user}>{@render children()}</AppShell>
{:else}
	{@render children()}
{/if}

<Toaster />
