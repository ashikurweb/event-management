/**
 * Theme Composable - Vue 3 Composition API with Pinia Store
 * Professional Theme Management for Event Management System
 */

import { computed } from 'vue';
import { useThemeStore } from '../stores/theme';

/**
 * Theme Composable Hook
 * Uses Pinia store for centralized state management
 */
export const useTheme = () => {
    const themeStore = useThemeStore();
    
    // Ensure theme is initialized
    if (!themeStore.isInitialized) {
        themeStore.initializeTheme();
    }
    
    return {
        // Reactive computed property from store
        currentTheme: computed(() => themeStore.currentTheme),
        // Store actions
        toggleTheme: () => themeStore.toggleTheme(),
        setTheme: (theme) => themeStore.setTheme(theme),
        // Store getters
        isDark: computed(() => themeStore.isDark),
        isLight: computed(() => themeStore.isLight),
    };
};

