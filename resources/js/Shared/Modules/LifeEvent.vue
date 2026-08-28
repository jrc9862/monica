<script setup>
import { onMounted, ref } from 'vue';
import { trans } from 'laravel-vue-i18n';
import Loading from '@/Shared/Loading.vue';
import PrettyButton from '@/Shared/Form/PrettyButton.vue';
import ContactCard from '@/Shared/ContactCard.vue';
import HoverMenu from '@/Shared/HoverMenu.vue';
import CreateLifeEvent from '@/Shared/Modules/CreateLifeEvent.vue';
import ChevronIcon from '@/Shared/Icons/ChevronIcon.vue';
import ClockIcon from '@/Shared/Icons/ClockIcon.vue';
import TwoPinMapIcon from '@/Shared/Icons/TwoPinMapIcon.vue';
import { Flame } from 'lucide-vue-next';

const props = defineProps({
  layoutData: Object,
  data: Object,
});

const createLifeEventModalShown = ref(false);
const loadingData = ref(true);
const paginator = ref([]);
const localTimelines = ref([]);
const showAddLifeEventModalForTimelineEventId = ref(0);

onMounted(() => {
  axios
    .get(props.data.url.load)
    .then((response) => {
      loadingData.value = false;
      response.data.data.timeline_events.forEach((entry) => {
        localTimelines.value.push(entry);
      });
      paginator.value = response.data.paginator;
    })
    .catch(() => {});
});

const loadMore = () => {
  axios
    .get(paginator.value.nextPageUrl)
    .then((response) => {
      loadingData.value = false;
      response.data.data.timeline_events.forEach((entry) => {
        localTimelines.value.push(entry);
      });
      paginator.value = response.data.paginator;
    })
    .catch(() => {});
};

const destroy = (timelineEvent) => {
  if (confirm(trans('Are you sure? This action cannot be undone.'))) {
    axios
      .delete(timelineEvent.url.destroy)
      .then(() => {
        var id = localTimelines.value.findIndex((x) => x.id === timelineEvent.id);
        localTimelines.value.splice(id, 1);
      })
      .catch(() => {});
  }
};

const destroyLifeEvent = (timelineEvent, lifeEvent) => {
  if (confirm(trans('Are you sure? This action cannot be undone.'))) {
    axios
      .delete(lifeEvent.url.destroy)
      .then(() => {
        var id = localTimelines.value.findIndex((x) => x.id === timelineEvent.id);
        var lifeEventId = localTimelines.value[id].life_events.findIndex((x) => x.id === lifeEvent.id);
        localTimelines.value[id].life_events.splice(lifeEventId, 1);
      })
      .catch(() => {});
  }
};

const refreshLifeEvent = (timelineEvent, lifeEvent) => {
  let id = localTimelines.value.findIndex((x) => x.id === timelineEvent.id);
  let lifeEventId = localTimelines.value[id].life_events.findIndex((x) => x.id === lifeEvent.id);
  localTimelines.value[id].life_events[lifeEventId] = lifeEvent;
};

const refreshTimelineEvents = (timelineEvent) => {
  localTimelines.value.unshift(timelineEvent);
};

const refreshLifeEvents = (lifeEvent) => {
  var id = localTimelines.value.findIndex((x) => x.id === lifeEvent.timeline_event.id);
  localTimelines.value[id].life_events.unshift(lifeEvent);
};

const showCreateLifeEventModal = () => {
  createLifeEventModalShown.value = true;
};

const toggleTimelineEventVisibility = (timelineEvent) => {
  timelineEvent.collapsed = !timelineEvent.collapsed;

  axios.post(timelineEvent.url.toggle);
};

const toggleLifeEventVisibility = (lifeEvent) => {
  lifeEvent.collapsed = !lifeEvent.collapsed;

  axios.post(lifeEvent.url.toggle);
};
</script>

