<template>
    <div class="date-picker-container">
        <a-range-picker v-model:value="dateValue" :placeholder="placeholder" :size="size" :format="format"
            style="width: 100%" @change="handleChange" v-bind="$attrs" />
    </div>
</template>

<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    modelValue: {
        type: Array,
        default: null,
    },
    placeholder: {
        type: Array,
        default: () => ['Start Date', 'End Date'],
    },
    size: {
        type: String,
        default: 'large',
    },
    format: {
        type: String,
        default: 'YYYY-MM-DD',
    },
});

const emit = defineEmits(['update:modelValue', 'change']);

const dateValue = ref(props.modelValue);

// Sync internal value with prop
watch(() => props.modelValue, (newVal) => {
    dateValue.value = newVal;
});

const handleChange = (dates, dateStrings) => {
    emit('update:modelValue', dates);
    emit('change', dates, dateStrings);
};
</script>

<style scoped>
.date-picker-container {
    width: 100%;
}
</style>
