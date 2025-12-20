<template>
    <DashboardLayout>
        <div class="page-container">
            <!-- Header section with Breadcrumb and Actions -->
            <div class="page-header">
                <Breadcrumb :items="breadcrumbItems" />
                <div class="action-buttons">
                    <a-tooltip title="Reload Data">
                        <a-button @click="handleRefresh" class="header-btn">
                            <template #icon>
                                <ReloadOutlined />
                            </template>
                        </a-button>
                    </a-tooltip>
                    <a-dropdown>
                        <a-button class="header-btn">
                            <template #icon>
                                <DownloadOutlined />
                            </template>
                            Export
                        </a-button>
                        <template #overlay>
                            <a-menu>
                                <a-menu-item key="pdf" @click="handleExportPDF">PDF Document</a-menu-item>
                                <a-menu-item key="excel" @click="handleExportExcel">Excel Sheet</a-menu-item>
                            </a-menu>
                        </template>
                    </a-dropdown>
                </div>
            </div>

            <!-- Bento Stats Section -->
            <div class="bento-stats">
                <div class="stat-card total">
                    <div class="stat-icon">
                        <IdcardOutlined />
                    </div>
                    <div class="stat-info">
                        <span class="label">Total Tickets</span>
                        <span class="value">{{ pagination.total }}</span>
                    </div>
                </div>
                <div class="stat-card active">
                    <div class="stat-icon">
                        <ThunderboltFilled />
                    </div>
                    <div class="stat-info">
                        <span class="label">Active</span>
                        <span class="value">{{ activeCount }}</span>
                    </div>
                </div>
                <div class="stat-card checkin">
                    <div class="stat-icon">
                        <CheckCircleFilled />
                    </div>
                    <div class="stat-info">
                        <span class="label">Checked In</span>
                        <span class="value">{{ checkedInCount }}</span>
                    </div>
                </div>
                <div class="stat-card action-box">
                    <a-button type="primary" block @click="handleCreate" class="bento-btn primary">
                        <PlusOutlined /> Manual Issue
                    </a-button>
                    <a-button block class="bento-btn scan">
                        <QrcodeOutlined /> Scan Check-in
                    </a-button>
                </div>
            </div>

            <!-- Main Content Card -->
            <div class="main-content glass-card">
                <!-- Filters Bar -->
                <div class="filter-bar">
                    <div class="search-box">
                        <Search v-model="filters.search" placeholder="Search by ticket ID, attendee, or email..."
                            @search="handleSearch" />
                    </div>
                    <div class="date-box">
                        <DatePicker v-model="dateRange" @change="handleDateChange" />
                    </div>
                </div>

                <!-- Bulk Actions Tray -->
                <transition name="tray">
                    <div v-if="selectedRowKeys.length > 0" class="bulk-tray">
                        <div class="tray-content">
                            <span class="count-tag">{{ selectedRowKeys.length }}</span>
                            <span class="tray-text">Tickets selected for bulk operations</span>
                            <a-divider type="vertical" />
                            <a-space>
                                <a-button size="small" @click="handleBulkAction('check-in')">Quick Check-in</a-button>
                                <a-button size="small" @click="handleBulkAction('cancel')">Cancel Selection</a-button>
                                <a-button size="small" danger ghost
                                    @click="handleBulkAction('delete')">Delete</a-button>
                            </a-space>
                        </div>
                    </div>
                </transition>

                <!-- Floating Row Table -->
                <div class="table-wrapper">
                    <a-table :columns="columns" :data-source="tickets.data" :row-key="(record) => record.id"
                        :pagination="false" :loading="loading" :row-selection="{
                            selectedRowKeys: selectedRowKeys,
                            onChange: onSelectChange,
                        }" @change="handleTableChange" class="ticket-strip-table">
                        <template #bodyCell="{ column, record }">
                            <!-- Ticket ID Cell -->
                            <template v-if="column.key === 'ticket_number'">
                                <div class="id-stub">
                                    <span class="tag">ID</span>
                                    <span class="num">{{ record.ticket_number }}</span>
                                </div>
                            </template>

                            <!-- Attendee Cell -->
                            <template v-else-if="column.key === 'attendee'">
                                <div class="attendee-profile">
                                    <div class="avatar-initials"
                                        :style="{ backgroundColor: getRandomColor(record.attendee_full_name) }">
                                        {{ getInitials(record.attendee_full_name || record.user?.name) }}
                                    </div>
                                    <div class="info">
                                        <div class="name">{{ record.attendee_full_name || record.user?.name || 'Guest'
                                        }}</div>
                                        <div class="email">{{ record.attendee_email || record.user?.email }}</div>
                                    </div>
                                </div>
                            </template>

                            <!-- Meta Cell -->
                            <template v-else-if="column.key === 'ticket_type'">
                                <div class="meta-stack">
                                    <div class="type-pill" :class="(record.ticket_type?.name || '').toLowerCase()">
                                        {{ record.ticket_type?.name }}
                                    </div>
                                    <div class="event-title text-truncate" :title="record.event?.title">
                                        {{ record.event?.title }}
                                    </div>
                                </div>
                            </template>

                            <!-- Status Cell -->
                            <template v-else-if="column.key === 'status'">
                                <div class="status-indicator" :class="record.status">
                                    <span class="pulse"></span>
                                    {{ record.status.toUpperCase() }}
                                </div>
                            </template>

                            <!-- Check-in Cell -->
                            <template v-else-if="column.key === 'checked_in'">
                                <div class="checkin-badge" :class="{ in: record.checked_in }">
                                    <component
                                        :is="record.checked_in ? 'CheckCircleOutlined' : 'ClockCircleOutlined'" />
                                    <span>{{ record.checked_in ? 'Arrived' : 'Expected' }}</span>
                                </div>
                            </template>

                            <!-- Actions Cell -->
                            <template v-else-if="column.key === 'actions'">
                                <div class="action-hub">
                                    <a-button type="text" shape="circle" @click="handleView(record)">
                                        <template #icon>
                                            <EyeOutlined />
                                        </template>
                                    </a-button>
                                    <a-button type="text" shape="circle" @click="handleEdit(record)">
                                        <template #icon>
                                            <EditOutlined />
                                        </template>
                                    </a-button>
                                    <a-popconfirm title="Revoke this ticket?" @confirm="handleCancel(record)">
                                        <a-button type="text" shape="circle" danger
                                            :disabled="record.status === 'cancelled'">
                                            <template #icon>
                                                <CloseCircleOutlined />
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
    PlusOutlined,
    EyeOutlined,
    EditOutlined,
    ReloadOutlined,
    FilePdfOutlined,
    FileExcelOutlined,
    QrcodeOutlined,
    CloseCircleOutlined,
    CheckCircleOutlined,
    ClockCircleOutlined,
    DownloadOutlined,
    IdcardOutlined,
    ThunderboltFilled,
    CheckCircleFilled
} from '@ant-design/icons-vue';
import { message } from 'ant-design-vue';
import dayjs from 'dayjs';

