<script setup lang="ts">
import type { HTMLAttributes } from "vue"
import { cn } from "@/lib/utils"

const props = defineProps<{
  class?: HTMLAttributes["class"]
}>()
</script>

<template>
  <!-- Scrolls sideways on narrow screens instead of breaking the page. -->
  <div data-slot="table-container" class="relative w-full overflow-x-auto">
    <table data-slot="table" :class="cn('w-full caption-bottom border-collapse text-sm', props.class)">
      <slot />
    </table>
  </div>
</template>
