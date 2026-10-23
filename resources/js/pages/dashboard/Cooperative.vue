<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { PhPackage, PhUsersThree, PhWarehouse } from '@phosphor-icons/vue';
import { computed } from 'vue';
import DashboardHeader from '@/components/DashboardHeader.vue';
import EmptyState from '@/components/EmptyState.vue';
import OrdersList from '@/components/OrdersList.vue';
import StatLine from '@/components/StatLine.vue';
import { formatArea, formatMoney, formatWeight } from '@/lib/format';
import type { DashboardOrder } from '@/types/dashboard';

const props = defineProps<{
    cooperative: { name: string; region: string } | null;
    members: {
        total: number;
        latest: {
            id: number;
            name: string;
            village: string;
            surfaceHa: number;
        }[];
    };
    stock: { filiere: string; quantityKg: number }[];
    ordersThisMonth: {
        count: number;
        totalXaf: number;
        latest: DashboardOrder[];
    };
}>();

/** The largest stock fills the bar; the others are drawn to scale. */
const largestStock = computed(() =>
    Math.max(1, ...props.stock.map((line) => line.quantityKg)),
);
</script>

<template>
    <Head :title="$t('nav.dashboard')" />

    <div class="mx-auto w-full max-w-6xl px-4 pb-10 md:px-8">
        <DashboardHeader
            :lead="
                cooperative
                    ? $t('dashboard.cooperative.lead', {
                          name: cooperative.name,
                      })
                    : $t('dashboard.cooperative.no_cooperative')
            "
        >
            <StatLine
                :label="$t('dashboard.cooperative.members')"
                :value="members.total"
            />
            <StatLine
                :label="$t('dashboard.cooperative.orders_this_month')"
                :value="ordersThisMonth.count"
            />
            <StatLine
                :label="$t('dashboard.cooperative.sales_this_month')"
                :value="formatMoney(ordersThisMonth.totalXaf)"
            />
        </DashboardHeader>

        <div
            class="grid gap-12 md:grid-cols-[minmax(0,7fr)_minmax(0,5fr)] md:gap-14"
        >
            <div class="flex flex-col gap-12">
                <section aria-labelledby="stock-title">
                    <h2 id="stock-title" class="text-2xl">
                        {{ $t('dashboard.cooperative.stock_by_filiere') }}
                    </h2>
                    <p class="mt-1 mb-5 text-sm text-muted-foreground">
                        {{ $t('dashboard.cooperative.stock_hint') }}
                    </p>
                    <ul v-if="stock.length > 0" class="flex flex-col gap-4">
                        <li v-for="line in stock" :key="line.filiere">
                            <div
                                class="mb-1.5 flex justify-between gap-3 text-[15px]"
                            >
                                <span>{{
                                    $t(`enums.filiere.${line.filiere}`)
                                }}</span>
                                <strong class="tabular-nums">{{
                                    formatWeight(line.quantityKg)
                                }}</strong>
                            </div>
                            <div
                                class="h-[3px] bg-neutral-300"
                                aria-hidden="true"
                            >
                                <div
                                    class="h-[3px] bg-brand-700"
                                    :style="{
                                        width: `${(line.quantityKg / largestStock) * 100}%`,
                                    }"
                                />
                            </div>
                        </li>
                    </ul>
                    <EmptyState
                        v-else
                        :icon="PhWarehouse"
                        :title="$t('dashboard.cooperative.no_stock')"
                        :description="$t('dashboard.cooperative.no_stock_hint')"
                    />
                </section>

                <section aria-labelledby="orders-title">
                    <h2 id="orders-title" class="mb-4 text-2xl">
                        {{ $t('dashboard.cooperative.orders_this_month') }}
                    </h2>
                    <OrdersList
                        v-if="ordersThisMonth.latest.length > 0"
                        :orders="ordersThisMonth.latest"
                        :counterpart-label="$t('orders.buyer')"
                    />
                    <EmptyState
                        v-else
                        :icon="PhPackage"
                        :title="$t('dashboard.cooperative.no_orders')"
                    />
                </section>
            </div>

            <section aria-labelledby="members-title">
                <h2 id="members-title" class="mb-4 text-2xl">
                    {{ $t('dashboard.cooperative.latest_members') }}
                </h2>
                <ul
                    v-if="members.latest.length > 0"
                    class="divide-y divide-border"
                >
                    <li
                        v-for="member in members.latest"
                        :key="member.id"
                        class="flex items-baseline justify-between gap-3 py-3"
                    >
                        <div class="min-w-0">
                            <p class="truncate font-semibold">
                                {{ member.name }}
                            </p>
                            <p class="text-sm text-neutral-700">
                                {{ member.village }}
                            </p>
                        </div>
                        <span class="text-sm whitespace-nowrap tabular-nums">{{
                            formatArea(member.surfaceHa)
                        }}</span>
                    </li>
                </ul>
                <EmptyState
                    v-else
                    :icon="PhUsersThree"
                    :title="$t('dashboard.cooperative.no_members')"
                />
            </section>
        </div>
    </div>
</template>
