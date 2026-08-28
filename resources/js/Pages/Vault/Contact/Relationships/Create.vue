<template>
  <layout :layout-data="layoutData" :inside-vault="true">
    <!-- breadcrumb -->
    <nav class="bg-surface dark:bg-surface sm:border-b">
      <div class="max-w-8xl mx-auto hidden px-4 py-2 sm:px-6 md:block">
        <div class="flex items-baseline justify-between space-x-6">
          <ul class="text-sm">
            <li class="me-2 inline text-text dark:text-text">
              {{ $t('You are here:') }}
            </li>
            <li class="me-2 inline">
              <InertiaLink :href="layoutData.vault.url.contacts" class="text-accent hover:underline">
                {{ $t('Contacts') }}
              </InertiaLink>
            </li>
            <li class="relative me-2 inline">
              <svg
                xmlns="http://www.w3.org/2000/svg"
                class="icon-breadcrumb relative inline h-3 w-3"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </li>
            <li class="me-2 inline">
              <InertiaLink :href="data.url.contact" class="text-accent hover:underline">
                {{ $t('Profile of :name', { name: data.contact.name }) }}
              </InertiaLink>
            </li>
            <li class="relative me-2 inline">
              <svg
                xmlns="http://www.w3.org/2000/svg"
                class="icon-breadcrumb relative inline h-3 w-3"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </li>
            <li class="inline">{{ $t('Add a relationship') }}</li>
          </ul>
        </div>
      </div>
    </nav>

    <main class="relative">
      <div class="mx-auto max-w-lg px-2 py-2 sm:px-6 sm:py-6 lg:px-8">
        <form
          class="mb-6 rounded-lg border border-border bg-surface dark:border-border dark:bg-surface"
          @submit.prevent="submit()">
          <!-- header -->
          <div
            class="section-head border-b border-border bg-surface-raised p-5 dark:border-border dark:bg-surface-raised">
            <h1 class="text-center text-2xl font-medium">{{ $t('Add a relationship') }}</h1>
          </div>

          <div class="border-b border-border p-5 dark:border-border">
            <errors :errors="form.errors" />

            <!-- relationship type -->
            <label for="types" class="mb-2 block text-sm"> {{ $t('Select a relationship type') }} </label>
            <Dropdown
              id="types"
              v-model="form.relationship_type_id"
              name="types"
              class="w-full rounded-md border-border bg-surface px-3 py-2 shadow-xs focus:border-accent focus:outline-hidden focus:ring-3 focus:ring-accent/30 dark:bg-surface sm:text-sm"
              @update:model-value="load"
              :data="fromRelationshipOptions" />
          </div>

          <!-- data once the relatonship type has been selected -->
          <div v-if="showRelationshipTypeDetails">
            <div class="border-b border-border p-5 dark:border-border">
              <!-- relationship -->
              <div class="mb-6">
                <p
                  class="mb-2 inline-block flex-none rounded-md bg-bg px-2 py-1 text-xs font-semibold uppercase tracking-wide text-text dark:text-text">
                  {{ fromRelationship }}
                </p>
                <div class="flex items-center">
                  <avatar :data="data.contact.avatar" :class="'me-2 h-5 w-5'" />

                  <span>{{ data.contact.name }}</span>
                </div>
              </div>

              <!-- switch -->
              <div
                class="w-100 mb-4 block cursor-pointer text-center text-text-muted hover:text-text"
                @click="toggle()">
                <div class="flex">
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="me-2 h-5 w-5"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="2">
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                  </svg>
                  <span class="text-xs"> {{ $t('Switch role') }} </span>
                </div>
              </div>

              <!-- reverse relationship -->
              <div>
                <p
                  class="mb-2 inline-block flex-none rounded-md bg-bg px-2 py-1 text-xs font-semibold uppercase tracking-wide text-text dark:text-text">
                  {{ toRelationship }}
                </p>
                <div>
                  <!-- I don't know the name -->
                  <div class="mb-2 flex items-center">
                    <input
                      id="unknown"
                      v-model="form.choice"
                      value="unknown"
                      name="name-order"
                      type="radio"
                      class="h-4 w-4 border-border text-sky-500 dark:border-border"
                      @click="hideContactNameField" />
                    <label for="unknown" class="ms-3 block cursor-pointer text-sm font-medium text-text dark:text-text">
                      {{ $t('I don’t know the name') }}
                    </label>
                  </div>

                  <!-- I know the contact's name -->
                  <div class="mb-2 flex items-center">
                    <input
                      id="name"
                      v-model="form.choice"
                      value="name"
                      name="name-order"
                      type="radio"
                      class="h-4 w-4 border-border text-sky-500 dark:border-border"
                      @click="displayContactNameField" />
                    <label for="name" class="ms-3 block cursor-pointer text-sm font-medium text-text dark:text-text">
                      {{ $t('I know the name') }}
                    </label>
                  </div>

                  <div v-if="showContactName" class="ps-6">
                    <text-input
                      ref="contactName"
                      v-model="form.first_name"
                      :autofocus="true"
                      :class="'mb-5'"
                      :input-class="'block w-full'"
                      :required="true"
                      :maxlength="255" />

                    <!-- last name -->
                    <text-input
                      v-if="showLastNameField"
                      :id="'last_name'"
                      v-model="form.last_name"
                      :class="'mb-5'"
                      :input-class="'block w-full'"
                      :required="false"
                      :maxlength="255"
                      :label="$t('Last name')" />

                    <div class="mb-4 flex flex-wrap text-xs">
                      <span
                        v-if="!showLastNameField"
                        class="mb-2 me-2 flex cursor-pointer flex-wrap rounded-lg border bg-bg px-1 py-1 hover:bg-hover dark:bg-surface dark:text-text"
                        @click="displayLastNameField">
                        {{ $t('+ last name') }}
                      </span>
                      <span
                        v-if="!showMiddleNameField"
                        class="mb-2 me-2 flex cursor-pointer flex-wrap rounded-lg border bg-bg px-1 py-1 hover:bg-hover dark:bg-surface dark:text-text"
                        @click="displayMiddleNameField">
                        {{ $t('+ middle name') }}
                      </span>
                      <span
                        v-if="!showNicknameField"
                        class="mb-2 me-2 flex cursor-pointer flex-wrap rounded-lg border bg-bg px-1 py-1 hover:bg-hover dark:bg-surface dark:text-text"
                        @click="displayNicknameField">
                        {{ $t('+ nickname') }}
                      </span>
                      <span
                        v-if="!showMaidenNameField"
                        class="mb-2 me-2 flex cursor-pointer flex-wrap rounded-lg border bg-bg px-1 py-1 hover:bg-hover dark:bg-surface dark:text-text"
                        @click="displayMaidenNameField">
                        {{ $t('+ maiden name') }}
                      </span>
                    </div>
                  </div>

                  <!-- Choose an existing contact -->
                  <div class="mb-2 flex items-center">
                    <input
                      id="contact"
                      v-model="form.choice"
                      value="contact"
                      name="name-order"
                      type="radio"
                      class="h-4 w-4 border-border text-sky-500 dark:border-border"
                      @input="displayContactSelector" />
                    <label for="contact" class="ms-3 block cursor-pointer text-sm font-medium text-text dark:text-text">
                      {{ $t('Choose an existing contact') }}
                    </label>
                  </div>

                  <div v-if="form.choice === 'contact'" class="ps-6">
                    <contact-selector
                      v-model="form.other_contact_id"
                      :search-url="layoutData.vault.url.search_contacts_only"
                      :most-consulted-contacts-url="layoutData.vault.url.get_most_consulted_contacts"
                      :display-most-consulted-contacts="false"
                      :add-multiple-contacts="false"
                      :required="true"
                      :class="'flex-1 border-border dark:border-border'" />
                  </div>
                </div>
              </div>
            </div>

            <div v-if="showMoreContactOptions" class="border-b border-border p-5 dark:border-border">
              <!-- middle name -->
              <text-input
                v-if="showMiddleNameField"
                :id="'middle_name'"
                v-model="form.middle_name"
                :class="'mb-5'"
                :input-class="'block w-full'"
                :required="false"
                :maxlength="255"
                :label="$t('Middle name')" />

              <!-- nickname -->
              <text-input
                v-if="showNicknameField"
                :id="'nickname'"
                v-model="form.nickname"
                :class="'mb-5'"
                :input-class="'block w-full'"
                :required="false"
                :maxlength="255"
                :label="$t('Nickname')" />

              <!-- nickname -->
              <text-input
                v-if="showMaidenNameField"
                :id="'maiden_name'"
                v-model="form.maiden_name"
                :class="'mb-5'"
                :input-class="'block w-full'"
                :required="false"
                :maxlength="255"
                :label="$t('Maiden name')" />

              <!-- genders -->
              <dropdown
                v-if="showGenderField"
                v-model="form.gender_id"
                :data="data.genders"
                :required="false"
                :class="'mb-5'"
                :placeholder="$t('Choose a value')"
                :dropdown-class="'block w-full'"
                :label="$t('Gender')" />

              <!-- pronouns -->
              <dropdown
                v-if="showPronounField"
                v-model="form.pronoun_id"
                :data="data.pronouns"
                :required="false"
                :class="'mb-5'"
                :placeholder="$t('Choose a value')"
                :dropdown-class="'block w-full'"
                :label="$t('Pronoun')" />

              <!-- other fields -->
              <div class="flex flex-wrap text-xs">
                <span
                  v-if="data.genders.length > 0 && !showGenderField"
                  class="mb-2 me-2 flex cursor-pointer flex-wrap rounded-lg border bg-bg px-1 py-1 hover:bg-hover dark:bg-surface dark:text-text"
                  @click="displayGenderField">
                  {{ $t('+ gender') }}
                </span>
                <span
                  v-if="data.pronouns.length > 0 && !showPronounField"
                  class="mb-2 me-2 flex cursor-pointer flex-wrap rounded-lg border bg-bg px-1 py-1 hover:bg-hover dark:bg-surface dark:text-text"
                  @click="displayPronounField">
                  {{ $t('+ pronoun') }}
                </span>
              </div>
            </div>

            <!-- create a contact entry -->
            <div v-if="form.choice !== 'contact'" class="border-b border-border p-5 dark:border-border">
              <div class="relative flex items-start">
                <input
                  id="create-contact"
                  v-model="form.create_contact_entry"
                  name="create-contact"
                  type="checkbox"
                  class="focus:ring-3 relative h-4 w-4 rounded-xs border border-border bg-bg focus:ring-accent/40 dark:border-border dark:bg-surface dark:ring-offset-gray-800 dark:focus:ring-accent/40" />
                <label for="create-contact" class="ms-2 block cursor-pointer text-sm text-text dark:text-white">
                  {{ $t('Create a contact entry for this person') }}
                </label>
              </div>
            </div>
          </div>

          <!-- actions -->
          <div class="flex justify-between p-5">
            <pretty-link :href="data.url.back" :text="$t('Cancel')" :class="'me-3'" />
            <pretty-button
              :href="'data.url.vault.create'"
              :text="$t('Add')"
              :state="loadingState"
              :icon="'check'"
              :class="'save'" />
          </div>
        </form>
      </div>
    </main>
  </layout>
