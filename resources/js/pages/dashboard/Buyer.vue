<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { PhPackage, PhStar, PhStorefront } from '@phosphor-icons/vue';
import DashboardHeader from '@/components/DashboardHeader.vue';
import EmptyState from '@/components/EmptyState.vue';
import OrdersList from '@/components/OrdersList.vue';
import ScoreBadge from '@/components/ScoreBadge.vue';
import StatLine from '@/components/StatLine.vue';
import { Button } from '@/components/ui/button';
import { formatNumber } from '@/lib/format';
import marketplace from '@/routes/marketplace';
import type { DashboardOrder, TopSupplier } from '@/types/dashboard';

defineProps<{
    ordersInProgress: number;
    recentOrders: DashboardOrder[];
    topSuppliers: TopSupplier[];
}>();
</script>

<template>
    <Head :title="$t('nav.dashboard')" />

    <div class="mx-auto w-full max-w-6xl px-4 pb-10 md:px-8">
        <DashboardHeader :lead="$t('dashboard.buyer.lead')">
            <StatLine
                :label="$t('dashboard.buyer.orders_in_progress')"
                :value="ordersInProgress"
            />
            <StatLine
                :label="$t('dashboard.buyer.recent_orders')"
                :value="recentOrders.length"
            />
            <StatLine
                :label="$t('dashboard.buyer.rated_suppliers')"
                :value="topSuppliers.length"
            />
        </DashboardHeader>

        <div
            class="grid gap-12 md:grid-cols-[minmax(0,7fr)_minmax(0,5fr)] md:gap-14"
        >
            <section aria-labelledby="orders-title">
                <h2 id="orders-title" class="mb-4 text-2xl">
                    {{ $t('dashboard.buyer.recent_orders') }}
                </h2>
                <OrdersList
                    v-if="recentOrders.length > 0"
                    :orders="recentOrders"
                    :counterpart-label="$t('orders.producer')"
                />
                <EmptyState
                    v-else
                    :icon="PhPackage"
                    :title="$t('dashboard.buyer.no_orders')"
                    :description="$t('dashboard.buyer.no_orders_hint')"
                >
                    <Button as-child size="sm">
                        <Link :href="marketplace.index()">{{
                            $t('dashboard.buyer.browse')
                        }}</Link>
                    </Button>
                </EmptyState>
            </section>

            <section aria-labelledby="suppliers-title">
                <h2 id="suppliers-title" class="text-2xl">
                    {{ $t('dashboard.buyer.top_suppliers') }}
                </h2>
                <p class="mt-1 mb-4 text-sm text-muted-foreground">
                    {{ $t('dashboard.buyer.top_suppliers_hint') }}
                </p>
                <ol
                    v-if="topSuppliers.length > 0"
                    class="divide-y divide-border"
                >
                    <li
                        v-for="supplier in topSuppliers"
                        :key="supplier.id"
                        class="py-3"
                    >
                        <div class="flex items-baseline justify-between gap-3">
                            <p class="min-w-0 truncate font-semibold">
                                {{ supplier.name }}
                            </p>
                            <p
                                class="flex items-center gap-1 whitespace-nowrap tabular-nums"
                            >
                                <PhStar
                                    weight="fill"
                                    class="size-4 text-brand-700"
                                    aria-hidden="true"
                                />
                                <span>{{
                                    $t('dashboard.buyer.rating', {
                                        value: formatNumber(
                                            supplier.averageRating,
                                            1,
                                        ),
                                    })
                                }}</span>
                            </p>
                        </div>
                        <p class="text-sm text-neutral-700">
                            {{ supplier.village
                            }}<template v-if="supplier.region"
                                >,
                                {{
                                    $t(`enums.region.${supplier.region}`)
                                }}</template
                            >
                            <span aria-hidden="true"> · </span>
                            {{
                                $t('dashboard.buyer.reviews_count', {
                                    count: String(supplier.reviewsCount),
                                })
                            }}
                        </p>
                        <ScoreBadge
                            v-if="supplier.score !== null"
                            :score="supplier.score"
                            class="mt-2"
                        />
                    </li>
                </ol>
                <EmptyState
                    v-else
                    :icon="PhStorefront"
                    :title="$t('dashboard.buyer.no_suppliers')"
                />
            </section>
        </div>
    </div>
</template>
