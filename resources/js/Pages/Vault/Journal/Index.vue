<script setup>
import { Link } from '@inertiajs/vue3';
import Layout from '@/Layouts/Layout.vue';
import ScreenHeader from '@/Shared/Crm/ScreenHeader.vue';
import CrmCardGrid from '@/Shared/Crm/CrmCardGrid.vue';
import CrmCard from '@/Shared/Crm/CrmCard.vue';
import CrmIcon from '@/Shared/Icons/CrmIcon.vue';
import { trans } from 'laravel-vue-i18n';

defineProps({
  layoutData: Object,
  data: Object,
});

const meta = (journal) => (journal.last_updated ? trans('Updated on :date', { date: journal.last_updated }) : null);
</script>

<template>
  <Layout :title="$t('Journals')" :layout-data="layoutData" :inside-vault="true">
    <div class="min-w-0 flex-1 p-6">
      <ScreenHeader :title="$t('Journals in this vault')">
        <template #count>{{ data.journals.length }}</template>
        <template #actions>
          <Link
            v-if="layoutData.vault.permission.at_least_editor"
            :href="data.url.create"
            class="crm-press crm-btn h-11 px-5">
            <CrmIcon name="plus" :size="18" />
            {{ $t('Create a journal') }}
          </Link>
        </template>
      </ScreenHeader>

      <CrmCardGrid v-if="data.journals.length !== 0">
        <CrmCard
          v-for="journal in data.journals"
          :key="journal.id"
          :href="journal.url.show"
          icon="note"
          :title="journal.name"
          :meta="meta(journal)"
          :description="journal.description" />
      </CrmCardGrid>

      <div v-else class="crm-card flex flex-col items-center gap-3 px-6 py-12">
        <span class="flex" style="color: var(--faint)"><CrmIcon name="note" :size="28" /></span>
        <span class="crm-meta-12">{{ $t('Create a journal to document your life.') }}</span>
      </div>
    </div>
  </Layout>
</template>
