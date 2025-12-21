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

        <a-card class="ticket-types-card">
            <template #title>
                <div class="card-header">
                    <h2 class="card-title">Ticket Types</h2>
                    <a-button type="primary" @click="handleCreate">
                        <template #icon>
                            <PlusOutlined />
                        </template>
                        Create Ticket Type
                    </a-button>
                </div>
            </template>

            <!-- Filters -->
            <div class="filters-section">
                <a-row :gutter="16">
                    <a-col :xs="24" :sm="12" :md="12">
                        <Search v-model="filters.search" placeholder="Search ticket types..." @search="handleSearch" />
                    </a-col>
                    <a-col :xs="24" :sm="12" :md="12">
                        <DatePicker v-model="dateRange" @change="handleDateChange" />
                    </a-col>
                </a-row>
            </div>

            <!-- Bulk Actions -->
            <div v-if="selectedRowKeys.length > 0" class="bulk-actions">
                <span class="selected-count">
                    {{ selectedRowKeys.length }} selected
                </span>
                <a-space>
                    <a-button @click="handleBulkAction('publish')">Publish</a-button>
                    <a-button @click="handleBulkAction('unpublish')">Unpublish</a-button>
                    <a-button danger @click="handleBulkAction('delete')">Delete</a-button>
                </a-space>
            </div>

            <!-- Table -->
            <a-table :columns="columns" :data-source="ticketTypes.data" :row-key="(record) => record.id"
                :pagination="false" :loading="loading" :row-selection="{
                    selectedRowKeys: selectedRowKeys,
                    onChange: onSelectChange,
                }" @change="handleTableChange">
                <template #bodyCell="{ column, record }">
                    <template v-if="column.key === 'name'">
                        <div class="flex flex-col">
                            <span class="font-semibold">{{ record.name }}</span>
                            <span class="text-xs text-muted">{{ record.event?.name }}</span>
                        </div>
                    </template>

                    <template v-else-if="column.key === 'type'">
                        <a-tag :color="getTypeColor(record.type)" class="capitalize">
                            {{ record.type }}
                        </a-tag>
                    </template>

                    <template v-else-if="column.key === 'price'">
                        <span class="font-mono">
                            {{ record.type === 'free' ? 'Free' : record.currency + ' ' + record.price }}
                        </span>
                    </template>

                    <template v-else-if="column.key === 'availability'">
                        <div class="flex flex-col gap-1 w-full max-w-[150px]">
                            <div class="flex justify-between text-xs">
                                <span>{{ record.quantity_sold }} / {{ record.quantity_total || '∞' }}</span>
                                <span v-if="record.available_quantity !== null" class="text-muted">{{
                                    record.available_quantity }} left</span>
                            </div>
                            <a-progress v-if="record.quantity_total"
                                :percent="Math.round((record.quantity_sold / record.quantity_total) * 100)" size="small"
                                :status="record.quantity_sold >= record.quantity_total ? 'exception' : 'normal'"
                                :show-info="false" />
                        </div>
                    </template>

                    <template v-else-if="column.key === 'is_visible'">
                        <a-tag :color="record.is_visible ? 'green' : 'default'">
                            {{ record.is_visible ? 'Visible' : 'Hidden' }}
                        </a-tag>
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
                            <a-popconfirm title="Are you sure you want to delete this ticket type?" ok-text="Yes"
                                cancel-text="No" @confirm="handleDelete(record)">
                                <a-button type="link" size="small" danger>
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
    SearchOutlined,
    EditOutlined,
    DeleteOutlined,
    EyeOutlined,
    ReloadOutlined,
    FilePdfOutlined,
    FileExcelOutlined,
    DownOutlined
} from '@ant-design/icons-vue';
import { message } from 'ant-design-vue';
import dayjs from 'dayjs';

const page = usePage();

const ticketTypes = computed(() => page.props.ticketTypes || { data: [] });
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
    { label: 'Ticket Types' },
]);

const columns = [
    { title: 'Name', key: 'name', dataIndex: 'name', sorter: true },
    { title: 'Type', key: 'type', dataIndex: 'type' },
    { title: 'Price', key: 'price', dataIndex: 'price', sorter: true },
    { title: 'Availability', key: 'availability' },
    { title: 'Status', key: 'is_visible', dataIndex: 'is_visible', align: 'center' },
    { title: 'Actions', key: 'actions', align: 'right', width: 150 },
];

const pagination = computed(() => ({
    current: ticketTypes.value.meta?.current_page || 1,
    pageSize: ticketTypes.value.meta?.per_page || 10,
    total: ticketTypes.value.meta?.total || 0,
}));

const onSelectChange = (keys) => {
    selectedRowKeys.value = keys;
};

const getTypeColor = (type) => {
    switch (type) {
        case 'free': return 'green';
        case 'paid': return 'blue';
        case 'donation': return 'orange';
        default: return 'default';
    }
};

const handleSearch = () => {
    router.get('/dashboard/ticket-types', filters.value, {
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
    router.get('/dashboard/ticket-types', { ...filters.value, ...params }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const handlePaginationChange = ({ current, pageSize }) => {
    router.get('/dashboard/ticket-types', { ...filters.value, page: current, per_page: pageSize }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const handlePageSizeChange = ({ current, pageSize }) => {
    router.get('/dashboard/ticket-types', { ...filters.value, page: current, per_page: pageSize }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const handleCreate = () => router.visit('/dashboard/ticket-types/create');
const handleView = (record) => router.visit(`/dashboard/ticket-types/${record.id}`);
const handleEdit = (record) => router.visit(`/dashboard/ticket-types/${record.id}/edit`);

const handleDelete = (record) => {
    router.delete(`/dashboard/ticket-types/${record.id}`, {
        preserveScroll: true,
        onSuccess: () => message.success('Ticket type deleted successfully'),
    });
};

const handleBulkAction = (action) => {
    router.post('/dashboard/ticket-types/bulk-action', {
        action,
        ids: selectedRowKeys.value,
    }, {
        onSuccess: () => {
            selectedRowKeys.value = [];
            message.success('Bulk action completed successfully');
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
    router.visit('/dashboard/ticket-types', { preserveState: false });
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

.ticket-types-card {
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
