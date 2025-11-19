<template>
  <DashboardLayout>
    <div class="breadcrumb-wrapper">
      <Breadcrumb :items="breadcrumbItems" />
      <div class="breadcrumb-actions">
        <a-button @click="handleRefresh" title="Refresh">
          <template #icon><ReloadOutlined /></template>
        </a-button>
        <a-button @click="handleExportPDF" title="Export PDF">
          <template #icon><FilePdfOutlined /></template>
        </a-button>
        <a-button @click="handleExportExcel" title="Export Excel">
          <template #icon><FileExcelOutlined /></template>
        </a-button>
      </div>
    </div>

    <a-card class="surveys-card">
      <template #title>
        <div class="card-title-wrapper">
          <h2 class="card-title">All Surveys</h2>
          <a-button type="primary" @click="handleCreate">
            <template #icon><PlusOutlined /></template>
            Create Survey
          </a-button>
        </div>
      </template>

      <!-- Filters -->
      <div class="filters-section">
        <a-row :gutter="16">
          <a-col :xs="24" :sm="12" :md="8">
            <a-input
              v-model:value="filters.search"
              placeholder="Search surveys..."
              allow-clear
              @pressEnter="handleSearch"
            >
              <template #prefix><SearchOutlined /></template>
            </a-input>
          </a-col>
          <a-col :xs="24" :sm="12" :md="8">
            <a-range-picker
              v-model:value="dateRange"
              :placeholder="['Start Date', 'End Date']"
              style="width: 100%"
              @change="handleDateChange"
            />
          </a-col>
          <a-col :xs="24" :sm="12" :md="4">
            <a-select
              v-model:value="filters.status"
              placeholder="Status"
              allow-clear
              style="width: 100%"
              @change="handleSearch"
            >
              <a-select-option value="draft">Draft</a-select-option>
              <a-select-option value="active">Active</a-select-option>
              <a-select-option value="closed">Closed</a-select-option>
            </a-select>
          </a-col>
          <a-col :xs="24" :sm="12" :md="4">
            <a-select
              v-model:value="filters.is_required"
              placeholder="Required"
              allow-clear
              style="width: 100%"
              @change="handleSearch"
            >
              <a-select-option value="1">Required</a-select-option>
              <a-select-option value="0">Optional</a-select-option>
            </a-select>
          </a-col>
        </a-row>
        <a-row :gutter="16" style="margin-top: 12px">
          <a-col :xs="24" :sm="12" :md="8">
            <a-select
              v-model:value="filters.event_id"
              placeholder="Filter by event"
              allow-clear
              show-search
              :filter-option="filterEventOption"
              style="width: 100%"
              @change="handleSearch"
            >
              <a-select-option
                v-for="event in events"
                :key="event.id"
                :value="event.id"
              >
                {{ event.title }}
              </a-select-option>
            </a-select>
          </a-col>
        </a-row>
      </div>

      <!-- Bulk Actions -->
      <div v-if="selectedRowKeys.length > 0" class="bulk-actions">
        <span class="selected-count">
          {{ selectedRowKeys.length }} selected
        </span>
        <a-space>
          <a-button @click="handleBulkAction('activate')">Activate</a-button>
          <a-button @click="handleBulkAction('close')">Close</a-button>
          <a-button danger @click="handleBulkAction('delete')">Delete</a-button>
        </a-space>
      </div>

      <!-- Table -->
      <a-table
        :columns="columns"
        :data-source="surveys.data"
        :row-key="(record) => record.id"
        :pagination="false"
        :loading="loading"
        :row-selection="{
          selectedRowKeys: selectedRowKeys,
          onChange: onSelectChange,
        }"
        @change="handleTableChange"
      >
        <template #bodyCell="{ column, record }">
          <template v-if="column.key === 'title'">
            <div class="survey-title-cell">
              <span class="survey-title">{{ record.title }}</span>
              <span v-if="record.event" class="survey-event">{{ record.event.title }}</span>
            </div>
          </template>

          <template v-if="column.key === 'status'">
            <a-tag :color="getStatusColor(record.status)">
              {{ record.status.toUpperCase() }}
            </a-tag>
          </template>

          <template v-if="column.key === 'is_required'">
            <a-tag :color="record.is_required ? 'red' : 'default'">
              {{ record.is_required ? 'Required' : 'Optional' }}
            </a-tag>
          </template>

          <template v-if="column.key === 'dates'">
            <div class="date-info">
              <div v-if="record.opens_at">
                <span class="date-label">Opens:</span>
                {{ formatDate(record.opens_at) }}
              </div>
              <div v-if="record.closes_at">
                <span class="date-label">Closes:</span>
                {{ formatDate(record.closes_at) }}
              </div>
              <span v-if="!record.opens_at && !record.closes_at" class="text-muted">—</span>
            </div>
          </template>

          <template v-if="column.key === 'responses'">
            <a-badge :count="record.responses_count || 0" :number-style="{ backgroundColor: '#52c41a' }" />
          </template>

          <template v-if="column.key === 'actions'">
            <a-space>
              <a-button type="link" size="small" @click="handleView(record)">
                <template #icon><EyeOutlined /></template>
              </a-button>
              <a-button type="link" size="small" @click="handleEdit(record)">
                <template #icon><EditOutlined /></template>
              </a-button>
              <a-popconfirm
                title="Are you sure you want to delete this survey?"
                ok-text="Yes"
                cancel-text="No"
                @confirm="handleDelete(record)"
              >
                <a-button type="link" size="small" danger>
                  <template #icon><DeleteOutlined /></template>
                </a-button>
              </a-popconfirm>
            </a-space>
          </template>
        </template>
      </a-table>

      <!-- Modern Pagination -->
      <Pagination
        :current="pagination.current"
        :page-size="pagination.pageSize"
        :total="pagination.total"
        :page-size-options="[10, 15, 20, 50, 100]"
        @change="handlePaginationChange"
        @page-size-change="handlePageSizeChange"
      />
    </a-card>
  </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import DashboardLayout from '../../../Layouts/DashboardLayout.vue';
