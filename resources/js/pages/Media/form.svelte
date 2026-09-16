<script lang="ts">    
    import * as Field from "@/components/ui/field/index.js";
    import { Input } from "@/components/ui/input/index.js";
    import { Switch } from '@/components/ui/switch';
    import {
        Tooltip,
        TooltipContent,
        TooltipTrigger,
    } from "@/components/ui/tooltip";

    import { Info } from "lucide-svelte";

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
                <Field.Set>
                    <Field.Legend>
                        <span class="field-clarification">
                            Support Medium
                            <Tooltip>
                                <TooltipTrigger><Info class="icon" /></TooltipTrigger>
                                <TooltipContent>
                                    Dictates whether this medium is something you paint/draw on, rather than with (e.g. canvas, linen, etc.)
                                </TooltipContent>
                            </Tooltip>
                        </span>
                    </Field.Legend>
                    {#if mode !== 'show'}
                    <Switch
                        id="is_support"
                        name="is_support"
                        bind.value={!!form.is_support}
                        checked={form ? form.is_support : false}
                    />
                    {:else}
                    {form.is_support ? 'Yes' : 'No'}
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