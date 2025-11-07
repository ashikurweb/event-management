<template>
    <button
        @click="handleToggle"
        class="theme-toggle"
        :title="currentTheme === 'light' ? 'Switch to Dark Mode' : 'Switch to Light Mode'"
        aria-label="Toggle theme"
    >
        <span v-if="currentTheme === 'light'" class="theme-icon">🌙</span>
        <span v-else class="theme-icon">☀️</span>
    </button>
</template>

<script setup>
import { useTheme } from '../Composables/useTheme';

const { currentTheme, toggleTheme } = useTheme();

// Ensure instant theme change on click
const handleToggle = () => {
    toggleTheme();
};
</script>

<style scoped>
.theme-toggle {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: var(--radius-full);
    background-color: var(--bg-elevated);
    border: 1px solid var(--border-color);
    cursor: pointer;
    transition: all var(--transition-fast);
    color: var(--text-primary);
}

.theme-toggle:hover {
    background-color: var(--bg-hover);
    border-color: var(--border-color-hover);
    transform: scale(1.05);
}

.theme-toggle:active {
    transform: scale(0.95);
}

.theme-icon {
    font-size: 20px;
    line-height: 1;
}
</style>

