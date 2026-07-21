<script lang="ts">    
    import * as Field from "@/components/ui/field/index.js";
    import { Input } from "@/components/ui/input/index.js";

    import ItemHeader from '@/components/ItemHeader.svelte';
    import FormButtons from '@/components/FormButtons.svelte';

    let {
        mode,
        medium = $bindable()
    } = $props();

    let form = $state(medium ?? {});
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
                <FormButtons
                    mode={mode}
                    type='media'
                    id={form?.id}
                />
            </Field.Group>
        </form>
    </div>
</div>