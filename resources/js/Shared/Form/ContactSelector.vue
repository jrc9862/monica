<script setup>
import { computed, nextTick, onMounted, ref, watch, useTemplateRef } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import Errors from '@/Shared/Form/Errors.vue';
import { ScanSearch } from 'lucide-vue-next';

const props = defineProps({
  modelValue: {
    type: Array,
    default: () => [],
  },
  inputClass: String,
  placeholder: {
    type: String,
    default: () => trans('Find a contact in this vault'),
  },
  label: String,
  labelCta: {
    type: String,
    default: () => trans('+ add a contact'),
  },
  type: {
    type: String,
    default: 'text',
  },
  required: Boolean,
  displayMostConsultedContacts: Boolean,
  addMultipleContacts: Boolean,
  searchUrl: String,
  mostConsultedContactsUrl: String,
});

const emit = defineEmits(['update:modelValue']);

const searchInput = useTemplateRef('searchInput');
const addContactMode = ref(false);
const processingSearch = ref(false);
const localContacts = ref(props.modelValue);
const mostConsultedContacts = ref([]);
const searchResults = ref([]);
const form = useForm({
  searchTerm: '',
  errors: [],
});

const displayAddContactButton = computed(() => {
  if (!props.addMultipleContacts && localContacts.value.length >= 1) {
    return false;
  }

  return !addContactMode.value;
});

const localInputClasses = computed(() => {
  return [
    'ps-8 w-full',
    'bg-surface border-border text-text',
    'focus:border-accent focus:ring-2 focus:ring-accent/30 focus:outline-hidden',
    'disabled:opacity-50',
    props.inputClass,
  ];
});

onMounted(() => {
  if (props.displayMostConsultedContacts) {
    lookupMostConsultedContacts();
  }
});

watch(
  () => props.modelValue,
  (value) => {
    localContacts.value = value;
  },
);

const showAddContactMode = () => {
  addContactMode.value = true;
  form.searchTerm = '';
  nextTick().then(() => searchInput.value.focus());
};

const sendEscKey = () => {
  search.cancel();
  addContactMode.value = false;
};

const lookupMostConsultedContacts = () => {
  axios.get(props.mostConsultedContactsUrl).then((response) => {
    mostConsultedContacts.value = response.data.data;
  });
};

const add = (contact) => {
  let id = localContacts.value.findIndex((x) => x.id === contact.id);

  if (id === -1) {
    localContacts.value.push(contact);
    form.searchTerm = '';
    addContactMode.value = false;
    nextTick().then(() => emit('update:modelValue', localContacts.value));
  }
};

const remove = (contact) => {
  let id = localContacts.value.findIndex((x) => x.id === contact.id);
  localContacts.value.splice(id, 1);

  nextTick().then(() => emit('update:modelValue', localContacts.value));
};

const search = _.debounce(() => {
  if (form.searchTerm !== '' && form.searchTerm.length >= 3) {
    processingSearch.value = true;

    axios
      .post(props.searchUrl, form)
      .then((response) => {
        searchResults.value = _.filter(response.data.data, (contact) =>
          _.every(localContacts.value, (e) => contact.id !== e.id),
        );
        processingSearch.value = false;
      })
      .catch((error) => {
        form.errors = error.response.data;
        processingSearch.value = false;
      });
  } else {
    searchResults.value = [];
  }
}, 300);
</script>

<template>
  <div>
    <!-- input -->
    <div>
      <label v-if="label" class="mb-2 block text-sm">
        {{ label }}
        <span v-if="!required" class="optional-badge rounded-xs px-[3px] py-px text-xs">
          {{ $t('optional') }}
        </span>
      </label>

      <!-- list of selected contacts -->
      <ul v-if="localContacts.length > 0" class="mb-4 border border-border bg-surface">
        <li
          v-for="contact in localContacts"
          :key="contact.id"
          class="item-list flex items-center justify-between border-b border-border px-3 py-2 hover:bg-surface-raised">
          <Link :href="contact.url">
            {{ contact.name }}
          </Link>

          <!-- actions -->
          <ul class="text-sm">
            <li class="inline cursor-pointer text-accent hover:underline" @click="remove(contact)">
              {{ $t('Remove') }}
            </li>
          </ul>
        </li>
      </ul>

      <p
        v-if="displayAddContactButton"
        class="inline-block cursor-pointer border border-border bg-surface-raised px-1 py-1 text-xs text-text hover:border-accent"
        @click="showAddContactMode">
        {{ labelCta }}
      </p>
    </div>

    <!-- mode to add a contact -->
    <div v-if="addContactMode">
      <div class="relative mb-3">
        <ScanSearch class="absolute start-2 top-2 h-4 w-4 text-gray-400" />

        <input
          ref="searchInput"
          v-model="form.searchTerm"
          :class="localInputClasses"
          :type="type"
          :name="name"
          :required="required"
          :placeholder="placeholder"
          @keyup="search"
          @keydown.esc="sendEscKey" />
        <span v-if="maxlength && displayMaxLength" class="length absolute rounded-xs text-xs">
          {{ charactersLeft }}
        </span>
      </div>

      <!-- blank state - case where we suggest most contacted contacts in the vault -->
      <div
        v-if="
          localContacts.length === 0 &&
          displayMostConsultedContacts &&
          searchResults.length === 0 &&
          form.searchTerm.length === 0
        "
        class="mb-6">
        <p class="mb-2 mt-2 text-center text-sm text-gray-600 dark:text-gray-400">
          {{ $t('Maybe one of these contacts?') }}
        </p>
        <ul class="border border-border bg-surface">
          <li
            v-for="contact in mostConsultedContacts"
            :key="contact.id"
            class="item-list flex items-center justify-between border-b border-border px-3 py-2 hover:bg-surface-raised">
            {{ contact.name }}

            <!-- actions -->
            <ul class="text-sm">
              <li class="inline cursor-pointer text-accent hover:underline" @click="add(contact)">{{ $t('Add') }}</li>
            </ul>
          </li>
        </ul>
      </div>

      <!-- searching results -->
      <div v-if="processingSearch" class="mb-6 border border-border bg-surface p-6 text-center text-text-muted">
        <p>{{ $t('Searching…') }}</p>
      </div>

      <!-- not enough characters -->
      <div
        v-if="form.searchTerm.length < 3 && form.searchTerm.length !== 0"
        class="mb-6 border border-border bg-surface p-6 text-center text-text-muted">
        <p>{{ $t('Please enter at least 3 characters to initiate a search.') }}</p>
      </div>

      <!-- search results: results found -->
      <div v-if="searchResults.length !== 0 && form.searchTerm.length !== 0" class="mb-3">
        <errors :errors="form.errors" />

        <ul class="mb-4 border border-border bg-surface">
          <li
            v-for="contact in searchResults"
            :key="contact.id"
            class="item-list flex items-center justify-between border-b border-border px-3 py-2 hover:bg-surface-raised">
            {{ contact.name }}

            <!-- actions -->
            <ul class="text-sm">
              <li class="inline cursor-pointer text-accent hover:underline" @click="add(contact)">{{ $t('Add') }}</li>
            </ul>
          </li>
        </ul>
      </div>

      <!-- search results: no results found -->
      <div
        v-if="searchResults.length === 0 && form.searchTerm.length >= 3"
        class="mb-3 border border-border bg-surface p-6 text-center text-text-muted">
        <p>{{ $t('No results found') }}</p>
      </div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.optional-badge {
  color: var(--color-text-muted);
  background-color: var(--color-surface-raised);
}

.item-list {
  &:last-child {
    border-bottom: 0;
  }
}
</style>
