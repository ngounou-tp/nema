<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import PublicHeader from '@/components/PublicHeader.vue';
import { Button } from '@/components/ui/button';
import { dashboard, home } from '@/routes';

const { status } = defineProps<{
    status: 403 | 404 | 429 | 500 | 503;
}>();

const page = usePage();
const isSignedIn = computed(() => Boolean(page.props.auth.user));
</script>

<template>
    <Head :title="$t(`errors.${status}.title`)" />

    <div class="flex min-h-svh flex-col">
        <PublicHeader />

        <main
            class="mx-auto w-full max-w-3xl flex-1 px-4 py-10 md:px-8 md:py-16"
        >
            <div class="rule-double" aria-hidden="true" />
            <p
                class="mt-8 text-7xl leading-none font-semibold tracking-[-0.03em] text-brand-700 tabular-nums md:text-9xl"
                aria-hidden="true"
            >
                {{ status }}
            </p>
            <h1 class="mt-4 text-3xl md:text-4xl">
                {{ $t(`errors.${status}.title`) }}
            </h1>
            <p class="mt-3 max-w-[36em] text-lg">
                {{ $t(`errors.${status}.description`) }}
            </p>

            <div class="mt-8 flex flex-wrap gap-2">
                <Button as-child size="lg">
                    <Link :href="isSignedIn ? dashboard() : home()">{{
                        isSignedIn
                            ? $t('errors.to_dashboard')
                            : $t('errors.to_home')
                    }}</Link>
                </Button>
            </div>
        </main>
    </div>
</template>
