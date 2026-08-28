<script setup>
import { Link } from '@inertiajs/vue3';
import Layout from '@/Layouts/Layout.vue';
import PrettyLink from '@/Shared/Form/PrettyLink.vue';

defineProps({
  layoutData: Object,
  data: Object,
});
</script>

<template>
  <layout :layout-data="layoutData" :inside-vault="true">
    <!-- breadcrumb -->
    <nav class="bg-surface dark:bg-surface sm:border-b">
      <div class="max-w-8xl mx-auto hidden px-4 py-2 sm:px-6 md:block">
        <div class="flex items-baseline justify-between space-x-6">
          <ul class="text-sm">
            <li class="me-2 inline text-text dark:text-text">
              {{ $t('You are here:') }}
            </li>
            <li class="me-2 inline">
              <Link :href="layoutData.vault.url.journals" class="text-accent hover:underline">
                {{ $t('Journals') }}
              </Link>
            </li>
            <li class="relative me-2 inline">
              <svg
                xmlns="http://www.w3.org/2000/svg"
                class="icon-breadcrumb relative inline h-3 w-3"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </li>
            <li class="inline">
              <Link :href="data.url.back" class="text-accent hover:underline">
                {{ data.journal.name }}
              </Link>
            </li>
            <li class="relative me-2 inline">
              <svg
                xmlns="http://www.w3.org/2000/svg"
                class="icon-breadcrumb relative inline h-3 w-3"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </li>
            <li class="inline">
              {{ $t('Choose a template') }}
            </li>
          </ul>
        </div>
      </div>
    </nav>

    <main class="relative">
      <div class="mx-auto max-w-lg px-2 py-2 sm:px-6 sm:py-6 lg:px-8">
        <h3 class="mb-4">{{ $t('Please choose a template for this new post') }}</h3>
        <ul class="mb-6 rounded-lg border border-border dark:border-border dark:bg-surface">
          <li
            v-for="template in data.templates"
            :key="template.id"
            class="template-list border-b border-border px-3 py-2 hover:bg-hover dark:border-border dark:hover:bg-hover">
            <div class="flex items-center justify-between">
              <div>
                <p class="font-semibold">{{ template.label }}</p>
                <p class="text-sm text-text-muted">
                  {{
                    $tChoice(':count template section|:count template sections', template.sections.length, {
                      count: template.sections.length,
                    })
                  }}
                </p>
              </div>

              <!-- choose button -->
              <pretty-link
                v-if="layoutData.vault.permission.at_least_editor"
                :href="template.url.create"
                :text="$t('Choose')"
                :icon="'plus'" />
            </div>
          </li>
        </ul>
      </div>
    </main>
  </layout>
</template>

<style lang="scss" scoped>
.template-list {
  &:hover:first-child {
    border-top-left-radius: 8px;
    border-top-right-radius: 8px;
  }

  &:last-child {
    border-bottom: 0;
  }

  &:hover:last-child {
    border-bottom-left-radius: 8px;
    border-bottom-right-radius: 8px;
  }
}
</style>
