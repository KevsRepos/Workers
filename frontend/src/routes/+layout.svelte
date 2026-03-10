<script lang="ts">
import './layout.css';
import favicon from '$lib/assets/favicon.svg';
import { Hamburger, Menu } from '@lucide/svelte';
import { AppBar } from '@skeletonlabs/skeleton-svelte';
import { page } from '$app/state';

let { children, data } = $props();
</script>

<svelte:head><link rel="icon" href={favicon} /></svelte:head>


{#if !data.loggedIn}
	<form class="flex flex-col gap-2 p-4" action="?/login" method="post">
		<input placeholder="username" name="username" />
		<input placeholder="password" name="password"/>
		<button>Login</button>
	</form>
{:else}
	<AppBar>
		<AppBar.Toolbar class="grid-cols-[auto_1fr_auto]">
			<AppBar.Lead>
				<button><Menu size="32"/></button>
			</AppBar.Lead>
			<AppBar.Headline class="falcon-poster-bold text-3xl">Workers</AppBar.Headline>
		</AppBar.Toolbar>
	</AppBar>

	{@render children()}
{/if}
