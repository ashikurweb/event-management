<template>
  <div class="modern-input-wrapper-root" v-bind="$attrs">
    <a-input-password v-if="type === 'password'" v-model:value="inputValue" :placeholder="placeholder" :size="size"
      :disabled="disabled" :readonly="readonly" :maxlength="maxlength" :class="inputClass">
      <template v-if="$slots.prefix || icon" #prefix>
        <slot name="prefix">
          <component v-if="icon" :is="icon" />
        </slot>
      </template>
      <template v-if="$slots.suffix" #suffix>
        <slot name="suffix" />
      </template>
    </a-input-password>
    <a-input v-else v-model:value="inputValue" :type="type" :placeholder="placeholder" :size="size" :disabled="disabled"
      :readonly="readonly" :maxlength="maxlength" :class="inputClass">
      <template v-if="$slots.prefix || icon" #prefix>
        <slot name="prefix">
          <component v-if="icon" :is="icon" />
        </slot>
      </template>
      <template v-if="$slots.suffix" #suffix>
        <slot name="suffix" />
      </template>
    </a-input>
  </div>
</template>

<script setup>
import { computed } from 'vue';

// Disable standard attribute inheritance since we handle it on the wrapper
defineOptions({
  inheritAttrs: false
});

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

const emit = defineEmits(['update:modelValue']);

const inputValue = computed({
  get: () => props.modelValue,
  set: (value) => {
    emit('update:modelValue', value);
  },
});
</script>

<style scoped></style>
