<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  href: String,
  active: Boolean,
});

const classes = computed(() => {
  return props.active
    ? 'inline-flex items-center px-1 pt-1 border-b-2 border-accent dark:border-accent text-sm font-medium leading-5 text-text dark:text-text focus:outline-hidden focus:border-accent dark:focus:border-accent transition'
    : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-text-muted hover:text-text dark:hover:text-text hover:border-accent dark:hover:border-accent focus:outline-hidden focus:text-text dark:focus:text-text-muted focus:border-border dark:focus:border-border transition';
});
</script>

<template>
  <Link :href="href" :class="classes">
    <slot />
  </Link>
</template>
