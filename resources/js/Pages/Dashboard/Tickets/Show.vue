<template>

    <Head :title="`Ticket ${ticket.ticket_number}`" />
    <DashboardLayout>
        <div class="breadcrumb-wrapper">
            <Breadcrumb :items="breadcrumbItems" />
        </div>

        <div class="ticket-show-container">
            <a-row :gutter="24">
                <!-- Ticket Card -->
                <a-col :xs="24" :lg="8">
                    <a-card class="ticket-visual-card shadow-lg" :bordered="false">
                        <div class="ticket-status-overlay">
                            <a-tag :color="getStatusColor(ticket.status)" class="status-badge">{{ ticket.status
                                }}</a-tag>
                        </div>

                        <div class="ticket-qr-section">
                            <div class="qr-placeholder">
                                <QrcodeOutlined style="font-size: 64px; opacity: 0.2" />
                                <p class="mt-2 text-xs text-muted">Ticket #{{ ticket.ticket_number }}</p>
                            </div>
                        </div>

                        <div class="ticket-header-info">
                            <h2 class="event-name">{{ ticket.event?.name }}</h2>
                            <p class="ticket-type-name uppercase tracking-widest">{{ ticket.ticket_type?.name }}</p>
                        </div>

                        <a-divider />

                        <div class="ticket-attendee-info">
                            <div class="info-row">
                                <span class="label">Attendee</span>
                                <span class="value">{{ ticket.attendee_full_name || ticket.user?.name }}</span>
                            </div>
                            <div class="info-row">
                                <span class="label">Email</span>
                                <span class="value">{{ ticket.attendee_email || ticket.user?.email }}</span>
                            </div>
                            <div class="info-row" v-if="ticket.checked_in">
                                <span class="label">Checked In</span>
                                <span class="value text-success">
                                    <div class="flex flex-col items-end">
                                        <span>Yes</span>
                                        <span class="text-[10px] text-muted">{{ ticket.checked_in_at }}</span>
                                    </div>
                                </span>
                            </div>
                        </div>

                        <div class="ticket-actions mt-6">
                            <a-button type="primary" block @click="handleCheckIn" v-if="!ticket.checked_in">
                                Check-in Now
                            </a-button>
                            <a-button block @click="handleEdit" class="mt-2">
                                Edit Details
                            </a-button>
                        </div>
                    </a-card>
                </a-col>

                <!-- Ticket Details & Timeline -->
                <a-col :xs="24" :lg="16">
                    <a-card title="Ticket Details" class="details-card">
                        <template #extra>
                            <a-button @click="router.visit('/dashboard/tickets')">Back to List</a-button>
                        </template>

                        <a-descriptions bordered :column="{ md: 2, sm: 1 }">
                            <a-descriptions-item label="Order ID">
                                <a-button type="link" size="small" v-if="ticket.order_id">#{{ ticket.order_id
                                    }}</a-button>
                                <span v-else class="text-muted">Direct Issue</span>
                            </a-descriptions-item>
                            <a-descriptions-item label="Purchase Date">{{ ticket.created_at }}</a-descriptions-item>
                            <a-descriptions-item label="Price">{{ ticket.price_formatted || 'Free'
                                }}</a-descriptions-item>
                            <a-descriptions-item label="Currency">{{ ticket.currency }}</a-descriptions-item>
                            <a-descriptions-item label="Checked In By" v-if="ticket.checked_in">
                                {{ ticket.checked_in_by_user?.name || 'Scanner System' }}
                            </a-descriptions-item>
                            <a-descriptions-item label="Last Updated">{{ ticket.updated_at }}</a-descriptions-item>
                        </a-descriptions>

                        <div class="mt-8">
                            <h3 class="text-lg font-semibold mb-4">Activity History</h3>
                            <!-- Placeholder for activities -->
                            <a-empty description="Activity log coming soon" v-if="!activities?.length" />
                        </div>
                    </a-card>
                </a-col>
            </a-row>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import {
    QrcodeOutlined,
    CheckCircleOutlined,
    HistoryOutlined
} from '@ant-design/icons-vue';
import { message } from 'ant-design-vue';

const props = defineProps({
    ticket: Object,
    activities: Array
});

const breadcrumbItems = ref([
    { label: 'Dashboard', href: '/dashboard' },
    { label: 'Tickets', href: '/dashboard/tickets' },
    { label: 'Ticket Details' },
]);

const getStatusColor = (status) => {
    switch (status) {
        case 'active': return 'blue';
        case 'used': return 'green';
        case 'cancelled': return 'red';
        default: return 'default';
    }
};

const handleCheckIn = () => {
    router.post(`/dashboard/tickets/${props.ticket.id}/check-in`, {}, {
        onSuccess: () => message.success('Attendee checked in successfully')
    });
};

const handleEdit = () => {
    router.visit(`/dashboard/tickets/${props.ticket.id}/edit`);
};
</script>

<style scoped>
.breadcrumb-wrapper {
    margin-bottom: 24px;
}

.ticket-visual-card {
    position: relative;
    overflow: hidden;
    background: linear-gradient(135deg, #ffffff 0%, #f9f9f9 100%);
    border-radius: 16px;
}

[data-theme="dark"] .ticket-visual-card {
    background: linear-gradient(135deg, #1f1f1f 0%, #141414 100%);
}

.ticket-status-overlay {
    position: absolute;
    top: 16px;
    right: 16px;
}

.ticket-qr-section {
    display: flex;
    justify-content: center;
    padding: 40px 0;
}

.qr-placeholder {
    width: 180px;
    height: 180px;
    background: white;
    border-radius: 12px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    border: 1px dashed #ddd;
}

[data-theme="dark"] .qr-placeholder {
    background: #262626;
    border-color: #434343;
}

.ticket-header-info {
    text-align: center;
}

.event-name {
    font-size: 20px;
    font-weight: 700;
    margin-bottom: 4px;
}

.ticket-type-name {
    font-size: 12px;
    color: var(--ant-primary-color);
}

.info-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 12px;
}

.label {
    color: var(--text-tertiary, #8c8c8c);
    font-size: 12px;
}

.value {
    font-weight: 600;
    font-size: 14px;
}

.details-card {
    border-radius: 16px;
}
</style>
