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
                <a-button @click="handleExportExcel" title="Export Excel">
                    <template #icon>
                        <FileExcelOutlined />
                    </template>
                </a-button>
            </div>
        </div>

        <a-card class="tickets-card">
            <template #title>
                <div class="card-header">
                    <h2 class="card-title">Tickets</h2>
                    <a-space>
                        <a-button @click="handleCreate">
                            <template #icon>
                                <PlusOutlined />
                            </template>
                            Manual Issue
                        </a-button>
                        <a-button type="primary">
                            <template #icon>
                                <QrcodeOutlined />
                            </template>
                            Scan Check-in
                        </a-button>
                    </a-space>
                </div>
            </template>

            <!-- Filters -->
            <div class="filters-section">
                <a-row :gutter="16">
                    <a-col :xs="24" :sm="12" :md="8">
                        <Search v-model="filters.search" placeholder="Search ticket #, name, email..."
                            @search="handleSearch" />
                    </a-col>
                    <a-col :xs="24" :sm="12" :md="8">
                        <DatePicker v-model="dateRange" @change="handleDateChange" />
                    </a-col>
                    <a-col :xs="24" :sm="24" :md="8">
                        <div class="flex justify-end h-full items-center gap-4">
                            <div class="stats-summary flex gap-4">
                                <a-tooltip title="Active / Total">
                                    <span class="text-xs font-semibold text-blue-600 dark:text-blue-400">ACT: {{
                                        activeCount
                                        }}</span>
                                </a-tooltip>
                                <a-tooltip title="Checked In">
                                    <span class="text-xs font-semibold text-green-600 dark:text-green-400">IN: {{
                                        checkedInCount
                                        }}</span>
                                </a-tooltip>
                            </div>
                        </div>
                    </a-col>
                </a-row>
            </div>

            <!-- Bulk Actions -->
            <div v-if="selectedRowKeys.length > 0" class="bulk-actions">
                <span class="selected-count">
                    {{ selectedRowKeys.length }} selected
                </span>
                <a-space>
                    <a-button @click="handleBulkAction('check-in')">Check-in</a-button>
                    <a-button @click="handleBulkAction('cancel')">Cancel</a-button>
                    <a-button danger @click="handleBulkAction('delete')">Delete</a-button>
                </a-space>
            </div>

            <!-- Table -->
            <a-table :columns="columns" :data-source="tickets.data" :row-key="(record) => record.id" :pagination="false"
                :loading="loading" :row-selection="{
                    selectedRowKeys: selectedRowKeys,
                    onChange: onSelectChange,
                }" @change="handleTableChange">
                <template #bodyCell="{ column, record }">
                    <template v-if="column.key === 'ticket_number'">
                        <span class="font-mono font-bold text-blue-600 dark:text-blue-400">{{ record.ticket_number
                            }}</span>
                    </template>

                    <template v-else-if="column.key === 'attendee'">
                        <div class="flex flex-col">
                            <span class="font-medium">{{ record.attendee_full_name || record.user?.name || 'N/A'
                                }}</span>
                            <span class="text-xs text-muted">{{ record.attendee_email || record.user?.email }}</span>
                        </div>
                    </template>

                    <template v-else-if="column.key === 'ticket_type'">
                        <div class="flex flex-col">
                            <span class="text-xs font-semibold">{{ record.ticket_type?.name }}</span>
                            <span class="text-[10px] uppercase text-muted tracking-tight">{{ record.event?.name
                                }}</span>
                        </div>
                    </template>

                    <template v-else-if="column.key === 'status'">
                        <a-tag :color="getStatusColor(record.status)" class="capitalize">
                            {{ record.status }}
                        </a-tag>
                    </template>

                    <template v-else-if="column.key === 'checked_in'">
                        <a-tooltip :title="record.checked_in_at ? 'At: ' + record.checked_in_at : ''">
                            <a-badge :status="record.checked_in ? 'success' : 'processing'"
                                :text="record.checked_in ? 'In' : 'Pending'" />
                        </a-tooltip>
                    </template>

                    <template v-else-if="column.key === 'actions'">
                        <a-space>
                            <a-button type="link" size="small" @click="handleView(record)">
                                <template #icon>
                                    <EyeOutlined />
                                </template>
                            </a-button>
                            <a-button type="link" size="small" @click="handleEdit(record)">
                                <template #icon>
                                    <EditOutlined />
                                </template>
                            </a-button>
                            <a-popconfirm title="Cancel this ticket?" @confirm="handleCancel(record)">
                                <a-button type="link" size="small" danger :disabled="record.status === 'cancelled'">
                                    <template #icon>
                                        <CloseCircleOutlined />
                                    </template>
                                </a-button>
                            </a-popconfirm>
                        </a-space>
                    </template>
                </template>
            </a-table>

            <!-- Modern Pagination -->
            <Pagination :current="pagination.current" :page-size="pagination.pageSize" :total="pagination.total"
                :page-size-options="[10, 15, 20, 50, 100]" @change="handlePaginationChange"
                @page-size-change="handlePageSizeChange" />
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
    PlusOutlined,
    EyeOutlined,
    EditOutlined,
    DeleteOutlined,
    ReloadOutlined,
    FilePdfOutlined,
    FileExcelOutlined,
    QrcodeOutlined,
    CloseCircleOutlined,
    DownloadOutlined
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

