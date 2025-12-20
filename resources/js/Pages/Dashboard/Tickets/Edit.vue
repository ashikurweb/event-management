<template>

    <Head title="Edit Ticket" />
    <DashboardLayout>
        <div class="breadcrumb-wrapper">
            <Breadcrumb :items="breadcrumbItems" />
        </div>

        <a-card class="ticket-edit-card">
            <template #title>
                <div class="card-header">
                    <div class="flex items-center gap-4">
                        <h2 class="card-title">Edit Ticket #{{ ticket.ticket_number }}</h2>
                        <a-tag :color="getStatusColor(ticket.status)">{{ ticket.status }}</a-tag>
                    </div>
                    <a-button @click="router.visit('/dashboard/tickets')">
                        <template #icon>
                            <ArrowLeftOutlined />
                        </template>
                        Back
                    </a-button>
                </div>
            </template>

            <a-form layout="vertical" :model="form" @finish="submit">
                <a-row :gutter="24">
                    <!-- Selection Column -->
                    <a-col :xs="24" :md="12">
                        <a-card title="Ticket Configuration" class="form-section-card">
                            <a-form-item label="Event" required>
                                <a-select v-model:value="form.event_id" disabled
                                    :options="events.map(e => ({ value: e.id, label: e.title }))" />
                            </a-form-item>

                            <a-form-item label="Ticket Type" required>
                                <a-select v-model:value="form.ticket_type_id"
                                    :options="ticketTypes.filter(t => t.event_id === form.event_id).map(t => ({ value: t.id, label: t.name }))" />
                            </a-form-item>

                            <a-form-item label="Owner" required>
                                <a-select v-model:value="form.user_id" show-search
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
                                        <a-input v-model:value="form.attendee_first_name" />
                                    </a-form-item>
                                </a-col>
                                <a-col :span="12">
                                    <a-form-item label="Last Name">
                                        <a-input v-model:value="form.attendee_last_name" />
                                    </a-form-item>
                                </a-col>
                            </a-row>

                            <a-form-item label="Email">
                                <a-input v-model:value="form.attendee_email" />
                            </a-form-item>

                            <a-form-item label="Phone">
                                <a-input v-model:value="form.attendee_phone" />
                            </a-form-item>

                            <a-row :gutter="16">
                                <a-col :span="12">
                                    <a-form-item label="Current Status">
                                        <a-select v-model:value="form.status">
                                            <a-select-option value="active">Active</a-select-option>
                                            <a-select-option value="used">Used</a-select-option>
                                            <a-select-option value="cancelled">Cancelled</a-select-option>
                                            <a-select-option value="transferred">Transferred</a-select-option>
                                        </a-select>
                                    </a-form-item>
                                </a-col>
                                <a-col :span="12">
                                    <a-form-item label="Check-in Status" class="flex items-end h-full">
                                        <a-switch v-model:checked="form.checked_in" />
                                        <span class="ml-2 text-xs text-muted" v-if="ticket.checked_in_at">Checked {{
                                            ticket.checked_in_at }}</span>
                                    </a-form-item>
                                </a-col>
                            </a-row>
                        </a-card>
                    </a-col>
                </a-row>

                <div class="mt-6 flex justify-end gap-3">
                    <a-button size="large" @click="router.visit('/dashboard/tickets')">Cancel</a-button>
                    <a-button type="primary" size="large" :loading="form.processing" html-type="submit">
                        <template #icon>
                            <SaveOutlined />
                        </template>
                        Update Ticket
                    </a-button>
                </div>
            </a-form>
        </a-card>
    </DashboardLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import { ArrowLeftOutlined, SaveOutlined } from '@ant-design/icons-vue';
import { message } from 'ant-design-vue';

const props = defineProps({
    ticket: Object,
    events: Array,
    ticketTypes: Array,
    users: Array
});

const form = useForm({
    event_id: props.ticket.event_id,
    ticket_type_id: props.ticket.ticket_type_id,
    user_id: props.ticket.user_id,
    attendee_first_name: props.ticket.attendee_first_name || '',
    attendee_last_name: props.ticket.attendee_last_name || '',
    attendee_email: props.ticket.attendee_email || '',
    attendee_phone: props.ticket.attendee_phone || '',
    status: props.ticket.status,
    checked_in: props.ticket.checked_in,
});

const breadcrumbItems = ref([
    { label: 'Dashboard', href: '/dashboard' },
    { label: 'Tickets', href: '/dashboard/tickets' },
    { label: 'Edit Ticket' },
]);

const getStatusColor = (status) => {
    switch (status) {
        case 'active': return 'blue';
        case 'used': return 'green';
        case 'cancelled': return 'red';
        default: return 'default';
    }
};

const submit = () => {
    form.put(`/dashboard/tickets/${props.ticket.id}`, {
        onSuccess: () => message.success('Ticket updated successfully'),
        onError: () => message.error('Update failed. Please check the form.'),
    });
};
</script>

<style scoped>
.breadcrumb-wrapper {
    margin-bottom: 16px;
}

.ticket-edit-card {
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
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
}

.form-section-card {
    border: 1px solid var(--border-color, #f0f0f0);
    margin-bottom: 24px;
}

.text-muted {
    color: var(--text-tertiary, #8c8c8c);
}
</style>
