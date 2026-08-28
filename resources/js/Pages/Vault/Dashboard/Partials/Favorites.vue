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
    <PanelHeader icon="heart" :title="$t('Favorites')" color="var(--pink)" />

    <div class="flex flex-col gap-2">
      <Link
        v-for="favorite in data"
        :key="favorite.id"
        :href="favorite.url.show"
        class="crm-sidebar-link flex items-center gap-2 text-left">
        <CrmAvatar :data="favorite.avatar" :size="24" />
        <span
          class="overflow-hidden text-ellipsis whitespace-nowrap"
          style="font: 500 13px var(--mono); color: var(--primary)">
          {{ favorite.name }}
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
