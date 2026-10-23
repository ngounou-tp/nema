<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { toUrl } from '@/lib/utils';
import type { NavItem } from '@/types';

defineProps<{
    items: NavItem[];
}>();

const { isCurrentOrParentUrl } = useCurrentUrl();
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel>{{ $t('nav.main') }}</SidebarGroupLabel>
        <SidebarMenu>
            <SidebarMenuItem v-for="item in items" :key="toUrl(item.href)">
                <SidebarMenuButton
                    as-child
                    size="lg"
                    :is-active="isCurrentOrParentUrl(item.href)"
                    :tooltip="item.title"
                    class="h-10 text-[15px] data-[active=true]:font-semibold"
                >
                    <Link
                        :href="item.href"
                        :aria-current="
                            isCurrentOrParentUrl(item.href) ? 'page' : undefined
                        "
                    >
                        <component
                            :is="item.icon"
                            weight="duotone"
                            class="size-5!"
                            aria-hidden="true"
                        />
                        <span>{{ item.title }}</span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
