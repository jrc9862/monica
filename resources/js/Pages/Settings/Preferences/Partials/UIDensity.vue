<template>
  <div class="mb-16">
    <!-- title -->
    <div class="mb-3 mt-8 items-center justify-between sm:mt-0 sm:flex">
      <h3 class="mb-4 flex font-semibold sm:mb-0">
        <span class="me-2">
          {{ $t('Interface density') }}
        </span>
      </h3>
    </div>

    <div class="mb-6 rounded-lg border border-gray-200 bg-white px-5 pb-4 pt-3 dark:border-gray-700 dark:bg-gray-900">
      <p class="mb-3 text-sm text-gray-500 dark:text-gray-400">
        {{ $t('Choose how compact the interface looks.') }}
      </p>

      <div class="flex gap-4">
        <label class="flex cursor-pointer items-center gap-2">
          <input v-model="form.ui_density" type="radio" value="minimal" class="text-blue-600" @change="submit" />
          <span class="dark:text-gray-300">{{ $t('Minimal') }}</span>
        </label>

        <label class="flex cursor-pointer items-center gap-2">
          <input v-model="form.ui_density" type="radio" value="chunky" class="text-blue-600" @change="submit" />
          <span class="dark:text-gray-300">{{ $t('Chunky') }}</span>
        </label>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    data: {
      type: Object,
      default: null,
    },
  },

  data() {
    return {
      form: {
        ui_density: 'minimal',
        errors: [],
      },
    };
  },

  mounted() {
    this.form.ui_density = this.data.ui_density;
  },

  methods: {
    submit() {
      axios
        .post(this.data.url.store, { ui_density: this.form.ui_density })
        .then(() => {
          this.flash(this.$t('Changes saved'), 'success');
          // Apply the new density class immediately
          document.documentElement.classList.remove('chunky', 'minimal');
          document.documentElement.classList.add(this.form.ui_density);
        })
        .catch((error) => {
          this.form.errors = error.response.data;
        });
    },
  },
};
</script>
