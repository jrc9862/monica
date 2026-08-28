<template>
  <div class="mb-12">
    <!-- title + cta -->
    <div class="mb-3 mt-8 items-center justify-between sm:mt-0 sm:flex">
      <h3 class="mb-4 sm:mb-0">
        <span class="me-1"> 🏷 </span>
        {{ $t('All the labels used in the vault') }}
      </h3>
      <pretty-button v-if="!createlabelModalShown" :text="$t('Add a label')" :icon="'plus'" @click="showLabelModal" />
    </div>

    <!-- modal to create a new label -->
    <form
      v-if="createlabelModalShown"
      class="mb-6 rounded-lg border border-border bg-bg dark:border-border dark:bg-surface"
      @submit.prevent="submit()">
      <div class="border-b border-border p-5 dark:border-border">
        <errors :errors="form.errors" />

        <text-input
          ref="newLabel"
          v-model="form.name"
          :label="$t('Name')"
          :type="'text'"
          :autofocus="true"
          :input-class="'block w-full'"
          :required="true"
          :autocomplete="false"
          :maxlength="255"
          :class="'mb-4'"
          @esc-key-pressed="createlabelModalShown = false" />

        <p class="mb-2 block text-sm">
          {{ $t('Choose a color') }}
        </p>
        <div class="grid grid-cols-8 gap-4">
          <div v-for="color in data.label_colors" :key="color.bg_color" class="flex items-center">
            <input
              :id="color.bg_color"
              v-model="form.bg_color"
              :value="color.bg_color"
              name="name-order"
              type="radio"
              class="h-4 w-4 border-border text-sky-500 dark:border-border"
              @click="form.text_color = color.text_color" />
            <label
              :for="color.bg_color"
              class="ms-2 inline-block cursor-pointer text-sm font-medium text-text dark:text-text">
              <div class="rounded-xs p-4" :class="color.bg_color" />
            </label>
          </div>
        </div>
      </div>

      <div class="flex justify-between p-5">
        <pretty-span :text="$t('Cancel')" :class="'me-3'" @click="createlabelModalShown = false" />
        <pretty-button :text="$t('Create label')" :state="loadingState" :icon="'plus'" :class="'save'" />
      </div>
    </form>

    <!-- list of label -->
    <ul
      v-if="localLabels.length > 0"
      class="mb-6 rounded-lg border border-border bg-surface dark:border-border dark:bg-surface">
      <li
        v-for="label in localLabels"
        :key="label.id"
        class="item-list border-b border-border hover:bg-hover dark:border-border dark:bg-surface dark:hover:bg-hover dark:hover:bg-hover">
        <!-- detail of the label -->
        <div v-if="editLabelModalShownId !== label.id" class="flex items-center justify-between px-5 py-2">
          <span class="flex items-center text-base">
            <div class="me-2 inline-block h-4 w-4 rounded-full" :class="label.bg_color" />
            <span class="me-2">{{ label.name }}</span>
            <span v-if="label.count > 0" class="text-xs text-text-muted"
              >({{ $tChoice(':count contact|:count contacts', label.count, { count: label.count }) }})</span
            >
          </span>

          <!-- actions -->
          <ul class="text-sm">
            <li class="me-4 inline cursor-pointer" @click="updateLabelModal(label)">
              <span class="text-accent hover:underline">{{ $t('Edit') }}</span>
            </li>
            <li
              class="inline cursor-pointer text-red-500 hover:text-red-900 dark:hover:text-red-100"
              @click="destroy(label)">
              {{ $t('Delete') }}
            </li>
          </ul>
        </div>

        <!-- edit a label modal -->
        <form
          v-if="editLabelModalShownId === label.id"
          class="item-list border-b border-border bg-bg hover:bg-hover dark:border-border dark:bg-surface dark:bg-surface dark:hover:bg-hover dark:hover:bg-hover"
          @submit.prevent="update(label)">
          <div class="border-b border-border p-5 dark:border-border">
            <errors :errors="form.errors" />

            <text-input
              ref="rename"
              v-model="form.name"
              :label="$t('Name')"
              :type="'text'"
              :autofocus="true"
              :input-class="'block w-full'"
              :required="true"
              :autocomplete="false"
              :maxlength="255"
              :class="'mb-4'"
              @esc-key-pressed="editLabelModalShownId = 0" />

            <p class="mb-2 block text-sm">
              {{ $t('Choose a color') }}
            </p>
            <div class="grid grid-cols-8 gap-4">
              <div v-for="color in data.label_colors" :key="color.bg_color" class="flex items-center">
                <input
                  :id="color.bg_color"
                  v-model="form.bg_color"
                  :value="color.bg_color"
                  name="name-order"
                  type="radio"
                  class="h-4 w-4 border-border text-sky-500 dark:border-border"
                  @click="form.text_color = color.text_color" />
                <label
                  :for="color.bg_color"
                  class="ms-2 inline-block cursor-pointer text-sm font-medium text-text dark:text-text">
                  <div class="rounded-xs p-4" :class="color.bg_color" />
                </label>
              </div>
            </div>
          </div>

          <div class="flex justify-between p-5">
            <pretty-span :text="$t('Cancel')" :class="'me-3'" @click.prevent="editLabelModalShownId = 0" />
            <pretty-button :text="$t('Rename')" :state="loadingState" :icon="'check'" :class="'save'" />
          </div>
        </form>
      </li>
    </ul>

    <!-- blank state -->
    <div
      v-if="localLabels.length === 0"
      class="mb-6 rounded-lg border border-border bg-surface dark:border-border dark:bg-surface">
      <p class="p-5 text-center">
        {{ $t('Labels let you classify contacts using a system that matters to you.') }}
      </p>
    </div>
  </div>
