<script setup lang="ts">
import type { HTMLAttributes } from "vue"
import { cn } from "@/lib/utils"

const props = defineProps<{
  class?: HTMLAttributes["class"]
}>()
</script>

<template>
  <tr data-slot="table-row" :class="cn('border-b border-border/60 transition-colors hover:bg-accent/60', props.class)">
    <slot />
  </tr>
</template>
