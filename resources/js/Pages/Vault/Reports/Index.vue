<script setup>
import Layout from '@/Layouts/Layout.vue';
import ScreenHeader from '@/Shared/Crm/ScreenHeader.vue';
import CrmCardGrid from '@/Shared/Crm/CrmCardGrid.vue';
import CrmCard from '@/Shared/Crm/CrmCard.vue';

defineProps({
  layoutData: Object,
  data: Object,
});
</script>

<template>
  <Layout :title="$t('Reports')" :layout-data="layoutData" :inside-vault="true">
    <div class="min-w-0 flex-1 p-6">
      <ScreenHeader :title="$t('Reports for this vault')" />

      <CrmCardGrid>
        <CrmCard
          :href="data.url.addresses"
          icon="location"
          :title="$t('Addresses')"
          :description="$t('List of addresses of the contacts in the vault')" />

        <CrmCard
          :href="data.url.mood_tracking_events"
          icon="face"
          tint="var(--mint)"
          tint-fg="var(--green)"
          :title="$t('Mood')"
          :description="$t('Mood tracking events')" />

        <CrmCard
          :href="data.url.important_date_summary"
          icon="cake"
          tint="var(--sun)"
          tint-fg="var(--yellow)"
          :title="$t('Important dates')"
          :description="$t('Important date summary')" />
      </CrmCardGrid>
    </div>
  </Layout>
</template>
