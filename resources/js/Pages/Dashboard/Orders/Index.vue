<template>
    <DashboardLayout>
        <div class="breadcrumb-wrapper">
            <Breadcrumb :items="breadcrumbItems" />
            <div class="breadcrumb-actions">
                <a-button @click="handleRefresh" title="Refresh">
                    <template #icon>
                        <ReloadOutlined />
                    </template>
                </a-button>
                <a-button @click="handleExportPDF" title="Export PDF">
                    <template #icon>
                        <FilePdfOutlined />
                    </template>
                </a-button>
            </div>
        </div>

        <a-card class="orders-card">
            <template #title>
                <div class="card-header">
                    <h2 class="card-title">Order Management</h2>
                    <div class="header-stats">
                        <a-space size="large">
                            <div class="stat-item">
                                <span class="stat-label">Total Orders:</span>
                                <span class="stat-value">{{ pagination.total }}</span>
                            </div>
                        </a-space>
                    </div>
                </div>
            </template>

            <!-- Filters -->
            <div class="filters-section">
                <a-row :gutter="16">
                    <a-col :xs="24" :md="12">
                        <Search v-model="filters.search" placeholder="Search order #, name or email..."
                            @search="handleSearch" />
                    </a-col>
                    <a-col :xs="24" :md="12">
                        <DatePicker v-model="dateRange" @change="handleDateChange" />
                    </a-col>
                </a-row>
                <a-row :gutter="16" class="mt-4">
                    <a-col :xs="24" :sm="12" :md="12">
                        <a-select v-model:value="filters.event_id" placeholder="Filter by Event" allow-clear
                            class="w-full" @change="handleSearch" show-search
                            :options="events.map(e => ({ value: e.id, label: e.title }))" />
                    </a-col>
                    <a-col :xs="24" :sm="12" :md="12">
                        <a-select v-model:value="filters.status" placeholder="Filter by Status" allow-clear
                            class="w-full" @change="handleSearch">
                            <a-select-option v-for="status in statuses" :key="status" :value="status">
                                {{ status.charAt(0).toUpperCase() + status.slice(1) }}
                            </a-select-option>
                        </a-select>
                    </a-col>
                </a-row>
            </div>

            <!-- Table -->
            <a-table :columns="columns" :data-source="orders.data" :row-key="record => record.id" :pagination="false"
                :loading="loading" @change="handleTableChange">
                <template #bodyCell="{ column, record }">
                    <template v-if="column.key === 'order_number'">
                        <div class="order-number-cell">
                            <span class="font-bold text-primary">{{ record.order_number }}</span>
                            <span class="text-xs text-muted block">{{ record.created_at }}</span>
                        </div>
                    </template>

                    <template v-if="column.key === 'buyer'">
                        <div class="buyer-cell">
                            <div class="font-medium">{{ record.buyer.first_name }} {{ record.buyer.last_name }}</div>
                            <div class="text-xs text-muted">{{ record.buyer.email }}</div>
                        </div>
                    </template>

                    <template v-if="column.key === 'event'">
                        <span class="text-truncate" style="max-width: 200px; display: block;">
                            {{ record.event.title }}
                        </span>
                    </template>

                    <template v-if="column.key === 'total_amount'">
                        <div class="amount-cell font-bold">
                            {{ record.currency }} {{ record.total_amount }}
                        </div>
                    </template>

                    <template v-if="column.key === 'status'">
                        <a-tag :color="getStatusColor(record.status)">
                            {{ record.status.toUpperCase() }}
                        </a-tag>
                    </template>

                    <template v-if="column.key === 'payment_status'">
                        <a-badge :status="getPaymentStatusType(record.payment_status)" :text="record.payment_status" />
                    </template>

                    <template v-if="column.key === 'actions'">
                        <a-space>
                            <a-tooltip title="View Details">
                                <a-button type="link" @click="handleView(record)">
                                    <template #icon>
                                        <EyeOutlined />
                                    </template>
                                </a-button>
                            </a-tooltip>
                            <a-popconfirm title="Are you sure you want to delete this order?"
                                @confirm="handleDelete(record)">
                                <a-button type="link" danger>
                                    <template #icon>
                                        <DeleteOutlined />
                                    </template>
                                </a-button>
                            </a-popconfirm>
                        </a-space>
                    </template>
                </template>
            </a-table>

            <!-- Modern Pagination -->
            <Pagination :current="pagination.current" :page-size="pagination.pageSize" :total="pagination.total"
                @change="handlePaginationChange" @page-size-change="handlePageSizeChange" />
        </a-card>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import DashboardLayout from '../../../Layouts/DashboardLayout.vue';
