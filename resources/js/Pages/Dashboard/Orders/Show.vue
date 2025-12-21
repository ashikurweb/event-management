<template>
    <DashboardLayout>
        <div class="breadcrumb-wrapper">
            <Breadcrumb :items="breadcrumbItems" />
            <div class="breadcrumb-actions">
                <a-button @click="handlePrint">
                    <template #icon>
                        <PrinterOutlined />
                    </template>
                    Print Invoice
                </a-button>
                <a-button type="primary" ghost @click="$router.visit('/dashboard/orders')">
                    Back to Orders
                </a-button>
            </div>
        </div>

        <div class="order-detail-container">
            <a-row :gutter="24">
                <!-- Left Column: Order Summary & Items -->
                <a-col :xs="24" :lg="16">
                    <a-card class="detail-card glass-card mb-6">
                        <template #title>
                            <div class="flex justify-between items-center">
                                <span>Order Items</span>
                                <a-tag :color="getStatusColor(order.data.status)">
                                    {{ order.data.status.toUpperCase() }}
                                </a-tag>
                            </div>
                        </template>

                        <a-table :columns="itemColumns" :data-source="order.data.items" :pagination="false"
                            row-key="id">
                            <template #bodyCell="{ column, record }">
                                <template v-if="column.key === 'ticket_type'">
                                    <div class="ticket-type-info">
                                        <div class="font-bold">{{ record.ticket_type.name }}</div>
                                        <div class="text-xs text-muted">Ticket ID: {{ record.id }}</div>
                                    </div>
                                </template>
                                <template v-if="column.key === 'unit_price'">
                                    {{ order.data.currency }} {{ record.unit_price }}
                                </template>
                                <template v-if="column.key === 'total'">
                                    <span class="font-bold">{{ order.data.currency }} {{ record.total_amount }}</span>
                                </template>
                            </template>
                        </a-table>

                        <div class="order-summary-footer mt-6">
                            <a-row justify="end">
                                <a-col :span="12">
                                    <div class="summary-line">
                                        <span>Subtotal</span>
                                        <span>{{ order.data.currency }} {{ order.data.subtotal }}</span>
                                    </div>
                                    <div class="summary-line" v-if="order.data.tax_amount > 0">
                                        <span>Tax</span>
                                        <span>{{ order.data.currency }} {{ order.data.tax_amount }}</span>
                                    </div>
                                    <div class="summary-line" v-if="order.data.service_fee > 0">
                                        <span>Service Fee</span>
                                        <span>{{ order.data.currency }} {{ order.data.service_fee }}</span>
                                    </div>
                                    <div class="summary-line discount" v-if="order.data.discount_amount > 0">
                                        <span>Discount</span>
                                        <span>-{{ order.data.currency }} {{ order.data.discount_amount }}</span>
                                    </div>
                                    <a-divider />
                                    <div class="summary-line total">
                                        <span>Total Amount</span>
                                        <span>{{ order.data.currency }} {{ order.data.total_amount }}</span>
                                    </div>
                                </a-col>
                            </a-row>
                        </div>
                    </a-card>

                    <!-- Payment History -->
                    <a-card class="detail-card glass-card" title="Payment Transactions">
                        <a-table :columns="paymentColumns" :data-source="order.data.payments" :pagination="false"
                            row-key="id">
                            <template #bodyCell="{ column, record }">
                                <template v-if="column.key === 'status'">
                                    <a-tag :color="record.status === 'completed' ? 'green' : 'orange'">
                                        {{ record.status.toUpperCase() }}
                                    </a-tag>
                                </template>
                            </template>
                        </a-table>
                    </a-card>
                </a-col>

                <!-- Right Column: Buyer & Event Info -->
                <a-col :xs="24" :lg="8">
                    <!-- Status Actions -->
                    <a-card class="detail-card glass-card mb-6" title="Order Actions">
                        <div class="flex flex-col gap-3">
                            <a-select v-model:value="statusForm.status" class="w-full">
                                <a-select-option value="pending">Pending</a-select-option>
                                <a-select-option value="completed">Completed</a-select-option>
                                <a-select-option value="cancelled">Cancelled</a-select-option>
                                <a-select-option value="refunded">Refunded</a-select-option>
                            </a-select>
                            <a-button type="primary" block @click="updateStatus" :loading="statusForm.processing">
                                Update Status
                            </a-button>
                        </div>
                    </a-card>

                    <a-card class="detail-card glass-card mb-6" title="Customer Information">
                        <div class="info-item">
                            <label>Name</label>
                            <div>{{ order.data.buyer.first_name }} {{ order.data.buyer.last_name }}</div>
                        </div>
                        <div class="info-item">
                            <label>Email</label>
                            <div>{{ order.data.buyer.email }}</div>
                        </div>
                        <div class="info-item">
                            <label>Phone</label>
                            <div>{{ order.data.buyer.phone || 'N/A' }}</div>
                        </div>
                        <div class="info-item">
                            <label>User Account</label>
                            <div v-if="order.data.user.id">
                                <a-tag color="blue">Registered User</a-tag>
                            </div>
                            <div v-else><a-tag>Guest</a-tag></div>
                        </div>
                    </a-card>

                    <a-card class="detail-card glass-card" title="Event Details">
                        <div class="info-item">
                            <label>Event Name</label>
                            <div class="font-bold">{{ order.data.event.title }}</div>
                        </div>
                        <div class="info-item">
                            <label>Order Date</label>
                            <div>{{ order.data.created_at }}</div>
                        </div>
                        <div class="info-item" v-if="order.data.promo_code">
                            <label>Promo Code</label>
                            <div><a-tag color="purple">{{ order.data.promo_code.code }}</a-tag></div>
                        </div>
                    </a-card>
                </a-col>
            </a-row>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { useForm, router } from '@inertiajs/vue3';
