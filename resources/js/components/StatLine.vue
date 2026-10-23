<script setup lang="ts">
defineProps<{
    label: string;
    value: string | number;
}>();
</script>

<template>
    <!-- Broadsheet index line: label, dotted leader, figure. -->
    <div class="flex items-baseline gap-2 leading-8">
        <dt>{{ label }}</dt>
        <span class="leader" aria-hidden="true" />
        <dd class="text-lg font-semibold tabular-nums">{{ value }}</dd>
    </div>
</template>
