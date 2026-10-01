<script lang="ts">
let { label, checked = $bindable(), inputClasses = '', ...restProps } = $props();
</script>

<label class="flex items-center gap-2 {restProps.class}">
    <span>{label}</span>
    <input {...restProps} class="checkbox" type="checkbox" bind:checked={checked} />
</label>