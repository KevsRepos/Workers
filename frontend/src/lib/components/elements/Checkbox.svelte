<script lang="ts">
import { Check } from "@lucide/svelte";

let { label, checked = $bindable(), inputClasses = '', ...restProps } = $props();
</script>

<label class="w-full flex justify-between rounded bg-surface-200-800 items-center gap-2 {restProps.class}">
    <span class="p-2 italic">{label}</span>
    <input {...restProps} class="peer relative hidden" type="checkbox" bind:checked={checked} />
    <span class="peer-checked:border-0 bg-surface-100-900 peer-checked:bg-primary-500 p-1 mr-2 h-full rounded-lg">
        <Check color={checked ? 'white' : 'transparent'} size="16" />
    </span>
</label>