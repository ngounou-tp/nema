<script setup lang="ts">
import type { Component } from 'vue';

defineProps<{
    title: string;
    description?: string;
    icon?: Component;
}>();
</script>

<template>
    <div
        class="flex flex-col items-start gap-2 rounded-md border border-dashed border-neutral-400 px-4 py-5"
    >
        <component
            :is="icon"
            v-if="icon"
            weight="duotone"
            class="size-7 text-neutral-600"
            aria-hidden="true"
        />
        <p class="font-semibold">{{ title }}</p>
        <p v-if="description" class="max-w-prose text-sm text-muted-foreground">
            {{ description }}
        </p>
        <div v-if="$slots.default" class="mt-1">
            <slot />
        </div>
    </div>
</template>
