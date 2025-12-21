<template>
    <DashboardLayout>
        <div class="page-container">
            <!-- Header Section -->
            <div class="page-header">
                <Breadcrumb :items="breadcrumbItems" />
                <div class="action-buttons">
                    <a-tooltip title="Refresh Orders">
                        <a-button @click="handleRefresh" class="header-btn">
                            <template #icon>
                                <ReloadOutlined />
                            </template>
                        </a-button>
                    </a-tooltip>
                    <a-button @click="handleExportPDF" class="header-btn">
                        <template #icon>
                            <FilePdfOutlined />
                        </template>
                        Export PDF
                    </a-button>
                </div>
            </div>

            <!-- Bento Stats Section -->
            <div class="bento-stats">
                <div class="stat-card orders">
                    <div class="stat-icon">
                        <ShoppingCartOutlined />
                    </div>
                    <div class="stat-info">
                        <span class="label">Total Orders</span>
                        <span class="value">{{ pagination.total }}</span>
                    </div>
                </div>
                <div class="stat-card revenue">
                    <div class="stat-icon">
                        <DollarCircleOutlined />
                    </div>
                    <div class="stat-info">
                        <span class="label">Total Revenue</span>
                        <span class="value">{{ totalRevenueFormatted }}</span>
                    </div>
                </div>
                <div class="stat-card pending">
                    <div class="stat-icon">
                        <ClockCircleOutlined />
                    </div>
                    <div class="stat-info">
                        <span class="label">Pending Orders</span>
                        <span class="value text-warning">{{ pendingCount }}</span>
                    </div>
                </div>
                <div class="stat-card completed">
                    <div class="stat-icon">
                        <CheckCircleOutlined />
                    </div>
                    <div class="stat-info">
                        <span class="label">Success Rate</span>
                        <span class="value text-success">{{ successRate }}%</span>
                    </div>
                </div>
            </div>

            <!-- Main Content Card -->
            <div class="main-content glass-card">
                <!-- Advanced Multi-Filter Bar -->
                <div class="filter-bar">
                    <div class="main-filters">
                        <div class="search-box">
                            <Search v-model="filters.search" placeholder="Order ID, buyer name or email..."
                                @search="handleSearch" />
                        </div>
                        <div class="date-box">
                            <DatePicker v-model="dateRange" @change="handleDateChange" />
                        </div>
                    </div>
                    <div class="secondary-filters mt-4">
                        <a-row :gutter="16">
                            <a-col :xs="24" :sm="12">
                                <a-select v-model:value="filters.event_id" placeholder="All Events" allow-clear
                                    class="premium-select" @change="handleSearch" show-search
                                    :options="events.map(e => ({ value: e.id, label: e.title }))" />
                            </a-col>
                            <a-col :xs="24" :sm="12">
                                <a-select v-model:value="filters.status" placeholder="All Statuses" allow-clear
                                    class="premium-select" @change="handleSearch">
                                    <a-select-option v-for="status in statuses" :key="status" :value="status">
                                        {{ status.charAt(0).toUpperCase() + status.slice(1) }}
                                    </a-select-option>
                                </a-select>
                            </a-col>
                        </a-row>
                    </div>
                </div>

                <!-- Floating Order Table -->
                <div class="table-wrapper">
                    <a-table :columns="columns" :data-source="orders.data" :row-key="record => record.id"
                        :pagination="false" :loading="loading" @change="handleTableChange" class="order-strip-table">
                        <template #bodyCell="{ column, record }">
                            <!-- Order ID Cell -->
                            <template v-if="column.key === 'order_number'">
                                <div class="order-stub">
                                    <div class="stub-accent"></div>
                                    <div class="stub-content">
                                        <span class="num">{{ record.order_number }}</span>
                                        <span class="date">{{ record.created_at }}</span>
                                    </div>
                                </div>
                            </template>

                            <!-- Buyer Cell -->
                            <template v-else-if="column.key === 'buyer'">
                                <div class="buyer-profile">
                                    <div class="avatar"
                                        :style="{ background: getAvatarGradient(record.buyer.first_name) }">
                                        {{ record.buyer.first_name[0] }}{{ record.buyer.last_name[0] }}
                                    </div>
                                    <div class="details">
                                        <div class="name">{{ record.buyer.first_name }} {{ record.buyer.last_name }}
                                        </div>
                                        <div class="email">{{ record.buyer.email }}</div>
                                    </div>
                                </div>
                            </template>

                            <!-- Event Cell -->
                            <template v-else-if="column.key === 'event'">
                                <div class="event-info">
                                    <span class="event-title text-truncate" :title="record.event.title">
                                        {{ record.event.title }}
                                    </span>
                                </div>
                            </template>

                            <!-- Amount Cell -->
                            <template v-else-if="column.key === 'total_amount'">
                                <div class="amount-badge">
                                    <span class="currency">{{ record.currency }}</span>
                                    <span class="amount">{{ record.total_amount }}</span>
                                </div>
                            </template>

                            <!-- Status Cell -->
                            <template v-else-if="column.key === 'status'">
                                <div class="status-pill" :class="record.status">
                                    <span class="dot"></span>
                                    {{ record.status.toUpperCase() }}
                                </div>
                            </template>

                            <!-- Payment Cell -->
                            <template v-else-if="column.key === 'payment_status'">
                                <div class="payment-tag" :class="record.payment_status">
                                    {{ record.payment_status.toUpperCase() }}
                                </div>
                            </template>

                            <!-- Actions Cell -->
                            <template v-else-if="column.key === 'actions'">
                                <div class="action-hub">
                                    <a-tooltip title="View Order">
                                        <a-button type="text" shape="circle" @click="handleView(record)">
                                            <template #icon>
                                                <EyeOutlined />
                                            </template>
                                        </a-button>
                                    </a-tooltip>
                                    <a-popconfirm title="Delete this record?" @confirm="handleDelete(record)">
                                        <a-button type="text" shape="circle" danger>
                                            <template #icon>
                                                <DeleteOutlined />
                                            </template>
                                        </a-button>
                                    </a-popconfirm>
                                </div>
                            </template>
                        </template>
                    </a-table>
                </div>

                <!-- Footer Pagination -->
                <div class="table-footer">
                    <Pagination :current="pagination.current" :page-size="pagination.pageSize" :total="pagination.total"
                        @change="handlePaginationChange" @page-size-change="handlePageSizeChange" />
                </div>
            </div>
        </div>
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
    DeleteOutlined,
    ShoppingCartOutlined,
    DollarCircleOutlined,
    ClockCircleOutlined,
    CheckCircleOutlined
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
    { label: 'Admin', href: '/dashboard' },
    { label: 'Orders' },
];

