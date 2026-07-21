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
    // import Dropzone from "./Dropzone_old.svelte";

    let {
        piece = $bindable(),
        index = $bindable(),
        mode = '',
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
        active: +piece?.active,
        file: null,
        hash: piece?.hash ?? ''
    });

    const errors = $state({});

    let open = $state(false);
    let deleteOpen = $state(false);

    function deleteItem() {
        deleteOpen = false;
        open = false;
        deleteChild(index);
    }

    function closeModal(save = false) {
        if (!save) {

        }
        else {
            const data = $state.snapshot(form);

            Object.keys(data).forEach(k => delete errors[k]);
            required.forEach(r => {                
                if (!data[r]) {
                    errors[r] = true;
                }
            });

            if (Object.keys(errors).length > 0)
                return;

            if (!piece) {
                addChild(data);
                Object.keys(form).forEach(k => form[k] = null);
            }

            updateChild(index, data);
            toast.info('Changes will be reflected when you save the main Piece');
        }
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
                        style="background-image: url(https://katieart.s3.us-east-2.amazonaws.com/hashed_compressed/{form.hash}.webp);"
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
                <Dialog.Description>
                </Dialog.Description>
            </Dialog.Header>
                <div class="popup-content">
                    <div class="popup-image">
                        {#if form.hash}
                        <div class="popup-image-img" style="background-image: url(https://katieart.s3.us-east-2.amazonaws.com/hashed_compressed/{form.hash}.webp);">
                            {#if mode !== 'show'}
                            <!-- <Dropzone
                                piece={form}
                            /> -->
                            {/if}
                        </div>
                        {:else}
                        <div class="default-image">
                            <div class="popup-image-img default-image">
                                {#if mode !== 'show'}
                                <!-- <Dropzone
                                    piece={form}
                                /> -->
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
                                    required
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
                            <Field.Set>
                                <Field.Legend>Image File</Field.Legend>
                                <Input
                                    id="file-{index}"
                                    name="children[{index}][file]"
                                    bind:files={form.file}
                                    type="file"
                                />
                            </Field.Set>
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
                                    <Button onclick={deleteItem} variant="destructive">
                                        Delete
                                    </Button>
                            </AlertDialog.Footer>
                        </AlertDialog.Content>
                    </AlertDialog.Root>
                    {/if}
                </div>
                <div>
                    <Dialog.Close>
                        <Button variant="ghost" style="margin-right: 5px;">Close</Button>
                    </Dialog.Close>
                    {#if mode !== 'show'}
                    <Button onclick={closeModal}>
                        Save
                    </Button>
                    {/if}
                </div>                
            </Dialog.Footer>
        </Dialog.Content>
    </Dialog.Root>
</div>