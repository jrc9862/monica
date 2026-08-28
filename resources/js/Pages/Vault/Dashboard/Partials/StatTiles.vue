<script setup>
import CrmIcon from '@/Shared/Icons/CrmIcon.vue';

// Both figures come from the server, computed off the same queries the
// contacts list and the notes modules read.
defineProps({
  statistics: { type: Object, required: true },
});
</script>

<template>
  <div class="flex min-w-[240px] flex-1 flex-col gap-6">
    <section class="crm-card flex flex-1 items-center justify-between gap-4 p-6">
      <div>
        <div class="crm-label">{{ $t('Contacts') }}</div>
        <div class="crm-figure">{{ statistics.contacts }}</div>
      </div>
      <span class="crm-tint h-[88px] w-[88px]" style="background: var(--mint); color: var(--green)">
        <CrmIcon name="people" :size="34" />
      </span>
    </section>

    <section class="crm-card flex flex-1 items-center justify-between gap-4 p-6">
      <div>
        <div class="crm-label">{{ $t('Notes written') }}</div>
        <div class="crm-figure">{{ statistics.notes }}</div>
      </div>
      <span class="crm-tint h-[88px] w-[88px]" style="background: var(--blush); color: var(--pink)">
        <CrmIcon name="note" :size="34" />
      </span>
    </section>
  </div>
</template>
