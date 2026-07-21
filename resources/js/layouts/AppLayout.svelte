<script lang="ts">
    import type { Snippet } from 'svelte';
    import AppLayout from '@/layouts/app/AppSidebarLayout.svelte';
    import type { BreadcrumbItem } from '@/types';
    import { Toaster } from '@/components/ui/sonner';
    import TooltipProvider from '@/components/ui/tooltip/tooltip-provider.svelte';

    import { toast } from "svelte-sonner";

    let {
        breadcrumbs = [],
        children,
    }: {
        breadcrumbs?: BreadcrumbItem[];
        children?: Snippet;
    } = $props();

    const params = new URL(window.location.href).searchParams;
    const msg = params.get('msg');
    if (msg) {
        toast.success(msg);
        params.delete('msg');
    }
    // window.history.replaceState({}, '', `${window.location.pathname}?${params}`);
</script>

<AppLayout {breadcrumbs}>
    {@render children?.()}
    <Toaster />
    <TooltipProvider></TooltipProvider>
</AppLayout>
