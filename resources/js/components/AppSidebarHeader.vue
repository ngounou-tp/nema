<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import LanguageSwitcher from '@/components/LanguageSwitcher.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItem[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);

const page = usePage();
const user = computed(() => page.props.auth.user);
</script>

<template>
    <header
        class="flex h-14 shrink-0 items-center gap-3 border-b border-border px-4 md:h-16 md:px-6"
    >
        <!-- On phones the menu lives in the bottom bar: the header shows the brand instead. -->
        <SidebarTrigger class="-ml-1 hidden md:inline-flex" />
        <Link
            :href="dashboard()"
            class="md:hidden"
            :aria-label="$t('nav.home')"
        >
            <AppLogo />
        </Link>
        <Breadcrumbs
            v-if="breadcrumbs.length > 0"
            :breadcrumbs="breadcrumbs"
            class="hidden md:flex"
        />

        <div class="ml-auto flex items-center gap-4">
            <p v-if="user" class="hidden text-right leading-tight sm:block">
                <span class="block text-sm font-semibold">{{ user.name }}</span>
                <span class="block text-xs text-muted-foreground">{{
                    $t(`enums.user_role.${user.role}`)
                }}</span>
            </p>
            <LanguageSwitcher />
        </div>
    </header>
</template>
