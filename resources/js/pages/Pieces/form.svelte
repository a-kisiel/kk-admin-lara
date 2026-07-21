<script lang="ts">    
    import * as Field from "@/components/ui/field/index.js";
    import { Input } from "@/components/ui/input/index.js";
    import { Switch } from '@/components/ui/switch';
    import { Textarea } from '@/components/ui/textarea';
    import {
        Tooltip,
        TooltipContent,
        TooltipTrigger,
    } from "@/components/ui/tooltip";

    import MultiSelect from 'svelte-multiselect'

    import { Image, Info } from "lucide-svelte";

    import PieceModal from '@/components/PieceModal.svelte';
    import ItemHeader from '@/components/ItemHeader.svelte';
    import FormButtons from '@/components/FormButtons.svelte';
    import Dropzone from "@/components/Dropzone.svelte";

    let {
        imgUrl,
        mode,
        piece = $bindable(),
        media = [],
        collections = []
    } = $props();

    let form = $state(piece ?? {});

    let imageURL = $state(piece?.hash ? `${imgUrl}compressed/${piece.hash}.webp` : '');
    let children = $state(piece?.children ?? []);

    let formValid = $derived(mode === 'edit' || form && form.title && (form.hash || imageURL !== ''));

    let selectedMedia = $state([]);
    form.media?.forEach((m: any) => selectedMedia.push({value: m.id, label: m.title}));
    let selectedCollections = $state([]);
    form.collections?.forEach((c: any) => selectedCollections.push({value: c.id, label: c.title}))

    function addChild(data: any) {
        children.push(data);
    }

    function deleteChild(index: number) {
        children.splice(index, 1);
    }

    function updateImage(data: any) {
        imageURL = data.url;
    }
</script>

<div>
    <ItemHeader
        item={form}
        type='pieces'
        label='Piece'
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
                <div class="piece-children">
                   {#if children}
                    {#each children as child, index (child.id)}
                    <PieceModal
                        parent={form}
                        piece={child}
                        index={index}
                        mode={mode}
                        imageUrl={imgUrl}
                        {deleteChild}
                    />
                    {/each}
                    {/if}
                    {#if mode !== 'show' && formValid}
                    <PieceModal
                        parent={form}
                        index={children.length}
                        mode={mode}
                        imageUrl={imgUrl}
                        {addChild}
                    />
                    {/if}
                </div>
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
                            bind.value={form.end_date}
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
                    <Field.Legend>Collections</Field.Legend>
                        {#if mode !== 'show'}
                        <MultiSelect
                            bind:value={selectedCollections}
                            name='collections'
                            options={collections} />
                        {:else}
                        <ul class="media-container">
                            {#each form.collections as collection}
                            <li><a href="/collections/{collection.id}">{collection.title}</a></li>
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
                        bind.value={form.active}
                        checked={form ? form.active : true}
                    />
                    {:else}
                    {form.active ? 'Yes' : 'No'}
                    {/if}
                </Field.Set>
                <Field.Set>
                    <Field.Legend>Use as wallpaper</Field.Legend>
                    {#if mode !== 'show'}
                    <Switch
                        id="is_wallpaper"
                        name="is_wallpaper"
                        bind.value={form.is_wallpaper}
                        checked={form?.is_wallpaper}
                    />
                    {:else}
                    {form.is_wallpaper ? 'Yes' : 'No'}
                    {/if}
                </Field.Set>
                {#if mode !== 'show'}
                <!-- <Field.Set>
                    <Field.Legend>Image File</Field.Legend>
                    <Input
                        id="file"
                        name="file"
                        type="file"
                    />
                </Field.Set> -->
                {/if}
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
                    type='pieces'
                    id={form?.id}
                />
            </Field.Group>
        </form>
    </div>
</div>