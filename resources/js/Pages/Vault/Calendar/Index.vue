<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Tooltip as ATooltip } from 'ant-design-vue';
import Layout from '@/Layouts/Layout.vue';
import ContactCard from '@/Shared/ContactCard.vue';
import CrmIcon from '@/Shared/Icons/CrmIcon.vue';
defineProps({
  layoutData: Object,
  data: Object,
});

const loadedDay = ref([]);
const dayDetailsLoaded = ref(false);

const get = (day) => {
  if (day.is_in_month === false) {
    return;
  }

  axios.get(day.url.show).then((response) => {
    dayDetailsLoaded.value = true;
    loadedDay.value = response.data.data;
  });
};
</script>

<template>
  <layout :title="$t('Calendar')" :inside-vault="true" :layout-data="layoutData">
    <div class="min-w-0 flex-1 p-6">
      <div class="special-grid grid grid-cols-1 gap-6">
        <!-- left -->
        <div>
          <!-- month browser -->
          <div class="mb-5 flex items-center justify-between">
            <span class="crm-section-title">{{ data.current_month }}</span>

            <div class="flex gap-3">
              <Link :href="data.url.previous" class="crm-nav-btn" :title="data.previous_month">
                <CrmIcon name="arrow-left" :size="18" />
              </Link>
              <Link :href="data.url.next" class="crm-nav-btn" :title="data.next_month">
                <CrmIcon name="arrow-right" :size="18" />
              </Link>
            </div>
          </div>

          <!-- days -->
          <div
            class="grid grid-cols-7"
            style="background: var(--bg); border: 1px solid var(--line); border-bottom: 1px solid var(--divider)">
            <span class="crm-weekday">{{ $t('Monday') }}</span>
            <span class="crm-weekday">{{ $t('Tuesday') }}</span>
            <span class="crm-weekday">{{ $t('Wednesday') }}</span>
            <span class="crm-weekday">{{ $t('Thursday') }}</span>
            <span class="crm-weekday">{{ $t('Friday') }}</span>
            <span class="crm-weekday">{{ $t('Saturday') }}</span>
            <span class="crm-weekday">{{ $t('Sunday') }}</span>
          </div>

          <!-- actual calendar -->
          <div
            v-for="week in data.weeks"
            :key="week.id"
            class="grid grid-cols-7"
            style="border-left: 1px solid var(--line); border-right: 1px solid var(--line)">
            <div
              v-for="day in week"
              :key="day.id"
              @click="get(day)"
              class="crm-day min-h-[104px] px-2.5 py-2"
              :class="day.is_in_month ? 'cursor-pointer' : ''"
              :style="{
                background: day.is_today ? 'var(--primary-soft)' : day.is_in_month ? 'var(--card)' : 'var(--bg)',
              }">
              <!-- date of the day -->
              <div class="flex items-center justify-between">
                <span
                  style="font: 500 12px var(--mono)"
                  :style="{ color: day.is_today ? 'var(--primary)' : day.is_in_month ? 'var(--ink2)' : 'var(--faint)' }"
                  >{{ day.date }}</span
                >

                <!-- mood for the day -->
                <div class="flex">
                  <div v-for="mood in day.mood_events" :key="mood.id">
                    <a-tooltip placement="topLeft" :title="mood.mood_tracking_parameter.label" arrow-point-at-center>
                      <div
                        class="me-2 inline-block h-4 w-4 rounded-full"
                        :class="mood.mood_tracking_parameter.hex_color" />
                    </a-tooltip>
                  </div>
                </div>
              </div>

              <!-- important dates -->
              <div v-if="day.important_dates?.length > 0" class="crm-event mt-1.5">
                {{ $t('Important dates') }}
              </div>
              <div v-if="day.important_dates?.length > 0" class="flex">
                <div v-for="date in day.important_dates" :key="date.id">
                  <contact-card
                    :contact="date.contact"
                    :avatar-classes="'h-5 w-5 rounded-full me-2'"
                    :display-name="false" />
                </div>
              </div>

              <!-- posts of journal -->
              <div v-if="day.posts?.length > 0" class="crm-event mt-1">
                {{ $tChoice(':count post|:count posts', day.posts.length, { count: day.posts.length }) }}
              </div>
            </div>
          </div>
        </div>

        <!-- right part: detail of a day -->
        <div v-if="dayDetailsLoaded" class="crm-card">
          <!-- day name -->
          <div class="border-b border-border p-2 text-center text-sm font-semibold dark:border-border">
            {{ loadedDay.day }}
          </div>

          <!-- mood -->
          <div v-if="loadedDay.mood_events.length > 0" class="border-b border-border dark:border-border">
            <h2
              class="border-b border-border bg-bg px-3 py-2 text-sm font-semibold text-text dark:border-border dark:bg-surface">
              {{ $t('Your mood that day') }}
            </h2>
            <ul class="p-3">
              <li v-for="mood in loadedDay.mood_events" :key="mood.id" class="mb-2">
                <!-- mood tracking parameter -->
                <div class="flex items-center">
                  <div class="me-2 inline-block h-4 w-4 rounded-full" :class="mood.mood_tracking_parameter.hex_color" />
                  <span>{{ mood.mood_tracking_parameter.label }}</span>
                </div>

                <!-- optional information -->
                <div
                  v-if="mood.number_of_hours_slept || mood.note"
                  class="rounded-lg border border-border p-3 dark:border-border">
                  <!-- number of hours slept -->
                  <div v-if="mood.number_of_hours_slept" class="mb-1 flex items-center text-sm text-text">
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke-width="1.5"
                      stroke="currentColor"
                      class="me-1 h-4 w-4 text-text-muted">
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                    </svg>

                    {{
                      $tChoice(':count hour slept|:count hours slept', mood.number_of_hours_slept, {
                        count: mood.number_of_hours_slept,
                      })
                    }}
                  </div>

                  <!-- note -->
                  <div v-if="mood.note" class="flex items-center text-sm text-text">
                    {{ mood.note }}
                  </div>
                </div>
              </li>
            </ul>
          </div>

          <!-- important dates -->
          <div v-if="loadedDay.important_dates.length > 0">
            <h2
              class="border-b border-border bg-bg px-3 py-2 text-sm font-semibold text-text dark:border-border dark:bg-surface">
              {{ $t('Important dates') }}
            </h2>
            <ul class="p-3">
              <li
                v-for="importantDate in loadedDay.important_dates"
                :key="importantDate.id"
                class="mb-1 flex justify-between">
                <span>{{ importantDate.label }}</span>
                <span
                  ><contact-card
                    :contact="importantDate.contact"
                    :avatar-classes="'h-5 w-5 rounded-full me-2'"
                    :display-name="false"
                /></span>
              </li>
            </ul>
          </div>

          <!-- journal entries -->
          <div v-if="loadedDay.posts.length > 0" class="border-b border-border dark:border-border">
            <h2
              class="border-b border-border bg-bg px-3 py-2 text-sm font-semibold text-text dark:border-border dark:bg-surface">
              {{ $t('Posts in your journals') }}
            </h2>
            <ul class="p-3">
              <li v-for="post in loadedDay.posts" :key="post.id" class="mb-2">
                <Link :href="post.url.show" class="text-sm text-accent hover:underline">{{ post.title }}</Link>
              </li>
            </ul>
          </div>

          <!-- case of no data in the day -->
          <div
            v-if="
              loadedDay.mood_events.length === 0 &&
              loadedDay.important_dates.length === 0 &&
              loadedDay.posts.length === 0
            "
            class="flex items-center justify-center">
            <p class="mt-4 px-5 pb-5 pt-2 text-center text-text">
              {{ $t('There are no events on that day, future or past.') }}
            </p>
          </div>
        </div>

        <!-- no day selected: blank state -->
        <div v-else class="crm-card flex flex-col items-center justify-center gap-3 px-6 py-12">
          <span class="flex" style="color: var(--faint)"><CrmIcon name="calendar" :size="28" /></span>
          <p class="crm-meta-12 text-center">{{ $t('Click on a day to see the details') }}</p>
        </div>
      </div>
    </div>
  </layout>
</template>

<style lang="scss" scoped>
.special-grid {
  grid-template-columns: 1fr 300px;
}

.crm-weekday {
  padding: 10px 12px;
  font: 500 11px var(--mono);
  color: var(--muted);
  letter-spacing: 0.12em;
  text-transform: uppercase;
}

.crm-day {
  border-right: 1px solid var(--divider);
  border-bottom: 1px solid var(--divider);
}

.crm-event {
  padding: 3px 7px;
  border-radius: 2px;
  background: var(--primary-soft);
  color: var(--primary);
  font: 500 10px/14px var(--mono);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.crm-nav-btn {
  width: 44px;
  height: 44px;
  border-radius: 2px;
  border: 1px solid var(--line);
  background: var(--card);
  color: var(--ink2);
  display: flex;
  align-items: center;
  justify-content: center;

  &:hover {
    color: var(--primary);
  }
}
</style>
