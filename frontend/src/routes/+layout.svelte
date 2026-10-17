<script lang="ts">
import '@ionic/core';
import '@ionic/core/css/core.css';

// import '@ionic/core/css/normalize.css';
// import '@ionic/core/css/structure.css';

import './layout.css';

// Basic CSS for apps built with Ionic

// import '@ionic/core/css/typography.css';

// Optional CSS utils that can be commented out
// import '@ionic/core/css/padding.css';
// import '@ionic/core/css/float-elements.css';
// import '@ionic/core/css/text-alignment.css';
// import '@ionic/core/css/text-transformation.css';
// import '@ionic/core/css/flex-utils.css';
// import '@ionic/core/css/display.css';

import { defineCustomElements } from '@ionic/core/loader';

import favicon from '$lib/assets/favicon.svg';
import { invalidateAll } from '$app/navigation';
import { auth } from '$lib/auth.svelte';
import PageHeadline from '$lib/components/PageHeadline.svelte';
import HamburgerMenu from '$lib/components/HamburgerMenu.svelte';
import { onMount } from 'svelte';

let { data, children } = $props();

onMount(() => {
	window.Ionic = {
		config: {
			mode: 'ios', //window.localStorage.getItem('ionicMode') || 'md'
			inputLabelPlacement: 'floating',
		}
	}

    defineCustomElements(window);
});

// Hydrate auth state from server data
$effect(() => {
    auth.hydrate(data.token);
});

let username = $state('');
let password = $state('');
let error = $state('');

async function handleLogin(e: Event) {
    e.preventDefault();
    error = '';
    
    const result = await auth.login(username, password);
    
    if (!result.success) {
        error = result.error || 'Login fehlgeschlagen';
        return;
    }

    await invalidateAll();
}
</script>

<svelte:head><link rel="icon" href={favicon} /></svelte:head>

<ion-app class="justify-start!">
	<HamburgerMenu />

	<ion-header>
		<ion-toolbar>
		    <ion-buttons slot="start">
				<ion-menu-button></ion-menu-button>
			</ion-buttons>

			<ion-title class="falcon-poster-bold px-5 text-3xl">Workers</ion-title>
		</ion-toolbar>
	</ion-header>

	<ion-content id="main-content">
		{#if !data.loggedIn}
			<PageHeadline>Login</PageHeadline>

			<form class="flex flex-col gap-2 p-4 md:max-w-200" onsubmit={handleLogin}>
				<ion-input fill="solid" label-placement="floating" label="E-Mail" placeholder="E-Mail" oninput={(e: any) => username = e.target.value}></ion-input>
				<ion-input fill="solid" label-placement="floating" label="Passwort" placeholder="Passwort" type="password" value={password} oninput={(e: any) => password = e.target.value}>
					<ion-input-password-toggle slot="end"></ion-input-password-toggle>
				</ion-input>
				<!-- <input class="input bg-surface-200-800" placeholder="Passwort" type="password" bind:value={password} /> -->
				{#if error}<p class="text-red-500">{error}</p>{/if}
				<ion-button type="submit" class="btn preset-filled">Login</ion-button>
			</form>
		{:else}
			{@render children()}
		{/if}
	</ion-content>

	<footer class="bottom-0 w-full bg-gray-200 text-center p-2"></footer>
</ion-app>