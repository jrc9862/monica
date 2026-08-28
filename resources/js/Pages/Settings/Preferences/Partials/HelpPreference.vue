<template>
  <div class="mb-16">
    <!-- title + cta -->
    <div class="mb-3 mt-8 items-center justify-between sm:mt-0 sm:flex">
      <h3 class="mb-4 flex font-semibold sm:mb-0">
        <span class="me-1"> ⁉️ </span>
        <span class="me-2">
          {{ $t('Help') }}
        </span>

        <help :url="$page.props.help_links.settings_preferences_help" :top="'5px'" />
      </h3>
    </div>

    <div class="mb-6 rounded-lg border border-border bg-surface px-5 pb-2 pt-3 dark:border-border dark:bg-surface">
      <label for="default-toggle" class="relative inline-flex cursor-pointer items-center">
        <input id="default-toggle" v-model="form.checked" type="checkbox" class="peer hidden" @click="submit" />
        <div
          class="peer h-6 w-11 rounded-full bg-bg after:absolute after:left-[2px] after:right-[22px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-border after:bg-surface after:transition-all after:content-[''] peer-checked:bg-accent peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:outline-hidden peer-focus:ring-4 peer-focus:ring-accent/40 dark:border-border dark:bg-surface dark:peer-focus:ring-accent/40" />
        <span class="ms-3 dark:text-text">
          {{ $t('Display help links in the interface to help you (English only)') }}
        </span>
      </label>
    </div>
  </div>
</template>

<script>
import Help from '@/Shared/Help.vue';

export default {
  components: {
    Help,
  },

  props: {
    data: {
      type: Object,
      default: null,
    },
  },

  data() {
    return {
      form: {
        checked: false,
        errors: [],
      },
    };
  },

  mounted() {
    this.form.checked = this.data.help_shown;
  },

  methods: {
    submit() {
      axios
        .post(this.data.url.store, this.form)
        .then(() => {
          this.flash(this.$t('Changes saved'), 'success');
          this.$page.props.auth.user.help_shown = this.form.checked;
        })
        .catch((error) => {
          this.form.errors = error.response.data;
        });
    },
  },
};
</script>
