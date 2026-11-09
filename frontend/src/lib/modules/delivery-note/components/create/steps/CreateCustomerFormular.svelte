<script lang="ts">
import Input from "$lib/components/forms/Input.svelte";
import AddressFormular from "$lib/modules/customer/components/AddressFormular.svelte";
import { createCustomer as createCustomerRequest } from "$lib/modules/customer/api";
import { Address } from "$lib/modules/customer/components/Address.svelte";
import Checkbox from "$lib/components/forms/Checkbox.svelte";
import { X } from "@lucide/svelte";
import { onMount } from "svelte";

let { input, oncreate }: { input: string; oncreate?: (customer: any) => void } = $props();

let addressForms: Address[] = $state([new Address("", "", "", "", false, false)]);

let isCompany = $state(false);
let withoutAddress = $state(false);

let firstName = $state("");
let surname = $state("");
let companyName = $state("");

const initializeFromInput = (value: string): void => {
    firstName = value.split(" ")[0] ?? "";
    surname = value.split(" ").slice(1).join(" ") ?? "";
    companyName = value;
};

onMount(() => {
    initializeFromInput(input);
});

let email = $state("");
let phone = $state("");

const addAddressFormular = () => {
    addressForms.push(new Address("", "", "", "", false, false));
};

const createCustomer = async () => {
    const json = await createCustomerRequest({
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

const removeAddressForms = (evt: Event) => {
    if((evt.target as HTMLInputElement).checked)  {
        addressForms = [];
    } else {
        addressForms.push(new Address("", "", "", "", false, false));
    }
};

const verifycustomerCreation = () => {
    if(
        (!withoutAddress && addressForms.some((form) => !form.validity)) || 
        (!isCompany && (!firstName || !surname)) || 
        (isCompany && !companyName)
    ) {
        return false;
    }

    return true;
}
</script>

<div class="flex flex-col gap-4 mt-4">
    <label class="flex items-center gap-2">
        <span>Firma/Verein</span>
        <input class="checkbox" type="checkbox" bind:checked={isCompany} />
    </label>

    {#if !isCompany}
        <fieldset class="flex flex-col sm:flex-row justify-between gap-2">
            <Input label="Vorname" placeholder="Vorname" bind:value={firstName} />
            <Input label="Nachname" placeholder="Nachname" bind:value={surname} />
        </fieldset>
    {:else}
        <fieldset class="flex justify-between gap-2">
            <Input label="Firma/Verein" placeholder="Firma/Verein" bind:value={companyName} />
        </fieldset>
    {/if}

    <fieldset class="flex flex-col sm:flex-row justify-between gap-2">
        <Input label="E-Mail" placeholder="E-Mail" bind:value={email} />
        <Input label="Telefon" placeholder="Telefon" bind:value={phone} />
    </fieldset>

    <Checkbox label="Ohne Adresse anlegen" bind:checked={withoutAddress} oninput={(evt: Event) => removeAddressForms(evt)} />

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

    <button type="button" onclick={createCustomer} class="btn preset-filled-primary-50-950" disabled={!verifycustomerCreation()}>Kunden anlegen</button>
</div>