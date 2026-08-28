<template>
  <layout :layout-data="layoutData">
    <!-- breadcrumb -->
    <nav class="bg-surface dark:bg-surface sm:border-b border-border dark:border-border">
      <div class="max-w-8xl mx-auto hidden px-4 py-2 sm:px-6 md:block">
        <div class="flex items-baseline justify-between space-x-6">
          <ul class="text-sm">
            <li class="me-2 inline text-text dark:text-text">
              {{ $t('You are here:') }}
            </li>
            <li class="me-2 inline">
              <InertiaLink :href="data.url.settings" class="text-accent hover:underline">
                {{ $t('Settings') }}
              </InertiaLink>
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
            <li class="me-2 inline">
              <InertiaLink :href="data.url.channels" class="text-accent hover:underline">
                {{ $t('Notification channels') }}
              </InertiaLink>
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
              {{ $t('Log details') }}
            </li>
          </ul>
        </div>
      </div>
    </nav>

    <main class="relative">
      <div class="mx-auto max-w-3xl px-2 py-2 sm:px-6 sm:py-6 lg:px-8">
        <!-- title + cta -->
        <div class="mb-3 mt-8 sm:mt-0">
          <h3 class="mb-4 text-center sm:mb-0">
            {{ $t('History of the notification sent') }}
          </h3>
          <ul class="bulleted-list text-center">
            <li class="me-2 inline">
              <span class="text-text-muted">{{ $t('Type:') }}</span>
              {{ data.channel.type }}
            </li>
            <li class="inline">
              <span class="text-text-muted">{{ $t('Label:') }}</span>
              {{ data.channel.label }}
            </li>
          </ul>
        </div>

        <!-- help text -->
        <div class="mb-6 flex rounded-xs border bg-bg px-3 py-2 text-sm dark:border-border dark:bg-surface">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-6 grow pe-2"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>

          <div>
            <p>
              {{
                $t(
                  'This page shows all the notifications that have been sent in this channel in the past. It primarily serves as a way to debug in case you don’t receive the notification you’ve set up.',
                )
              }}
            </p>
          </div>
        </div>

        <ul
          v-if="data.notifications.length > 0"
          class="mb-6 rounded-lg border border-border bg-surface dark:border-border dark:bg-surface">
          <li
            v-for="notification in data.notifications"
            :key="notification.id"
            class="item-list border-b border-border px-5 py-2 hover:bg-hover dark:border-border dark:bg-surface dark:hover:bg-hover">
            <span class="me-2 text-sm text-text-muted">{{ notification.sent_at }}</span>
            <span class="text-sm text-red-600 dark:text-red-400" v-if="notification.error !== ''">
              {{ notification.error }}
            </span>
            <span v-else>
              {{ notification.subject_line }}
            </span>
          </li>
        </ul>

        <!-- blank state -->
        <div
          v-if="data.notifications.length === 0"
          class="mb-6 rounded-lg border border-border bg-surface dark:border-border dark:bg-surface">
          <p class="p-5 text-center">
            {{ $t('You haven’t received a notification in this channel yet.') }}
          </p>
        </div>
      </div>
    </main>
  </layout>
</template>

<script>
import { Link } from '@inertiajs/vue3';
import Layout from '@/Layouts/Layout.vue';

export default {
  components: {
    InertiaLink: Link,
    Layout,
  },

  props: {
    layoutData: {
      type: Object,
      default: null,
    },
    data: {
      type: Object,
      default: null,
    },
  },
};
</script>

<style lang="scss" scoped>
.item-list {
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
