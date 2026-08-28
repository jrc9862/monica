<script setup>
import CrmIcon from '@/Shared/Icons/CrmIcon.vue';

defineProps({
  icon: { type: String, default: 'grid' },
  title: { type: String, required: true },
  color: { type: String, default: 'var(--muted)' },
  size: { type: Number, default: 15 },
});
</script>

<template>
  <div class="crm-panel-header">
    <span class="flex" :style="{ color }">
      <CrmIcon :name="icon" :size="size" />
    </span>
    <span class="crm-title">{{ title }}</span>
    <slot name="right" />
  </div>
</template>
