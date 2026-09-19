<script lang="ts">
    import AppHead from '@/components/AppHead.svelte';
    import * as Field from "@/components/ui/field/index.js";
    import { Input } from "@/components/ui/input/index.js";
    import { Switch } from '@/components/ui/switch';
    import { Textarea } from '@/components/ui/textarea';
    import {
        Tooltip,
        TooltipContent,
        TooltipTrigger,
    } from "@/components/ui/tooltip";

    import MultiSelect from 'svelte-multiselect';

    import { Image, Info } from "lucide-svelte";

    import ItemHeader from '@/components/ItemHeader.svelte';
    import FormButtons from '@/components/FormButtons.svelte';
    import Dropzone from "@/components/Dropzone.svelte";

    let {
        imgUrl,
        mode,
        sketch = $bindable(),
        media = []
    } = $props();

    let form = $state(sketch ?? {});

    let imageURL = $state(sketch?.hash ? `${imgUrl}sketches/compressed/${sketch.hash}.webp` : '');

    let selectedMedia = $state(new Array());
    form.media?.forEach((m: any) => selectedMedia.push({value: m.id, label: m.title}));

    function updateImage(data: any) {
        imageURL = data.url;
        form.compressed = data.compressed.files;
        form.uncompressed = data.uncompressed.files;
    }
</script>

<AppHead title={form.title ?? 'New Sketch'} />

<div>
    <ItemHeader
        item={form}
        type='sketches'
        label='Sketch'
        mode={mode}
    />
    
    <div class="item-form">
        <form
            method="POST"
            enctype="multipart/form-data"
            class="piece-form"
        >
            <div class="piece-images">
                {#if imageURL}
                <div
                    class="main-preview"
                    style="background-image: url({imageURL});"
                    role="img"
                    title={form.title}
                >
                    {#if mode !== 'show'}
                    <Dropzone
                        index='main'
                        piece={form}
                        {updateImage}
                    />
                    {/if}

                    {#if form.compressed}
                        <input hidden
                            name="main_compressed"
                            type="file"
                            files={form.compressed}
                        >
                    {/if}
                    {#if form.uncompressed}
                        <input hidden
                            name="main_uncompressed"
                            type="file"
                            files={form.uncompressed}
                        >
                    {/if}
                </div>
                {:else}
                <div class="main-preview default-image">
                    <Dropzone
                        index='main'
                        piece={form}
                        {updateImage}
                    />
                    <Image class="default-icon" />
                </div>
                {/if}
            </div>
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
                    <Field.Legend>Dimensions</Field.Legend>
                    {#if mode !== 'show'}
                    <Input
                        id="dimensions"
                        name="dimensions"
                        bind:value={form.dimensions}
                    />
                    {:else}
                    {form.dimensions}
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
                <Field.Group className="grid max-w-sm grid-cols-2">
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
                            value={form.end_date}
                            on:input={(e) => (form.end_date = e.currentTarget.value)}
                        />
                        {:else}
                        {form.end_date}
                        {/if}
                    </Field.Set>
                </Field.Group>
                <Field.Set>
                    <Field.Legend>Media</Field.Legend>
                        {#if mode !== 'show'}
                        <MultiSelect
                            bind:value={selectedMedia}
                            name='media'
                            options={media} />
                        {:else}
                        <ul class="media-container">
                            {#each form.media as medium}
                            <li><a href="/media/{medium.id}">{medium.title}</a></li>
                            {/each}
                        </ul>
                        {/if}
                </Field.Set>
                <Field.Set>
                    <Field.Legend>Active</Field.Legend>
                    {#if mode !== 'show'}
                    <Switch
                        id="active"
                        name="active"
                        bind.value={!!form.active}
                        checked={form ? form.active : true}
                    />
                    {:else}
                    {form.active ? 'Yes' : 'No'}
                    {/if}
                </Field.Set>
                <Field.Set>
                    <Field.Legend>
                        <span class="field-clarification">
                            Location
                            {#if mode !== 'show'}
                            <Tooltip>
                                <TooltipTrigger><Info class="icon" /></TooltipTrigger>
                                <TooltipContent>
                                    Purely internal field to track the state/physical location of sketches
                                </TooltipContent>
                            </Tooltip>
                            {/if}
                        </span>
                    </Field.Legend>
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
                    <Field.Legend>
                        <span class="field-clarification">
                            Hash
                            {#if mode !== 'show'}
                            <Tooltip>
                                <TooltipTrigger><Info class="icon" /></TooltipTrigger>
                                <TooltipContent>
                                    This field is automatically generated when you upload a file, but can be used if you already uploaded a file to S3 that you'd like to use instead.
                                </TooltipContent>
                            </Tooltip>
                            {/if}
                        </span>
                    </Field.Legend>
                    {#if mode !== 'show'}
                    <Input
                        id="hash"
                        name="hash"
                        bind:value={form.hash}
                    />
                    {:else}
                    {form.hash}
                    {/if}
                </Field.Set>
                <FormButtons
                    mode={mode}
                    type='sketches'
                    id={form?.id}
                />
            </Field.Group>
        </form>
    </div>
</div>