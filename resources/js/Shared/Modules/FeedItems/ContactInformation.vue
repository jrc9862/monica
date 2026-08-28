<template>
  <div class="rounded-lg border border-border dark:border-border">
    <!-- contact information -->
    <div v-if="!contactViewMode" class="flex items-center border-b border-border px-3 py-2 text-sm dark:border-border">
      <avatar
        :data="data.contact.avatar"
        :class="'relative me-2 h-5 w-5 rounded-full border border-border dark:border-border'" />

      <div class="flex flex-col">
        <InertiaLink :href="data.contact.url" class="text-text hover:underline dark:text-text">{{
          data.contact.name
        }}</InertiaLink>
      </div>
    </div>

    <div class="px-3 py-2">
      <!-- the contact information still exists in the system -->
      <span v-if="data.information.object">
        <a :href="data.information.object.data" class="text-accent hover:underline"
          >{{ data.information.object.label }}
        </a>
      </span>

      <!-- the label was deleted -->
      <span v-else>
        {{ data.information.description }}
      </span>
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
