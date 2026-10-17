<script lang="ts">
import { fetchApi } from "$lib/fetchApi";
import SearchSelectionBox from "../SearchSelectionBox.svelte";
import CreateCustomerFormular from "./CreateCustomerFormular.svelte";
import Checkbox from "../elements/Checkbox.svelte";
import AddressFormular from "../customer/AddressFormular.svelte";
import { Address } from "../customer/Address.svelte";
import { Mail, MapPinHouse, Phone } from "@lucide/svelte";
import { onMount } from "svelte";

let {
    selectedCustomer = $bindable(),
    selectedShippingAddress = $bindable(),
    selectedBillingAddress = $bindable(),
    autoFocus = false,
    jump,
} = $props<{
    selectedCustomer: any;
    selectedShippingAddress?: string | null;
    selectedBillingAddress?: string | null;
    autoFocus?: boolean;
    jump?: () => void;
}>();

let searchTimeout: ReturnType<typeof setTimeout>;

let searchItems = $state([]);

let inputElement: HTMLInputElement | null = $state(null);

onMount(() => {
    if (autoFocus) {
        inputElement?.focus();
    }
});

let customerInput: string = $state('');

let freezedInput = $state('');

let addingCustomer = $state(false);

let differentBillingAddress = $state(selectedBillingAddress !== null);

let newAddress: Address | null = $state(null);

$effect(() => {
    if(!differentBillingAddress) {
        selectedBillingAddress = null;
    }
});

const searchCustomer = async (event: Event) => {

    const query = (event.target as HTMLInputElement).value;

    customerInput = query;
    
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(async () => {
        if (query.length < 2) {
            searchItems = [];

            return;
        }
        
        const json = await fetchApi(`customers/search?customerQuery=${encodeURIComponent(query)}`, 'GET');

        searchItems = json.map((item: any) => {
            return {id: item.id, name: item.customerName};
        });
    }, 300);
}

const getCustomer = async (customerId: string) => {
    const json = await fetchApi(`customer/${encodeURIComponent(customerId)}`, 'GET');
    
    return json;
}

const selectCustomer = async (item: { id: string; name: string }) => {
    const customer = await getCustomer(item.id);

    if(customer.defaultShippingAddress) {
        selectedShippingAddress = customer.defaultShippingAddress.id;
    }

    if(customer.defaultBillingAddress) {
        selectedBillingAddress = customer.defaultBillingAddress.id;
    }

    selectedCustomer = customer;
    
    customerInput = '';
    jump?.();
}

const addCustomer = async () => {
    freezedInput = customerInput;
    customerInput = '';

    addingCustomer = true;

    selectedCustomer = null;
}

const onCustomerCreation = (customer: any) => {
    addingCustomer = false;
    selectedCustomer = customer;
    freezedInput = '';

    if(customer.defaultShippingAddress) {
        selectedShippingAddress = customer.defaultShippingAddress.id;
    }

    if(customer.defaultBillingAddress) {
        selectedBillingAddress = customer.defaultBillingAddress.id;
    }
}

const saveAddress = async () => {
    if(!selectedCustomer || !newAddress) {
        return;
    }

    const json = await fetchApi(`customer-address/${encodeURIComponent(selectedCustomer.id)}`, 'POST', {
        street: newAddress.street,
        houseNumber: newAddress.houseNumber,
        postalCode: newAddress.postalCode,
        city: newAddress.city,
        standardShippingAddress: newAddress.standardShippingAddress,
        standardBillingAddress: newAddress.standardBillingAddress
    }).catch((error) => {
        console.error('Failed to save address:', error);
    });

    if(newAddress.standardShippingAddress) {
        selectedShippingAddress = json.data[0];
    }

    selectedCustomer.addresses.push(json.data[0]);

    newAddress = null;
};

// let addressCollection = $derived(useListCollection({
//     items: Object.values(selectedCustomer?.addresses ?? []).map((a: any) => ({ ...a, value: a.id }))
// }));
</script>

