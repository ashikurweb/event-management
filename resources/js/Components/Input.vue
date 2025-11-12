<template>
  <a-input-password
    v-if="type === 'password'"
    v-model:value="inputValue"
    :placeholder="placeholder"
    :size="size"
    :disabled="disabled"
    :readonly="readonly"
    :maxlength="maxlength"
    :class="inputClass"
    v-bind="$attrs"
    @change="handleChange"
    @blur="handleBlur"
    @focus="handleFocus"
  >
    <template v-if="$slots.prefix || icon" #prefix>
      <slot name="prefix">
        <component v-if="icon" :is="icon" />
      </slot>
    </template>
    <template v-if="$slots.suffix" #suffix>
      <slot name="suffix" />
    </template>
  </a-input-password>
  <a-input
    v-else
    v-model:value="inputValue"
    :type="type"
    :placeholder="placeholder"
    :size="size"
    :disabled="disabled"
    :readonly="readonly"
    :maxlength="maxlength"
    :class="inputClass"
    v-bind="$attrs"
    @change="handleChange"
    @blur="handleBlur"
    @focus="handleFocus"
  >
    <template v-if="$slots.prefix || icon" #prefix>
      <slot name="prefix">
        <component v-if="icon" :is="icon" />
      </slot>
    </template>
    <template v-if="$slots.suffix" #suffix>
      <slot name="suffix" />
    </template>
  </a-input>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  modelValue: {
    type: [String, Number],
    default: '',
  },
  type: {
    type: String,
    default: 'text',
    validator: (value) => ['text', 'email', 'password', 'number', 'tel', 'url', 'search'].includes(value),
  },
  placeholder: {
    type: String,
    default: '',
  },
  size: {
    type: String,
    default: 'large',
    validator: (value) => ['small', 'middle', 'large'].includes(value),
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  readonly: {
    type: Boolean,
    default: false,
  },
  maxlength: {
    type: Number,
    default: undefined,
  },
  icon: {
    type: [Object, Function],
    default: null,
  },
  inputClass: {
    type: String,
    default: '',
  },
});

const emit = defineEmits(['update:modelValue', 'change', 'blur', 'focus']);

const inputValue = computed({
  get: () => props.modelValue,
  set: (value) => {
    emit('update:modelValue', value);
  },
});

const handleChange = (e) => {
  emit('change', e);
};

const handleBlur = (e) => {
  emit('blur', e);
};

const handleFocus = (e) => {
  emit('focus', e);
};
</script>

<style scoped>
/* Input padding */
:deep(.ant-input),
:deep(.ant-input-password .ant-input) {
  padding: 8px 12px !important;
}

:deep(.ant-input-password) {
  padding: 0 !important;
}
</style>
