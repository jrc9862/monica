<script setup>
import { Link } from '@inertiajs/vue3';
import CrmAvatar from '@/Shared/CrmAvatar.vue';
import PanelHeader from '@/Shared/Crm/PanelHeader.vue';
import CrmIcon from '@/Shared/Icons/CrmIcon.vue';

defineProps({
  data: { type: Object, default: null },
});

const toggle = (task) => {
  task.completed = !task.completed;
  axios.put(task.url.toggle);
};
</script>

<template>
  <div>
    <PanelHeader icon="task" :title="$t('Tasks due soon')" :size="16" />

    <div v-if="Object.keys(data.tasks).length > 0" class="crm-card overflow-hidden">
      <div
        v-for="task in data.tasks"
        :key="task.id"
        class="crm-row-hover flex items-center gap-[10px] px-[18px] py-4"
        style="border-bottom: 1px solid var(--divider)">
        <button
          type="button"
          class="flex h-[18px] w-[18px] flex-none items-center justify-center rounded-[2px]"
          :style="{
            border: `1.5px solid ${task.completed ? 'var(--primary)' : 'var(--faint)'}`,
            background: task.completed ? 'var(--primary)' : 'transparent',
            color: 'var(--on-primary)',
          }"
          @click="toggle(task)">
          <CrmIcon v-if="task.completed" name="check" :size="12" />
        </button>

        <span
          class="min-w-0 flex-1 overflow-hidden text-ellipsis whitespace-nowrap"
          :style="{
            font: '400 15px Inter, sans-serif',
            color: task.completed ? 'var(--muted)' : 'var(--ink)',
            textDecoration: task.completed ? 'line-through' : 'none',
          }">
          {{ task.label }}
        </span>

        <Link :href="task.contact.url.show" class="flex flex-none items-center gap-1.5">
          <CrmAvatar :data="task.contact.avatar" :size="18" />
        </Link>

        <span
          v-if="task.due_at !== null"
          class="flex-none whitespace-nowrap"
          :style="{
            font: '400 11px var(--mono)',
            color: task.due_at.is_late && !task.completed ? 'var(--pink)' : 'var(--muted)',
          }">
          {{ task.due_at.formatted }}
        </span>
      </div>
    </div>

    <div v-else class="crm-card flex items-center gap-3 p-[18px]">
      <span class="crm-tint h-[38px] w-[38px]" style="background: var(--primary-soft); color: var(--primary)">
        <CrmIcon name="task" :size="18" />
      </span>
      <p class="crm-meta-12">{{ $t('No tasks.') }}</p>
    </div>

    <div class="mt-3 text-center">
      <Link :href="data.url.index" class="crm-btn-quiet">{{ $t('View all') }}</Link>
    </div>
  </div>
</template>
