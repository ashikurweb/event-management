<template>

    <Head title="Create Ticket Type" />
    <DashboardLayout>
        <div class="p-6">
            <div class="mb-6 flex items-center gap-4">
                <a-button @click="router.visit('/dashboard/ticket-types')">
                    <template #icon>
                        <ArrowLeftOutlined />
                    </template>
                </a-button>
                <div>
                    <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Create Ticket Type</h1>
                    <p class="text-gray-500 dark:text-gray-400">Add a new ticket category for your event.</p>
                </div>
            </div>

            <a-form layout="vertical" :model="form" @finish="submit" class="max-w-4xl">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Basic Info -->
                    <a-card title="Basic Information" class="shadow-sm border-none">
                        <a-form-item label="Event" required>
                            <a-select v-model:value="form.event_id" placeholder="Select an event"
                                :options="events.map(e => ({ value: e.id, label: e.title }))" show-search />
                        </a-form-item>

                        <a-form-item label="Ticket Name" required>
                            <a-input v-model:value="form.name" placeholder="e.g. VIP Pass, Early Bird" />
                        </a-form-item>

                        <a-form-item label="Description">
                            <a-textarea v-model:value="form.description" :rows="3"
                                placeholder="Brief details about this ticket" />
                        </a-form-item>

                        <div class="grid grid-cols-2 gap-4">
                            <a-form-item label="Type" required>
                                <a-select v-model:value="form.type">
                                    <a-select-option value="free">Free</a-select-option>
                                    <a-select-option value="paid">Paid</a-select-option>
                                    <a-select-option value="donation">Donation</a-select-option>
                                </a-select>
                            </a-form-item>

                            <a-form-item label="Price" v-if="form.type === 'paid'" required>
                                <a-input-number v-model:value="form.price" class="w-full" :min="0" :precision="2"
                                    placeholder="0.00">
                                    <template #addonBefore>{{ form.currency }}</template>
                                </a-input-number>
                            </a-form-item>
                        </div>
                    </a-card>

                    <!-- Availability & Settings -->
                    <a-card title="Inventory & Sales" class="shadow-sm border-none">
                        <div class="grid grid-cols-2 gap-4">
                            <a-form-item label="Total Quantity">
                                <a-input-number v-model:value="form.quantity_total" class="w-full" :min="1"
                                    placeholder="Unlimited" />
                            </a-form-item>

                            <a-form-item label="Display Order">
                                <a-input-number v-model:value="form.display_order" class="w-full" />
                            </a-form-item>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <a-form-item label="Min Per Order">
                                <a-input-number v-model:value="form.min_per_order" class="w-full" :min="1" />
                            </a-form-item>
                            <a-form-item label="Max Per Order">
                                <a-input-number v-model:value="form.max_per_order" class="w-full" :min="1" />
                            </a-form-item>
                        </div>

                        <a-form-item label="Sales Period">
                            <div class="flex flex-col gap-2">
                                <a-date-picker v-model:value="form.sale_start" show-time placeholder="Starts"
                                    class="w-full" value-format="YYYY-MM-DD HH:mm:ss" />
                                <a-date-picker v-model:value="form.sale_end" show-time placeholder="Ends" class="w-full"
                                    value-format="YYYY-MM-DD HH:mm:ss" />
                            </div>
                        </a-form-item>
                    </a-card>
                </div>

                <a-card class="mt-6 shadow-sm border-none">
                    <div class="flex gap-4 items-center">
                        <a-form-item class="mb-0">
                            <a-checkbox v-model:checked="form.is_visible">Visible to Public</a-checkbox>
                        </a-form-item>
                        <a-form-item class="mb-0">
                            <a-checkbox v-model:checked="form.requires_approval">Require Approval</a-checkbox>
                        </a-form-item>
                        <a-form-item class="mb-0">
                            <a-checkbox v-model:checked="form.absorb_fees">Absorb Service Fees</a-checkbox>
                        </a-form-item>
                    </div>
                </a-card>

                <div class="mt-8 flex justify-end gap-4">
                    <a-button size="large" @click="router.visit('/dashboard/ticket-types')">Cancel</a-button>
                    <a-button type="primary" size="large" :loading="form.processing" html-type="submit">
                        Create Ticket Type
                    </a-button>
                </div>
            </a-form>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { reactive } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { ArrowLeftOutlined } from '@ant-design/icons-vue';
import { message } from 'ant-design-vue';

const props = defineProps({
    events: Array
});

const form = useForm({
    event_id: null,
    name: '',
    description: '',
    type: 'paid',
    price: 0,
    currency: 'USD',
    quantity_total: null,
    min_per_order: 1,
    max_per_order: 10,
    sale_start: null,
    sale_end: null,
    is_visible: true,
    requires_approval: false,
    absorb_fees: false,
    display_order: 0
});

const submit = () => {
    form.post('/dashboard/ticket-types', {
        onSuccess: () => {
            message.success('Ticket type created successfully');
        },
        onError: (errors) => {
            message.error('Please check the form for errors');
        }
    });
};
</script>
