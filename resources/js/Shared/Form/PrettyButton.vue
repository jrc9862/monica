<script setup>
import PlusIcon from '@/Shared/Icons/PlusIcon.vue';
import CheckedIcon from '@/Shared/Icons/CheckedIcon.vue';
import MinusIcon from '@/Shared/Icons/MinusIcon.vue';
import ArrowIcon from '@/Shared/Icons/ArrowIcon.vue';
import { Trash2 } from 'lucide-vue-next';

defineProps({
  text: String,
  icon: String,
  state: String,
});
</script>

<template>
  <button
    :class="'relative flex border border-border bg-surface-raised text-sm text-text hover:border-accent'"
    :disabled="state === 'loading' || state === 'disabled'"
    type="submit">
    <span v-if="state === 'loading'">
      {{ $t('Loading…') }}
    </span>

    <template v-else>
      <PlusIcon v-if="icon === 'plus'" />

      <CheckedIcon v-else-if="icon === 'check'" />

      <MinusIcon v-else-if="icon === 'minus'" />

      <Trash2 class="icon relative me-1 inline h-5 w-5" v-else-if="icon === 'trash'" />

      <span>
        {{ text }}
      </span>

      <ArrowIcon :type="'right'" :size="'big'" v-if="icon === 'arrow'" />
    </template>
  </button>
</template>

<style lang="scss" scoped>
.icon {
  top: -1px;
}

.save,
.dark .save {
  background-color: var(--primary) !important;
  color: var(--on-primary) !important;
}

button {
  --tw-shadow: 4px 4px 0 var(--stamp) !important;
  border-radius: 2px !important;
  border-width: 1px !important;
  border-color: var(--stamp) !important;
  box-shadow:
    var(--tw-ring-offset-shadow, 0 0 transparent), var(--tw-ring-shadow, 0 0 transparent), var(--tw-shadow) !important;
  display: inline-block !important;
  position: relative !important;
  text-decoration: none !important;
  transition-duration: 0.15s !important;
  transition-property:
    background-color,
    border-color,
    color,
    fill,
    stroke,
    opacity,
    box-shadow,
    transform,
    filter,
    backdrop-filter,
    -webkit-backdrop-filter !important;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1) !important;
  font: 500 12px var(--mono) !important;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  line-height: 44px !important;
  padding-left: 18px;
  padding-right: 18px;

  &:hover {
    box-shadow: none !important;
    transform: translate(4px, 4px);
  }

  &:disabled {
    box-shadow: none;
    transform: translate(0, 0);
  }
}
</style>