const page = usePage();
const tickets = computed(() => page.props.tickets || { data: [] });
const initialFilters = page.props.filters || {};
const filters = ref({
    search: initialFilters.search || '',
    date_from: initialFilters.date_from || '',
    date_to: initialFilters.date_to || '',
});

const dateRange = ref(
    initialFilters.date_from && initialFilters.date_to
        ? [dayjs(initialFilters.date_from), dayjs(initialFilters.date_to)]
        : null
);

const selectedRowKeys = ref([]);
const loading = ref(false);

const breadcrumbItems = [
    { label: 'Admin', href: '/dashboard' },
    { label: 'Ticket Hub' },
];

const columns = [
    { title: 'Ticket Reference', key: 'ticket_number', width: 180 },
    { title: 'Attendee Details', key: 'attendee' },
    { title: 'Event Access', key: 'ticket_type' },
    { title: 'Current Status', key: 'status', align: 'center' },
    { title: 'Attendance', key: 'checked_in', align: 'center' },
    { title: '', key: 'actions', align: 'right', width: 120 },
];

const pagination = computed(() => ({
    current: tickets.value.meta?.current_page || 1,
    pageSize: tickets.value.meta?.per_page || 10,
    total: tickets.value.meta?.total || 0,
}));

const checkedInCount = computed(() => tickets.value.data.filter(t => t.checked_in).length);
const activeCount = computed(() => tickets.value.data.filter(t => t.status === 'active').length);

const onSelectChange = (keys) => {
    selectedRowKeys.value = keys;
};

// Utils
const getInitials = (name) => {
    if (!name) return '?';
    return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
};

const getRandomColor = (name) => {
    const colors = ['#1890ff', '#52c41a', '#722ed1', '#fa8c16', '#eb2f96'];
    const index = (name?.length || 0) % colors.length;
    return colors[index];
};

// Handlers
const handleSearch = () => {
    router.get('/dashboard/tickets', filters.value, { preserveState: true, preserveScroll: true });
};

const handleDateChange = (dates) => {
    if (dates && dates.length === 2) {
        filters.value.date_from = dates[0].format('YYYY-MM-DD');
        filters.value.date_to = dates[1].format('YYYY-MM-DD');
    } else {
        filters.value.date_from = '';
        filters.value.date_to = '';
    }
    handleSearch();
};

const handlePaginationChange = (page) => {
    router.get('/dashboard/tickets', { ...filters.value, page }, { preserveState: true });
};

const handlePageSizeChange = (current, size) => {
    router.get('/dashboard/tickets', { ...filters.value, per_page: size }, { preserveState: true });
};

const handleRefresh = () => router.visit('/dashboard/tickets');
const handleCreate = () => router.visit('/dashboard/tickets/create');
const handleEdit = (record) => router.visit(`/dashboard/tickets/${record.id}/edit`);
const handleView = (record) => router.visit(`/dashboard/tickets/${record.id}`);

const handleCancel = (record) => {
    message.loading('Processing revocation...');
    router.post(`/dashboard/tickets/${record.id}/cancel`, {}, {
        onSuccess: () => message.success('Ticket revoked'),
        onError: () => message.error('Failed to revoke')
    });
};

