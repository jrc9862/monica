<script setup>
import { Link } from '@inertiajs/vue3';
import Avatar from '@/Shared/Avatar.vue';

defineProps({
  data: Object,
  contactViewMode: Boolean,
});
</script>

<template>
  <div class="rounded-lg border border-border dark:border-border">
    <!-- contact information -->
    <div v-if="!contactViewMode" class="flex items-center border-b border-border px-3 py-2 text-sm dark:border-border">
      <Avatar
        :data="data.contact.avatar"
        :class="'relative me-2 h-5 w-5 rounded-full border border-border dark:border-border'" />

      <div class="flex flex-col">
        <Link :href="data.contact.url" class="text-text hover:underline dark:text-text">{{ data.contact.name }}</Link>
      </div>
    </div>

    <div class="px-3 pb-2 pt-2">
      <!-- the note still exists in the system -->
      <div v-if="data.note.object">
        <div v-if="data.note.object.title" class="mb-2 block">{{ data.note.object.title }}</div>
        <div>{{ data.note.object.body }}</div>
      </div>

      <!-- the note was deleted -->
      <span v-else class="mb-2 me-2 inline-block rounded-xs bg-bg px-2 py-1 text-xs font-semibold text-text last:me-0">
        <span>{{ data.note.description }}</span>
      </span>
    </div>
  </div>
</template>