const columns = [
    { title: 'Reference', key: 'order_number', width: 160 },
    { title: 'Buyer', key: 'buyer' },
    { title: 'Event', key: 'event' },
    { title: 'Total', key: 'total_amount', align: 'right' },
    { title: 'Status', key: 'status', align: 'center' },
    { title: 'Payment', key: 'payment_status', align: 'center' },
    { title: '', key: 'actions', align: 'right', width: 100 },
];

const pagination = computed(() => ({
    current: orders.value.meta?.current_page || 1,
    pageSize: filters.value.per_page,
    total: orders.value.meta?.total || 0,
}));

// Stats Calculation
const totalRevenueFormatted = computed(() => {
    const total = orders.value.data.reduce((acc, order) => acc + parseFloat(order.total_amount || 0), 0);
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(total);
});

const pendingCount = computed(() => orders.value.data.filter(o => o.status === 'pending').length);
const successRate = computed(() => {
    if (orders.value.data.length === 0) return 100;
    const completed = orders.value.data.filter(o => o.status === 'completed').length;
    return Math.round((completed / orders.value.data.length) * 100);
});

const getAvatarGradient = (name) => {
    const colors = [
        'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
        'linear-gradient(135deg, #ff9a9e 0%, #fecfef 99%, #fecfef 100%)',
        'linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%)',
        'linear-gradient(135deg, #fccb90 0%, #d57eeb 100%)'
    ];
    return colors[(name?.length || 0) % colors.length];
};

const handleSearch = () => {
    router.get('/dashboard/orders', filters.value, { preserveState: true, preserveScroll: true });
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

const handleTableChange = () => handleSearch();

const handlePaginationChange = (page) => {
    router.get('/dashboard/orders', { ...filters.value, page }, { preserveState: true, preserveScroll: true });
};

const handlePageSizeChange = (current, size) => {
    filters.value.per_page = size;
    handleSearch();
};

const handleView = (record) => router.visit(`/dashboard/orders/${record.id}`);
const handleDelete = (record) => router.delete(`/dashboard/orders/${record.id}`, { preserveScroll: true });
const handleRefresh = () => router.visit('/dashboard/orders');
const handleExportPDF = () => window.print();
</script>

<style scoped>
.page-container {
    padding: 12px;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

.action-buttons {
    display: flex;
    gap: 8px;
}

.header-btn {
    border-radius: 8px;
    height: 40px;
    background: var(--card-bg);
}

/* Bento Stats Section */
.bento-stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}

.stat-card {
    background: var(--card-bg);
    border: 1px solid var(--card-border);
    border-radius: 16px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    transition: transform 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-md);
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
}

