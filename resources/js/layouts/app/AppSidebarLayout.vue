<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import DemoBanner from '@/components/DemoBanner.vue';
import MobileBottomNav from '@/components/MobileBottomNav.vue';
import { Toaster } from '@/components/ui/sonner';
import type { BreadcrumbItem } from '@/types';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppShell variant="sidebar">
        <AppSidebar />
        <AppContent variant="sidebar" class="min-w-0 overflow-x-clip">
            <DemoBanner />
            <AppSidebarHeader :breadcrumbs="breadcrumbs" />
            <!-- Room for the mobile bottom bar. -->
            <div class="flex flex-1 flex-col pb-20 md:pb-0">
                <slot />
            </div>
        </AppContent>
        <MobileBottomNav />
        <Toaster />
    </AppShell>
</template>