import Breadcrumb from '../../../Components/Breadcrumb.vue';
import Pagination from '../../../Components/Pagination.vue';
import Search from '../../../Components/Search.vue';
import DatePicker from '../../../Components/DatePicker.vue';
import {
    ReloadOutlined,
    FilePdfOutlined,
    EyeOutlined,
    DeleteOutlined
} from '@ant-design/icons-vue';
import dayjs from 'dayjs';

const page = usePage();
const orders = computed(() => page.props.orders || { data: [] });
const events = computed(() => page.props.events || []);
const statuses = computed(() => page.props.statuses || []);
const initialFilters = page.props.filters || {};

const filters = ref({
    search: initialFilters.search || '',
    status: initialFilters.status || null,
    event_id: initialFilters.event_id || null,
    date_from: initialFilters.date_from || null,
    date_to: initialFilters.date_to || null,
    per_page: initialFilters.per_page || 10,
});

const dateRange = ref(
    initialFilters.date_from && initialFilters.date_to
        ? [dayjs(initialFilters.date_from), dayjs(initialFilters.date_to)]
        : null
);

const loading = ref(false);

const breadcrumbItems = [
    { label: 'Dashboard', href: '/dashboard' },
    { label: 'Orders' },
];

const columns = [
    { title: 'Order Details', key: 'order_number', sorter: true },
    { title: 'Buyer', key: 'buyer' },
    { title: 'Event', key: 'event' },
    { title: 'Total', key: 'total_amount', align: 'right', sorter: true },
    { title: 'Status', key: 'status', align: 'center' },
    { title: 'Payment', key: 'payment_status', align: 'center' },
    { title: 'Actions', key: 'actions', align: 'right' },
];

const pagination = computed(() => ({
    current: orders.value.meta?.current_page || 1,
    pageSize: filters.value.per_page,
    total: orders.value.meta?.total || 0,
}));

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

const getPaymentStatusType = (status) => {
    const types = {
        paid: 'success',
        unpaid: 'warning',
        partially_refunded: 'processing',
        refunded: 'error',
    };
    return types[status] || 'default';
};

const handleSearch = () => {
    router.get('/dashboard/orders', filters.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const handleDateChange = (dates) => {
    if (dates && dates.length === 2) {
        filters.value.date_from = dates[0].format('YYYY-MM-DD');
        filters.value.date_to = dates[1].format('YYYY-MM-DD');
    } else {
        filters.value.date_from = null;
        filters.value.date_to = null;
    }
    handleSearch();
};

const handleTableChange = (pag, filtersObj, sorter) => {
    // Handle sorting if needed
    handleSearch();
};

const handlePaginationChange = (page) => {
    router.get('/dashboard/orders', { ...filters.value, page }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const handlePageSizeChange = (current, size) => {
    filters.value.per_page = size;
    handleSearch();
};

const handleView = (record) => {
    router.visit(`/dashboard/orders/${record.id}`);
};

const handleDelete = (record) => {
    router.delete(`/dashboard/orders/${record.id}`, {
        preserveScroll: true,
    });
};

const handleRefresh = () => {
    router.visit('/dashboard/orders');
};

const handleExportPDF = () => {
    window.print();
};
</script>

<style scoped>
.breadcrumb-wrapper {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

.breadcrumb-actions {
    display: flex;
    gap: 12px;
}

.orders-card {
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 16px;
    box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07);
}

[data-theme="dark"] .orders-card {
    background: rgba(20, 20, 20, 0.7);
    border-color: rgba(255, 255, 255, 0.05);
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-title {
    font-size: 1.25rem;
    font-weight: 700;
    margin: 0;
    background: linear-gradient(135deg, #1890ff 0%, #096dd9 100%);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
}

.filters-section {
    margin-bottom: 24px;
    padding: 20px;
    background: rgba(255, 255, 255, 0.5);
    border-radius: 12px;
}

[data-theme="dark"] .filters-section {
    background: rgba(255, 255, 255, 0.03);
}

.stat-label {
    font-size: 0.875rem;
    color: var(--text-muted);
    margin-right: 8px;
}

.stat-value {
    font-size: 1rem;
    font-weight: 700;
    color: var(--primary-color);
}

.mt-4 {
    margin-top: 1rem;
}

.w-full {
    width: 100%;
}
</style>
