<script setup>
import { onMounted, nextTick, useTemplateRef } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import Layout from '@/Layouts/Layout.vue';
import PrettyLink from '@/Shared/Form/PrettyLink.vue';
import PrettyButton from '@/Shared/Form/PrettyButton.vue';
import TextInput from '@/Shared/Form/TextInput.vue';
import TextArea from '@/Shared/Form/TextArea.vue';
import Errors from '@/Shared/Form/Errors.vue';

const props = defineProps({
  layoutData: Object,
  data: Object,
});

const form = useForm({
  name: props.data.name,
  description: props.data.description,
});

const nameField = useTemplateRef('nameField');

onMounted(() => {
  nextTick().then(() => nameField.value.focus());
});

const submit = () => {
  form.put(props.data.url.update, {
    onFinish: () => {},
  });
};
</script>

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
              <Link :href="data.url.back" class="text-accent hover:underline">
                {{ $t('Journals') }}
              </Link>
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
              <Link :href="data.url.back" class="text-accent hover:underline">{{ data.name }}</Link>
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
            <li class="inline">{{ $t('Edit journal') }}</li>
          </ul>
        </div>
      </div>
    </nav>

    <main class="relative">
      <div class="mx-auto max-w-lg px-2 py-2 sm:px-6 sm:py-6 lg:px-8">
        <form
          class="mb-6 rounded-lg border border-border bg-surface dark:border-border dark:bg-surface"
          @submit.prevent="submit()">
          <div
            class="section-head border-b border-border bg-surface-raised p-5 dark:border-border dark:bg-surface-raised">
            <h1 class="text-center text-2xl font-medium">
              {{ $t('Edit a journal') }}
            </h1>
          </div>
          <div class="border-b border-border p-5 dark:border-border">
            <errors :errors="form.errors" />

            <text-input
              v-model="form.name"
              ref="nameField"
              :autofocus="true"
              :class="'mb-5'"
              :input-class="'block w-full'"
              :required="true"
              :maxlength="255"
              :label="$t('Name')" />

            <text-area
              v-model="form.description"
              :label="$t('Description')"
              :maxlength="255"
              :textarea-class="'block w-full'" />
          </div>

          <!-- actions -->
          <div class="flex justify-between p-5">
            <pretty-link :href="data.url.back" :text="$t('Cancel')" :class="'me-3'" />
            <pretty-button :text="$t('Add')" :state="loadingState" :icon="'check'" :class="'save'" />
          </div>
        </form>
      </div>
    </main>
  </layout>
</template>

<style lang="scss" scoped>
.section-head {
  border-top-left-radius: 7px;
  border-top-right-radius: 7px;
}
</style>