import DashboardLayout from '../../../Layouts/DashboardLayout.vue';
import Breadcrumb from '../../../Components/Breadcrumb.vue';
import {
    PrinterOutlined,
    ArrowLeftOutlined
} from '@ant-design/icons-vue';
import { message } from 'ant-design-vue';

const props = defineProps({
    order: Object
});

const breadcrumbItems = [
    { label: 'Dashboard', href: '/dashboard' },
    { label: 'Orders', href: '/dashboard/orders' },
    { label: `Order ${props.order.data.order_number}` },
];

const itemColumns = [
    { title: 'Ticket Type', key: 'ticket_type' },
    { title: 'Qty', dataIndex: 'quantity', key: 'quantity', align: 'center' },
    { title: 'Price', key: 'unit_price', align: 'right' },
    { title: 'Total', key: 'total', align: 'right' },
];

const paymentColumns = [
    { title: 'Transaction ID', dataIndex: 'transaction_id', key: 'transaction_id' },
    { title: 'Method', dataIndex: 'payment_method', key: 'payment_method' },
    { title: 'Amount', dataIndex: 'amount', key: 'amount' },
    { title: 'Status', key: 'status', align: 'center' },
    { title: 'Date', dataIndex: 'created_at', key: 'date' },
];

const statusForm = useForm({
    status: props.order.data.status
});

const getStatusColor = (status) => {
    const colors = {
        pending: 'orange',
        completed: 'green',
        failed: 'red',
        cancelled: 'default',
        refunded: 'purple',
    };
    return colors[status] || 'default';
};

const updateStatus = () => {
    statusForm.put(`/dashboard/orders/${props.order.data.id}/status`, {
        onSuccess: () => message.success('Order status updated'),
        onError: () => message.error('Failed to update status'),
    });
};

const handlePrint = () => {
    window.print();
};
</script>

<style scoped>
.order-detail-container {
    padding-top: 12px;
}

.glass-card {
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 16px;
    box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07);
}

[data-theme="dark"] .glass-card {
    background: rgba(20, 20, 20, 0.7);
    border-color: rgba(255, 255, 255, 0.05);
}

.mb-6 {
    margin-bottom: 24px;
}

.info-item {
    margin-bottom: 16px;
}

.info-item label {
    display: block;
    font-size: 0.75rem;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 4px;
}

.summary-line {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
    font-size: 0.95rem;
}

.summary-line.total {
    font-size: 1.25rem;
    font-weight: 800;
    color: var(--primary-color);
}

.summary-line.discount span:last-child {
    color: #f5222d;
}

.text-truncate {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

@media print {

    .breadcrumb-wrapper,
    .breadcrumb-actions,
    .ant-btn {
        display: none !important;
    }
}
</style>
