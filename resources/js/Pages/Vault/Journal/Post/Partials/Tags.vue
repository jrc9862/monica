<template>
  <div class="mb-8">
    <p class="mb-2 flex items-center justify-between font-bold">
      <span>{{ $t('Tags') }}</span>

      <span
        v-if="!editTagModalShown"
        class="relative cursor-pointer text-xs text-text dark:text-text"
        @click="showEditModal">
        {{ $t('Edit') }}
      </span>

      <!-- close button -->
      <span
        v-if="editTagModalShown"
        class="cursor-pointer text-xs text-text dark:text-text"
        @click="editTagModalShown = false">
        {{ $t('Close') }}
      </span>
    </p>

    <!-- edit labels -->
    <div
      v-if="editTagModalShown"
      class="mb-6 rounded-lg border border-border bg-surface dark:border-border dark:bg-surface">
      <!-- filter list of tags -->
      <div class="border-b border-border p-2 dark:border-border">
        <errors :errors="form.errors" />

        <text-input
          ref="tag"
          v-model="form.search"
          :type="'text'"
          :placeholder="$t('Filter list or create a new tag')"
          :autofocus="true"
          :input-class="'block w-full'"
          :required="true"
          :autocomplete="false"
          @esc-key-pressed="editTagModalShown = false" />
      </div>

      <!-- tags in vault -->
      <ul class="tag-list overflow-auto bg-surface dark:bg-surface" :class="filteredTags.length > 0 ? 'h-40' : ''">
        <li
          v-for="tag in filteredTags"
          :key="tag.id"
          class="flex cursor-pointer items-center justify-between border-b border-border px-3 py-2 hover:bg-hover dark:border-border dark:bg-surface dark:hover:bg-hover"
          @click="set(tag)">
          <div>
            <span class="me-2 inline-block h-4 w-4 rounded-full" :class="tag.bg_color" />
            <span>{{ tag.name }}</span>
          </div>

          <svg
            v-if="tag.taken"
            xmlns="http://www.w3.org/2000/svg"
            class="h-6 w-6 text-green-700"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
        </li>

        <!-- case if the tag does not exist and needs to be created -->
        <li
          v-if="filteredTags.length === 0 && form.search.length !== ''"
          class="cursor-pointer border-b border-border px-3 py-2 hover:bg-hover dark:border-border dark:bg-surface dark:hover:bg-hover"
          @click="store()">
          {{ $t('Create new tag') }} <span class="italic">"{{ form.search }}"</span>
        </li>

        <!-- blank state when there is no tag at all -->
        <li
          v-if="filteredTags.length === 0 && form.search.length === ''"
          class="border-b border-border px-3 py-2 text-sm text-text hover:bg-hover dark:border-border dark:bg-surface dark:text-text dark:hover:bg-hover">
          {{ $t('Please type a few characters to create a new tag.') }}
        </li>
      </ul>
    </div>

    <!-- list of tags -->
    <div class="flex flex-wrap">
      <span
        v-for="tag in localTags"
        :key="tag.id"
        class="me-2 inline-block rounded-xs bg-bg px-2 py-1 text-xs font-semibold text-text-muted last:me-0">
        <InertiaLink :href="tag.url.show">{{ tag.name }}</InertiaLink>
      </span>
    </div>

    <!-- blank state -->
    <p v-if="localTags.length === 0" class="text-sm text-text dark:text-text">{{ $t('Not set') }}</p>
  </div>
</template>

<script>
import { Link } from '@inertiajs/vue3';
import TextInput from '@/Shared/Form/TextInput.vue';
import Errors from '@/Shared/Form/Errors.vue';

export default {
  components: {
    InertiaLink: Link,
    TextInput,
    Errors,
  },

  props: {
    data: {
      type: Object,
      default: null,
    },
  },

  data() {
    return {
      editTagModalShown: false,
      localTags: [],
      localTagsInVault: [],
      form: {
        search: '',
        name: '',
        errors: [],
      },
    };
  },

  computed: {
    filteredTags() {
      return this.localTagsInVault.filter((tag) => {
        return tag.name.toLowerCase().indexOf(this.form.search.toLowerCase()) > -1;
      });
    },
  },

  created() {
    this.localTags = this.data.tags_in_post;

    // TODO: this should not be loaded up front. we should do a async call once
    // the edit mode is active to load all the tags from the backend instead.
    this.localTagsInVault = this.data.tags_in_vault;
  },

  methods: {
    showEditModal() {
      this.form.name = '';
      this.editTagModalShown = true;

      this.$nextTick().then(() => {
        this.$refs.tag.focus();
      });
    },

    store() {
      this.form.name = this.form.search;

      axios
        .post(this.data.url.tag_store, this.form)
        .then((response) => {
          this.flash(this.$t('The tag has been added'), 'success');
          this.form.search = '';
          this.localTagsInVault.push(response.data.data);
          this.localTags.push(response.data.data);
        })
        .catch((error) => {
          this.form.errors = error.response.data;
        });
    },

    set(tag) {
      if (tag.taken) {
        this.remove(tag);
        return;
      }

      axios
        .put(tag.url.update)
        .then((response) => {
          this.localTagsInVault[this.localTagsInVault.findIndex((x) => x.id === tag.id)] = response.data.data;
          this.localTags.push(response.data.data);
        })
        .catch((error) => {
          this.form.errors = error.response.data;
        });
    },

    remove(tag) {
      axios
        .delete(tag.url.destroy)
        .then((response) => {
          this.localTagsInVault[this.localTagsInVault.findIndex((x) => x.id === tag.id)] = response.data.data;

          var id = this.localTags.findIndex((x) => x.id === tag.id);
          this.localTags.splice(id, 1);
        })
        .catch((error) => {
          this.form.errors = error.response.data;
        });
    },
  },
};
</script>

<style lang="scss" scoped>
.tag-list {
  border-bottom-left-radius: 8px;
  border-bottom-right-radius: 8px;

  li:last-child {
    border-bottom: 0;
    border-bottom-left-radius: 8px;
    border-bottom-right-radius: 8px;
  }

  li:hover:last-child {
    border-bottom-left-radius: 8px;
    border-bottom-right-radius: 8px;
  }
}
</style>
