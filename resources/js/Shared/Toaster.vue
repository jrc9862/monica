<script setup>
import emitter from 'tiny-emitter/instance';
import { computed, onMounted, ref } from 'vue';
import CrmIcon from '@/Shared/Icons/CrmIcon.vue';

const props = defineProps({
  level: String,
  message: String,
});

const isOpen = ref(false);
const closeAfter = 3200;
const currentLevel = ref('success');
const messageText = ref(null);
let timer = null;

const tint = computed(() => {
  if (currentLevel.value === 'error') return { background: 'var(--blush)', color: 'var(--pink)' };
  if (currentLevel.value === 'warning') return { background: 'var(--sun)', color: 'var(--yellow)' };
  return { background: 'var(--mint)', color: 'var(--green)' };
});

const glyph = computed(() => (currentLevel.value === 'success' ? 'check' : 'bell'));

const show = (data) => {
  if (!data) return;
  messageText.value = data.message;
  currentLevel.value = data.level ?? 'success';
  isOpen.value = true;

  clearTimeout(timer);
  timer = setTimeout(() => (isOpen.value = false), closeAfter);
};

onMounted(() => {
  if (props.message) {
    show(props);
  }

  emitter.on('flash', (data) => show(data));
});
</script>

<template>
  <div
    v-if="isOpen"
    class="crm-toast-in fixed left-1/2 top-6 z-[80] flex -translate-x-1/2 items-center gap-[14px] rounded-[2px] px-[18px] py-[14px]"
    style="background: var(--card); border: 1px solid var(--line); box-shadow: var(--pop)">
    <span class="crm-tint h-[30px] w-[30px]" :style="tint">
      <CrmIcon :name="glyph" :size="17" />
    </span>
    <span
      class="whitespace-nowrap"
      style="
        font:
          700 16px Inter,
          sans-serif;
        color: var(--ink);
      ">
      {{ messageText }}
    </span>
    <button
      type="button"
      class="ms-1.5 flex cursor-pointer border-none bg-transparent p-0"
      style="color: var(--muted)"
      @click="isOpen = false">
      <CrmIcon name="close" :size="18" />
    </button>
  </div>
</template>
