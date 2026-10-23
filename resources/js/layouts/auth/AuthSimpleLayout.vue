<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLogo from '@/components/AppLogo.vue';
import DemoBanner from '@/components/DemoBanner.vue';
import LanguageSwitcher from '@/components/LanguageSwitcher.vue';
import { home } from '@/routes';

/**
 * title and description are translation keys (pages set them in defineOptions).
 */
defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="flex min-h-svh flex-col bg-background">
        <DemoBanner />
        <div class="flex justify-end px-4 pt-4 md:px-8">
            <LanguageSwitcher />
        </div>

        <div
            class="flex flex-1 flex-col items-center justify-center p-6 md:p-10"
        >
            <div class="w-full max-w-sm">
                <div class="flex flex-col gap-8">
                    <div class="flex flex-col items-center gap-4">
                        <Link :href="home()" :aria-label="$t('nav.home')">
                            <AppLogo size="lg" />
                        </Link>
                        <div class="rule-double w-full" aria-hidden="true" />
                        <div class="space-y-2 text-center">
                            <h1 v-if="title" class="text-2xl">
                                {{ $t(title) }}
                            </h1>
                            <p
                                v-if="description"
                                class="text-center text-[15px] text-muted-foreground"
                            >
                                {{ $t(description) }}
                            </p>
                        </div>
                    </div>
                    <slot />
                </div>
            </div>
        </div>
    </div>
</template>
