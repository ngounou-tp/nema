<script setup lang="ts">
import type { HTMLAttributes } from "vue"
import { cn } from "@/lib/utils"

const props = defineProps<{
  class?: HTMLAttributes["class"]
}>()
</script>

<template>
  <td data-slot="table-cell" :class="cn('px-2.5 py-2.5 align-middle first:pl-0', props.class)">
    <slot />
  </td>
</template>
