<script lang="ts">
import TextInput from "../elements/TextInput.svelte";
import AddressFormular from "../customer/AddressFormular.svelte";
import { fetchApi } from "$lib/fetchApi";
import { Address } from "../customer/Address.svelte.ts";
import Checkbox from "../elements/Checkbox.svelte";
import { X } from "@lucide/svelte";

let { input, oncreate }: { input: string; oncreate?: (customer: any) => void } = $props();

const addressForms: Address[] = $state([new Address("", "", "", "", false, false)]);

let isCompany = $state(false);
let withoutAddress = $state(false);

let firstName = $state(input.split(" ")[0] ?? "");
let surname = $state(input.split(" ").slice(1).join(" ") ?? "");
let companyName = $state(input);

let email = $state("");
let phone = $state("");

const addAddressFormular = () => {
    addressForms.push(new Address("", "", "", "", false, false));
};

const createCustomer = async () => {
    const json = await fetchApi("customers", "POST", {
        firstName: isCompany ? null : firstName || null,
        surname: isCompany ? null : surname || null,
        companyName: isCompany ? companyName || null : null,
        email: email || null,
        phone: phone || null,
        addresses: addressForms.map((a) => ({
            street: a.street || null,
            houseNumber: a.houseNumber || null,
            postalCode: a.postalCode || null,
            city: a.city || null,
            country: null,
            standardShippingAddress: a.standardShippingAddress,
            standardBillingAddress: a.standardBillingAddress,
        })),
    });

    oncreate?.(json?.data?.customer);
};
</script>

<div class="flex flex-col gap-4 mt-4">
    <label class="flex items-center gap-2">
        <span>Firma/Verein</span>
        <input class="checkbox" type="checkbox" bind:checked={isCompany} />
    </label>

    {#if !isCompany}
        <fieldset class="flex flex-col sm:flex-row justify-between gap-2">
            <TextInput label="Vorname" placeholder="Vorname" bind:value={firstName} />
            <TextInput label="Nachname" placeholder="Nachname" bind:value={surname} />
        </fieldset>
    {:else}
        <fieldset class="flex justify-between gap-2">
            <TextInput label="Firma/Verein" placeholder="Firma/Verein" bind:value={companyName} />
        </fieldset>
    {/if}

    <fieldset class="flex flex-col sm:flex-row justify-between gap-2">
        <TextInput label="E-Mail" placeholder="E-Mail" bind:value={email} />
        <TextInput label="Telefon" placeholder="Telefon" bind:value={phone} />
    </fieldset>

    <Checkbox label="Ohne Adresse anlegen" bind:checked={withoutAddress} />

    {#if !withoutAddress}
        {#each addressForms as form, index}
            {#if index > 0}
                <button type="button" onclick={() => addressForms.splice(addressForms.indexOf(form), 1)} class="w-fit self-end bg-surface-500 text-white rounded">
                    <X />
                </button>
            {/if}

            <AddressFormular address={form} bind:validity={form.validity} />
            <hr />
        {/each}
    {/if}

    {#if !withoutAddress}
        <button type="button" onclick={addAddressFormular} disabled={addressForms.some((form) => !form.validity)}>Weitere Adresse hinzufügen</button>
    {/if}

    <button type="button" onclick={createCustomer} class="btn preset-filled-primary-50-950" disabled={addressForms.some((form) => !form.validity)}>Kunden anlegen</button>
</div>
