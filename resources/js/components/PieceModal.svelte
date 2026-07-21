<script lang="ts">
    import { Button, buttonVariants } from "@/components/ui/button/index.js";
    import * as Dialog from "@/components/ui/dialog/index.js";
    import * as AlertDialog from "@/components/ui/alert-dialog/index.js";
    import { Input } from "@/components/ui/input/index.js";
    import { Switch } from '@/components/ui/switch';
    import * as Field from "@/components/ui/field/index.js";
    import Label from "./ui/label/Label.svelte";
    import Textarea from "./ui/textarea/textarea.svelte";
    import FieldError from "./ui/field/field-error.svelte"
    import {
        Tooltip,
        TooltipContent,
        TooltipTrigger,
    } from "@/components/ui/tooltip";

    import { Image, SquarePlus } from "lucide-svelte";
    import { toast } from "svelte-sonner";
    import Dropzone from "./Dropzone.svelte";
    import Spinner from "./ui/spinner/Spinner.svelte";

    let {
        parent,
        piece = $bindable(),
        index = $bindable(),
        mode = '',
        imageUrl,
        addChild = () => {},
        deleteChild = () => {}
    } = $props();

    const required = ['title'];

    let form = $state({
        id: piece?.id,
        title: piece?.title,
        description: piece?.description,
        start_date: piece?.start_date,
        end_date: piece?.end_date,
        active: +piece?.active,
        compressed: new DataTransfer(),
        uncompressed: new DataTransfer(),
        hash: piece?.hash ?? ''
    });

    let imgUrl = $state(form.hash ? `${imageUrl}compressed/${form.hash}.webp` : '');

    let initial = $state.snapshot(form);

    const errors = $state({});

    let open = $state(false);
    let deleteOpen = $state(false);
    let saving = $state(false);
    let deleting = $state(false);

    async function deleteItem() {
        deleting = true;

        const res = await fetch(`/api/pieces/${piece.id}/delete`);
        deleteChild(index);

        deleting = false;
        deleteOpen = false;

        if (res.status === 200) {
            toast.success('Deletion successful');
            open = false;
        }
        else {
            toast.error('Error deleting -- try again');
        }
    }

    function updateImage(data: any) {
        form.compressed = new DataTransfer();
        form.uncompressed = new DataTransfer();

        Array.from(data.uncompressed.files).forEach((f: File) => form.uncompressed.items.add(f));
        Array.from(data.compressed.files).forEach((f: File) => form.compressed.items.add(f));
        
        imgUrl = data.url;
    }

    async function save()
    {
        const data = $state.snapshot(form);
        initial = data;

        // Set up files
        const fd = new FormData();
        Object.keys(data).forEach(k => {
            if (k !== 'compressed' && k !== 'uncompressed')
                fd.append(k, data[k] ?? '');
        });

        if (data.compressed.files[0] && data.uncompressed.files[0]) {
            fd.append('compressed', data.compressed.files[0]);
            fd.append('uncompressed', data.uncompressed.files[0]);
        }

        let parentID = parent.id ?? null;
        // If parent doesn't exist yet, create it with only the title
        if (!parent.id) {
            const p = $state.snapshot(parent);
            const pfd = new FormData();
            Object.keys(p).forEach(k => pfd.append(k, p[k]));
            const cRes = await fetch('/api/pieces/create', {
                'method': 'POST',
                'body': pfd
            });
            const created = await cRes.json();
            parentID = created.id;
        }

        const url = piece ?
            `/api/pieces/${piece.id}/update` :
            `/api/pieces/${parentID}/add-child`;

        const res = await fetch(url, {
            'method': 'POST',
            'body': fd,
        });

        const status = res.status;

        // Image was created -- add to parent's children
        if (!piece) {
            const c = await res.json();
            addChild(c);
        }

        if (status === 200 || status === 201) {
            toast.success('Saved');
            open = false;
        } else {
            console.log(status)
            toast.error('There was an error saving -- try again');
        }
    }

    function closeModal() {
        const data = $state.snapshot(form);
        let changed = false;
        Object.keys(data).forEach(k => {
            if (form[k] != initial[k])
                changed = true;
        });
        
        if (changed)
            toast.info("Your changes in the popup will not be saved unless you hit save in the popup");

        open = false;
    }

</script>

