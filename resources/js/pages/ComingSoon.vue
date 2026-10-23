<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { PhHammer } from '@phosphor-icons/vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';

defineProps<{
    module: string;
}>();
</script>

<template>
    <Head :title="$t(`modules.${module}.title`)" />

    <div class="mx-auto w-full max-w-3xl px-4 py-8 md:px-8 md:py-12">
        <Badge variant="neutral">{{ $t('coming_soon.badge') }}</Badge>
        <h1 class="mt-3 text-3xl md:text-5xl">
            {{ $t(`modules.${module}.title`) }}
        </h1>
        <p class="mt-4 max-w-[38em] text-lg">
            {{ $t(`modules.${module}.description`) }}
        </p>

        <div
            class="mt-8 flex items-start gap-3 rounded-md bg-card p-4 text-[15px]"
        >
            <PhHammer
                weight="duotone"
                class="size-6 shrink-0 text-brand-700"
                aria-hidden="true"
            />
            <p>{{ $t('coming_soon.body') }}</p>
        </div>

        <Button as-child variant="outline" class="mt-6">
            <Link :href="dashboard()">{{ $t('coming_soon.back') }}</Link>
        </Button>
    </div>
</template>
