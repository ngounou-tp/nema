<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import LanguageSwitcher from '@/components/LanguageSwitcher.vue';
import { dashboard, home, login } from '@/routes';

const page = usePage();
const isSignedIn = computed(() => Boolean(page.props.auth.user));
</script>

<template>
    <header
        class="mx-auto flex w-full max-w-6xl items-center gap-4 px-4 py-4 md:px-8 md:py-5"
    >
        <Link :href="home()" class="mr-auto" :aria-label="$t('nav.home')">
            <AppLogo />
        </Link>
        <Link
            :href="isSignedIn ? dashboard() : login()"
            class="text-sm underline-offset-4 hover:text-brand-700 hover:underline"
        >
            {{ isSignedIn ? $t('nav.dashboard') : $t('nav.login') }}
        </Link>
        <LanguageSwitcher />
    </header>
</template>
