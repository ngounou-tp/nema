<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { PhDotsThreeOutline } from '@phosphor-icons/vue';
import { useSidebar } from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { useNavigation } from '@/composables/useNavigation';
import { toUrl } from '@/lib/utils';

const { bottomBarItems, hasMoreItems } = useNavigation();
const { isCurrentOrParentUrl } = useCurrentUrl();
const { setOpenMobile } = useSidebar();
</script>

<template>
    <nav
        :aria-label="$t('nav.main')"
        class="fixed inset-x-0 bottom-0 z-30 border-t border-border bg-background/95 pb-[env(safe-area-inset-bottom)] backdrop-blur md:hidden"
    >
        <ul class="flex">
            <li
                v-for="item in bottomBarItems"
                :key="toUrl(item.href)"
                class="min-w-0 flex-1"
            >
                <Link
                    :href="item.href"
                    :aria-current="
                        isCurrentOrParentUrl(item.href) ? 'page' : undefined
                    "
                    class="flex min-h-14 flex-col items-center justify-center gap-0.5 px-1 text-[11px] leading-tight"
                    :class="
                        isCurrentOrParentUrl(item.href)
                            ? 'font-semibold text-brand-700'
                            : 'text-foreground'
                    "
                >
                    <component
                        :is="item.icon"
                        weight="duotone"
                        class="size-6"
                        aria-hidden="true"
                    />
                    <span class="w-full truncate text-center">{{
                        item.title
                    }}</span>
                </Link>
            </li>
            <li v-if="hasMoreItems" class="min-w-0 flex-1">
                <button
                    type="button"
                    class="flex min-h-14 w-full flex-col items-center justify-center gap-0.5 px-1 text-[11px] leading-tight"
                    @click="setOpenMobile(true)"
                >
                    <PhDotsThreeOutline
                        weight="duotone"
                        class="size-6"
                        aria-hidden="true"
                    />
                    <span>{{ $t('nav.more') }}</span>
                </button>
            </li>
        </ul>
    </nav>
</template>
