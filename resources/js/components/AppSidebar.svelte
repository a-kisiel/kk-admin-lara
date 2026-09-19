<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import LayoutGrid from 'lucide-svelte/icons/layout-grid';
    import Image from 'lucide-svelte/icons/image';
    import Brush from 'lucide-svelte/icons/brush';
    import SquareLibrary from 'lucide-svelte/icons/square-library';
    import BookOpenText from 'lucide-svelte/icons/book-open-text';
    import NotebookPen from 'lucide-svelte/icons/notebook-pen';
    import type { Snippet } from 'svelte';
    import AppLogo from '@/components/AppLogo.svelte';
    import NavFooter from '@/components/NavFooter.svelte';
    import NavMain from '@/components/NavMain.svelte';
    import NavUser from '@/components/NavUser.svelte';
    import {
        Sidebar,
        SidebarContent,
        SidebarFooter,
        SidebarHeader,
        SidebarMenu,
        SidebarMenuButton,
        SidebarMenuItem,
    } from '@/components/ui/sidebar';
    import { toUrl } from '@/lib/utils';
    import { dashboard } from '@/routes';
    import type { NavItem } from '@/types';

    let {
        children,
    }: {
        children?: Snippet;
    } = $props();

    const mainNavItems: NavItem[] = [
        {
            title: 'Dashboard',
            href: dashboard(),
            icon: LayoutGrid,
        },
        {
            title: 'Pieces',
            href: '/pieces',
            icon: Image
        },
        {
            title: 'Books',
            href: '/books',
            icon: BookOpenText
        },
        {
            title: 'Sketches',
            href: '/sketches',
            icon: NotebookPen
        },
        {
            title: 'Media',
            href: '/media',
            icon: Brush
        },
        {
            title: 'Collections',
            href: '/collections',
            icon: SquareLibrary
        }
    ];
</script>

<Sidebar collapsible="icon" variant="inset">
    <SidebarHeader>
        <SidebarMenu>
            <SidebarMenuItem>
                <SidebarMenuButton size="lg" asChild>
                    {#snippet children(props)}
                        <Link
                            {...props}
                            href={toUrl(dashboard())}
                            class={props.class}
                        >
                            <AppLogo />
                        </Link>
                    {/snippet}
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarHeader>

    <SidebarContent>
        <NavMain items={mainNavItems} />
    </SidebarContent>

    <SidebarFooter>
        <NavUser />
    </SidebarFooter>
</Sidebar>
{@render children?.()}
