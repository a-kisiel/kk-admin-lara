<script lang="ts">    
    import * as Field from "@/components/ui/field/index.js";
    import { Input } from "@/components/ui/input/index.js";
    import { Textarea } from "@/components/ui/textarea/index.js";

    import ItemHeader from '@/components/ItemHeader.svelte';
    import FormButtons from '@/components/FormButtons.svelte';

    let {
        mode,
        collection = $bindable()
    } = $props();

    let form = $state(collection ?? {});
    console.log($state.snapshot(form))
</script>

<div>
    <ItemHeader
        item={form}
        type='collections'
        label='Collection'
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
                        placeholder=""
                        bind:value={form.title}
                        required
                    />
                    {:else}
                    {form.title}
                    {/if}
                </Field.Set>
                <Field.Set>
                    <Field.Legend>Location</Field.Legend>
                    {#if mode !== 'show'}
                    <Input
                        id="location"
                        name="location"
                        bind:value={form.location}
                    />
                    {:else}
                    {form.location}
                    {/if}
                </Field.Set>
                <Field.Set>
                    <Field.Legend>Description</Field.Legend>
                    {#if mode !== 'show'}
                    <Textarea
                        id="description"
                        name="description"
                        bind:value={form.description}
                    />
                    {:else}
                    {form.description}
                    {/if}
                </Field.Set>
                <Field.Set>
                    <Field.Legend>Start Date</Field.Legend>
                    {#if mode !== 'show'}
                    <Input
                        id="start_date"
                        name="start_date"
                        bind:value={form.start_date}
                    />
                    {:else}
                    {form.start_date}
                    {/if}
                </Field.Set>
                <Field.Set>
                    <Field.Legend>End Date</Field.Legend>
                    {#if mode !== 'show'}
                    <Input
                        id="end_date"
                        name="end_date"
                        bind:value={form.end_date}
                    />
                    {:else}
                    {form.end_date}
                    {/if}
                </Field.Set>
                <FormButtons
                    mode={mode}
                    type='collections'
                    id={form.id}
                />
            </Field.Group>
        </form>
    </div>
</div>