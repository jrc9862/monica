<script setup>
import { computed } from 'vue';

// Renders Monica's own contact avatar (Multiavatar SVG, or the uploaded photo)
// inside the design's square 2px-radius tile.
const props = defineProps({
  data: { type: Object, default: null },
  size: { type: [Number, String], default: 46 },
});

const box = computed(() => ({
  width: `${props.size}px`,
  height: `${props.size}px`,
}));
</script>

<template>
  <div
    class="crm-avatar shrink-0 overflow-hidden rounded-[2px]"
    :style="{ ...box, background: 'var(--faint)' }"
    aria-hidden="true">
    <div v-if="data && data.type === 'svg'" class="h-full w-full" v-html="data.content" />
    <img v-else-if="data" :src="data.content" alt="" class="h-full w-full object-cover" />
  </div>
</template>

<style scoped>
.crm-avatar :deep(svg) {
  width: 100%;
  height: 100%;
  display: block;
}
</style>
