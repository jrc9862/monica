<script setup>
defineProps({
  type: {
    type: String,
    default: 'submit',
  },
});
</script>

<template>
  <button
    :type="type"
    class="inline-flex items-center rounded-md border border-transparent bg-accent px-4 py-2 text-xs font-semibold uppercase tracking-widest text-[var(--on-primary)] transition hover:bg-accent-hover focus:outline-hidden focus:ring-3 focus:ring-accent/30 disabled:opacity-25">
    <slot />
  </button>
</template>
