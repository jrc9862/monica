<template>
  <div v-if="!contactViewMode" class="flex items-center rounded-lg border border-border px-3 py-2 dark:border-border">
    <avatar :data="data.avatar" :class="'relative me-2 h-8 w-8 rounded-full border border-border dark:border-border'" />

    <div class="flex flex-col">
      <InertiaLink :href="data.url" class="text-text hover:underline dark:text-text">{{ data.name }}</InertiaLink>
      <span v-if="data.age || data.age === 0" class="text-text-muted">{{ data.age }}</span>
      <span v-else class="text-xs text-text-muted">{{ $t('Unknown age') }}</span>
    </div>
  </div>
</template>

<script>
import { Link } from '@inertiajs/vue3';
import Avatar from '@/Shared/Avatar.vue';

export default {
  components: {
    InertiaLink: Link,
    Avatar,
  },

  props: {
    data: {
      type: Object,
      default: null,
    },
    contactViewMode: {
      type: Boolean,
      default: true,
    },
  },
};
</script>
