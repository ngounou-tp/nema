<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Label } from '@/components/ui/label';

defineProps<{
    id: string;
    label: string;
    hint?: string;
    error?: string;
}>();
</script>

<template>
    <div class="grid gap-1.5">
        <Label :for="id">{{ label }}</Label>
        <slot :described-by="hint ? `${id}-hint` : undefined" />
        <p v-if="hint" :id="`${id}-hint`" class="text-xs text-muted-foreground">
            {{ hint }}
        </p>
        <InputError :message="error" />
    </div>
</template>
