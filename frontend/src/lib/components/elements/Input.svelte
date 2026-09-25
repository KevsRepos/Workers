<script lang="ts">
let { label, placeholder = "", value = $bindable(""), type, ...restProps }: {
    label: string;
    placeholder?: string;
    value?: string|number;
    type?: 'text'|'number'
    [key: string]: any;
    
} = $props();
</script>

<label class="label">
    <span class="label-text">{label}</span>
    <input
        class="input bg-surface-50-950"
        type={type || 'text'}
        placeholder={placeholder}
        bind:value={value}
        {...restProps}
    />
</label>