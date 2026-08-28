<script setup>
import { ref, onMounted, reactive } from 'vue';
import { debounce } from 'lodash';
import Layout from '@/Layouts/Layout.vue';
import TextInput from '@/Shared/Form/TextInput.vue';
import Contact from '@/Pages/Vault/Search/Partials/Contact.vue';
import Note from '@/Pages/Vault/Search/Partials/Note.vue';
import Group from '@/Pages/Vault/Search/Partials/Group.vue';
import Loading from '@/Shared/Loading.vue';

const props = defineProps({
  layoutData: Object,
  data: Object,
});

const processingSearch = ref(false);
const form = reactive({
  searchTerm: props.data.query || '',
  errors: [],
});
const results = ref([]);

onMounted(() => {
  search();
});

const search = debounce(() => {
  if (form.searchTerm !== undefined && form.searchTerm.length >= 3) {
    processingSearch.value = true;

    axios
      .post(props.data.url.search, form)
      .then((response) => {
        results.value = response.data.data;
        processingSearch.value = false;
      })
      .catch((error) => {
        form.errors = error.response.data;
        processingSearch.value = false;
      });
  } else {
    results.value = [];
  }
}, 300);
</script>

<template>
  <Layout :layout-data="layoutData" :inside-vault="true">
    <main class="relative">
      <div class="mx-auto max-w-4xl px-2 py-2 sm:px-6 sm:py-6 lg:px-8">
        <form
          class="mb-8 rounded-lg border border-border bg-surface dark:border-border dark:bg-surface"
          @submit.prevent="search">
          <div
            class="section-head border-b border-border bg-surface-raised p-5 dark:border-border dark:bg-surface-raised">
            <h1 class="text-center text-2xl font-medium">{{ $t('Search something in the vault') }}</h1>
          </div>
          <div class="p-5">
            <TextInput
              ref="searchField"
              v-model="form.searchTerm"
              :type="'text'"
              :autofocus="true"
              :input-class="'block w-full'"
              :placeholder="$t('Type something')"
              :required="true"
              :autocomplete="false"
              :maxlength="255"
              @keyup="search" />
          </div>
        </form>

        <!-- search results -->
        <div v-if="!processingSearch && Object.keys(results).length !== 0">
          <Contact :data="results.contacts" />

          <Group :data="results.groups" />

          <Note :data="results.notes" />
        </div>

        <!-- searching results -->
        <div
          v-if="processingSearch"
          class="mb-6 rounded-lg border border-border bg-surface p-6 text-center text-text-muted dark:border-border dark:bg-surface">
          <Loading />
        </div>

        <!-- not enough characters -->
        <div
          v-if="form.searchTerm.length < 3"
          class="mb-6 rounded-lg border border-border bg-surface p-6 text-center text-text-muted dark:border-border dark:bg-surface">
          <p>{{ $t('Please enter at least 3 characters to initiate a search.') }}</p>
        </div>
      </div>
    </main>
  </Layout>
</template>
