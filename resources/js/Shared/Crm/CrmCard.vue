<script setup>
import { Link } from '@inertiajs/vue3';
import CrmIcon from '@/Shared/Icons/CrmIcon.vue';

// One card serves the Vaults screen and the Journals / Groups / Companies /
// Files grids: tint tile, title, description, mono meta.
defineProps({
  href: { type: String, required: true },
  icon: { type: String, default: 'note' },
  tint: { type: String, default: 'var(--primary-soft)' },
  tintFg: { type: String, default: 'var(--primary)' },
  title: { type: String, required: true },
  meta: { type: String, default: null },
  description: { type: String, default: null },
  // 52px tile with the title beneath (vaults) vs. 56px tile beside it (grids).
  stacked: { type: Boolean, default: false },
});
</script>

<template>
  <Link :href="href" class="crm-card crm-card-hover flex flex-col gap-3 p-6 text-left no-underline">
    <template v-if="stacked">
      <span class="crm-tint h-[52px] w-[52px]" :style="{ background: tint, color: tintFg }">
        <CrmIcon :name="icon" :size="24" />
      </span>
      <span
        style="
          font:
            700 22px Inter,
            sans-serif;
          color: var(--ink);
        "
        >{{ title }}</span
      >
    </template>

    <div v-else class="flex items-center gap-3">
      <span class="crm-tint h-[56px] w-[56px]" :style="{ background: tint, color: tintFg }">
        <CrmIcon :name="icon" :size="20" />
      </span>
      <div class="min-w-0 flex-1">
        <div
          class="overflow-hidden text-ellipsis whitespace-nowrap"
          style="
            font:
              700 18px Inter,
              sans-serif;
            color: var(--ink);
          ">
          {{ title }}
        </div>
        <div
          v-if="meta"
          class="overflow-hidden text-ellipsis whitespace-nowrap"
          style="font: 400 12px var(--mono); color: var(--muted)">
          {{ meta }}
        </div>
      </div>
    </div>

    <p
      v-if="description"
      class="m-0"
      style="
        font:
          400 15px/26px Inter,
          sans-serif;
        color: var(--ink2);
      ">
      {{ description }}
    </p>

    <span v-if="stacked && meta" style="font: 400 12px var(--mono); color: var(--muted)">{{ meta }}</span>

    <div v-if="$slots.footer" class="mt-auto flex gap-1.5">
      <slot name="footer" />
    </div>
  </Link>
</template>