</template>

<script>
import { Link } from '@inertiajs/vue3';
import Layout from '@/Layouts/Layout.vue';
import PrettyLink from '@/Shared/Form/PrettyLink.vue';
import PrettyButton from '@/Shared/Form/PrettyButton.vue';
import TextInput from '@/Shared/Form/TextInput.vue';
import Dropdown from '@/Shared/Form/Dropdown.vue';
import Errors from '@/Shared/Form/Errors.vue';
import ContactSelector from '@/Shared/Form/ContactSelector.vue';
import Avatar from '@/Shared/Avatar.vue';

export default {
  components: {
    InertiaLink: Link,
    Layout,
    PrettyLink,
    PrettyButton,
    TextInput,
    Dropdown,
    Errors,
    ContactSelector,
    Avatar,
  },

  props: {
    layoutData: {
      type: Object,
      default: null,
    },
    data: {
      type: Object,
      default: null,
    },
  },

  data() {
    return {
      loadingState: '',
      showRelationshipTypeDetails: false,
      showMoreContactOptions: false,
      showContactName: false,
      showLastNameField: false,
      showMiddleNameField: false,
      showNicknameField: false,
      showMaidenNameField: false,
      showGenderField: false,
      showPronounField: false,
      fromRelationship: '',
      toRelationship: '',
      form: {
        choice: 'unknown',
        create_contact_entry: false,
        relationship_type_id: 0,
        base_contact_id: 0,
        other_contact_id: [],
        last_name: '',
        middle_name: '',
        nickname: '',
        maiden_name: '',
        gender_id: '',
        pronoun_id: '',
        errors: [],
      },
    };
  },

  computed: {
    fromRelationshipOptions() {
      return _.map(this.data.relationship_group_types, (group) => {
        return {
          id: group.id,
          optgroup: group.name,
          options: _.map(group.types, (type) => {
            return {
              id: type.id,
              name: type.name,
            };
          }),
        };
      });
    },
  },

  created() {
    this.form.base_contact_id = this.data.contact.id;
    this.fromRelationship = 'Father';
    this.toRelationship = 'Child';
  },

  methods: {
    displayContactNameField() {
      this.form.choice = 'name';
      this.showContactName = true;
      this.showMoreContactOptions = true;

      this.$nextTick().then(() => {
        this.$refs.contactName.focus();
      });
    },

    hideContactNameField() {
      this.form.choice = 'unknown';
      this.form.first_name = '';
      this.form.last_name = '';
      this.form.middle_name = '';
      this.form.nickname = '';
      this.form.maiden_name = '';
      this.form.gender_id = '';
      this.form.pronoun_id = '';
      this.showContactName = false;
      this.showMoreContactOptions = false;
    },

    displayContactSelector() {
      this.form.choice = 'choice';
      this.showContactName = false;
      this.showMoreContactOptions = false;
    },

    displayLastNameField() {
      this.showLastNameField = true;
    },

    displayMiddleNameField() {
      this.showMiddleNameField = true;
    },

    displayNicknameField() {
      this.showNicknameField = true;
    },

    displayMaidenNameField() {
      this.showMaidenNameField = true;
    },

    displayGenderField() {
      this.showGenderField = true;
    },

    displayPronounField() {
      this.showPronounField = true;
    },

    toggle() {
      var temp = this.fromRelationship;
      this.fromRelationship = this.toRelationship;
      this.toRelationship = temp;

      if (this.form.base_contact_id === this.data.contact.id) {
        this.form.base_contact_id = 0;
      } else {
        this.form.base_contact_id = this.data.contact.id;
      }
    },

    load(target) {
      var id = this.data.relationship_types.findIndex((x) => x.id === parseInt(target));
      this.fromRelationship = this.data.relationship_types[id].name;
      this.toRelationship = this.data.relationship_types[id].name_reverse_relationship;
      this.showRelationshipTypeDetails = true;
    },

    submit() {
      this.loadingState = 'loading';

      axios
        .post(this.data.url.store, this.form)
        .then((response) => {
          localStorage.success = this.$t('The relationship has been added');
          this.$inertia.visit(response.data.data);
        })
        .catch((error) => {
          this.form.errors = error.response.data;
          this.loadingState = null;
        });
    },
  },
};
</script>

<style lang="scss" scoped>
.section-head {
  border-top-left-radius: 7px;
  border-top-right-radius: 7px;
}

input[type='checkbox'] {
  top: 3px;
}
</style>
