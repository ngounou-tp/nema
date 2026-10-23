<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import type { AppLocale } from '@/lib/i18n';
import { update } from '@/routes/locale';

const page = usePage();
const current = computed(() => page.props.locale);

/**
 * Saved in the session, and in users.locale when signed in (LocaleController).
 */
function switchTo(locale: AppLocale): void {
    if (locale === current.value) {
        return;
    }

    router.put(
        update.url(),
        { locale },
        { preserveScroll: true, preserveState: true },
    );
}
</script>

<template>
    <div
        role="radiogroup"
        :aria-label="$t('locale.switch')"
        class="inline-flex shrink-0 overflow-hidden rounded-md border border-input"
    >
        <button
            v-for="locale in page.props.locales"
            :key="locale"
            type="button"
            role="radio"
            :aria-checked="locale === current"
            :lang="locale"
            :title="$t(`enums.locale.${locale}`)"
            class="min-h-9 min-w-11 px-3 text-sm transition-colors not-first:border-l not-first:border-input focus-visible:outline-offset-[-2px]"
            :class="
                locale === current
                    ? 'bg-primary text-primary-foreground'
                    : 'hover:bg-accent'
            "
            @click="switchTo(locale)"
        >
            <span aria-hidden="true">{{ $t(`locale.short.${locale}`) }}</span>
            <span class="sr-only">{{ $t(`enums.locale.${locale}`) }}</span>
        </button>
    </div>
</template>