import Breadcrumb from '../../../Components/Breadcrumb.vue';
import Pagination from '../../../Components/Pagination.vue';
import {
  PlusOutlined,
  SearchOutlined,
  EyeOutlined,
  EditOutlined,
  DeleteOutlined,
  ReloadOutlined,
  FilePdfOutlined,
  FileExcelOutlined,
} from '@ant-design/icons-vue';
import dayjs from 'dayjs';

const page = usePage();

const surveys = computed(() => page.props.surveys || { data: [] });
const events = computed(() => page.props.events || []);
const initialFilters = page.props.filters || {};
if (initialFilters.is_required !== undefined && initialFilters.is_required !== null && initialFilters.is_required !== '') {
  initialFilters.is_required = initialFilters.is_required ? '1' : '0';
}
const filters = ref(initialFilters);
const dateRange = ref(
  initialFilters.date_from && initialFilters.date_to
    ? [dayjs(initialFilters.date_from), dayjs(initialFilters.date_to)]
    : null
);
const selectedRowKeys = ref([]);
const loading = ref(false);

const breadcrumbItems = ref([
  { label: 'Dashboard', href: '/dashboard' },
  { label: 'Surveys' },
]);

const getStatusColor = (status) => {
  const colors = {
    draft: 'default',
    active: 'green',
    closed: 'red',
  };
  return colors[status] || 'default';
};

const formatDate = (date) => {
  return dayjs(date).format('YYYY-MM-DD HH:mm');
};

const filterEventOption = (input, option) => {
  const searchText = input.toLowerCase();
  const eventTitle = option.children[0].children.toLowerCase();
  return eventTitle.includes(searchText);
};

const columns = [
  {
    title: 'Title',
    key: 'title',
    dataIndex: 'title',
    sorter: true,
  },
  {
    title: 'Event',
    key: 'event',
    dataIndex: 'event',
  },
  {
    title: 'Status',
    key: 'status',
    dataIndex: 'status',
    align: 'center',
    sorter: true,
  },
  {
    title: 'Required',
    key: 'is_required',
    dataIndex: 'is_required',
    align: 'center',
  },
  {
    title: 'Dates',
    key: 'dates',
    width: 200,
  },
  {
    title: 'Responses',
    key: 'responses',
    align: 'center',
  },
  {
    title: 'Actions',
    key: 'actions',
    align: 'right',
    width: 150,
  },
];

const pagination = computed(() => ({
  current: surveys.value.current_page || 1,
  pageSize: surveys.value.per_page || 15,
  total: surveys.value.total || 0,
  showSizeChanger: true,
  showTotal: (total) => `Total ${total} surveys`,
}));

const handleCreate = () => {
  router.visit('/dashboard/surveys/create');
};

