import { usePage } from '@inertiajs/vue3';
import {
    PhChartLineUp,
    PhChatCircleText,
    PhFirstAidKit,
    PhHouse,
    PhListMagnifyingGlass,
    PhQrCode,
    PhShieldCheck,
    PhStorefront,
    PhUsersThree,
} from '@phosphor-icons/vue';
import { trans } from 'laravel-vue-i18n';
import type { Component } from 'vue';
import { computed } from 'vue';
import { dashboard } from '@/routes';
import admin from '@/routes/admin';
import assistant from '@/routes/assistant';
import cooperative from '@/routes/cooperative';
import diagnosis from '@/routes/diagnosis';
import forecasts from '@/routes/forecasts';
import marketplace from '@/routes/marketplace';
import traceability from '@/routes/traceability';
import type { NavItem, UserRole } from '@/types';

type Destination = {
    key: string;
    icon: Component;
    href: NavItem['href'];
};

const destinations = {
    dashboard: { key: 'dashboard', icon: PhHouse, href: dashboard() },
    assistant: {
        key: 'assistant',
        icon: PhChatCircleText,
        href: assistant.index(),
    },
    diagnosis: {
        key: 'diagnosis',
        icon: PhFirstAidKit,
        href: diagnosis.index(),
    },
    marketplace: {
        key: 'marketplace',
        icon: PhStorefront,
        href: marketplace.index(),
    },
    traceability: {
        key: 'traceability',
        icon: PhQrCode,
        href: traceability.index(),
    },
    forecasts: {
        key: 'forecasts',
        icon: PhChartLineUp,
        href: forecasts.index(),
    },
    cooperative: {
        key: 'cooperative',
        icon: PhUsersThree,
        href: cooperative.index(),
    },
    moderation: {
        key: 'moderation',
        icon: PhShieldCheck,
        href: admin.moderation(),
    },
    aiLog: { key: 'ai_log', icon: PhListMagnifyingGlass, href: admin.aiLog() },
} satisfies Record<string, Destination>;

/**
 * Each role's menu, most frequent jobs first: the first four also make the
 * mobile bottom bar. Access is enforced server-side by the `role` middleware.
 */
const menus: Record<UserRole, Destination[]> = {
    producer: [
        destinations.dashboard,
        destinations.diagnosis,
        destinations.assistant,
        destinations.marketplace,
        destinations.forecasts,
        destinations.traceability,
    ],
    cooperative: [
        destinations.dashboard,
        destinations.cooperative,
        destinations.traceability,
        destinations.marketplace,
        destinations.diagnosis,
        destinations.assistant,
        destinations.forecasts,
    ],
    buyer: [
        destinations.dashboard,
        destinations.marketplace,
        destinations.traceability,
        destinations.forecasts,
    ],
    admin: [
        destinations.dashboard,
        destinations.moderation,
        destinations.aiLog,
        destinations.marketplace,
        destinations.traceability,
        destinations.forecasts,
        destinations.cooperative,
        destinations.diagnosis,
        destinations.assistant,
    ],
};

export const BOTTOM_BAR_SIZE = 4;

export function useNavigation() {
    const page = usePage();

    const items = computed<NavItem[]>(() => {
        const role = page.props.auth.user?.role ?? 'buyer';

        return menus[role].map((destination) => ({
            title: trans(`nav.${destination.key}`),
            href: destination.href,
            icon: destination.icon,
        }));
    });

    const bottomBarItems = computed(() =>
        items.value.slice(0, BOTTOM_BAR_SIZE),
    );
    const hasMoreItems = computed(() => items.value.length > BOTTOM_BAR_SIZE);

    return { items, bottomBarItems, hasMoreItems };
}
