<script setup>
import { Link } from '@inertiajs/vue3';
import CrmAvatar from '@/Shared/CrmAvatar.vue';
import PanelHeader from '@/Shared/Crm/PanelHeader.vue';
import CrmIcon from '@/Shared/Icons/CrmIcon.vue';

defineProps({
  data: { type: Object, default: null },
});
</script>

<template>
  <div>
    <PanelHeader icon="calendar" :title="$t('Reminders for the next 30 days')" :size="16" />

    <div v-if="data.reminders.length > 0" class="crm-card overflow-hidden">
      <Link
        v-for="reminder in data.reminders"
        :key="reminder.id"
        :href="reminder.contact.url.show"
        class="crm-row-hover block px-[18px] py-4"
        style="border-bottom: 1px solid var(--divider)">
        <div class="mb-0.5 flex items-center gap-2">
          <span class="whitespace-nowrap" style="font: 400 11px var(--mono); color: var(--muted)">
            {{ reminder.scheduled_at }}
          </span>
          <CrmAvatar :data="reminder.contact.avatar" :size="18" />
          <span style="font: 500 13px var(--mono); color: var(--primary)">{{ reminder.contact.name }}</span>
        </div>
        <div
          style="
            font:
              400 15px Inter,
              sans-serif;
            color: var(--ink);
          ">
          {{ reminder.label }}
        </div>
      </Link>
    </div>

    <div v-else class="crm-card flex items-center gap-3 p-[18px]">
      <span class="crm-tint h-[38px] w-[38px]" style="background: var(--primary-soft); color: var(--primary)">
        <CrmIcon name="bell" :size="18" />
      </span>
      <p class="crm-meta-12">{{ $t('No upcoming reminders.') }}</p>
    </div>

    <div v-if="data.reminders.length > 0" class="mt-3 text-center">
      <Link :href="data.url.index" class="crm-btn-quiet">{{ $t('View all') }}</Link>
    </div>
  </div>
</template>
