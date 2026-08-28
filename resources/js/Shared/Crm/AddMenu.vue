<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import CrmIcon from '@/Shared/Icons/CrmIcon.vue';

const props = defineProps({
  layoutData: { type: Object, required: true },
});

const emit = defineEmits(['close']);

// Only destinations that exist without a contact in scope. Notes, calls,
// reminders and tasks all hang off a contact in Monica, so they are reached
// through the contacts list rather than a vault-level form.
const items = computed(() => {
  const vault = props.layoutData.vault;
  if (!vault) return [];

  return [
    { label: trans('Add a contact'), icon: 'people', url: route('contact.create', { vault: vault.id }) },
    {
      label: trans('Start a journal entry'),
      icon: 'note',
      url: vault.url.journals,
      hidden: !vault.visibility.show_journal_tab,
    },
    { label: trans('Add a group'), icon: 'group', url: vault.url.groups, hidden: !vault.visibility.show_group_tab },
    { label: trans('Add a task'), icon: 'task', url: vault.url.tasks, hidden: !vault.visibility.show_tasks_tab },
    { label: trans('Log a call or meeting'), icon: 'call', url: vault.url.contacts },
    { label: trans('Add a reminder'), icon: 'bell', url: vault.url.contacts },
  ].filter((item) => !item.hidden);
});
</script>

<template>
  <div class="fixed inset-0 z-60 crm-fade" style="background: var(--scrim)" @click="emit('close')" />
  <div
    class="crm-rise fixed right-6 top-[100px] z-61 w-[320px] rounded-[2px] p-[10px]"
    style="background: var(--card); border: 1px solid var(--line); box-shadow: var(--pop)">
    <Link
      v-for="item in items"
      :key="item.label"
      :href="item.url"
      class="crm-add-item flex w-full items-center gap-[14px] rounded-[2px] px-[14px] py-[11px] text-left"
      style="
        color: var(--ink);
        font:
          500 14px Inter,
          sans-serif;
      "
      @click="emit('close')">
      <span class="crm-tint h-9 w-9" style="background: var(--primary-soft); color: var(--primary)">
        <CrmIcon :name="item.icon" :size="18" />
      </span>
      <span class="flex-1">{{ item.label }}</span>
    </Link>
  </div>
</template>

<style scoped>
.crm-add-item:hover {
  background: var(--hover);
  text-decoration: none;
}
</style>
