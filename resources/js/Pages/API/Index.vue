<script setup>
import { Link } from '@inertiajs/vue3';
import ApiTokenManager from '@/Pages/API/Partials/ApiTokenManager.vue';
import Layout from '@/Layouts/Layout.vue';

defineProps({
  layoutData: Object,
  tokens: Array,
  availablePermissions: Array,
  defaultPermissions: Array,
});
</script>

<template>
  <Layout title="API Tokens" :layout-data="layoutData">
    <!-- breadcrumb -->
    <nav class="bg-surface dark:bg-surface sm:border-b border-border dark:border-border">
      <div class="max-w-8xl mx-auto hidden px-4 py-2 sm:px-6 md:block">
        <div class="flex items-baseline justify-between space-x-6">
          <ul class="text-sm">
            <li class="me-2 inline text-text dark:text-text">
              {{ $t('You are here:') }}
            </li>
            <li class="me-2 inline">
              <Link :href="route('settings.index')" class="text-accent hover:underline">
                {{ $t('Settings') }}
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
              {{ $t('API Tokens') }}
            </li>
          </ul>
        </div>
      </div>
    </nav>

    <div class="relative">
      <div class="mx-auto max-w-3xl px-2 py-2 sm:px-6 sm:py-6 lg:px-8">
        <ApiTokenManager
          :tokens="tokens"
          :available-permissions="availablePermissions"
          :default-permissions="defaultPermissions" />
      </div>
    </div>
  </Layout>
</template>
