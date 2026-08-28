<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import Layout from '@/Layouts/Layout.vue';
import LastUpdated from '@/Pages/Vault/Dashboard/Partials/LastUpdated.vue';
import UpcomingReminders from '@/Pages/Vault/Dashboard/Partials/UpcomingReminders.vue';
import Favorites from '@/Pages/Vault/Dashboard/Partials/Favorites.vue';
import DueTasks from '@/Pages/Vault/Dashboard/Partials/DueTasks.vue';
import LifeMetrics from '@/Pages/Vault/Dashboard/Partials/LifeMetrics.vue';
import MoodTrackingEvents from '@/Pages/Vault/Dashboard/Partials/MoodTrackingEvents.vue';
import NextReminder from '@/Pages/Vault/Dashboard/Partials/NextReminder.vue';
import StatTiles from '@/Pages/Vault/Dashboard/Partials/StatTiles.vue';
import PanelHeader from '@/Shared/Crm/PanelHeader.vue';
import CrmIcon from '@/Shared/Icons/CrmIcon.vue';
import Feed from '@/Shared/Modules/Feed.vue';
import LifeEvent from '@/Shared/Modules/LifeEvent.vue';

const props = defineProps({
  layoutData: Object,
  data: Object,
  statistics: Object,
  lastUpdatedContacts: Object,
  upcomingReminders: Object,
  favorites: Object,
  url: Array,
  dueTasks: Object,
  moodTrackingEvents: Object,
  lifeEvents: Object,
  lifeMetrics: Object,
  defaultTab: String,
});

const currentTab = ref(props.defaultTab);

const tabs = [
  { id: 'activity', label: trans('Activity in this vault'), icon: 'clock' },
  { id: 'life_events', label: trans('Your life events'), icon: 'flag' },
  { id: 'life_metrics', label: trans('Life metrics'), icon: 'chart' },
];

const form = useForm({
  default_activity_tab: null,
});

const changeTab = (tab) => {
  currentTab.value = tab;
  form.default_activity_tab = tab;

  axios.put(props.url.default_tab, form);
};
</script>

<template>
  <Layout :title="$t('Dashboard')" :inside-vault="true" :layout-data="layoutData">
    <div class="flex min-w-0 flex-1">
      <!-- left column -->
      <div class="flex w-[230px] flex-none flex-col gap-7 py-6 ps-6">
        <Favorites v-if="favorites.length > 0" :data="favorites" />
        <LastUpdated :data="lastUpdatedContacts" />
      </div>

      <!-- centre -->
      <div class="min-w-0 flex-1 p-6">
        <!-- the block row -->
        <div class="mb-6 flex items-stretch gap-6">
          <NextReminder :reminders="upcomingReminders.reminders" />
          <StatTiles :statistics="statistics" />
        </div>

        <!-- segmented control -->
        <div class="mb-6 flex justify-center">
          <div class="flex gap-1 rounded-[2px] p-1" style="border: 1px solid var(--line); background: var(--card)">
            <button
              v-for="tab in tabs"
              :key="tab.id"
              type="button"
              class="flex h-12 cursor-pointer items-center gap-2 whitespace-nowrap rounded-[2px] border-none px-[22px]"
              style="
                font:
                  600 15px Inter,
                  sans-serif;
                transition: all 0.18s ease;
              "
              :style="{
                background: currentTab === tab.id ? 'var(--primary-soft)' : 'transparent',
                color: currentTab === tab.id ? 'var(--primary)' : 'var(--muted)',
              }"
              @click="changeTab(tab.id)">
              <CrmIcon :name="tab.icon" :size="16" />
              {{ tab.label }}
            </button>
          </div>
        </div>

        <Feed v-if="currentTab === 'activity'" :url="url.feed" :contact-view-mode="false" />
        <LifeEvent v-else-if="currentTab === 'life_events'" :data="lifeEvents" :layout-data="layoutData" />
        <LifeMetrics v-else-if="currentTab === 'life_metrics'" :data="lifeMetrics" />
      </div>

      <!-- right rail -->
      <div
        class="flex w-[417px] flex-none flex-col gap-7 p-6"
        style="background: var(--rail); border-left: 1px solid var(--line)">
        <div>
          <PanelHeader icon="face" :title="$t('How am I doing?')" :size="16" />
          <MoodTrackingEvents :data="moodTrackingEvents" />
        </div>

        <UpcomingReminders :data="upcomingReminders" />
        <DueTasks :data="dueTasks" />
      </div>
    </div>
  </Layout>
</template>
