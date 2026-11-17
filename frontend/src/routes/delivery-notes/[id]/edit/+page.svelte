<script lang="ts">
	import DeliveryNoteEditor from '$lib/components/delivery-notes/DeliveryNoteEditor.svelte';
	import PageHeader from '$lib/components/layout/PageHeader.svelte';
	import Alert from '$lib/components/ui/Alert.svelte';
	import type { Customer } from '$lib/api/types';
	import { DeliveryNoteStatus } from '$lib/api/types';
	import { draftFromNote } from '$lib/domain/deliveryNote';

	let { data } = $props();

	// svelte-ignore state_referenced_locally
	let draft = $state(draftFromNote(data.note));
	// svelte-ignore state_referenced_locally
	let customer = $state<Customer | null>(data.customer);
</script>

<svelte:head><title>{data.note.number} bearbeiten · Workers</title></svelte:head>

<PageHeader
	title="{data.note.number} bearbeiten"
	subtitle={data.note.customer.displayName}
	back="/delivery-notes/{data.note.id}"
/>

{#if data.note.status === DeliveryNoteStatus.Completed || data.note.status === DeliveryNoteStatus.Canceled}
	<div class="mb-6">
		<Alert tone="warning"
			>Dieser Lieferschein ist {data.note.statusLabel.toLowerCase()}. Änderungen werden im Audit-Log protokolliert.</Alert
		>
	</div>
{/if}

<DeliveryNoteEditor bind:draft bind:customer noteId={data.note.id} />
