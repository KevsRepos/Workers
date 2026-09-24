<script lang="ts">
import { fetchApi } from "$lib/fetchApi";
import { useListCollection, Listbox } from "@skeletonlabs/skeleton-svelte";
import SearchSelectionBox from "../SearchSelectionBox.svelte";
import CreateCustomerFormular from "./CreateCustomerFormular.svelte";
import Checkbox from "../elements/Checkbox.svelte";

let { selectedCustomer = $bindable(), selectedShippingAddress = $bindable(), selectedBillingAddress = $bindable() } = $props();

let searchTimeout: ReturnType<typeof setTimeout>;

let searchItems = $state([]);

let customerInput: string = $state('');

let freezedInput = $state('');

let addingCustomer = $state(false);

let differentBillingAddress = $state(selectedBillingAddress !== null);

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

let addressCollection = $derived(useListCollection({
    items: Object.values(selectedCustomer?.addresses ?? []).map((a: any) => ({ ...a, value: a.id }))
}));
</script>

<SearchSelectionBox bind:input={customerInput} bind:items={searchItems} searchItem={searchCustomer} onSelect={selectCustomer} label="Kunde">
    {#snippet aboveContent()}
        <button onclick={addCustomer} class="btn preset-filled m-1">Kunden {customerInput} anlegen</button>
    {/snippet}

    {#snippet content(item, onSelect)}
        <button onclick={() => onSelect(item)} class="w-full text-left p-2 hover:bg-gray-200 cursor-pointer">{item.name}</button>
    {/snippet}
</SearchSelectionBox>

{#if selectedCustomer}
    <div class="py-2 text-3xl">
        {#if selectedCustomer.company}
            {selectedCustomer.companyName}
        {:else}
            {selectedCustomer.firstName} {selectedCustomer.surname}
        {/if}
    </div>

    {#if selectedCustomer.addresses.length === 0}
        <div>Keine Adressen vorhanden</div>
    {:else}
        <Listbox defaultValue={selectedShippingAddress ? [selectedShippingAddress] : []} onValueChange={(e) => selectedShippingAddress = e.value[0]} collection={addressCollection} deselectable={true} class="mb-2">
            <Listbox.Label>Adresse</Listbox.Label>
            <Listbox.Content>
                {#each addressCollection.items as address (address.id)}
                    <Listbox.Item item={address}>
                        <div class="flex justify-between w-full">
                            <div>{address.street} {address.houseNumber}, {address.postalCode} {address.city}</div>
                            <div class="text-sm text-surface-400-600 italic">
                                {#if address.defaultShippingAddress}
                                    <span>Standard Versandadresse</span>
                                {/if}
                                {#if address.defaultBillingAddress}
                                    <span>Standard Rechnungsadresse</span>
                                {/if}
                            </div>
                        </div>
                    </Listbox.Item>
                {/each}
            </Listbox.Content>
        </Listbox>

        <Checkbox label="Abweichende Rechnungsadresse" bind:checked={differentBillingAddress} />

        {#if differentBillingAddress}
            <Listbox defaultValue={selectedBillingAddress ? [selectedBillingAddress] : []} onValueChange={(e) => {console.log(e.value); selectedBillingAddress = e.value[0]}} collection={addressCollection} deselectable={true} class="mt-2">
                <Listbox.Label>Rechnungsadresse</Listbox.Label>
                <Listbox.Content>
                    {#each addressCollection.items as address (address.id)}
                        <Listbox.Item item={address}>
                            <div class="flex justify-between w-full">
                                <div>{address.street} {address.houseNumber}, {address.postalCode} {address.city}</div>
                                <div class="text-sm text-surface-400-600 italic">
                                    {#if address.defaultShippingAddress}
                                        <span>Standard Versandadresse</span>
                                    {/if}
                                    {#if address.defaultBillingAddress}
                                        <span>Standard Rechnungsadresse</span>
                                    {/if}
                                </div>
                            </div>
                        </Listbox.Item>
                    {/each}
                </Listbox.Content>
            </Listbox>
        {/if}
    {/if}
{/if}

{#if addingCustomer}
    <CreateCustomerFormular input={freezedInput} oncreate={onCustomerCreation} />
{/if}