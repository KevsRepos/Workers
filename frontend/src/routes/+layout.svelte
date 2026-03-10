<script lang="ts">
	import './layout.css';
	import favicon from '$lib/assets/favicon.svg';

	let { children } = $props();
</script>

<svelte:head><link rel="icon" href={favicon} /></svelte:head>
<header class="border-b shadow-md">
	<a href="/" class="falcon-poster-bold text-3xl pl-2">Workers</a>
</header>

{@render children()}
