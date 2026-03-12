<script lang="ts">
import { fetchApi } from "$lib/fetchApi";
import { useListCollection, type ComboboxRootProps, Combobox, Portal } from "@skeletonlabs/skeleton-svelte";

let { selectedCustomer = $bindable(), jump } = $props();

let searchTimeout: ReturnType<typeof setTimeout>;
let customers: Array<any> = $state([]);

let customerInput: string = $state('');

const collection = $derived(useListCollection({ 
    items: customers,
    itemToString: (item) => item.firstName + ' ' + item.surname,
    itemToValue: (item) => item
}));

const searchCustomer = async (event: Event) => {
    const query = (event.target as HTMLInputElement).value;

    customerInput = query;
    
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(async () => {
        if (query.length < 2) {
            customers = [];

            open = false;
            return;
        }

        open = true;
        
        const json = await fetchApi(`customers/search?customerName=${encodeURIComponent(query)}`, 'GET');
        
        customers = json;
    }, 300);
}

const selectCustomer: ComboboxRootProps['onSelect'] = (event) => {    
    selectedCustomer = event.itemValue;

    open = false;

    jump();
}

let open = $state(false);

const addCustomer = async () => {
    const [firstName, ...surnameParts] = customerInput.trim().split(/\s+/);
    const surname = surnameParts.join(' ');

    const json = await fetchApi('customers', 'POST',  { firstName, surname });

    console.log(json);

    selectedCustomer = json.data;

    open = false;
}
</script>

<Combobox defaultInputValue={selectedCustomer ? `${selectedCustomer.firstName} ${selectedCustomer.surname}` : ''} open={open} autoFocus={true} collection={collection} onSelect={selectCustomer} placeholder="Kunde auswählen" class="w-full px-4">
    <Combobox.Label>Kunde</Combobox.Label>
    <Combobox.Control>
        <Combobox.Input oninput={searchCustomer} />
    </Combobox.Control>
    <Portal>
        <Combobox.Positioner>
            <Combobox.Content>
                {#if customers.length === 0 && customerInput.trim().split(/\s+/).length >= 2}
                    <button onclick={addCustomer} class="btn preset-filled">Kunden {customerInput} anlegen</button>
                {:else if customers.length > 0}
                    {#each customers as item (item.id)}
                        {item.value}
                        <Combobox.Item item={item}>
                            <Combobox.ItemText>{item.firstName} {item.surname}</Combobox.ItemText>
                            <Combobox.ItemIndicator />
                        </Combobox.Item>
                    {/each}
                {/if}
            </Combobox.Content>
        </Combobox.Positioner>
    </Portal>
</Combobox>