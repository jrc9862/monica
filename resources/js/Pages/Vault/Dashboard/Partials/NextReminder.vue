<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import CrmAvatar from '@/Shared/CrmAvatar.vue';
import CrmIcon from '@/Shared/Icons/CrmIcon.vue';

const props = defineProps({
  reminders: { type: Array, default: () => [] },
});

const next = computed(() => props.reminders[0] ?? null);
</script>

<template>
  <section
    class="relative flex min-w-[300px] flex-col gap-7 overflow-hidden rounded-[2px] p-6"
    style="flex: 1.15; background: var(--primary)">
    <span
      class="pointer-events-none absolute h-[300px] w-[300px] rounded-full opacity-50"
      style="
        left: 56px;
        top: 150px;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.25) 0%, rgba(255, 255, 255, 0) 65%);
      " />

    <div class="relative flex items-center justify-between gap-3">
      <span
        class="whitespace-nowrap"
        style="font: 500 12px var(--mono); color: var(--on-primary); letter-spacing: 0.16em; text-transform: uppercase">
        {{ $t('Next reminder') }}
      </span>
      <span class="h-[10px] w-[10px] flex-none rounded-full" style="background: #fff" />
    </div>

    <template v-if="next">
      <div class="relative flex items-center gap-[14px]">
        <CrmAvatar :data="next.contact.avatar" :size="52" />
        <div class="min-w-0">
          <div
            style="
              font:
                700 18px/26px Inter,
                sans-serif;
              color: var(--on-primary);
            ">
            {{ next.contact.name }}
          </div>
          <div
            class="overflow-hidden text-ellipsis whitespace-nowrap"
            style="font: 400 13px/22px var(--mono); color: var(--on-primary-dim)">
            {{ next.label }}
          </div>
        </div>
      </div>

      <div class="relative flex gap-10">
        <div>
          <div
            class="whitespace-nowrap"
            style="
              font: 500 11px var(--mono);
              color: var(--on-primary-dim);
              letter-spacing: 0.14em;
              text-transform: uppercase;
            ">
            {{ $t('Date') }}
          </div>
          <div class="crm-figure-sm" style="color: var(--on-primary)">{{ next.scheduled_at }}</div>
        </div>
      </div>

      <div class="relative mt-auto flex flex-wrap items-center gap-x-4 gap-y-3">
        <span class="min-w-0 flex-1" style="font: 400 12px var(--mono); color: var(--on-primary-dim)">
          {{ next.label }}
        </span>
        <Link :href="next.contact.url.show" class="crm-press crm-btn crm-btn-on-moss flex-none">
          {{ $t('See contact') }}
        </Link>
      </div>
    </template>

    <div v-else class="relative mt-auto flex items-center gap-3">
      <span class="crm-tint h-[52px] w-[52px]" style="background: rgba(255, 255, 255, 0.28); color: var(--on-primary)">
        <CrmIcon name="bell" :size="24" />
      </span>
      <span style="font: 400 13px/22px var(--mono); color: var(--on-primary-dim)">
        {{ $t('No upcoming reminders.') }}
      </span>
    </div>
  </section>
</template>
