<template>

    <Head :title="'Ticket Type: ' + ticketType.name" />
    <DashboardLayout>
        <div class="p-6">
            <div class="mb-6 flex justify-between items-start">
                <div class="flex items-center gap-4">
                    <a-button @click="router.visit('/dashboard/ticket-types')">
                        <template #icon>
                            <ArrowLeftOutlined />
                        </template>
                    </a-button>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">{{ ticketType.name }}</h1>
                        <p class="text-gray-500">{{ ticketType.event?.name }}</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <a-button @click="router.visit(`/dashboard/ticket-types/${ticketType.id}/edit`)">
                        <template #icon>
                            <EditOutlined />
                        </template> Edit
                    </a-button>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Details Card -->
                <a-card title="Ticket Details" class="lg:col-span-2 shadow-sm border-none">
                    <a-descriptions bordered column="1">
                        <a-descriptions-item label="Type">
                            <a-tag :color="ticketType.type === 'free' ? 'green' : 'blue'">{{ ticketType.type }}</a-tag>
                        </a-descriptions-item>
                        <a-descriptions-item label="Price">
                            <span class="font-bold text-lg text-primary">{{ ticketType.currency }} {{ ticketType.price
                            }}</span>
                        </a-descriptions-item>
                        <a-descriptions-item label="Description">
                            {{ ticketType.description || 'No description provided' }}
                        </a-descriptions-item>
                        <a-descriptions-item label="Sales Period">
                            <div v-if="ticketType.sale_start || ticketType.sale_end" class="flex flex-col">
                                <span>Starts: {{ ticketType.sale_start || 'Immediate' }}</span>
                                <span>Ends: {{ ticketType.sale_end || 'Never' }}</span>
                            </div>
                            <span v-else>Ongoing</span>
                        </a-descriptions-item>
                    </a-descriptions>
                </a-card>

                <!-- Stats Card -->
                <a-card title="Sales Overview" class="shadow-sm border-none">
                    <div class="flex flex-col items-center py-4">
                        <a-progress type="dashboard"
                            :percent="Math.round((ticketType.quantity_sold / (ticketType.quantity_total || 1)) * 100)"
                            :stroke-color="{ '0%': '#108ee9', '100%': '#87d068' }" />
                        <div class="mt-4 text-center">
                            <div class="text-2xl font-black">{{ ticketType.quantity_sold }}</div>
                            <div class="text-gray-400 text-xs">Total Sold / {{ ticketType.quantity_total || '∞' }}</div>
                        </div>

                        <div class="w-full mt-8 grid grid-cols-2 gap-4">
                            <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg text-center">
                                <div class="text-sm font-bold">{{ ticketType.available_quantity ?? '∞' }}</div>
                                <div class="text-[10px] text-gray-400">Available</div>
                            </div>
                            <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg text-center">
                                <div class="text-sm font-bold">{{ ticketType.quantity_reserved }}</div>
                                <div class="text-[10px] text-gray-400">Reserved</div>
                            </div>
                        </div>
                    </div>
                </a-card>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { ArrowLeftOutlined, EditOutlined } from '@ant-design/icons-vue';

const props = defineProps({
    ticketType: Object
});
</script>