const handleSearch = () => {
  router.get('/dashboard/surveys/search', filters.value, {
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

  router.get('/dashboard/surveys/search', { ...filters.value, ...params }, {
    preserveState: true,
    preserveScroll: true,
  });
};

const handlePaginationChange = ({ current, pageSize }) => {
  const params = {
    page: current,
    per_page: pageSize,
  };

  router.get('/dashboard/surveys/search', { ...filters.value, ...params }, {
    preserveState: true,
    preserveScroll: true,
  });
};

const handlePageSizeChange = ({ current, pageSize }) => {
  const params = {
    page: current,
    per_page: pageSize,
  };

  router.get('/dashboard/surveys/search', { ...filters.value, ...params }, {
    preserveState: true,
    preserveScroll: true,
  });
};

const onSelectChange = (keys) => {
  selectedRowKeys.value = keys;
};

const handleView = (record) => {
  router.visit(`/dashboard/surveys/${record.id}`);
};

const handleEdit = (record) => {
  router.visit(`/dashboard/surveys/${record.id}/edit`);
};

const handleDelete = (record) => {
  router.delete(`/dashboard/surveys/${record.id}`, {
    preserveScroll: true,
    onSuccess: () => {
      // Success handled by Inertia
    },
  });
};

const handleBulkAction = (action) => {
  if (selectedRowKeys.value.length === 0) return;

  router.post(
    '/dashboard/surveys/bulk-action',
    {
      action,
      ids: selectedRowKeys.value,
    },
    {
      preserveScroll: true,
      onSuccess: () => {
        selectedRowKeys.value = [];
      },
    }
  );
};

const handleDateChange = (dates) => {
  if (dates && dates.length === 2) {
    filters.value.date_from = dates[0].format('YYYY-MM-DD');
    filters.value.date_to = dates[1].format('YYYY-MM-DD');
  } else {
    delete filters.value.date_from;
    delete filters.value.date_to;
  }
  handleSearch();
};

const handleRefresh = () => {
  filters.value = {
    search: '',
    status: '',
    event_id: '',
    is_required: '',
  };
  dateRange.value = null;
  selectedRowKeys.value = [];
  
  router.visit('/dashboard/surveys', {
    preserveState: false,
    preserveScroll: false,
  });
};

const handleExportPDF = () => {
  const tableData = surveys.value.data || [];
  const columns = ['Title', 'Event', 'Status', 'Required', 'Opens At', 'Closes At'];
  
  let content = `
    <html>
    <head>
      <title>Surveys Export</title>
      <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        h1 { color: #333; }
      </style>
    </head>
    <body>
      <h1>Surveys List</h1>
      <p>Generated on: ${new Date().toLocaleString()}</p>
      <table>
        <thead>
          <tr>
            ${columns.map(col => `<th>${col}</th>`).join('')}
          </tr>
        </thead>
        <tbody>
          ${tableData.map(row => `
            <tr>
              <td>${row.title || ''}</td>
              <td>${row.event?.title || ''}</td>
              <td>${row.status ? row.status.toUpperCase() : ''}</td>
              <td>${row.is_required ? 'Required' : 'Optional'}</td>
              <td>${row.opens_at ? dayjs(row.opens_at).format('YYYY-MM-DD HH:mm') : '—'}</td>
              <td>${row.closes_at ? dayjs(row.closes_at).format('YYYY-MM-DD HH:mm') : '—'}</td>
            </tr>
          `).join('')}
        </tbody>
      </table>
    </body>
    </html>
  `;
  
  const printWindow = window.open('', '_blank');
  printWindow.document.write(content);
  printWindow.document.close();
  setTimeout(() => {
    printWindow.print();
  }, 250);
};

const handleExportExcel = () => {
  const tableData = surveys.value.data || [];
  
  const headers = ['Title', 'Event', 'Status', 'Required', 'Description', 'Opens At', 'Closes At', 'Created At'];
  const rows = tableData.map(row => [
    row.title || '',
    row.event?.title || '',
    row.status ? row.status.toUpperCase() : '',
    row.is_required ? 'Required' : 'Optional',
    row.description || '',
    row.opens_at ? dayjs(row.opens_at).format('YYYY-MM-DD HH:mm:ss') : '',
    row.closes_at ? dayjs(row.closes_at).format('YYYY-MM-DD HH:mm:ss') : '',
    row.created_at ? dayjs(row.created_at).format('YYYY-MM-DD HH:mm:ss') : '',
  ]);
  
  const csvContent = [
    headers.join(','),
    ...rows.map(row => row.map(cell => `"${String(cell).replace(/"/g, '""')}"`).join(','))
  ].join('\n');
  
  const blob = new Blob(['\ufeff' + csvContent], { type: 'text/csv;charset=utf-8;' });
  const link = document.createElement('a');
  const url = URL.createObjectURL(blob);
  link.setAttribute('href', url);
  link.setAttribute('download', `surveys_${dayjs().format('YYYY-MM-DD')}.csv`);
  link.style.visibility = 'hidden';
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};
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

.surveys-card {
  border-radius: 8px;
  box-shadow: var(--card-shadow, 0 2px 8px rgba(0, 0, 0, 0.06));
}

.card-title-wrapper {
  display: flex;
  justify-content: space-between;
  align-items: center;
  width: 100%;
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

.survey-title-cell {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.survey-title {
  font-weight: 500;
}

.survey-event {
  font-size: 12px;
  color: var(--text-secondary, #8c8c8c);
}

[data-theme="dark"] .survey-event {
  color: rgba(255, 255, 255, 0.65);
}

.date-info {
  display: flex;
  flex-direction: column;
  gap: 4px;
  font-size: 12px;
}

.date-label {
  font-weight: 500;
  color: var(--text-secondary, #8c8c8c);
}

[data-theme="dark"] .date-label {
  color: rgba(255, 255, 255, 0.65);
}

.text-muted {
  color: var(--text-tertiary, #8c8c8c);
}

[data-theme="dark"] .text-muted {
  color: rgba(255, 255, 255, 0.45);
}
</style>

