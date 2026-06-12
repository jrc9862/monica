<script setup>
import { computed, onMounted, useTemplateRef } from 'vue';
import { isObject, map, uniqueId } from 'lodash';

const props = defineProps({
  id: {
    type: String,
    default: null,
  },
  data: Object,
  dropdownClass: String,
  modelValue: {
    type: [String, Number],
    default: '',
  },
  help: String,
  label: String,
  required: Boolean,
  disabled: Boolean,
  autocomplete: String,
  autofocus: Boolean,
  placeholder: String,
});

const emit = defineEmits(['esc-key-pressed', 'update:modelValue']);

const input = useTemplateRef('input');

const localDropdownClasses = computed(() => {
  return [
    'py-2 px-3 ps-2 pe-5 ltr:bg-[right_3px_center] rtl:bg-[left_3px_center] sm:text-sm',
    'bg-surface border-border text-text',
    'focus:border-accent focus:ring-2 focus:ring-accent/30 focus:outline-hidden',
    props.dropdownClass,
  ];
});

const localData = computed(() => {
  return map(props.data, (value) => {
    if (isObject(value)) {
      return value;
    } else {
      return {
        id: value,
        name: value,
      };
    }
  });
});

const change = (event) => {
  emit('update:modelValue', event.target.value);
};

const realId = computed(() => {
  return props.id ?? uniqueId('dropdown-');
});

onMounted(() => {
  if (props.autofocus) {
    focus();
  }
});

const sendEscKey = () => {
  emit('esc-key-pressed');
};
const focus = () => {
  input.value.focus();
};

defineExpose({ focus: focus });
</script>

<template>
  <div>
    <label v-if="label" class="mb-2 block text-sm" :for="realId">
      {{ label }}
      <span v-if="!required" class="optional-badge rounded-xs px-[3px] py-px text-xs">
        {{ $t('optional') }}
      </span>
    </label>

    <div class="component relative">
      <select
        :id="realId"
        ref="input"
        :value="modelValue"
        :class="localDropdownClasses"
        :required="required"
        :disabled="disabled"
        :placeholder="placeholder"
        @keydown.esc="sendEscKey"
        @change="change">
        <template v-for="item in localData" :key="item.id">
          <optgroup v-if="item.optgroup" :label="item.optgroup">
            <option v-for="option in item.options" :key="option.id" :value="option.id">
              {{ option.name }}
            </option>
          </optgroup>
          <option v-else :value="item.id">
            {{ item.name }}
          </option>
        </template>
      </select>
    </div>

    <p v-if="help" class="mt-1 text-xs">
      {{ help }}
    </p>
  </div>
</template>

<style lang="scss" scoped>
.optional-badge {
  color: var(--color-text-muted);
  background-color: var(--color-surface-raised);
}
</style>
