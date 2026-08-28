<script setup>
import { Link } from '@inertiajs/vue3';
import CrmAvatar from '@/Shared/CrmAvatar.vue';
import PanelHeader from '@/Shared/Crm/PanelHeader.vue';

defineProps({
  data: { type: Object, default: null },
});
</script>

<template>
  <div>
    <PanelHeader icon="clock" :title="$t('Last updated')" />

    <div class="flex flex-col gap-2">
      <Link
        v-for="contact in data"
        :key="contact.id"
        :href="contact.url.show"
        class="crm-sidebar-link flex items-center gap-2 text-left">
        <CrmAvatar :data="contact.avatar" :size="24" />
        <span
          class="overflow-hidden text-ellipsis whitespace-nowrap"
          style="font: 500 13px var(--mono); color: var(--primary)">
          {{ contact.name }}
        </span>
      </Link>
    </div>
  </div>
</template>

<style scoped>
.crm-sidebar-link:hover {
  opacity: 0.7;
  text-decoration: none;
}
</style>
