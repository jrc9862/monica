<script setup>
import { uniqueId } from 'lodash';
import { computed, onMounted, ref, useTemplateRef } from 'vue';

const props = defineProps({
  id: {
    type: String,
    default: null,
  },
  inputClass: String,
  modelValue: {
    type: [String, Number],
    default: '',
  },
  type: {
    type: String,
    default: 'text',
  },
  name: {
    type: String,
    default: 'input',
  },
  placeholder: String,
  help: String,
  label: String,
  required: Boolean,
  disabled: Boolean,
  autofocus: Boolean,
  autocomplete: {
    type: [String, Boolean],
    default: '',
  },
  maxlength: Number,
  min: Number,
  max: Number,
  step: {
    type: String,
    default: 'any',
  },
});
const emit = defineEmits(['esc-key-pressed', 'update:modelValue']);

const displayMaxLength = ref(false);
const input = useTemplateRef('input');

const realId = computed(() => {
  return props.id ?? uniqueId('text-input-');
});

const charactersLeft = computed(() => {
  let char = 0;
  if (props.modelValue) {
    char = props.modelValue.length;
  }

  return `${props.maxlength - char} / ${props.maxlength}`;
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
    <label v-if="label" class="mb-2 block text-sm dark:text-gray-100" :for="realId">
      {{ label }}
      <span v-if="!required" class="optional-badge rounded-xs px-[3px] py-px text-xs">
        {{ $t('optional') }}
      </span>
    </label>

    <div class="relative">
      <input
        :id="realId"
        ref="input"
        :class="[
          'bg-surface border-border text-text',
          'placeholder:text-text-muted',
          'focus:border-accent focus:ring-2 focus:ring-accent/30 focus:outline-hidden',
          'disabled:opacity-50',
          props.inputClass,
        ]"
        :value="modelValue"
        :type="type"
        :name="name"
        :maxlength="maxlength"
        :required="required"
        :autofocus="autofocus"
        :autocomplete="typeof autocomplete === 'string' ? autocomplete : autocomplete ? '' : 'off'"
        :disabled="disabled ? 'disabled' : null"
        :min="min"
        :max="max"
        :step="step"
        :placeholder="placeholder"
        @input="$emit('update:modelValue', $event.target.value)"
        @keydown.esc="sendEscKey"
        @focus="displayMaxLength = true"
        @blur="displayMaxLength = false" />
      <span
        v-if="maxlength && displayMaxLength"
        class="length absolute end-2.5 top-2.5 rounded-xs px-1 py-[3px] text-xs dark:text-gray-100">
        {{ charactersLeft }}
      </span>
    </div>

    <p v-if="help" class="mb-3 mt-1 text-xs">
      {{ help }}
    </p>
  </div>
</template>

<style lang="scss" scoped>
.optional-badge {
  color: var(--color-text-muted);
  background-color: var(--color-surface-raised);
}

.length {
  background-color: var(--color-surface-raised);
  color: var(--color-text-muted);
}
</style>
