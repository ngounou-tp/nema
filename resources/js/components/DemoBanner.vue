<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';

const page = usePage();
</script>

<template>
    <p
        v-if="page.props.demoMode"
        class="bg-neutral-200 px-4 py-1.5 text-center text-xs text-neutral-800"
    >
        {{ $t('demo.banner') }}
    </p>
</template>
