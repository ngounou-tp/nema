<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { formatDate } from '@/lib/format';

defineProps<{
    lead: string;
}>();

const page = usePage();
const user = computed(() => page.props.auth.user);
const today = computed(() => formatDate(new Date(), { dateStyle: 'full' }));
</script>

<template>
    <header class="pt-6 md:pt-10">
        <div class="rule-double" aria-hidden="true" />
        <!-- Dateline: who is reading, where, and when, like a front page. -->
        <p
            class="flex flex-wrap justify-between gap-x-6 gap-y-1 border-b border-ink py-2.5 text-xs tracking-[0.06em] text-neutral-700 uppercase md:text-[13px]"
        >
            <span>{{ $t(`enums.user_role.${user.role}`) }}</span>
            <span v-if="user.region">{{
                $t(`enums.region.${user.region}`)
            }}</span>
            <span class="first-letter:uppercase">{{ today }}</span>
        </p>

        <div
            class="grid gap-8 py-8 md:grid-cols-[minmax(0,7fr)_minmax(0,5fr)] md:items-end md:gap-14 md:py-12"
        >
            <div>
                <h1 class="text-4xl tracking-[-0.02em] md:text-[3.25rem]">
                    {{ $t('dashboard.greeting', { name: user.name }) }}
                </h1>
                <p class="mt-4 max-w-[34em] text-lg leading-relaxed">
                    {{ lead }}
                </p>
            </div>
            <dl class="flex flex-col">
                <slot />
            </dl>
        </div>
    </header>
</template>
