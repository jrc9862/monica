<script setup>
import { Link } from '@inertiajs/vue3';
import { Pencil } from 'lucide-vue-next';

defineProps({
  data: Object,
});
</script>

<template>
  <div class="mb-4">
    <div class="pb-1 mb-2 items-center justify-between border-b border-border dark:border-border flex">
      <div class="text-xs">{{ $t('Important dates') }}</div>
      <Link :href="data.url.edit" class="relative">
        <Pencil class="h-3 w-3 text-text-muted" />
      </Link>
    </div>

    <!-- list of dates -->
    <ul v-for="date in data.dates" :key="date.id">
      <li class="mb-1 flex items-center justify-between hover:bg-hover dark:bg-surface dark:hover:bg-hover">
        <span class="text-sm">{{ date.label }}</span>
        <span class="text-sm">
          {{ date.date }} <span v-if="date.age" class="text-xs text-text-muted">({{ date.age }})</span>
        </span>
      </li>
    </ul>

    <!-- blank state -->
    <p v-if="data.dates.length === 0" class="text-sm text-text dark:text-text">{{ $t('Not set') }}</p>
  </div>
</template>

<style lang="scss" scoped>
.icon-sidebar {
  top: -2px;
}
</style>
