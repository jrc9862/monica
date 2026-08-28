<script setup>
defineProps({
  value: String,
});
</script>

<template>
  <label class="block text-sm font-medium text-text dark:text-text">
    <span v-if="value">
      {{ value }}
    </span>
    <span v-else>
      <slot />
    </span>
  </label>
</template>