const breadcrumbItems = ref([
    { label: 'Dashboard', href: '/dashboard' },
    { label: 'Tickets' },
]);

const columns = [
    { title: 'Ticket #', key: 'ticket_number', dataIndex: 'ticket_number', sorter: true },
    { title: 'Attendee', key: 'attendee' },
    { title: 'Type', key: 'ticket_type' },
    { title: 'Status', key: 'status', dataIndex: 'status', align: 'center' },
    { title: 'Check-in', key: 'checked_in', align: 'center' },
    { title: 'Actions', key: 'actions', align: 'right', width: 150 },
];

const pagination = computed(() => ({
    current: tickets.value.meta?.current_page || 1,
    pageSize: tickets.value.meta?.per_page || 10,
    total: tickets.value.meta?.total || 0,
}));

// Mock counts (usually passed from controller meta)
const checkedInCount = computed(() => tickets.value.data.filter(t => t.checked_in).length);
const activeCount = computed(() => tickets.value.data.filter(t => t.status === 'active').length);

const onSelectChange = (keys) => {
    selectedRowKeys.value = keys;
};

const getStatusColor = (status) => {
    switch (status) {
        case 'active': return 'blue';
        case 'used': return 'green';
        case 'cancelled': return 'red';
        case 'transferred': return 'orange';
        default: return 'default';
    }
};

const handleSearch = () => {
    router.get('/dashboard/tickets', filters.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const handleTableChange = (pag, filters, sorter) => {
    const params = {
        page: pag.current,
        per_page: pag.pageSize,
    };
    if (sorter.field) {
        params.sort_by = sorter.field;
        params.sort_order = sorter.order === 'ascend' ? 'asc' : 'desc';
    }
    router.get('/dashboard/tickets', { ...filters.value, ...params }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const handlePaginationChange = ({ current, pageSize }) => {
    router.get('/dashboard/tickets', { ...filters.value, page: current, per_page: pageSize }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const handlePageSizeChange = ({ current, pageSize }) => {
    router.get('/dashboard/tickets', { ...filters.value, page: current, per_page: pageSize }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const handleView = (record) => router.visit(`/dashboard/tickets/${record.id}`);
const handleEdit = (record) => router.visit(`/dashboard/tickets/${record.id}/edit`);
const handleCreate = () => router.visit('/dashboard/tickets/create');

const handleCancel = (record) => {
    message.loading('Cancelling ticket...');
};

const handleBulkAction = (action) => {
    router.post('/dashboard/tickets/bulk-action', {
        action,
        ids: selectedRowKeys.value,
    }, {
        onSuccess: () => {
            selectedRowKeys.value = [];
            message.success('Bulk action completed');
        },
    });
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

const handleRefresh = () => {
    filters.value = { search: '', date_from: '', date_to: '' };
    dateRange.value = null;
    router.visit('/dashboard/tickets', { preserveState: false });
};

const handleExportPDF = () => message.info('Exporting PDF...');
const handleExportExcel = () => message.info('Exporting Excel...');
</script>

<style scoped>
.breadcrumb-wrapper {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
    flex-wrap: wrap;
    gap: 12px;
}

.breadcrumb-actions {
    display: flex;
    gap: 8px;
    align-items: center;
}

@media (max-width: 768px) {
    .breadcrumb-wrapper {
        flex-direction: column;
        align-items: flex-start;
    }

    .breadcrumb-actions {
        width: 100%;
        justify-content: flex-end;
    }
}

.tickets-card {
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

.filters-section {
    margin-bottom: 16px;
    padding-bottom: 16px;
    border-bottom: 1px solid var(--border-color, #f0f0f0);
}

[data-theme="dark"] .filters-section {
    border-bottom-color: var(--border-color, #434343);
}

.bulk-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px;
    margin-bottom: 16px;
    background: var(--bg-hover, #f5f5f5);
    border-radius: 6px;
}

[data-theme="dark"] .bulk-actions {
    background: rgba(255, 255, 255, 0.08);
}

.selected-count {
    font-weight: 500;
    color: var(--text-primary, #262626);
}

[data-theme="dark"] .selected-count {
    color: rgba(255, 255, 255, 0.85);
}

.text-muted {
    color: var(--text-tertiary, #8c8c8c);
}

[data-theme="dark"] .text-muted {
    color: rgba(255, 255, 255, 0.45);
}
</style>