.orders .stat-icon {
    background: rgba(24, 144, 255, 0.1);
    color: #1890ff;
}

.revenue .stat-icon {
    background: rgba(82, 196, 26, 0.1);
    color: #52c41a;
}

.pending .stat-icon {
    background: rgba(250, 173, 20, 0.1);
    color: #faad14;
}

.completed .stat-icon {
    background: rgba(114, 46, 209, 0.1);
    color: #722ed1;
}

.stat-info .label {
    display: block;
    font-size: 0.8rem;
    color: var(--text-muted);
    font-weight: 500;
}

.stat-info .value {
    font-size: 1.4rem;
    font-weight: 700;
    color: var(--text-primary);
}

.text-warning {
    color: #faad14;
}

.text-success {
    color: #52c41a;
}

/* Main Content Styles */
.main-content {
    border-radius: 20px;
    padding: 0;
    overflow: hidden;
}

.filter-bar {
    padding: 24px;
    border-bottom: 1px solid var(--border-color-light);
}

.main-filters {
    display: flex;
    gap: 16px;
}

.search-box {
    flex: 1;
}

.date-box {
    width: 300px;
}

.premium-select {
    width: 100%;
}

.premium-select :deep(.ant-select-selector) {
    border-radius: 8px !important;
    height: 40px !important;
}

/* Order Strip Table */
.table-wrapper {
    padding: 8px;
}

.order-strip-table :deep(.ant-table-content) {
    padding: 8px;
}

.order-strip-table :deep(.ant-table-cell) {
    padding: 16px !important;
    border: none !important;
}

.order-stub {
    display: flex;
    align-items: center;
    gap: 12px;
}

.stub-accent {
    width: 4px;
    height: 32px;
    background: #1890ff;
    border-radius: 2px;
}

.stub-content {
    display: flex;
    flex-direction: column;
}

.stub-content .num {
    font-weight: 800;
    color: var(--text-primary);
    font-family: 'Monaco', monospace;
}

.stub-content .date {
    font-size: 0.7rem;
    color: var(--text-muted);
}

.buyer-profile {
    display: flex;
    align-items: center;
    gap: 12px;
}

.avatar {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 700;
    font-size: 0.85rem;
}

.buyer-profile .name {
    font-weight: 600;
    color: var(--text-primary);
}

.buyer-profile .email {
    font-size: 0.75rem;
    color: var(--text-muted);
}

.event-info .event-title {
    font-size: 0.85rem;
    font-weight: 500;
    color: var(--text-primary);
}

.amount-badge {
    background: var(--bg-secondary);
    padding: 6px 12px;
    border-radius: 8px;
    display: inline-flex;
    gap: 4px;
    font-weight: 800;
}

.amount-badge .currency {
    color: #1890ff;
    font-size: 0.75rem;
}

.amount-badge .amount {
    color: var(--text-primary);
}

.status-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 0.7rem;
    font-weight: 800;
}

.status-pill.completed {
    background: #f6ffed;
    color: #52c41a;
}

.status-pill.pending {
    background: #fff7e6;
    color: #faad14;
}

.status-pill.failed {
    background: #fff1f0;
    color: #f5222d;
}

.status-pill.refunded {
    background: #f9f0ff;
    color: #722ed1;
}

.status-pill .dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: currentColor;
}

.payment-tag {
    font-size: 0.65rem;
    font-weight: 800;
    padding: 2px 8px;
    border-radius: 4px;
    background: #f0f0f0;
    color: #595959;
}

.payment-tag.paid {
    background: #52c41a;
    color: white;
}

.payment-tag.unpaid {
    background: #faad14;
    color: white;
}

.action-hub {
    display: flex;
    gap: 4px;
    justify-content: flex-end;
}

.table-footer {
    padding: 16px 24px;
    border-top: 1px solid var(--border-color-light);
}

.text-truncate {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 180px;
    display: block;
}

@media (max-width: 1200px) {
    .bento-stats {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .bento-stats {
        grid-template-columns: 1fr;
    }

    .main-filters {
        flex-direction: column;
    }

    .date-box {
        width: 100%;
    }
}
</style>
