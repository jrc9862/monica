<script setup>
import Layout from '@/Layouts/Layout.vue';
import ScreenHeader from '@/Shared/Crm/ScreenHeader.vue';
import CrmCardGrid from '@/Shared/Crm/CrmCardGrid.vue';
import CrmCard from '@/Shared/Crm/CrmCard.vue';
import CrmAvatar from '@/Shared/CrmAvatar.vue';
import CrmIcon from '@/Shared/Icons/CrmIcon.vue';
import { trans } from 'laravel-vue-i18n';

defineProps({
  layoutData: Object,
  data: Object,
});

const meta = (group) => (group.contacts ? trans(':count people', { count: group.contacts.length }) : null);
</script>

<template>
  <Layout :title="$t('Groups')" :layout-data="layoutData" :inside-vault="true">
    <div class="min-w-0 flex-1 p-6">
      <ScreenHeader :title="$t('Groups in this vault')">
        <template #count>{{ data.length }}</template>
      </ScreenHeader>

      <CrmCardGrid v-if="data.length !== 0">
        <CrmCard
          v-for="group in data"
          :key="group.id"
          :href="group.url.show"
          icon="group"
          tint="var(--mint)"
          tint-fg="var(--green)"
          :title="group.name"
          :meta="meta(group)">
          <template v-if="group.contacts && group.contacts.length" #footer>
            <span v-for="contact in group.contacts" :key="contact.id" class="-ms-2 first:ms-0">
              <CrmAvatar :data="contact.avatar" :size="24" />
            </span>
          </template>
        </CrmCard>
      </CrmCardGrid>

      <div v-else class="crm-card flex flex-col items-center gap-3 px-6 py-12">
        <span class="flex" style="color: var(--faint)"><CrmIcon name="group" :size="28" /></span>
        <span class="crm-meta-12">{{ $t('Groups let you put your contacts together in a single place.') }}</span>
      </div>
    </div>
  </Layout>
</template>
