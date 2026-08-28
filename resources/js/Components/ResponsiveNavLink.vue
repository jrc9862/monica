<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  active: Boolean,
  href: String,
  as: String,
});

const classes = computed(() => {
  return props.active
    ? 'block ps-3 pe-4 py-2 border-s-4 border-accent dark:border-accent text-base font-medium text-accent dark:text-accent bg-surface-raised dark:bg-surface-raised focus:outline-hidden focus:text-accent-hover dark:focus:text-accent focus:bg-surface-raised dark:focus:bg-surface-raised focus:border-accent dark:focus:border-accent transition'
    : 'block ps-3 pe-4 py-2 border-s-4 border-transparent text-base font-medium text-text dark:text-text hover:text-text dark:hover:text-text hover:bg-hover dark:hover:bg-hover hover:border-accent dark:hover:border-accent focus:outline-hidden focus:text-text dark:focus:text-text focus:bg-bg dark:focus:bg-surface focus:border-border dark:focus:border-border transition';
});
</script>

<template>
  <div>
    <button v-if="as === 'button'" :class="classes" class="w-full text-left">
      <slot />
    </button>

    <Link v-else :href="href" :class="classes">
      <slot />
    </Link>
  </div>
</template>
