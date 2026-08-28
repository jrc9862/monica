<script setup>
import { Link } from '@inertiajs/vue3';
import Layout from '@/Layouts/Layout.vue';
import CrmAvatar from '@/Shared/CrmAvatar.vue';
import CrmIcon from '@/Shared/Icons/CrmIcon.vue';

defineProps({
  layoutData: { type: Object, default: null },
  data: { type: Object, default: null },
});

const toggle = (task) => {
  task.completed = !task.completed;
  axios.put(task.url.toggle);
};
</script>

<template>
  <Layout :title="$t('Tasks')" :layout-data="layoutData" :inside-vault="true">
    <div class="min-w-0 flex-1 p-6">
      <div class="max-w-[1000px]">
        <div v-for="group in data.groups" :key="group.title" class="mb-7">
          <div class="mb-3 flex items-center gap-2">
            <span
              style="font: 500 11px var(--mono); letter-spacing: 0.12em; text-transform: uppercase"
              :style="{ color: group.color }">
              {{ group.title }}
            </span>
            <span class="crm-meta">{{ group.count }}</span>
          </div>

          <div v-if="group.tasks.length > 0" class="crm-card overflow-hidden">
            <div
              v-for="task in group.tasks"
              :key="task.id"
              class="crm-row-hover flex items-center gap-[14px] px-5 py-4"
              style="border-bottom: 1px solid var(--divider)">
              <button
                type="button"
                class="flex h-5 w-5 flex-none items-center justify-center rounded-[2px]"
                :style="{
                  border: `1.5px solid ${task.completed ? 'var(--primary)' : task.due_at?.is_late ? 'var(--pink)' : 'var(--faint)'}`,
                  background: task.completed ? 'var(--primary)' : 'transparent',
                  color: 'var(--on-primary)',
                }"
                @click="toggle(task)">
                <CrmIcon v-if="task.completed" name="check" :size="13" />
              </button>

              <span
                class="min-w-0 flex-1"
                :style="{
                  font: '400 16px Inter, sans-serif',
                  color: task.completed ? 'var(--muted)' : 'var(--ink)',
                  textDecoration: task.completed ? 'line-through' : 'none',
                }">
                {{ task.label }}
              </span>

              <Link :href="task.contact.url.show" class="flex flex-none items-center gap-2">
                <CrmAvatar :data="task.contact.avatar" :size="20" />
                <span class="whitespace-nowrap" style="font: 400 12px var(--mono); color: var(--primary)">
                  {{ task.contact.name }}
                </span>
              </Link>

              <span
                v-if="task.due_at !== null"
                class="w-[110px] flex-none whitespace-nowrap text-right"
                :style="{
                  font: '400 12px var(--mono)',
                  color: task.due_at.is_late && !task.completed ? 'var(--pink)' : 'var(--muted)',
                }">
                {{ task.due_at.formatted }}
              </span>
              <span v-else class="w-[110px] flex-none" />
            </div>
          </div>

          <div v-else class="crm-card flex flex-col items-center gap-3 px-6 py-8">
            <span class="flex" style="color: var(--faint)"><CrmIcon name="task" :size="24" /></span>
            <span class="crm-meta-12">{{ $t('No tasks.') }}</span>
          </div>
        </div>
      </div>
    </div>
  </Layout>
</template>
