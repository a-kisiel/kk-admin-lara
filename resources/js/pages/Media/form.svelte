<script lang="ts">    
    import * as Field from "@/components/ui/field/index.js";
    import { Input } from "@/components/ui/input/index.js";
    import * as Select from "@/components/ui/select/index.js";

    import ItemHeader from '@/components/ItemHeader.svelte';
    import FormButtons from '@/components/FormButtons.svelte';

    let {
        mode,
        medium = $bindable(),
        mediaTypes
    } = $props();

    let form = $state(medium ?? {});

    if (mode === 'add')
        form.type = 0;

    const selectedMediaType = $derived(mediaTypes.find((label: string, id: number) => id === form.type));
</script>

<div>
    <ItemHeader
        item={medium}
        type='media'
        label='Medium'
        mode={mode}
    />
    
    <div class="item-form">
        <form method="POST">
            <Field.Group>
                <Field.Set>
                    <Field.Legend>Title</Field.Legend>
                        {#if mode !== 'show'}
                        <Input
                            id="title"
                            name="title"
                            bind:value={form.title}
                            required
                        />
                        {:else}
                        {form.title}
                        {/if}
                </Field.Set>
                <Field.Set>
                    <Field.Legend>Type</Field.Legend>
                        {#if mode !== 'show'}
                        <Select.Root
                            name="type"
                            bind:value={form.type}
                            type="single"
                        >
                            <Select.Trigger class="w-[180px]">{selectedMediaType}</Select.Trigger>
                            <Select.Content>
                            {#each mediaTypes as label, id}
                            <Select.Item value={id}>{label}</Select.Item>
                            {/each}
                            </Select.Content>
                        </Select.Root>
                        {:else}
                        {form.typeLabel}
                        {/if}
                </Field.Set>
                <FormButtons
                    mode={mode}
                    type='media'
                    id={form?.id}
                />
            </Field.Group>
        </form>
    </div>
</div>