<template>
  <div class="mb-10">
    <!-- title + cta -->
    <div class="crm-panel-header justify-between">
      <div class="mb-2 sm:mb-0 flex items-center gap-2">
        <Flame class="h-4 w-4" />

        <span class="crm-title"> {{ $t('Life events') }} </span>
      </div>
      <pretty-button
        :text="$t('Add a life event')"
        :icon="'plus'"
        :class="'w-full sm:w-fit'"
        @click="showCreateLifeEventModal" />
    </div>

    <div>
      <!-- add a timeline event -->
      <create-life-event
        :data="data"
        :layout-data="layoutData"
        :open-modal="createLifeEventModalShown"
        :create-timeline-event="true"
        @close-modal="createLifeEventModalShown = false"
        @timeline-event-created="refreshTimelineEvents" />

      <!-- list of timeline events -->
      <div v-if="localTimelines">
        <div v-for="timelineEvent in localTimelines" :key="timelineEvent.id" class="mb-4">
          <!-- timeline event name -->
          <div
            class="mb-2 flex cursor-pointer items-center justify-between rounded-lg border border-border px-3 py-2 hover:bg-hover dark:border-border dark:bg-surface dark:hover:bg-hover"
            @click="toggleTimelineEventVisibility(timelineEvent)">
            <!-- timeline date / label / number of events -->
            <div>
              <span class="me-2 text-text-muted">{{ timelineEvent.happened_at }}</span>

              <span class="ms-3 whitespace-nowrap rounded-lg bg-bg px-2 py-0.5 text-sm text-text-muted dark:bg-surface">
                {{ timelineEvent.life_events.length }}
              </span>
            </div>

            <!-- chevrons and menu -->
            <div class="flex">
              <!-- chevrons -->
              <ChevronIcon v-if="timelineEvent.collapsed" :type="'down'" />

              <ChevronIcon v-if="!timelineEvent.collapsed" :type="'up'" />

              <!-- menu -->
              <hover-menu :show-edit="false" :show-delete="true" @delete="destroy(timelineEvent)" />
            </div>
          </div>

          <!-- life events -->
          <div v-if="!timelineEvent.collapsed">
            <div
              v-for="lifeEvent in timelineEvent.life_events"
              :key="lifeEvent.id"
              :class="!lifeEvent.collapsed ? 'border' : ''"
              class="mb-2 ms-6 rounded-lg border-border dark:border-border dark:bg-surface">
              <template v-if="lifeEvent.edit">
                <create-life-event
                  :data="data"
                  :layout-data="layoutData"
                  :open-modal="lifeEvent.edit"
                  :life-event="lifeEvent"
                  @close-modal="lifeEvent.edit = false"
                  @life-event-created="(event) => refreshLifeEvent(timelineEvent, event)" />
              </template>
              <template v-else>
                <!-- name of life event -->
                <div
                  :class="lifeEvent.collapsed ? 'rounded-lg border' : ''"
                  class="flex cursor-pointer items-center justify-between rounded-t-lg border-b border-border px-3 py-2 hover:bg-hover dark:border-border dark:hover:bg-hover">
                  <!-- title -->
                  <div @click="toggleLifeEventVisibility(lifeEvent)" class="flex items-center">
                    <p v-if="lifeEvent.summary" class="me-4 text-sm font-bold">{{ lifeEvent.summary }}</p>
                    <div>
                      <span
                        class="rounded-xs border bg-surface px-2 py-1 font-mono text-sm dark:border-border dark:bg-surface">
                        {{ lifeEvent.life_event_type.category.label }}
                      </span>
                      >
                      <span
                        class="rounded-xs border bg-surface px-2 py-1 font-mono text-sm dark:border-border dark:bg-surface">
                        {{ lifeEvent.life_event_type.label }}
                      </span>
                    </div>
                  </div>

                  <!-- chevrons and menu -->
                  <div class="flex">
                    <!-- chevrons -->
                    <ChevronIcon
                      v-if="timelineEvent.collapsed"
                      :type="'down'"
                      @click="toggleLifeEventVisibility(lifeEvent)" />

                    <ChevronIcon
                      v-if="!timelineEvent.collapsed"
                      :type="'up'"
                      @click="toggleLifeEventVisibility(lifeEvent)" />

                    <!-- menu -->
                    <hover-menu
                      :show-edit="true"
                      :show-delete="true"
                      @edit="lifeEvent.edit = true"
                      @delete="destroyLifeEvent(timelineEvent, lifeEvent)" />
                  </div>
                </div>

                <!-- description -->
                <div
                  v-if="!lifeEvent.collapsed && lifeEvent.description"
                  class="flex items-center border-b border-border px-3 py-2 dark:border-border">
                  {{ lifeEvent.description }}
                </div>

                <!-- date of life event | distance -->
                <div
                  v-if="!lifeEvent.collapsed"
                  class="flex items-center border-b border-border px-3 py-2 text-sm dark:border-border">
                  <!-- date -->
                  <div class="me-4 flex items-center">
                    <ClockIcon />

                    {{ lifeEvent.happened_at }}
                  </div>

                  <!-- distance -->
                  <div v-if="lifeEvent.distance" class="flex items-center">
                    <TwoPinMapIcon />

                    <span>{{ lifeEvent.distance }}</span>
                  </div>
                </div>

                <!-- participants -->
                <div v-if="!lifeEvent.collapsed" class="flex flex-wrap p-3 pb-1">
                  <div v-for="contact in lifeEvent.participants" :key="contact.id" class="me-4">
                    <contact-card
                      :contact="contact"
                      :avatar-classes="'h-5 w-5 rounded-full me-2'"
                      :display-name="true" />
                  </div>
                </div>
              </template>
            </div>

            <!-- add a new life event to the timeline -->
            <div class="mb-2 ms-6">
              <span
                @click="showAddLifeEventModalForTimelineEventId = timelineEvent.id"
                v-if="showAddLifeEventModalForTimelineEventId !== timelineEvent.id"
                class="cursor-pointer text-sm text-accent hover:underline">
                {{ $t('Add another life event') }}
              </span>

              <create-life-event
                :data="data"
                :layout-data="layoutData"
                :open-modal="showAddLifeEventModalForTimelineEventId === timelineEvent.id"
                :create-timeline-event="false"
                :timeline-event="timelineEvent"
                @close-modal="showAddLifeEventModalForTimelineEventId = 0"
                @life-event-created="refreshLifeEvents" />
            </div>
          </div>
        </div>

        <!-- pagination -->
        <div class="text-center" v-if="paginator.hasMorePages">
          <span
            @click="loadMore()"
            class="cursor-pointer rounded-xs border border-border px-3 py-1 text-sm text-accent hover:border-accent dark:border-border">
            {{ $t('Load previous entries') }}
          </span>
        </div>
      </div>

      <!-- loading mode -->
      <div v-if="loadingData" class="mb-5 rounded-lg border border-border p-20 text-center dark:border-border">
        <loading />
      </div>

      <!-- blank state -->
      <div
        v-if="localTimelines.length === 0"
        class="mb-6 rounded-lg border border-border bg-surface dark:border-border dark:bg-surface">
        <img src="/img/contact_blank_life_event.svg" :alt="$t('Life events')" class="mx-auto mt-4 h-20 w-20" />
        <p class="px-5 pb-5 pt-2 text-center">{{ $t('Life events let you document what happened in your life.') }}</p>
      </div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.icon-sidebar {
  top: -2px;
}
</style>
