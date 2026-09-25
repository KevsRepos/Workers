<script lang="ts">
import { Listbox, useListCollection } from "@skeletonlabs/skeleton-svelte";
import Input from "../elements/Input.svelte";
import type { Address } from "./Address.svelte.ts";

let { address, validity = $bindable(false)}: { address: Address; validity: boolean } = $props();

const collection = useListCollection({
    items: [
        { label: "Standardlieferadresse", value: "shipping" },
        { label: "Standardrechnungsadresse", value: "billing" },
    ]
});

let form = $state<HTMLFormElement>();
</script>

<form class="flex flex-col gap-4 mt-4" bind:this={form} oninput={() => validity = form?.checkValidity() ?? false}>
    <fieldset class="flex flex-col sm:flex-row justify-between gap-2">
        <Input label="Straße" placeholder="Straße" bind:value={address.street} required />
        <Input label="Hausnummer" placeholder="Hausnummer" bind:value={address.houseNumber} required />
    </fieldset>

    <fieldset class="flex justify-between gap-2">
        <Input label="PLZ" placeholder="PLZ" bind:value={address.postalCode} required />
        <Input label="Ort" placeholder="Ort" bind:value={address.city} required />
    </fieldset>

    <Listbox {collection} selectionMode="multiple" onValueChange={(e) => {
        address.standardShippingAddress = e.value.includes("shipping");
        address.standardBillingAddress = e.value.includes("billing");
    }}>
        <Listbox.Label>Adressart</Listbox.Label>
        <Listbox.Content>
            {#each collection.items as item (item.value)}
                <Listbox.Item {item}>
                    <Listbox.ItemText>{item.label}</Listbox.ItemText>
                    <Listbox.ItemIndicator />
                </Listbox.Item>
            {/each}
        </Listbox.Content>
    </Listbox>
</form>
