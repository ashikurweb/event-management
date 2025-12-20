<template>

    <Head title="Issue Manual Ticket" />
    <DashboardLayout>
        <div class="breadcrumb-wrapper">
            <Breadcrumb :items="breadcrumbItems" />
        </div>

        <a-card class="ticket-create-card">
            <template #title>
                <div class="card-header">
                    <h2 class="card-title">Issue Manual Ticket</h2>
                    <a-button @click="router.visit('/dashboard/tickets')">
                        <template #icon>
                            <ArrowLeftOutlined />
                        </template>
                        Back to List
                    </a-button>
                </div>
            </template>

            <a-form layout="vertical" :model="form" @finish="submit">
                <a-row :gutter="24">
                    <!-- Selection Column -->
                    <a-col :xs="24" :md="12">
                        <a-card title="Event & Type" class="form-section-card">
                            <a-form-item label="Target Event" required
                                :validate-status="form.errors.event_id ? 'error' : ''" :help="form.errors.event_id">
                                <a-select v-model:value="form.event_id" placeholder="Choose an event" show-search
                                    :options="events.map(e => ({ value: e.id, label: e.title }))"
                                    @change="handleEventChange" />
                            </a-form-item>

                            <a-form-item label="Ticket Category" required
                                :validate-status="form.errors.ticket_type_id ? 'error' : ''"
                                :help="form.errors.ticket_type_id">
                                <a-select v-model:value="form.ticket_type_id" placeholder="Select ticket type"
                                    :disabled="!form.event_id"
                                    :options="filteredTicketTypes.map(t => ({ value: t.id, label: t.name }))" />
                            </a-form-item>

                            <a-form-item label="User Account" required
                                :validate-status="form.errors.user_id ? 'error' : ''" :help="form.errors.user_id">
                                <a-select v-model:value="form.user_id" placeholder="Assigned to user" show-search
                                    :options="users.map(u => ({ value: u.id, label: u.first_name + ' ' + u.last_name }))" />
                            </a-form-item>
                        </a-card>
                    </a-col>

                    <!-- Attendee Details -->
                    <a-col :xs="24" :md="12">
                        <a-card title="Attendee Information" class="form-section-card">
                            <a-row :gutter="16">
                                <a-col :span="12">
                                    <a-form-item label="First Name">
                                        <a-input v-model:value="form.attendee_first_name" placeholder="John" />
                                    </a-form-item>
                                </a-col>
                                <a-col :span="12">
                                    <a-form-item label="Last Name">
                                        <a-input v-model:value="form.attendee_last_name" placeholder="Doe" />
                                    </a-form-item>
                                </a-col>
                            </a-row>

                            <a-form-item label="Email Address">
                                <a-input v-model:value="form.attendee_email" placeholder="john.doe@example.com" />
                            </a-form-item>

                            <a-form-item label="Phone Number">
                                <a-input v-model:value="form.attendee_phone" placeholder="+123456789" />
                            </a-form-item>

                            <a-row :gutter="16">
                                <a-col :span="12">
                                    <a-form-item label="Initial Status">
                                        <a-select v-model:value="form.status">
                                            <a-select-option value="active">Active</a-select-option>
                                            <a-select-option value="used">Used</a-select-option>
                                            <a-select-option value="cancelled">Cancelled</a-select-option>
                                        </a-select>
                                    </a-form-item>
                                </a-col>
                                <a-col :span="12">
                                    <a-form-item label="Check-in Now?" class="flex items-end h-full">
                                        <a-switch v-model:checked="form.checked_in" />
                                    </a-form-item>
                                </a-col>
                            </a-row>
                        </a-card>
                    </a-col>
                </a-row>

                <div class="mt-6 flex justify-end gap-3">
                    <a-button size="large" @click="router.visit('/dashboard/tickets')">Discard</a-button>
                    <a-button type="primary" size="large" :loading="form.processing" html-type="submit">
                        <template #icon>
                            <CheckOutlined />
                        </template>
                        Issue Ticket
                    </a-button>
                </div>
            </a-form>
        </a-card>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import {
    ArrowLeftOutlined,
    CheckOutlined,
    UserOutlined
} from '@ant-design/icons-vue';
import { message } from 'ant-design-vue';

const props = defineProps({
    events: Array,
    ticketTypes: Array,
    users: Array
});

const form = useForm({
    event_id: null,
    ticket_type_id: null,
    user_id: null,
    attendee_first_name: '',
    attendee_last_name: '',
    attendee_email: '',
    attendee_phone: '',
    status: 'active',
    checked_in: false,
});

const breadcrumbItems = ref([
    { label: 'Dashboard', href: '/dashboard' },
    { label: 'Tickets', href: '/dashboard/tickets' },
    { label: 'Issue Manual' },
]);

const filteredTicketTypes = computed(() => {
    if (!form.event_id) return [];
    return props.ticketTypes.filter(t => t.event_id === form.event_id);
});

const handleEventChange = () => {
    form.ticket_type_id = null;
};

const submit = () => {
    form.post('/dashboard/tickets', {
        onSuccess: () => message.success('Ticket issued successfully'),
        onError: () => message.error('Failed to issue ticket. Please check your input.'),
    });
};
</script>

<style scoped>
.breadcrumb-wrapper {
    margin-bottom: 16px;
}

.ticket-create-card {
    border-radius: 8px;
    box-shadow: var(--card-shadow, 0 2px 8px rgba(0, 0, 0, 0.06));
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-title {
    margin: 0;
    font-size: 20px;
    font-weight: 600;
    color: var(--text-primary, #262626);
}

[data-theme="dark"] .card-title {
    color: rgba(255, 255, 255, 0.85);
}

.form-section-card {
    border: 1px solid var(--border-color, #f0f0f0);
    background: var(--component-background, #fff);
    margin-bottom: 24px;
}

[data-theme="dark"] .form-section-card {
    border-color: #303030;
    background: #1f1f1f;
}

:deep(.ant-card-head-title) {
    font-size: 16px;
    font-weight: 600;
}
</style>
