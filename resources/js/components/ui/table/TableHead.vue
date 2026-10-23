<script setup lang="ts">
import type { HTMLAttributes } from "vue"
import { cn } from "@/lib/utils"

const props = defineProps<{
  class?: HTMLAttributes["class"]
}>()
</script>

<template>
  <th data-slot="table-head" :class="cn('h-10 px-2.5 text-left align-bottom text-xs font-normal tracking-[0.08em] whitespace-nowrap text-muted-foreground uppercase first:pl-0 [&:has([role=checkbox])]:pr-0', props.class)">
    <slot />
  </th>
</template>
