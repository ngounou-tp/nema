<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import DashboardHeader from '@/components/DashboardHeader.vue';
import StatLine from '@/components/StatLine.vue';
import { formatDate } from '@/lib/format';
import type { OrderStatus } from '@/types/dashboard';
import type { UserRole } from '@/types';

const props = defineProps<{
    users: { total: number; byRole: Record<UserRole, number> };
    orders: { total: number; byStatus: Record<OrderStatus, number> };
    aiCalls: { total: number; days: { date: string; count: number }[] };
}>();

/** The busiest day fills the column; the others are drawn to scale. */
const busiestDay = computed(() =>
    Math.max(1, ...props.aiCalls.days.map((day) => day.count)),
);
</script>

<template>
    <Head :title="$t('nav.dashboard')" />

    <div class="mx-auto w-full max-w-6xl px-4 pb-10 md:px-8">
        <DashboardHeader :lead="$t('dashboard.admin.lead')">
            <StatLine
                :label="$t('dashboard.admin.users')"
                :value="users.total"
            />
            <StatLine
                :label="$t('dashboard.admin.orders')"
                :value="orders.total"
            />
            <StatLine
                :label="$t('dashboard.admin.ai_calls')"
                :value="aiCalls.total"
            />
        </DashboardHeader>

        <div class="grid gap-12 md:grid-cols-3 md:gap-14">
            <section aria-labelledby="users-title">
                <h2 id="users-title" class="mb-3 text-2xl">
                    {{ $t('dashboard.admin.users_by_role') }}
                </h2>
                <dl>
                    <StatLine
                        v-for="(count, role) in users.byRole"
                        :key="role"
                        :label="$t(`enums.user_role.${role}`)"
                        :value="count"
                    />
                </dl>
            </section>

            <section aria-labelledby="orders-title">
                <h2 id="orders-title" class="mb-3 text-2xl">
                    {{ $t('dashboard.admin.orders_by_status') }}
                </h2>
                <dl>
                    <StatLine
                        v-for="(count, status) in orders.byStatus"
                        :key="status"
                        :label="$t(`enums.order_status.${status}`)"
                        :value="count"
                    />
                </dl>
            </section>

            <section aria-labelledby="ai-title">
                <h2 id="ai-title" class="text-2xl">
                    {{ $t('dashboard.admin.ai_calls') }}
                </h2>
                <p class="mt-1 mb-4 text-sm text-muted-foreground">
                    {{
                        aiCalls.total > 0
                            ? $t('dashboard.admin.ai_calls_hint')
                            : $t('dashboard.admin.no_ai_calls')
                    }}
                </p>
                <!-- Seven columns, one per day; each value is also written out for screen readers. -->
                <ol
                    class="flex h-36 items-end gap-2 border-b border-neutral-400"
                >
                    <li
                        v-for="day in aiCalls.days"
                        :key="day.date"
                        class="flex h-full flex-1 flex-col items-center justify-end gap-1"
                    >
                        <span class="text-xs tabular-nums">{{
                            day.count
                        }}</span>
                        <span
                            class="w-full max-w-8 bg-brand-700"
                            :style="{
                                height: `${Math.max(2, (day.count / busiestDay) * 100)}%`,
                            }"
                            aria-hidden="true"
                        />
                        <span class="sr-only">{{
                            formatDate(day.date, { dateStyle: 'full' })
                        }}</span>
                    </li>
                </ol>
                <ol
                    class="mt-1.5 flex gap-2 text-center text-xs text-muted-foreground"
                    aria-hidden="true"
                >
                    <li
                        v-for="day in aiCalls.days"
                        :key="day.date"
                        class="flex-1"
                    >
                        {{ formatDate(day.date, { weekday: 'narrow' }) }}
                    </li>
                </ol>
            </section>
        </div>
    </div>
</template>
