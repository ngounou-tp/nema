<script setup lang="ts">
import { Skeleton } from '@/components/ui/skeleton';
import { Spinner } from '@/components/ui/spinner';

const { label, lines = 3 } = defineProps<{
    label?: string;
    lines?: number;
}>();
</script>

<template>
    <div class="flex flex-col gap-3" aria-busy="true">
        <p class="flex items-center gap-2 text-sm text-muted-foreground">
            <Spinner />
            <span>{{ label ?? $t('ui.loading') }}</span>
        </p>
        <Skeleton
            v-for="line in lines"
            :key="line"
            class="h-4"
            :class="line === lines ? 'w-2/3' : 'w-full'"
        />
    </div>
</template>
