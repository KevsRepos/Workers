<script lang="ts">
	import { onMount } from 'svelte';
	import DeliveryNoteEditor from '$lib/components/delivery-notes/DeliveryNoteEditor.svelte';
	import PageHeader from '$lib/components/layout/PageHeader.svelte';
	import Alert from '$lib/components/ui/Alert.svelte';
	import Button from '$lib/components/ui/Button.svelte';
	import { api } from '$lib/api/client';
	import type { Customer } from '$lib/api/types';
	import { emptyDraft, type DeliveryNoteDraft } from '$lib/domain/deliveryNote';
	import { addDaysIso, todayIso } from '$lib/utils/format';

	let { data } = $props();

	const STORAGE_KEY = 'workers:delivery-note-draft';

	// svelte-ignore state_referenced_locally
	let customer = $state<Customer | null>(data.customer);
	let draft = $state<DeliveryNoteDraft>(emptyDraft(addDaysIso(todayIso(), 1)));
	let storedDraft = $state<DeliveryNoteDraft | null>(null);
	let editorKey = $state(0);

	// svelte-ignore state_referenced_locally
	if (data.customer) {
		draft.customerId = data.customer.id;
		draft.customerName = data.customer.displayName;
		draft.shippingAddressId = data.customer.defaultShippingAddressId ?? data.customer.addresses[0]?.id ?? null;
		draft.billingAddressId = data.customer.defaultBillingAddressId ?? draft.shippingAddressId;
	}

	onMount(() => {
		try {
			const raw = sessionStorage.getItem(STORAGE_KEY);
			const parsed = raw ? (JSON.parse(raw) as DeliveryNoteDraft) : null;
			if (parsed && (parsed.customerId || parsed.lines.length > 0) && !data.customer) storedDraft = parsed;
		} catch {
			sessionStorage.removeItem(STORAGE_KEY);
		}
	});

	const restore = async () => {
		if (!storedDraft) return;
		customer = storedDraft.customerId
			? await api.get<Customer>(`customers/${storedDraft.customerId}`).catch(() => null)
			: null;
		draft = { ...storedDraft, customerId: customer?.id ?? null };
		storedDraft = null;
		editorKey++;
	};

	const discard = () => {
		sessionStorage.removeItem(STORAGE_KEY);
		storedDraft = null;
	};
</script>

<svelte:head><title>Neuer Lieferschein · Workers</title></svelte:head>

<PageHeader title="Neuer Lieferschein" back="/delivery-notes" />

{#if storedDraft}
	<div class="mb-6">
		<Alert tone="info" title="Ungespeicherter Entwurf gefunden">
			<p>{storedDraft.customerName || 'Ohne Kunde'} · {storedDraft.lines.length} Artikel</p>
			<div class="mt-2 flex gap-2">
				<Button size="sm" onclick={restore}>Fortsetzen</Button>
				<Button size="sm" variant="secondary" onclick={discard}>Verwerfen</Button>
			</div>
		</Alert>
	</div>
{/if}

{#key editorKey}
	<DeliveryNoteEditor bind:draft bind:customer storageKey={STORAGE_KEY} />
{/key}
