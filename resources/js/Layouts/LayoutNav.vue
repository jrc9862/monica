<script setup>
import { Link, router } from '@inertiajs/vue3';

defineProps({
  links: Object,
});

const navigateToSelected = (event) => {
  router.visit(event.target.value);
};
</script>

<template>
  <div>
    <!-- vault sub menu on desktop -->
    <nav class="hidden sm:block border-b border-border bg-bg sm:border-b">
      <div class="max-w-8xl mx-auto px-4 py-2 sm:px-6 block">
        <ul class="list-none text-sm font-medium">
          <li v-for="link in links" :key="link.url" class="inline">
            <Link
              :href="link.url"
              :class="link.selected ? 'bg-accent text-bg' : 'text-text-muted hover:bg-surface-raised hover:text-text'"
              class="me-2 px-2 py-1">
              {{ link.title }}
            </Link>
          </li>
        </ul>
      </div>
    </nav>

    <!-- vault sub menu on mobile -->
    <nav class="block md:hidden px-4 py-2 bg-bg border-b border-border">
      <div class="relative">
        <select
          @change="navigateToSelected"
          class="w-full border border-border bg-surface py-2 pl-3 pr-10 text-base text-text focus:border-accent focus:outline-hidden">
          <option value="" disabled>{{ $t('Select a page') }}</option>
          <option v-for="link in links" :key="link.url" :value="link.url" :selected="link.selected">
            {{ link.title }}
          </option>
        </select>
      </div>
    </nav>
  </div>
</template>
