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

    let {
        piece = $bindable(),
        index = $bindable(),
        mode = '',
        imageUrl,
        addChild = () => {},
        updateChild = () => {},
        deleteChild = () => {}
    } = $props();

    const required = ['title'];

    let form = $state({
        id: piece?.id,
        title: piece?.title,
        description: piece?.description,
        start_date: piece?.start_date,
        end_date: piece?.end_date,
        active: !!piece?.active,
        hash: piece?.hash ?? '',
        compressed: null,
        uncompressed: null
    });

    let imgUrl = $state(form.hash ? `${imageUrl}hashed_compressed/${form.hash}.webp` : '');

    let initial = $state.snapshot(form);

    const errors = $state({});

    let open = $state(false);
    let deleteOpen = $state(false);

    let formIdentifier = $derived(piece?.id ?? `new_${index}`);

    async function deleteItem() {
        deleteChild(index);
        deleteOpen = false;
    }

    function updateImage(data: any) {
        imgUrl = data.url;
        form.compressed = data.compressed.files;
        form.uncompressed = data.uncompressed.files;
    }

    async function save()
    {
        const data = $state.snapshot(form);
        initial = data;

        if (!form.title)
            return;

        if (!piece) {
            data.id = `new_${index}`;
            addChild(data);
            form = {
                id: '',
                title: '',
                description: '',
                start_date: '',
                end_date: '',
                active: true,
                hash: '',
                compressed: null,
                uncompressed: null
            };
        }
        else {
            updateChild(index, data);
        }

        open = false;
    }

</script>

<div class="piece-popup">
    <Dialog.Root bind:open>
        <Dialog.Trigger>
            {#if form.hash || imgUrl}
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
                {#if form.compressed}
                <input hidden
                    name={`children[${form.id}][compressed]`}
                    files={form.compressed}
                    type="file"
                >
                {/if}
                {#if form.uncompressed}
                <input hidden
                    name={`children[${form.id}][uncompressed]`}
                    files={form.uncompressed}
                    type="file"
                >
                {/if}
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
                                <Label for="{formIdentifier}_title">Title</Label>
                                {#if mode !== 'show'}
                                <Input
                                    id="{formIdentifier}_title"
                                    name="children[{formIdentifier}][title]"
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
                                <Label for="{formIdentifier}_description">Description</Label>
                                {#if mode !== 'show'}
                                <Textarea
                                    id="{formIdentifier}_description"
                                    name="children[{formIdentifier}][description]"
                                    bind:value={form.description}
                                />
                                {:else}
                                {form.description}
                                {/if}
                            </Field.Set>
                            <Field.Set>
                                <Label for="{formIdentifier}_start_date">Start Date</Label>
                                {#if mode !== 'show'}
                                <Input
                                    id="{formIdentifier}_start_date"
                                    name="children[{formIdentifier}][start_date]"
                                    bind:value={form.start_date}
                                />
                                {:else}
                                {form.start_date}
                                {/if}
                            </Field.Set>
                            <Field.Set>
                                <Label for="{formIdentifier}_end_date">End Date</Label>
                                {#if mode !== 'show'}
                                <Input
                                    id="{formIdentifier}_end_date"
                                    name="children[{formIdentifier}][end_date]"
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
                            <Field.Set>
                                <Label for="{formIdentifier}_hash">Hash</Label>
                                {#if mode !== 'show'}
                                <Input
                                    id="{formIdentifier}_hash"
                                    name="children[{formIdentifier}][hash]"
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
                    {#if piece && mode !== 'show'}
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
                                    <Button onclick={deleteItem} variant="destructive">
                                        Delete
                                    </Button>
                            </AlertDialog.Footer>
                        </AlertDialog.Content>
                    </AlertDialog.Root>
                    {/if}
                </div>
                <div>
                    {#if mode !== 'show'}
                    <Button onclick={save}>
                        Close
                    </Button>
                    {/if}
                </div>                
            </Dialog.Footer>
        </Dialog.Content>
    </Dialog.Root>
</div>