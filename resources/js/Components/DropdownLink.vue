<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
  href: String,
  as: String,
});
</script>

<template>
  <div>
    <button
      v-if="as === 'button'"
      type="submit"
      class="block w-full px-4 py-2 text-left text-sm leading-5 text-text transition hover:bg-hover focus:bg-bg focus:outline-hidden dark:text-text dark:hover:bg-hover">
      <slot />
    </button>

    <a
      v-else-if="as === 'a'"
      :href="href"
      class="block px-4 py-2 text-sm leading-5 text-text transition hover:bg-hover focus:bg-bg focus:outline-hidden dark:text-text dark:hover:bg-hover">
      <slot />
    </a>

    <Link
      v-else
      :href="href"
      class="block px-4 py-2 text-sm leading-5 text-text transition hover:bg-hover focus:bg-bg focus:outline-hidden dark:text-text dark:hover:bg-hover">
      <slot />
    </Link>
  </div>
</template>
