<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLogo from '@/components/AppLogo.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useNavigation } from '@/composables/useNavigation';
import { dashboard } from '@/routes';

const { items } = useNavigation();
</script>

<template>
    <Sidebar collapsible="icon" variant="sidebar">
        <SidebarHeader class="border-b border-sidebar-border py-3">
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton
                        size="lg"
                        as-child
                        class="group-data-[collapsible=icon]:justify-center"
                    >
                        <Link :href="dashboard()" :aria-label="$t('nav.home')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent class="pt-3">
            <NavMain :items="items" />
        </SidebarContent>

        <SidebarFooter class="border-t border-sidebar-border">
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
