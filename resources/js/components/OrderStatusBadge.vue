<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import type { OrderStatus } from '@/types/dashboard';

defineProps<{
    status: OrderStatus;
}>();

/** Magenta only flags a problem: a cancelled order. */
const variants = {
    pending: 'neutral',
    confirmed: 'accent',
    delivered: 'outline',
    cancelled: 'alert',
} as const;
</script>

<template>
    <Badge :variant="variants[status]">{{
        $t(`enums.order_status.${status}`)
    }}</Badge>
</template>
