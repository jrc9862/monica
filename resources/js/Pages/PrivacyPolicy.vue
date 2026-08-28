<script setup>
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';

defineProps({
  policy: String,
});
</script>

<template>
  <div class="font-sans text-text antialiased">
    <div class="pt-4 bg-bg">
      <div class="min-h-screen flex flex-col items-center pt-6 sm:pt-0">
        <div>
          <AuthenticationCardLogo />
        </div>

        <div
          class="w-full sm:max-w-2xl mt-6 p-6 bg-surface shadow-md dark:shadow-gray-700 overflow-hidden sm:rounded-lg prose"
          v-html="policy"></div>
      </div>
    </div>
  </div>
</template>
