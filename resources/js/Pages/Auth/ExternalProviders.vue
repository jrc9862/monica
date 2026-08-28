<script setup>
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { size } from 'lodash';
import JetButton from '@/Components/Button.vue';

const props = defineProps({
  providers: Object,
  remember: Boolean,
});

const providerForm = useForm({});
const providersExists = computed(() => size(props.providers) > 0);

const open = (provider) => {
  providerForm
    .transform(() => ({
      redirect: location.href,
      remember: props.remember ? 'on' : '',
    }))
    .get(route('login.provider', { driver: provider }), {
      preserveScroll: true,
      onFinish: () => {
        providerForm.reset();
      },
    });
};
</script>

<template>
  <div v-if="providersExists">
    <fieldset class="border-t border-border dark:border-border">
      <legend class="mx-auto px-4 text-l italic">
        {{ $t('Or') }}
      </legend>
    </fieldset>
    <p class="mb-3 block text-sm font-medium text-text dark:text-text">
      {{ $t('Simply connect with:') }}
    </p>
    <div class="flex flex-wrap">
      <JetButton
        v-for="(provider, id) in providers"
        :key="id"
        class="cursor-pointer mb-2 me-2 inline w-32 align-middle !bg-surface !text-text hover:!bg-surface !focus:border-border !focus:ring-gray-700"
        :href="route('login.provider', { driver: id })"
        @click.prevent="open(id)">
        <img :src="provider.logo" :alt="provider.name" class="relative me-2 h-4 w-4 align-middle bg-surface" />
        <span class="align-middle">
          {{ provider.name }}
        </span>
      </JetButton>
    </div>
  </div>
</template>