const handleBulkAction = (action) => {
    router.post('/dashboard/tickets/bulk-action', { action, ids: selectedRowKeys.value }, {
        onSuccess: () => {
            selectedRowKeys.value = [];
            message.success(`Bulk operation: ${action} executed`);
        },
    });
};

const handleExportPDF = () => window.print();
const handleExportExcel = () => message.info('Generating excel report...');
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

.total .stat-icon {
    background: rgba(24, 144, 255, 0.1);
    color: #1890ff;
}

.active .stat-icon {
    background: rgba(250, 173, 20, 0.1);
    color: #faad14;
}

.checkin .stat-icon {
    background: rgba(82, 196, 26, 0.1);
    color: #52c41a;
}

.stat-info .label {
    display: block;
    font-size: 0.8rem;
    color: var(--text-muted);
    font-weight: 500;
}

.stat-info .value {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text-primary);
}

.action-box {
    flex-direction: column;
    padding: 12px;
    gap: 8px;
}

.bento-btn {
    border-radius: 10px;
    height: 38px;
    font-weight: 600;
}

.bento-btn.scan {
    background: var(--bg-secondary);
    border-color: var(--border-color);
}

/* Main Content Styles */
.main-content {
    border-radius: 20px;
    padding: 0;
    overflow: hidden;
}

.filter-bar {
    padding: 24px;
    display: flex;
    gap: 16px;
    border-bottom: 1px solid var(--border-color-light);
}

.search-box {
    flex: 1;
}

.date-box {
    width: 300px;
}

/* Bulk Tray */
.bulk-tray {
    background: #1890ff;
    color: white;
    padding: 12px 24px;
}

.tray-content {
    display: flex;
    align-items: center;
    gap: 12px;
}

.count-tag {
    background: rgba(255, 255, 255, 0.2);
    padding: 2px 10px;
    border-radius: 20px;
    font-weight: 700;
}

/* Ticket Strip Table */
.table-wrapper {
    padding: 8px;
}

.ticket-strip-table :deep(.ant-table-content) {
    padding: 8px;
}

.ticket-strip-table :deep(.ant-table-row) {
    background: transparent;
}

.ticket-strip-table :deep(.ant-table-cell) {
    padding: 16px !important;
    background: transparent !important;
}

.id-stub {
    display: flex;
    flex-direction: column;
    background: var(--bg-secondary);
    border-left: 4px solid #1890ff;
    padding: 8px 12px;
    border-radius: 4px 12px 12px 4px;
    width: fit-content;
}

.id-stub .tag {
    font-size: 0.65rem;
    font-weight: 800;
    color: #1890ff;
    letter-spacing: 0.05em;
}

.id-stub .num {
    font-family: 'SF Mono', 'Roboto Mono', monospace;
    font-weight: 700;
    color: var(--text-primary);
}

.attendee-profile {
    display: flex;
    align-items: center;
    gap: 12px;
}

.avatar-initials {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.9rem;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.attendee-profile .name {
    font-weight: 600;
    color: var(--text-primary);
}

.attendee-profile .email {
    font-size: 0.75rem;
    color: var(--text-muted);
}

.meta-stack {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.type-pill {
    font-size: 0.7rem;
    font-weight: 800;
    text-transform: uppercase;
    padding: 2px 8px;
    border-radius: 4px;
    width: fit-content;
    background: #e6f7ff;
    color: #1890ff;
}

.type-pill.vip {
    background: #fff7e6;
    color: #fa8c16;
}

.type-pill.early-bird {
    background: #f6ffed;
    color: #52c41a;
}

.event-title {
    font-size: 0.8rem;
    color: var(--text-muted);
    max-width: 200px;
}

.status-indicator {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 700;
    background: #f0f0f0;
    color: #595959;
}

.status-indicator.active {
    background: #e6f7ff;
    color: #1890ff;
}

.status-indicator.used {
    background: #f6ffed;
    color: #52c41a;
}

.status-indicator.cancelled {
    background: #fff1f0;
    color: #f5222d;
}

.status-indicator .pulse {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: currentColor;
}

.status-indicator.active .pulse {
    animation: status-pulse 1.5s infinite;
}

@keyframes status-pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(24, 144, 255, 0.4);
    }

    70% {
        box-shadow: 0 0 0 10px rgba(24, 144, 255, 0);
    }

    100% {
        box-shadow: 0 0 0 0 rgba(24, 144, 255, 0);
    }
}

.checkin-badge {
    display: flex;
    align-items: center;
    gap: 6px;
    font-weight: 600;
    font-size: 0.85rem;
    color: #faad14;
}

.checkin-badge.in {
    color: #52c41a;
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
}

/* Animations */
.tray-enter-active,
.tray-leave-active {
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.tray-enter-from,
.tray-leave-to {
    transform: translateY(-100%);
    opacity: 0;
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

    .filter-bar {
        flex-direction: column;
    }

    .date-box {
        width: 100%;
    }
}
</style>
