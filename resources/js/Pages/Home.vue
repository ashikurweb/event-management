<script setup>
import { ref } from 'vue';
import ThemeToggle from '../components/ThemeToggle.vue';
import { useTheme } from '../composables/useTheme';

const title = ref('Event Management System');
const { currentTheme, toggleTheme } = useTheme();
</script>

<template>
    <div class="min-h-screen" style="background-color: var(--bg-primary); color: var(--text-primary);">
        <div class="max-w-6xl mx-auto p-8 space-y-8">
            <!-- Header with Theme Toggle -->
            <div class="flex justify-between items-center">
                <div class="text-center flex-1 space-y-2">
                    <h1 class="text-4xl font-bold" style="color: var(--text-primary);">{{ title }}</h1>
                    <p style="color: var(--text-secondary);">Professional & Modern UI with Dark/Light Theme</p>
                </div>
                <ThemeToggle />
            </div>

            <!-- Theme Info -->
            <a-card :title="`Current Theme: ${currentTheme === 'light' ? 'Light' : 'Dark'}`" class="shadow-lg" style="background-color: var(--card-bg); border-color: var(--card-border);">
                <p style="color: var(--text-secondary);">
                    Click the theme toggle button (🌙/☀️) in the top right to switch between light and dark themes.
                    Your preference will be saved automatically.
                </p>
            </a-card>

            <!-- Color Palette Showcase -->
            <a-card title="Color Palette" class="shadow-lg" style="background-color: var(--card-bg); border-color: var(--card-border);">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <!-- Primary Blue -->
                    <div class="space-y-2">
                        <div class="h-20 rounded-lg bg-[#1890ff] flex items-center justify-center text-white font-semibold">
                            Primary
                        </div>
                        <p class="text-sm text-gray-600">#1890ff</p>
                        <a-button type="primary" block>Primary Button</a-button>
                    </div>

                    <!-- Success Green -->
                    <div class="space-y-2">
                        <div class="h-20 rounded-lg bg-[#52c41a] flex items-center justify-center text-white font-semibold">
                            Success
                        </div>
                        <p class="text-sm text-gray-600">#52c41a</p>
                        <a-button type="primary" style="background: #52c41a; border-color: #52c41a;" block>
                            Success
                        </a-button>
                    </div>

                    <!-- Warning Orange -->
                    <div class="space-y-2">
                        <div class="h-20 rounded-lg bg-[#faad14] flex items-center justify-center text-white font-semibold">
                            Warning
                        </div>
                        <p class="text-sm text-gray-600">#faad14</p>
                        <a-button type="primary" style="background: #faad14; border-color: #faad14;" block>
                            Warning
                        </a-button>
                    </div>

                    <!-- Error Red -->
                    <div class="space-y-2">
                        <div class="h-20 rounded-lg bg-[#ff4d4f] flex items-center justify-center text-white font-semibold">
                            Error
                        </div>
                        <p class="text-sm text-gray-600">#ff4d4f</p>
                        <a-button danger block>Error</a-button>
                    </div>
                </div>
            </a-card>

            <!-- Button Variants -->
            <a-card title="Button Variants" class="shadow-lg">
                <div class="space-y-4">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 mb-2">Primary Buttons</h3>
                        <div class="flex flex-wrap gap-2">
                            <a-button type="primary">Primary</a-button>
                            <a-button type="primary" size="large">Large</a-button>
                            <a-button type="primary" size="small">Small</a-button>
                            <a-button type="primary" :loading="true">Loading</a-button>
                            <a-button type="primary" disabled>Disabled</a-button>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 mb-2">Other Variants</h3>
                        <div class="flex flex-wrap gap-2">
                            <a-button>Default</a-button>
                            <a-button type="dashed">Dashed</a-button>
                            <a-button type="text">Text</a-button>
                            <a-button type="link">Link</a-button>
                        </div>
                    </div>
                </div>
            </a-card>

            <!-- Form Components -->
            <a-card title="Form Components" class="shadow-lg">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-4">
                        <a-input placeholder="Enter event name" />
                        <a-input-password placeholder="Password" />
                        <a-textarea placeholder="Event description" :rows="3" />
                        <a-select placeholder="Select category" style="width: 100%">
                            <a-select-option value="conference">Conference</a-select-option>
                            <a-select-option value="workshop">Workshop</a-select-option>
                            <a-select-option value="webinar">Webinar</a-select-option>
                        </a-select>
                    </div>
                    <div class="space-y-4">
                        <a-date-picker style="width: 100%" placeholder="Select date" />
                        <a-time-picker style="width: 100%" placeholder="Select time" />
                        <a-switch checked-children="Active" un-checked-children="Inactive" />
                        <a-checkbox>I agree to terms</a-checkbox>
                    </div>
                </div>
            </a-card>

            <!-- Cards & Alerts -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <a-card title="Success Message" class="shadow-lg">
                    <a-alert message="Event created successfully!" type="success" show-icon />
                </a-card>
                <a-card title="Info Message" class="shadow-lg">
                    <a-alert message="New event registration available" type="info" show-icon />
                </a-card>
                <a-card title="Warning Message" class="shadow-lg">
                    <a-alert message="Event capacity almost full" type="warning" show-icon />
                </a-card>
                <a-card title="Error Message" class="shadow-lg">
                    <a-alert message="Failed to process payment" type="error" show-icon />
                </a-card>
            </div>
        </div>
    </div>
</template>