<div class="piece-popup">
    <Dialog.Root bind:open>
        <Dialog.Trigger>
            {#if form.hash}
            <Tooltip>
                <TooltipTrigger>
                    <div
                        class="child-preview"
                        style="background-image: url({imgUrl});"
                        role="img"
                        title={form.title}
                    ></div>
                </TooltipTrigger>
                <TooltipContent>
                    {form.title}
                </TooltipContent>
            </Tooltip>
            {:else if piece}
            <Tooltip>
                <TooltipTrigger>
                    <div class="child-preview default-image">
                        <Image class="default-icon" />
                    </div>
                </TooltipTrigger>
                <TooltipContent>
                    {form.title}
                </TooltipContent>
            </Tooltip>
            {:else}
            <Tooltip>
                <TooltipTrigger>
                    <SquarePlus class="child-preview" />
                </TooltipTrigger>
                <TooltipContent>
                    Add new
                </TooltipContent>
            </Tooltip>
            {/if}
        </Dialog.Trigger>
        <Dialog.Content class="md:max-w-4xl">
            <Dialog.Header>
                <Dialog.Title>
                    {form.title ?? 'New Image'}
                </Dialog.Title>
            </Dialog.Header>
                <div class="popup-content">
                    <div class="popup-image">
                        {#if imgUrl}
                        <div class="popup-image-img" style="background-image: url({imgUrl});">
                            {#if mode !== 'show'}
                            <Dropzone
                                index={index}
                                piece={form}
                                {updateImage}
                            />
                            {/if}
                        </div>
                        {:else}
                        <div class="default-image">
                            <div class="popup-image-img default-image">
                                {#if mode !== 'show'}
                                <Dropzone
                                    index={index}
                                    piece={form}
                                    {updateImage}
                                />
                                {/if}
                                <Image class="default-icon" />
                            </div>
                        </div>
                        {/if}
                    </div>
                    <div class="image-form">
                        <Field.Group>
                            <Field.Set>
                                <Label for="{index}_title">Title</Label>
                                {#if mode !== 'show'}
                                <Input
                                    id="{index}_title"
                                    name="children[{index}][title]"
                                    bind:value={form.title}
                                />
                                {#if errors['title']}
                                <FieldError>Enter a title</FieldError>
                                {/if}
                                {:else}
                                {form.title}
                                {/if}
                            </Field.Set>
                            <Field.Set>
                                <Label for="{index}_description">Description</Label>
                                {#if mode !== 'show'}
                                <Textarea
                                    id="{index}_description"
                                    name="children[{index}][description]"
                                    bind:value={form.description}
                                />
                                {:else}
                                {form.description}
                                {/if}
                            </Field.Set>
                            <Field.Set>
                                <Label for="{index}_start_date">Start Date</Label>
                                {#if mode !== 'show'}
                                <Input
                                    id="{index}_start_date"
                                    name="children[{index}][start_date]"
                                    bind:value={form.start_date}
                                />
                                {:else}
                                {form.start_date}
                                {/if}
                            </Field.Set>
                            <Field.Set>
                                <Label for="{index}_end_date">End Date</Label>
                                {#if mode !== 'show'}
                                <Input
                                    id="{index}_end_date"
                                    name="children[{index}][end_date]"
                                    bind:value={form.end_date}
                                />
                                {:else}
                                {form.end_date}
                                {/if}
                            </Field.Set>
                            <Field.Set>
                                <Field.Legend>Active</Field.Legend>
                                {#if mode !== 'show'}
                                <Switch
                                    id="active"
                                    name="children[{index}][active]"
                                    bind:checked={form.active}
                                />
                                {:else}
                                {form.active ? 'Yes' : 'No'}
                                {/if}
                            </Field.Set>
                            <!-- <Field.Set>
                                <Field.Legend>Image File</Field.Legend>
                                <Input
                                    id="file-{index}"
                                    name="children[{index}][file]"
                                    bind:files={form.file}
                                    type="file"
                                />
                            </Field.Set> -->
                            <Field.Set>
                                <Label for="{index}_hash">Hash</Label>
                                {#if mode !== 'show'}
                                <Input
                                    id="{index}_hash"
                                    name="children[{index}][hash]"
                                    bind:value={form.hash}
                                />
                                {:else}
                                {form.hash}
                                {/if}
                            </Field.Set>
                        </Field.Group>
                    </div>
                </div>
            <Dialog.Footer style="display: flex; justify-content: space-between">
                <div>
                    {#if index !== 'new' && mode !== 'show'}
                    <AlertDialog.Root bind:open={deleteOpen}>
                        <AlertDialog.Trigger>
                            <Button onclick={() => deleteOpen = true} variant="destructive">
                                Delete
                            </Button>
                        </AlertDialog.Trigger>
                        <AlertDialog.Content class="sm:max-w-[425px]">
                            <AlertDialog.Header>
                                <AlertDialog.Title>
                                    Are you sure you want to delete this image?
                                    <br/>&nbsp;<br/>
                                    The main piece and its data will not be affected.
                                </AlertDialog.Title>
                            </AlertDialog.Header>
                            <AlertDialog.Footer>
                                <AlertDialog.Cancel variant='ghost'>Cancel</AlertDialog.Cancel>
                                    <Button onclick={deleteItem} variant="destructive" disabled={deleting}>
                                        Delete
                                        {#if deleting}
                                        <Spinner />
                                        {/if}
                                    </Button>
                            </AlertDialog.Footer>
                        </AlertDialog.Content>
                    </AlertDialog.Root>
                    {/if}
                </div>
                <div>
                    <Dialog.Close onclick={closeModal}>
                        <Button variant="ghost" style="margin-right: 5px;">Close</Button>
                    </Dialog.Close>
                    {#if mode !== 'show'}
                    <Button onclick={save} disabled={saving}>
                        Save
                        {#if saving}
                        <Spinner />
                        {/if}
                    </Button>
                    {/if}
                </div>                
            </Dialog.Footer>
        </Dialog.Content>
    </Dialog.Root>
</div>