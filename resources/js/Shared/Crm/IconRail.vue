<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import CrmIcon from '@/Shared/Icons/CrmIcon.vue';

const props = defineProps({
  layoutData: { type: Object, required: true },
  dark: { type: Boolean, default: false },
});

const emit = defineEmits(['toggle-theme']);

const page = usePage();

// Nav order and glyphs are fixed by the design. Contacts (`people`) and Groups
// (`group`) deliberately do not share a glyph.
const nav = computed(() => {
  const vault = props.layoutData.vault;
  if (!vault) return [];

  const v = vault.visibility;
  const component = page.component;

  return [
    {
      id: 'dashboard',
      label: trans('Dashboard'),
      icon: 'grid',
      url: vault.url.dashboard,
      on: component === 'Vault/Dashboard/Index',
    },
    {
      id: 'contacts',
      label: trans('Contacts'),
      icon: 'people',
      url: vault.url.contacts,
      on: component.startsWith('Vault/Contact'),
    },
    {
      id: 'calendar',
      label: trans('Calendar'),
      icon: 'calendar',
      url: vault.url.calendar,
      on: component.startsWith('Vault/Calendar'),
      hidden: !v.show_calendar_tab,
    },
    {
      id: 'journals',
      label: trans('Journals'),
      icon: 'note',
      url: vault.url.journals,
      on: component.startsWith('Vault/Journal'),
      hidden: !v.show_journal_tab,
    },
    {
      id: 'groups',
      label: trans('Groups'),
      icon: 'group',
      url: vault.url.groups,
      on: component.startsWith('Vault/Group'),
      hidden: !v.show_group_tab,
    },
    {
      id: 'companies',
      label: trans('Companies'),
      icon: 'buildings',
      url: vault.url.companies,
      on: component.startsWith('Vault/Companies'),
      hidden: !v.show_companies_tab,
    },
    {
      id: 'tasks',
      label: trans('Tasks'),
      icon: 'task',
      url: vault.url.tasks,
      on: component.startsWith('Vault/Dashboard/Task'),
      hidden: !v.show_tasks_tab,
    },
    {
      id: 'reports',
      label: trans('Reports'),
      icon: 'chart',
      url: vault.url.reports,
      on: component.startsWith('Vault/Reports'),
      hidden: !v.show_reports_tab,
    },
    {
      id: 'files',
      label: trans('Files'),
      icon: 'folder',
      url: vault.url.files,
      on: component.startsWith('Vault/Files'),
      hidden: !v.show_files_tab,
    },
  ].filter((item) => !item.hidden);
});
</script>

<template>
  <aside
    class="crm-no-scrollbar sticky top-0 flex h-screen w-[90px] flex-none flex-col items-center overflow-y-auto overflow-x-hidden"
    style="background: var(--rail); border-right: 1px solid var(--line)">
    <Link
      :href="layoutData.url.vaults"
      :title="$t('All vaults')"
      class="flex h-[90px] w-[90px] flex-none items-center justify-center"
      style="border-bottom: 1px solid var(--line)">
      <span
        class="flex h-[46px] w-[46px] items-center justify-center rounded-[2px]"
        style="background: #fff; border: 1px solid var(--line)">
        <img src="/img/favicon.svg" alt="Monica" class="block h-[26px] w-[26px]" />
      </span>
    </Link>

    <nav class="flex min-h-0 flex-none flex-col gap-[10px] py-4">
      <Link
        v-for="item in nav"
        :key="item.id"
        :href="item.url"
        :title="item.label"
        class="crm-rail-btn flex h-[50px] w-[50px] items-center justify-center rounded-[2px] border"
        :style="{
          background: item.on ? 'var(--card)' : 'transparent',
          color: item.on ? 'var(--ink)' : 'var(--muted)',
          borderColor: item.on ? 'var(--line)' : 'transparent',
        }">
        <CrmIcon :name="item.icon" :size="20" />
      </Link>
    </nav>

    <div class="mt-auto flex flex-none flex-col items-center gap-[10px] pb-4 pt-3">
      <button type="button" class="crm-icon-btn crm-rail-btn" :title="$t('Toggle theme')" @click="emit('toggle-theme')">
        <CrmIcon :name="dark ? 'sun' : 'moon'" :size="20" />
      </button>

      <Link :href="layoutData.url.settings" class="crm-icon-btn crm-rail-btn" :title="$t('Settings')">
        <CrmIcon name="setting" :size="20" />
      </Link>

      <Link
        method="post"
        as="button"
        :href="route('logout')"
        class="crm-icon-btn crm-rail-logout"
        :title="$t('Logout')">
        <CrmIcon name="door" :size="20" />
      </Link>
    </div>
  </aside>
</template>

<style scoped>
.crm-rail-btn {
  transition: all 0.18s ease;
}
.crm-rail-btn:hover {
  color: var(--ink);
}
.crm-rail-logout:hover {
  color: var(--pink);
}
</style>
