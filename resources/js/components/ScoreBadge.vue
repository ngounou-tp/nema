<script setup lang="ts">
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';

const { score } = defineProps<{
    score: number;
}>();

/**
 * Thresholds from the product rules: ≥ 80 very reliable, 60–79 reliable, < 60 to watch.
 */
const level = computed(() => {
    if (score >= 80) {
        return { key: 'very_reliable', variant: 'accent' } as const;
    }

    if (score >= 60) {
        return { key: 'reliable', variant: 'neutral' } as const;
    }

    return { key: 'to_watch', variant: 'alert' } as const;
});
</script>

<template>
    <span class="inline-flex items-baseline gap-2">
        <span class="text-2xl font-semibold tabular-nums"
            >{{ score
            }}<span class="text-sm font-normal text-muted-foreground"
                >/100</span
            ></span
        >
        <Badge :variant="level.variant">{{
            $t(`score.level.${level.key}`)
        }}</Badge>
    </span>
</template>
