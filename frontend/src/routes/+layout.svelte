<script lang="ts">
import './layout.css';
import favicon from '$lib/assets/favicon.svg';
import { Menu } from '@lucide/svelte';
import { AppBar } from '@skeletonlabs/skeleton-svelte';
import { invalidateAll } from '$app/navigation';
import { PUBLIC_BACKEND_URL } from '$env/static/public';
import { user } from '$lib/state/user.svelte';

let { data, children } = $props();

// Init user state from server data
$effect(() => {
    user.init();
});

let username = $state('');
let password = $state('');
let error = $state('');

async function handleLogin(e: Event) {
    e.preventDefault();
    error = 'hier';
    
    const response = await fetch(`${PUBLIC_BACKEND_URL}/login`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ username, password })
    });

    if (!response.ok) {
        error = 'Login fehlgeschlagen';
        return;
    }

    const result = await response.json();
    user.setAuth(result.token);
    
    await invalidateAll();
}
</script>

<svelte:head><link rel="icon" href={favicon} /></svelte:head>


{#if !data.loggedIn}
	<form class="flex flex-col gap-2 p-4" onsubmit={handleLogin}>
		<input placeholder="username" bind:value={username} />
		<input placeholder="password" bind:value={password} />
		{#if error}<p class="text-red-500">{error}</p>{/if}
		<button>Login</button>
	</form>
{:else}
	<AppBar>
		<AppBar.Toolbar class="grid-cols-[auto_1fr_auto]">
			<button><Menu /></button>
			
			<a href="/"><AppBar.Headline class="falcon-poster-bold text-3xl ">Workers</AppBar.Headline></a>
		</AppBar.Toolbar>
	</AppBar>

	{@render children()}
{/if}
