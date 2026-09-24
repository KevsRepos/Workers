<script lang="ts">
let { label, checked = $bindable() } = $props();
</script>

<label class="flex items-center gap-2">
    <span>{label}</span>
    <input class="checkbox" type="checkbox" bind:checked={checked} />
</label>