<template>
  <button
    :class="'cursor-pointer border border-accent-muted bg-accent px-3 py-1 font-semibold text-bg shadow-pixel-sm transition-[box-shadow,transform] duration-150 hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-none disabled:translate-x-0 disabled:translate-y-0 disabled:opacity-50 disabled:shadow-pixel-sm'"
    :disabled="state === 'loading' || state === 'disabled'"
    type="submit">
    <span v-if="state === 'loading'"> {{ $t('Loading…') }} </span>

    <span v-if="state !== 'loading'">
      {{ text }}
    </span>
  </button>
</template>

<script>
export default {
  props: {
    text: {
      type: String,
      default: '',
    },
    state: {
      type: String,
      default: '',
    },
  },
};
</script>
