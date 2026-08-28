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

const meta = (company) =>
  company.contacts ? trans(':count contacts work here', { count: company.contacts.length }) : null;
</script>

<template>
  <Layout :title="$t('Companies')" :layout-data="layoutData" :inside-vault="true">
    <div class="min-w-0 flex-1 p-6">
      <ScreenHeader :title="$t('Companies in this vault')">
        <template #count>{{ data.companies.length }}</template>
      </ScreenHeader>

      <CrmCardGrid v-if="data.companies.length !== 0">
        <CrmCard
          v-for="company in data.companies"
          :key="company.id"
          :href="company.url.show"
          icon="buildings"
          :title="company.name"
          :meta="meta(company)">
          <template v-if="company.contacts && company.contacts.length" #footer>
            <span v-for="contact in company.contacts" :key="contact.id" class="-ms-2 first:ms-0">
              <CrmAvatar :data="contact.avatar" :size="24" />
            </span>
          </template>
        </CrmCard>
      </CrmCardGrid>

      <div v-else class="crm-card flex flex-col items-center gap-3 px-6 py-12">
        <span class="flex" style="color: var(--faint)"><CrmIcon name="buildings" :size="28" /></span>
        <span class="crm-meta-12">
          {{ $t('You can add job information to your contacts and manage the companies here in this tab.') }}
        </span>
      </div>
    </div>
  </Layout>
</template>