<SearchSelectionBox bind:inputElement={inputElement} bind:input={customerInput} bind:items={searchItems} searchItem={searchCustomer} onSelect={selectCustomer} label="Kunden suchen" placeholder="Kundename, Firma, Verein...">
    {#snippet aboveContent()}
        <ion-button onclick={addCustomer} class="btn preset-filled m-1">Kunden {customerInput} anlegen</ion-button>
    {/snippet}

    {#snippet content(item, onSelect)}
        <button onclick={() => onSelect(item)} class="w-full text-left p-2 hover:bg-gray-200 cursor-pointer">{item.name}</button>
    {/snippet}
</SearchSelectionBox>

{#if selectedCustomer}
    <div class="my-3 flex flex-col gap-1">
        <div class="flex items-center gap-2 text-2xl font-bold">
            <div>{selectedCustomer.displayName}</div>
        </div>

        {#if selectedCustomer.phone}
            <div class="flex items-center gap-2">
                <Phone size="18" />
                <div>{selectedCustomer.phone}</div>
            </div>
        {/if}

        {#if selectedCustomer.email}
            <div class="flex items-center gap-2">
                <Mail size="18" />
                <div>{selectedCustomer.email}</div>
            </div>
        {/if}
    </div>

    {#if selectedCustomer.addresses.length === 0}
        <div class="text-gray-400">Keine Adressen vorhanden</div>
    {:else}
        <!-- <Listbox defaultValue={selectedShippingAddress ? [selectedShippingAddress] : []} onValueChange={(e) => selectedShippingAddress = e.value[0]} collection={addressCollection} deselectable={true} class="mb-2">
            <Listbox.Label>Adresse</Listbox.Label>
            <Listbox.Content>
                {#each addressCollection.items as address (address.id)}
                    <Listbox.Item item={address}>
                        <div class="flex justify-between w-full">
                            <div>{address.street} {address.houseNumber}, {address.postalCode} {address.city}</div>
                            <div class="text-sm text-surface-400-600 italic">
                                {#if address.defaultShippingAddress}
                                    <span class="align-middle">Standard Versandadresse</span>
                                {/if}
                                {#if address.defaultBillingAddress}
                                    <span class="align-middle">Standard Rechnungsadresse</span>
                                {/if}
                            </div>
                        </div>
                    </Listbox.Item>
                {/each}
            </Listbox.Content>
        </Listbox> -->

        <ion-select placeholder="Adresse auswählen">
            {#each selectedCustomer.addresses as address (address.id)}
                <ion-select-option value={address.id}>
                    {address.street} {address.houseNumber}, {address.postalCode} {address.city}
                    {#if address.defaultShippingAddress}
                        <span class="align-middle">Standard Versandadresse</span>
                    {/if}
                    {#if address.defaultBillingAddress}
                        <span class="align-middle">Standard Rechnungsadresse</span>
                    {/if}
                </ion-select-option>
            {/each}
        </ion-select>

        <Checkbox label="Abweichende Rechnungsadresse" bind:checked={differentBillingAddress} />

        {#if differentBillingAddress}
            <!-- <Listbox defaultValue={selectedBillingAddress ? [selectedBillingAddress] : []} onValueChange={(e) => {console.log(e.value); selectedBillingAddress = e.value[0]}} collection={addressCollection} deselectable={true} class="mt-2">
                <Listbox.Label>Rechnungsadresse</Listbox.Label>
                <Listbox.Content>
                    {#each addressCollection.items as address (address.id)}
                        <Listbox.Item item={address}>
                            <div class="flex justify-between w-full">
                                <div>{address.street} {address.houseNumber}, {address.postalCode} {address.city}</div>
                                <div class="text-sm text-surface-400-600 italic">
                                    {#if address.defaultShippingAddress}
                                        <span class="align-middle">Standard Versandadresse</span>
                                    {/if}
                                    {#if address.defaultBillingAddress}
                                        <span class="align-middle">Standard Rechnungsadresse</span>
                                    {/if}
                                </div>
                            </div>
                        </Listbox.Item>
                    {/each}
                </Listbox.Content>
            </Listbox> -->
        {/if}
    {/if}

    {#if !newAddress}
        <ion-button expand="block" color="light" class="pt-2 h-48" onclick={() => newAddress = new Address("", "", "", "", false, false)}>
            <MapPinHouse slot="start" />
            Neue Adresse hinzufügen
        </ion-button>
    {/if}

    {#if newAddress}
        <AddressFormular address={newAddress} bind:validity={newAddress.validity} />
        <button type="button" disabled={newAddress?.validity !== true} onclick={saveAddress} class="btn preset-filled-primary-50-950 mt-2 w-full">Addresse anlegen</button>
    {/if}
{/if}

{#if addingCustomer}
    <CreateCustomerFormular input={freezedInput} oncreate={onCustomerCreation} />
{/if}