</template>

<script>
import PrettyButton from '@/Shared/Form/PrettyButton.vue';
import PrettySpan from '@/Shared/Form/PrettySpan.vue';
import TextInput from '@/Shared/Form/TextInput.vue';
import Errors from '@/Shared/Form/Errors.vue';

export default {
  components: {
    PrettyButton,
    PrettySpan,
    TextInput,
    Errors,
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
      createlabelModalShown: false,
      editLabelModalShownId: 0,
      localLabels: [],
      form: {
        name: '',
        description: '',
        bg_color: '',
        text_color: '',
        errors: [],
      },
    };
  },

  mounted() {
    this.localLabels = this.data.labels;
    this.form.bg_color = this.data.label_colors[0].bg_color;
    this.form.text_color = this.data.label_colors[0].text_color;
  },

  methods: {
    showLabelModal() {
      this.form.name = '';
      this.createlabelModalShown = true;

      this.$nextTick().then(() => {
        this.$refs.newLabel.focus();
      });
    },

    updateLabelModal(label) {
      this.form.name = label.name;
      this.editLabelModalShownId = label.id;
      this.form.bg_color = label.bg_color;
      this.form.text_color = label.text_color;
    },

    submit() {
      this.loadingState = 'loading';

      axios
        .post(this.data.url.label_store, this.form)
        .then((response) => {
          this.flash(this.$t('The label has been created'), 'success');
          this.localLabels.unshift(response.data.data);
          this.loadingState = null;
          this.createlabelModalShown = false;
        })
        .catch((error) => {
          this.loadingState = null;
          this.form.errors = error.response.data;
        });
    },

    update(label) {
      this.loadingState = 'loading';

      axios
        .put(label.url.update, this.form)
        .then((response) => {
          this.flash(this.$t('The label has been updated'), 'success');
          this.localLabels[this.localLabels.findIndex((x) => x.id === label.id)] = response.data.data;
          this.loadingState = null;
          this.editLabelModalShownId = 0;
        })
        .catch((error) => {
          this.loadingState = null;
          this.form.errors = error.response.data;
        });
    },

    destroy(label) {
      if (confirm(this.$t('Are you sure? This action cannot be undone.'))) {
        axios
          .delete(label.url.destroy)
          .then(() => {
            this.flash(this.$t('The label has been deleted'), 'success');
            var id = this.localLabels.findIndex((x) => x.id === label.id);
            this.localLabels.splice(id, 1);
          })
          .catch((error) => {
            this.loadingState = null;
            this.form.errors = error.response.data;
          });
      }
    },
  },
};
</script>

<style lang="scss" scoped>
.item-list {
  &:hover:first-child {
    border-top-left-radius: 8px;
    border-top-right-radius: 8px;
  }

  &:last-child {
    border-bottom: 0;
  }

  &:hover:last-child {
    border-bottom-left-radius: 8px;
    border-bottom-right-radius: 8px;
  }
}
</style>
