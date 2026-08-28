<script setup>
import { ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import { DatePicker } from 'v-calendar';
import 'v-calendar/style.css';
import PrettySpan from '@/Shared/Form/PrettySpan.vue';
import TextArea from '@/Shared/Form/TextArea.vue';
import PrettyButton from '@/Shared/Form/PrettyButton.vue';
import TextInput from '@/Shared/Form/TextInput.vue';
import Errors from '@/Shared/Form/Errors.vue';
import { CloudSun } from 'lucide-vue-next';

const props = defineProps({
  data: Object,
});

const loadingState = ref('');
const createMoodEventModalShown = ref(false);
const datePickerFieldShown = ref(false);
const noteFieldShown = ref(false);
const hoursSleptFieldShown = ref(false);
const successShown = ref(false);

const masks = ref({
  modelValue: 'YYYY-MM-DD',
});

const form = useForm({
  parameter_id: 0,
  date: props.data.current_date,
  hours: null,
  note: null,
});

const showMoodEventModal = () => {
  datePickerFieldShown.value = false;
  noteFieldShown.value = false;
  hoursSleptFieldShown.value = false;
  createMoodEventModalShown.value = true;
  form.note = '';
  form.hours = null;
};

const showDatePickerField = () => {
  datePickerFieldShown.value = true;
};

const showNoteField = () => {
  noteFieldShown.value = true;
};

const showHoursSleptField = () => {
  hoursSleptFieldShown.value = true;
};

const submit = () => {
  loadingState.value = 'loading';

  axios
    .post(props.data.url.store, form)
    .then(() => {
      createMoodEventModalShown.value = false;
      successShown.value = true;
      loadingState.value = null;
    })
    .catch((error) => {
      loadingState.value = null;
      form.errors = error.response.data;
    });
};
</script>

<template>
  <div>
    <!-- cta -->
    <div v-if="!createMoodEventModalShown && !successShown" class="crm-card flex items-center gap-3 p-[18px]">
      <span class="crm-tint h-[38px] w-[38px]" style="background: var(--sun); color: var(--yellow)">
        <CloudSun class="h-[18px] w-[18px]" />
      </span>
      <p
        class="flex-1"
        style="
          font:
            400 15px Inter,
            sans-serif;
          color: var(--ink);
        ">
        {{ $t('How are you?') }}
      </p>
      <pretty-button :text="$t('Record your mood')" @click="showMoodEventModal" />
    </div>

    <!-- add an event modal -->
    <form v-if="createMoodEventModalShown" class="crm-card mb-6" @submit.prevent="submit()">
      <div class="border-b border-border p-5 dark:border-border">
        <div v-if="form.errors.length > 0" class="p-5">
          <Errors :errors="form.errors" />
        </div>

        <!-- mood tracking parameters -->
        <p class="mb-2 block text-sm dark:text-text">{{ $t('How do you feel right now?') }}</p>
        <ul class="mb-4">
          <li v-for="parameter in props.data.mood_tracking_parameters" :key="parameter.id" class="flex">
            <input
              :id="'input' + parameter.id"
              v-model="form.parameter_id"
              :value="parameter.id"
              name="date-format"
              type="radio"
              class="relative me-3 h-4 w-4 border-border text-sky-500 dark:border-border" />

            <label :for="'input' + parameter.id" class="block cursor-pointer font-medium text-text dark:text-text">
              <div class="me-2 inline-block h-4 w-4 rounded-full" :class="parameter.hex_color" />
              {{ parameter.label }}
            </label>
          </li>
        </ul>

        <div class="flex">
          <span
            v-if="!datePickerFieldShown"
            class="me-2 flex cursor-pointer flex-wrap rounded-lg border bg-bg px-1 py-1 text-sm hover:bg-hover dark:bg-surface dark:text-white"
            @click="showDatePickerField">
            {{ $t('+ change date') }}
          </span>

          <span
            v-if="!noteFieldShown"
            class="me-2 flex cursor-pointer flex-wrap rounded-lg border bg-bg px-1 py-1 text-sm hover:bg-hover dark:bg-surface dark:text-white"
            @click="showNoteField">
            {{ $t('+ note') }}
          </span>

          <span
            v-if="!hoursSleptFieldShown"
            class="me-2 flex cursor-pointer flex-wrap rounded-lg border bg-bg px-1 py-1 text-sm hover:bg-hover dark:bg-surface dark:text-white"
            @click="showHoursSleptField">
            {{ $t('+ number of hours slept') }}
          </span>
        </div>

        <!-- date picker -->
        <div v-if="datePickerFieldShown">
          <p class="mb-2 mt-2 block text-sm dark:text-text">{{ $t('Change date') }}</p>
          <DatePicker
            v-model.string="form.date"
            :timezone="'UTC'"
            class="inline-block h-full"
            :masks="masks"
            :locale="$page.props.auth.user?.locale_ietf"
            :is-dark="isDark()">
            <template #default="{ inputValue, inputEvents }">
              <input
                class="rounded-xs border bg-surface px-2 py-1 dark:bg-surface"
                :value="inputValue"
                v-on="inputEvents" />
            </template>
          </DatePicker>
        </div>

        <!-- note -->
        <div v-if="noteFieldShown" class="mt-4">
          <text-area
            v-model="form.note"
            :label="$t('Add a note')"
            :maxlength="65535"
            :textarea-class="'block w-full'" />
        </div>

        <!-- hours slept -->
        <div v-if="hoursSleptFieldShown" class="mt-4">
          <text-input
            v-model="form.hours"
            :label="$t('Number of hours slept')"
            :autofocus="true"
            :input-class="'block w-full'"
            :type="'number'"
            :min="0"
            :max="24"
            :required="false"
            :autocomplete="false" />
        </div>
      </div>

      <div class="flex justify-between p-5">
        <pretty-span :text="$t('Cancel')" :class="'me-3'" @click="createMoodEventModalShown = false" />
        <pretty-button :text="$t('Save')" :state="loadingState" :icon="'plus'" :class="'save'" />
      </div>
    </form>

    <!-- successShown -->
    <div v-if="successShown" class="crm-card flex items-center gap-3 p-[18px]">
      <span class="crm-tint h-[38px] w-[38px]" style="background: var(--mint); color: var(--green)">
        <CloudSun class="h-[18px] w-[18px]" />
      </span>
      <div class="flex flex-col">
        <p class="mb-2"><span class="me-1">🎉</span> {{ $t('Your mood has been recorded!') }}</p>
        <Link :href="data.url.history" class="text-center text-accent hover:underline">{{ $t('View history') }}</Link>
      </div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.icon-sidebar {
  color: #737e8d;
  top: -2px;
}
</style>
