<template>
  <InertiaLink
    :class="'flex items-center gap-1 relative border border-border bg-surface-raised text-sm text-text hover:border-accent'"
    :href="href"
    preserve-scroll>
    <Plus class="h-4 w-4" v-if="icon === 'plus'" />

    <Check class="h-4 w-4" v-else-if="icon === 'check'" />

    <DoorOpen class="h-4 w-4" v-else-if="icon === 'exit'" />

    <span>
      {{ text }}
    </span>
  </InertiaLink>
</template>

<script>
import { Link } from '@inertiajs/vue3';
import { Plus, Check, DoorOpen } from 'lucide-vue-next';

export default {
  components: {
    InertiaLink: Link,
    Plus,
    Check,
    DoorOpen,
  },

  props: {
    text: {
      type: String,
      default: '',
    },
    icon: {
      type: String,
      default: '',
    },
    href: {
      type: String,
      default: '',
    },
  },
};
</script>

<style lang="scss" scoped>
.icon {
  top: -1px;
}

a {
  --tw-shadow: 4px 4px 0 #000000 !important;
  border-radius: 0 !important;
  border-width: 1px !important;
  box-shadow:
    var(--tw-ring-offset-shadow, 0 0 transparent), var(--tw-ring-shadow, 0 0 transparent), var(--tw-shadow) !important;
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
  font-size: 0.875rem !important;
  line-height: 1.25rem !important;
  padding-left: 9px;
  padding-right: 9px;

  &:hover {
    box-shadow: none !important;
    transform: translate(4px, 4px);
  }
}
</style>
