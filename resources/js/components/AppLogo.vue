<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const { size = 'md' } = defineProps<{
    size?: 'md' | 'lg';
}>();

const page = usePage();
const logo = computed(() => page.props.brand.logo);
</script>

<template>
    <span class="inline-flex items-center">
        <img
            v-if="logo"
            :src="logo"
            :alt="page.props.brand.name"
            :class="size === 'lg' ? 'h-10 w-auto' : 'h-7 w-auto'"
        />
        <!-- Wordmark until public/images/logo.svg is provided. -->
        <span
            v-else
            class="leading-none font-semibold tracking-[0.06em]"
            :class="size === 'lg' ? 'text-3xl' : 'text-[22px]'"
            >{{ page.props.brand.name }}</span
        >
    </span>
</template>
