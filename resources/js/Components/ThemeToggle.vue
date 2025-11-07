<template>
    <button
        @click="handleToggle"
        class="theme-toggle"
        :title="currentTheme === 'light' ? 'Switch to Dark Mode' : 'Switch to Light Mode'"
        aria-label="Toggle theme"
    >
        <div class="toggle-track">
            <div class="toggle-thumb" :class="{ 'is-dark': currentTheme === 'dark' }">
                <span class="theme-icon">
                    <transition name="icon-fade" mode="out-in">
                        <span v-if="currentTheme === 'light'" key="moon">🌙</span>
                        <span v-else key="sun">☀️</span>
                    </transition>
                </span>
            </div>
        </div>
    </button>
</template>

<script setup>
import { useTheme } from '../Composables/useTheme';

const { currentTheme, toggleTheme } = useTheme();

const handleToggle = () => {
    toggleTheme();
};
</script>

<style scoped>
.theme-toggle {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    border: none;
    background: transparent;
    cursor: pointer;
    outline: none;
}

.toggle-track {
    position: relative;
    width: 56px;
    height: 28px;
    background: var(--bg-elevated);
    border: 1px solid var(--border-color);
    border-radius: 14px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.06);
}

/* Light mode specific */
:root[data-theme="light"] .toggle-track {
    background: #f3f4f6;
    border-color: #d1d5db;
    box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.08);
}

/* Dark mode specific */
:root[data-theme="dark"] .toggle-track {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-color: transparent;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
}

.theme-toggle:hover .toggle-track {
    border-color: var(--border-color-hover);
    transform: translateY(-1px);
}

/* Light mode hover effect */
:root[data-theme="light"] .theme-toggle:hover .toggle-track {
    background: #e5e7eb;
    border-color: #9ca3af;
}

/* Dark mode hover effect */
:root[data-theme="dark"] .theme-toggle:hover .toggle-track {
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.theme-toggle:active .toggle-track {
    transform: translateY(0);
}

.toggle-thumb {
    position: absolute;
    top: 2px;
    left: 2px;
    width: 22px;
    height: 22px;
    background: var(--text-primary);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

/* Light mode thumb */
:root[data-theme="light"] .toggle-thumb {
    background: #4b5563;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
}

/* Dark mode thumb */
:root[data-theme="dark"] .toggle-thumb {
    background: #ffffff;
}

.toggle-thumb.is-dark {
    transform: translateX(28px);
}

.theme-icon {
    font-size: 14px;
    line-height: 1;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Icon fade transition */
.icon-fade-enter-active,
.icon-fade-leave-active {
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

.icon-fade-enter-from {
    opacity: 0;
    transform: rotate(-90deg) scale(0.6);
}

.icon-fade-leave-to {
    opacity: 0;
    transform: rotate(90deg) scale(0.6);
}

.icon-fade-enter-to,
.icon-fade-leave-from {
    opacity: 1;
    transform: rotate(0deg) scale(1);
}

/* Subtle glow on hover for dark mode */
:root[data-theme="dark"] .theme-toggle:hover .toggle-thumb {
    box-shadow: 
        0 2px 6px rgba(0, 0, 0, 0.3),
        0 0 8px rgba(255, 255, 255, 0.3);
}

/* Light mode hover for thumb */
:root[data-theme="light"] .theme-toggle:hover .toggle-thumb {
    background: #374151;
}
</style>