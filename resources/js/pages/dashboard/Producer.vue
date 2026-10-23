<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    PhFirstAidKit,
    PhGauge,
    PhPackage,
    PhPlant,
} from '@phosphor-icons/vue';
import DashboardHeader from '@/components/DashboardHeader.vue';
import EmptyState from '@/components/EmptyState.vue';
import OrdersList from '@/components/OrdersList.vue';
import ScoreBadge from '@/components/ScoreBadge.vue';
import StatLine from '@/components/StatLine.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    formatDate,
    formatMoney,
    formatPercent,
    formatWeight,
} from '@/lib/format';
import { trans } from 'laravel-vue-i18n';
import diagnosis from '@/routes/diagnosis';
import type {
    DashboardDiagnosis,
    DashboardOrder,
    DashboardProduct,
} from '@/types/dashboard';

defineProps<{
    products: { total: number; available: number; latest: DashboardProduct[] };
    ordersInProgress: DashboardOrder[];
    score: { value: number; computedAt: string } | null;
    diagnoses: DashboardDiagnosis[];
}>();

/** « Terminé, confiance 82 % »: the status, then the confidence when there is one. */
function diagnosisSummary(item: DashboardDiagnosis): string {
    const status = trans(`enums.diagnosis_status.${item.status}`);

    if (item.confidence === null) {
        return status;
    }

    return `${status}, ${trans('dashboard.producer.confidence', { value: formatPercent(item.confidence) })}`;
}
</script>

<template>
    <Head :title="$t('nav.dashboard')" />

    <div class="mx-auto w-full max-w-6xl px-4 pb-10 md:px-8">
        <DashboardHeader :lead="$t('dashboard.producer.lead')">
            <StatLine
                :label="$t('dashboard.producer.products_on_sale')"
                :value="`${products.available} / ${products.total}`"
            />
            <StatLine
                :label="$t('dashboard.producer.orders_in_progress')"
                :value="ordersInProgress.length"
            />
            <StatLine
                :label="$t('dashboard.producer.reliability_score')"
                :value="score ? `${score.value} / 100` : '—'"
            />
        </DashboardHeader>

        <div
            class="grid gap-12 md:grid-cols-[minmax(0,7fr)_minmax(0,5fr)] md:gap-14"
        >
            <div class="flex flex-col gap-12">
                <section aria-labelledby="orders-title">
                    <h2 id="orders-title" class="mb-4 text-2xl">
                        {{ $t('dashboard.producer.orders_in_progress') }}
                    </h2>
                    <OrdersList
                        v-if="ordersInProgress.length > 0"
                        :orders="ordersInProgress"
                        :counterpart-label="$t('orders.buyer')"
                    />
                    <EmptyState
                        v-else
                        :icon="PhPackage"
                        :title="$t('dashboard.producer.no_orders')"
                        :description="$t('dashboard.producer.no_orders_hint')"
                    />
                </section>

                <section aria-labelledby="products-title">
                    <h2 id="products-title" class="mb-4 text-2xl">
                        {{ $t('dashboard.producer.my_products') }}
                    </h2>
                    <ul
                        v-if="products.latest.length > 0"
                        class="divide-y divide-border"
                    >
                        <li
                            v-for="product in products.latest"
                            :key="product.id"
                            class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1 py-3"
                        >
                            <div class="min-w-0">
                                <p class="font-semibold">{{ product.name }}</p>
                                <p class="text-sm text-neutral-700">
                                    {{
                                        $t(`enums.filiere.${product.filiere}`)
                                    }},
                                    {{ formatWeight(product.quantityKg) }}
                                </p>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="tabular-nums">{{
                                    $t('units.per_kg', {
                                        price: formatMoney(product.priceXaf),
                                    })
                                }}</span>
                                <Badge
                                    :variant="
                                        product.isAvailable
                                            ? 'accent'
                                            : 'neutral'
                                    "
                                    >{{
                                        product.isAvailable
                                            ? $t('dashboard.producer.on_sale')
                                            : $t(
                                                  'dashboard.producer.not_on_sale',
                                              )
                                    }}</Badge
                                >
                            </div>
                        </li>
                    </ul>
                    <EmptyState
                        v-else
                        :icon="PhPlant"
                        :title="$t('dashboard.producer.no_products')"
                        :description="$t('dashboard.producer.no_products_hint')"
                    />
                </section>
            </div>

            <div class="flex flex-col gap-12">
                <section aria-labelledby="score-title">
                    <h2 id="score-title" class="mb-4 text-2xl">
                        {{ $t('dashboard.producer.reliability_score') }}
                    </h2>
                    <div v-if="score" class="flex flex-col gap-2">
                        <ScoreBadge :score="score.value" />
                        <p class="text-sm text-muted-foreground">
                            {{
                                $t('dashboard.producer.score_computed_at', {
                                    date: formatDate(score.computedAt),
                                })
                            }}
                        </p>
                    </div>
                    <EmptyState
                        v-else
                        :icon="PhGauge"
                        :title="$t('dashboard.producer.no_score')"
                        :description="$t('dashboard.producer.no_score_hint')"
                    />
                </section>

                <section aria-labelledby="diagnoses-title">
                    <h2 id="diagnoses-title" class="mb-4 text-2xl">
                        {{ $t('dashboard.producer.latest_diagnoses') }}
                    </h2>
                    <ul
                        v-if="diagnoses.length > 0"
                        class="divide-y divide-border"
                    >
                        <li
                            v-for="item in diagnoses"
                            :key="item.id"
                            class="flex items-baseline justify-between gap-3 py-3"
                        >
                            <div>
                                <p class="font-semibold">
                                    {{
                                        item.crop ??
                                        $t('dashboard.producer.unknown_crop')
                                    }}
                                </p>
                                <p class="text-sm text-neutral-700">
                                    {{ diagnosisSummary(item) }}
                                </p>
                            </div>
                            <span
                                v-if="item.createdAt"
                                class="text-sm whitespace-nowrap text-muted-foreground"
                                >{{ formatDate(item.createdAt) }}</span
                            >
                        </li>
                    </ul>
                    <EmptyState
                        v-else
                        :icon="PhFirstAidKit"
                        :title="$t('dashboard.producer.no_diagnoses')"
                        :description="
                            $t('dashboard.producer.no_diagnoses_hint')
                        "
                    >
                        <Button as-child size="sm">
                            <Link :href="diagnosis.index()">{{
                                $t('dashboard.producer.start_diagnosis')
                            }}</Link>
                        </Button>
                    </EmptyState>
                </section>
            </div>
        </div>
    </div>
</template>
