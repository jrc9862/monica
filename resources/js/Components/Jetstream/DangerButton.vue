<script setup>
defineProps({
  type: {
    type: String,
    default: 'button',
  },
});
</script>

<template>
  <button
    :type="type"
    class="inline-flex items-center justify-center border border-red-800 bg-red-700 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white shadow-pixel-sm transition-[box-shadow,transform] duration-150 hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-none focus:outline-hidden disabled:opacity-25">
    <slot />
  </button>
</template>
