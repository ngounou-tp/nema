<script setup lang="ts">
import OrderStatusBadge from '@/components/OrderStatusBadge.vue';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { formatDate, formatMoney, formatWeight } from '@/lib/format';
import type { DashboardOrder } from '@/types/dashboard';

defineProps<{
    orders: DashboardOrder[];
    /** Column title for the other party: the buyer for sellers, the producer for buyers. */
    counterpartLabel: string;
}>();
</script>

<template>
    <!-- A table only when its own column is wide enough (container query), otherwise one block per order. -->
    <div class="@container">
        <ul class="divide-y divide-border @2xl:hidden">
            <li v-for="order in orders" :key="order.id" class="py-3">
                <div class="flex items-start justify-between gap-3">
                    <p class="font-semibold">{{ order.product }}</p>
                    <OrderStatusBadge :status="order.status" />
                </div>
                <p class="mt-0.5 text-sm text-neutral-700">
                    {{ order.counterpart }}
                </p>
                <p class="mt-1 flex justify-between text-sm tabular-nums">
                    <span>{{ formatWeight(order.quantityKg) }}</span>
                    <span class="font-semibold">{{
                        formatMoney(order.totalXaf)
                    }}</span>
                </p>
            </li>
        </ul>

        <Table class="hidden @2xl:table">
            <TableHeader>
                <TableRow class="hover:bg-transparent">
                    <TableHead>{{ $t('orders.product') }}</TableHead>
                    <TableHead>{{ counterpartLabel }}</TableHead>
                    <TableHead class="text-right">{{
                        $t('orders.quantity')
                    }}</TableHead>
                    <TableHead class="text-right">{{
                        $t('orders.amount')
                    }}</TableHead>
                    <TableHead>{{ $t('orders.status') }}</TableHead>
                    <TableHead class="text-right">{{
                        $t('orders.date')
                    }}</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow v-for="order in orders" :key="order.id">
                    <TableCell>
                        <span class="block">{{ order.product }}</span>
                        <span class="text-xs text-muted-foreground">{{
                            $t(`enums.filiere.${order.filiere}`)
                        }}</span>
                    </TableCell>
                    <TableCell>{{ order.counterpart }}</TableCell>
                    <TableCell
                        class="text-right whitespace-nowrap tabular-nums"
                        >{{ formatWeight(order.quantityKg) }}</TableCell
                    >
                    <TableCell
                        class="text-right whitespace-nowrap tabular-nums"
                        >{{ formatMoney(order.totalXaf) }}</TableCell
                    >
                    <TableCell
                        ><OrderStatusBadge :status="order.status"
                    /></TableCell>
                    <TableCell
                        class="text-right whitespace-nowrap text-muted-foreground tabular-nums"
                        >{{
                            order.createdAt ? formatDate(order.createdAt) : ''
                        }}</TableCell
                    >
                </TableRow>
            </TableBody>
        </Table>
    </div>
</template>
