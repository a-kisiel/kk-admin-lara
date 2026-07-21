<script module lang="ts">    
    import { Button } from "@/components/ui/button/index.js";
    import * as Field from "@/components/ui/field/index.js";
</script>
<script>
    const {
        mode,
        type = null,
        id = null,
        save = () => {}
    } = $props();
</script>

{#if mode !== 'show'}
<Field.Field orientation="horizontal">
    <Button onclick={(e: any) => {save(e)}} type="submit">Submit</Button>
    <Button variant="outline" type="button"><a href="/{type}/{id}">Cancel</a></Button>
</Field.Field>
{:else}
<Field.Field orientation="horizontal">
    <Button><a href="/{type}/{id}/edit">Edit</a></Button>
</Field.Field>
{/if}