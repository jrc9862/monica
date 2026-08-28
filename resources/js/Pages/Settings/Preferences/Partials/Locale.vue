<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { loadLanguageAsync, getActiveLanguage, trans } from 'laravel-vue-i18n';
import { flash } from '@/methods.js';
import PrettyButton from '@/Shared/Form/PrettyButton.vue';
import PrettyLink from '@/Shared/Form/PrettyLink.vue';
import Errors from '@/Shared/Form/Errors.vue';
import Help from '@/Shared/Help.vue';
import Dropdown from '@/Shared/Form/Dropdown.vue';

const props = defineProps({
  data: Object,
});

const loadingState = ref('');
const editMode = ref(false);
const localLocale = ref(props.data.name);
const form = useForm({
  locale: props.data.id,
  errors: [],
});

const submit = () => {
  loadingState.value = 'loading';

  axios
    .post(props.data.url.store, form.data())
    .then((response) => {
      flash(trans('Changes saved'), 'success');
      localLocale.value = response.data.data.name;
      editMode.value = false;
      loadingState.value = null;

      if (getActiveLanguage() !== form.locale) {
        loadLanguageAsync(response.data.data.id);
        document.getRootNode().querySelector('html').setAttribute('dir', response.data.data.dir);
      }
    })
    .catch((error) => {
      loadingState.value = null;
      form.errors = error.response.data;
    });
};
</script>

<template>
  <div class="mb-16">
    <!-- title + cta -->
    <div class="mb-3 mt-8 items-center justify-between sm:mt-0 sm:flex">
      <h3 class="mb-4 flex font-semibold sm:mb-0">
        <span class="me-1"> 🗓 </span>
        <span class="me-2">
          {{ $t('Language of the application') }}
        </span>

        <Help :url="$page.props.help_links.settings_preferences_language" :top="'5px'" />
      </h3>
      <PrettyButton v-if="!editMode" :text="$t('Edit')" @click="editMode = true" />
    </div>

    <!-- normal mode -->
    <div v-if="!editMode" class="mb-6 rounded-lg border border-border bg-surface dark:border-border dark:bg-surface">
      <p class="px-5 py-2">
        <span class="mb-2 block">{{ $t('Current language:') }}</span>
        <span class="mb-2 block rounded-xs bg-bg px-5 py-2 text-sm dark:bg-surface">{{ localLocale }}</span>
      </p>
    </div>

    <!-- edit mode -->
    <form
      v-else
      class="mb-6 rounded-lg border border-border bg-surface dark:border-border dark:bg-surface"
      @submit.prevent="submit()">
      <div class="border-b border-border px-5 py-2 dark:border-border">
        <Errors :errors="form.errors" />

        <Dropdown v-model="form.locale" name="locale" :data="data.locales" />
      </div>

      <!-- actions -->
      <div class="flex justify-between p-5">
        <PrettyLink :text="$t('Cancel')" :class="'me-3'" @click="editMode = false" />
        <PrettyButton :text="$t('Save')" :state="loadingState" :icon="'check'" :class="'save'" />
      </div>
    </form>
  </div>
</template>
