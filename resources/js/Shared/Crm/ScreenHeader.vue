<script setup>
defineProps({
  title: { type: String, required: true },
});
</script>

<template>
  <div class="mb-5 flex items-center justify-between gap-4">
    <span class="crm-section-title whitespace-nowrap">
      {{ title }}
      <span v-if="$slots.count" style="font: 400 20px var(--mono); color: var(--muted)">· <slot name="count" /></span>
    </span>
    <div class="flex gap-3">
      <slot name="actions" />
    </div>
  </div>
</template>
