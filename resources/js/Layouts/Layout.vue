<script setup>
import { ref, onMounted } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import Toaster from '@/Shared/Toaster.vue';
import IconRail from '@/Shared/Crm/IconRail.vue';
import CrmHeader from '@/Shared/Crm/CrmHeader.vue';
import { isDark, flash } from '@/methods.js';

defineProps({
  title: String,
  insideVault: Boolean,
  layoutData: Object,
  // Contact detail right-aligns the search field beside the add button.
  compactHeader: { type: Boolean, default: false },
  backUrl: { type: String, default: null },
  backLabel: { type: String, default: null },
});

const page = usePage();
const dark = ref(false);

const applyDensity = (density) => {
  if (typeof window === 'undefined') return;
  document.documentElement.classList.remove('chunky', 'minimal');
  document.documentElement.classList.add(density === 'chunky' ? 'chunky' : 'minimal');
};

onMounted(() => {
  dark.value = isDark();
  applyDensity(page.props.auth?.ui_density ?? 'minimal');

  if (localStorage.success) {
    flash(localStorage.success, 'success');
    localStorage.removeItem('success');
  }
});

const toggleTheme = () => {
  dark.value = !dark.value;
  localStorage.theme = dark.value ? 'dark' : 'light';
  document.documentElement.classList.toggle('dark', dark.value);
  document.documentElement.setAttribute('data-theme', dark.value ? 'dark' : 'light');
};
</script>

<template>
  <Head :title="title" />

  <div
    class="flex min-h-screen"
    style="
      min-width: 1180px;
      background: var(--bg);
      color: var(--ink);
      transition:
        background 0.25s ease,
        color 0.25s ease;
    ">
    <IconRail :layout-data="layoutData" :dark="dark" @toggle-theme="toggleTheme" />

    <div class="flex min-w-0 flex-1 flex-col">
      <CrmHeader
        :title="title"
        :layout-data="layoutData"
        :inside-vault="insideVault"
        :compact="compactHeader"
        :back-url="backUrl"
        :back-label="backLabel" />

      <div v-if="$slots.header" class="px-6 pt-6">
        <slot name="header" />
      </div>

      <main class="crm-fade flex min-w-0 flex-1">
        <slot />
      </main>
    </div>
  </div>

  <Toaster />
</template>
