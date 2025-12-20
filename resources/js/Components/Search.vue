<template>
    <div class="search-container">
        <a-input v-model:value="inputValue" :placeholder="placeholder" :size="size" allow-clear @input="handleInput"
            v-bind="$attrs">
            <template #prefix>
                <SearchOutlined />
            </template>
        </a-input>
    </div>
</template>

<script setup>
import { ref, watch, onBeforeUnmount } from 'vue';
import { SearchOutlined } from '@ant-design/icons-vue';

const props = defineProps({
    modelValue: {
        type: String,
        default: '',
    },
    placeholder: {
        type: String,
        default: 'Search...',
    },
    size: {
        type: String,
        default: 'large',
    },
    delay: {
        type: Number,
        default: 500,
    },
});

const emit = defineEmits(['update:modelValue', 'search']);

const inputValue = ref(props.modelValue);

// Sync internal value with prop
watch(() => props.modelValue, (newVal) => {
    inputValue.value = newVal;
});

let timeout = null;

const handleInput = () => {
    // Update modelValue for v-model binding
    emit('update:modelValue', inputValue.value);

    // Debounce the search emit
    if (timeout) clearTimeout(timeout);
    timeout = setTimeout(() => {
        emit('search', inputValue.value);
    }, props.delay);
};

onBeforeUnmount(() => {
    if (timeout) clearTimeout(timeout);
});
</script>

<style scoped>
.search-container {
    width: 100%;
}

:deep(.ant-input-affix-wrapper) {
    border-radius: 8px !important;
    padding-left: 12px !important;
}

:deep(.ant-input-prefix) {
    margin-right: 8px !important;
}
</style>
