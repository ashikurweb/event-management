/**
 * Theme Store - Pinia State Management
 * Professional Theme Management for Event Management System
 */

import { defineStore } from 'pinia';

export const useThemeStore = defineStore('theme', {
    state: () => ({
        currentTheme: 'light',
        isInitialized: false,
    }),

    getters: {
        isDark: (state) => state.currentTheme === 'dark',
        isLight: (state) => state.currentTheme === 'light',
    },

    actions: {
        /**
         * Get theme from localStorage or system preference
         */
        getInitialTheme() {
            // Check localStorage first
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme && ['light', 'dark'].includes(savedTheme)) {
                return savedTheme;
            }
            
            // Check system preference
            if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                return 'dark';
            }
            
            return 'light';
        },

        /**
         * Apply theme to document
         */
        applyTheme(theme) {
            const root = document.documentElement;
            root.setAttribute('data-theme', theme);
            this.currentTheme = theme;
            localStorage.setItem('theme', theme);
        },

        /**
         * Initialize theme on app load
         */
        initializeTheme() {
            if (this.isInitialized) return;
            
            const initialTheme = this.getInitialTheme();
            this.applyTheme(initialTheme);
            this.isInitialized = true;
            
            // Watch for system theme changes
            this.watchSystemTheme();
        },

        /**
         * Toggle between light and dark theme
         */
        toggleTheme() {
            const newTheme = this.currentTheme === 'light' ? 'dark' : 'light';
            this.applyTheme(newTheme);
        },

        /**
         * Set specific theme
         */
        setTheme(theme) {
            if (['light', 'dark'].includes(theme)) {
                this.applyTheme(theme);
            }
        },

        /**
         * Watch for system theme changes
         */
        watchSystemTheme() {
            if (window.matchMedia) {
                const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
                mediaQuery.addEventListener('change', (e) => {
                    // Only auto-switch if user hasn't manually set a preference
                    if (!localStorage.getItem('theme')) {
                        this.applyTheme(e.matches ? 'dark' : 'light');
                    }
                });
            }
        },
    },
});

