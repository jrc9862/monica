<script setup>
import JetSectionTitle from './SectionTitle.vue';

defineProps({
  danger: {
    type: Boolean,
    default: false,
  },
});
</script>

<template>
  <div>
    <JetSectionTitle :edit-mode="true">
      <template #title>
        <slot name="title" />
      </template>
      <template #description>
        <slot name="description" />
      </template>
      <template #icon>
        <slot name="icon" />
      </template>
      <template #help>
        <slot name="help" />
      </template>
    </JetSectionTitle>

    <div class="mb-6 rounded-lg border border-border bg-surface dark:border-border dark:bg-surface">
      <div class="border-b border-border px-5 py-4 dark:border-border">
        <slot name="content" />
      </div>
    </div>
  </div>
</template>
