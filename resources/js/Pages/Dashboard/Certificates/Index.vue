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

    <a-card class="certificates-card">
      <template #title>
        <div class="card-header">
          <h2 class="card-title">Certificates</h2>
          <a-button type="primary" @click="handleCreate">
            <template #icon><PlusOutlined /></template>
            Create Certificate
          </a-button>
        </div>
      </template>

      <!-- Filters -->
      <div class="filters-section">
        <a-row :gutter="16">
          <a-col :xs="24" :sm="12" :md="6">
            <a-input
              v-model:value="filters.search"
              placeholder="Search certificates..."
              allow-clear
              @pressEnter="handleSearch"
            >
              <template #prefix><SearchOutlined /></template>
            </a-input>
          </a-col>
          <a-col :xs="24" :sm="12" :md="6">
            <a-select
              v-model:value="filters.event_id"
              placeholder="Filter by event"
              allow-clear
              style="width: 100%"
              show-search
              :filter-option="filterOption"
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
          <a-col :xs="24" :sm="12" :md="6">
            <a-select
              v-model:value="filters.user_id"
              placeholder="Filter by user"
              allow-clear
              style="width: 100%"
              show-search
              :filter-option="filterOption"
              @change="handleSearch"
            >
              <a-select-option
                v-for="user in users"
                :key="user.id"
                :value="user.id"
              >
                {{ user.first_name }} {{ user.last_name }} ({{ user.email }})
              </a-select-option>
            </a-select>
          </a-col>
          <a-col :xs="24" :sm="12" :md="6">
            <a-select
              v-model:value="filters.is_downloaded"
              placeholder="Download status"
              allow-clear
              style="width: 100%"
              @change="handleSearch"
            >
              <a-select-option value="1">Downloaded</a-select-option>
              <a-select-option value="0">Not Downloaded</a-select-option>
            </a-select>
          </a-col>
        </a-row>
        <a-row :gutter="16" style="margin-top: 12px">
          <a-col :xs="24" :sm="12" :md="12">
            <a-range-picker
              v-model:value="dateRange"
              :placeholder="['Issued From', 'Issued To']"
              style="width: 100%"
              @change="handleDateChange"
            />
          </a-col>
          <a-col :xs="24" :sm="12" :md="12">
            <a-space>
              <a-button type="primary" @click="handleSearch">
                <template #icon><SearchOutlined /></template>
                Search
              </a-button>
              <a-button @click="handleResetFilters">Reset</a-button>
            </a-space>
          </a-col>
        </a-row>
      </div>

      <!-- Bulk Actions -->
      <div v-if="selectedRowKeys.length > 0" class="bulk-actions">
        <span class="selected-count">
          {{ selectedRowKeys.length }} selected
        </span>
        <a-space>
          <a-button danger @click="handleBulkAction('delete')">Delete</a-button>
        </a-space>
      </div>

      <!-- Table -->
      <a-table
        :columns="columns"
        :data-source="certificates.data"
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
          <template v-if="column.key === 'certificate_number'">
            <div class="certificate-number-cell">
              <span class="certificate-number">{{ record.certificate_number }}</span>
            </div>
          </template>

          <template v-if="column.key === 'event'">
            <span v-if="record.event">{{ record.event.title }}</span>
            <span v-else class="text-muted">—</span>
          </template>

          <template v-if="column.key === 'user'">
            <span v-if="record.user">
              {{ record.user.first_name }} {{ record.user.last_name }}
            </span>
            <span v-else class="text-muted">—</span>
          </template>

          <template v-if="column.key === 'issued_at'">
            <span>{{ formatDate(record.issued_at) }}</span>
          </template>

          <template v-if="column.key === 'downloaded_at'">
            <a-tag v-if="record.downloaded_at" color="green">
              {{ formatDate(record.downloaded_at) }}
            </a-tag>
            <a-tag v-else color="default">Not Downloaded</a-tag>
          </template>

          <template v-if="column.key === 'verification_code'">
            <a-tooltip :title="record.verification_code">
              <span class="verification-code">{{ truncateText(record.verification_code, 20) }}</span>
            </a-tooltip>
          </template>

          <template v-if="column.key === 'actions'">
            <a-space>
              <a-button type="link" size="small" @click="handleView(record)">
                <template #icon><EyeOutlined /></template>
              </a-button>
              <a-button type="link" size="small" @click="handleEdit(record)">
                <template #icon><EditOutlined /></template>
              </a-button>
              <a-button type="link" size="small" @click="handleDownload(record)">
                <template #icon><DownloadOutlined /></template>
              </a-button>
              <a-popconfirm
                title="Are you sure you want to delete this certificate?"
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
  DownloadOutlined,
} from '@ant-design/icons-vue';
import dayjs from 'dayjs';

const page = usePage();

const certificates = computed(() => page.props.certificates || { data: [] });
const events = computed(() => page.props.events || []);
const users = computed(() => page.props.users || []);
const initialFilters = page.props.filters || {};

// Convert boolean is_downloaded to string for select component
if (initialFilters.is_downloaded !== undefined && initialFilters.is_downloaded !== null && initialFilters.is_downloaded !== '') {
  initialFilters.is_downloaded = initialFilters.is_downloaded ? '1' : '0';
}

const filters = ref(initialFilters);
// Initialize date range if filters have date values
const dateRange = ref(
  initialFilters.date_from && initialFilters.date_to
    ? [dayjs(initialFilters.date_from), dayjs(initialFilters.date_to)]
    : null
);
const selectedRowKeys = ref([]);
const loading = ref(false);

const breadcrumbItems = ref([
  { label: 'Dashboard', href: '/dashboard' },
  { label: 'Certificates' },
]);

const columns = [
  {
    title: 'Certificate Number',
    key: 'certificate_number',
    dataIndex: 'certificate_number',
    sorter: true,
  },
  {
    title: 'Event',
    key: 'event',
    dataIndex: 'event',
  },
  {
    title: 'User',
    key: 'user',
    dataIndex: 'user',
  },
  {
    title: 'Issued At',
    key: 'issued_at',
    dataIndex: 'issued_at',
    sorter: true,
  },
  {
    title: 'Downloaded At',
    key: 'downloaded_at',
    dataIndex: 'downloaded_at',
    sorter: true,
  },
  {
    title: 'Verification Code',
    key: 'verification_code',
    dataIndex: 'verification_code',
  },
  {
    title: 'Actions',
    key: 'actions',
    align: 'right',
    width: 200,
  },
];

const pagination = computed(() => ({
  current: certificates.value.current_page || 1,
  pageSize: certificates.value.per_page || 15,
  total: certificates.value.total || 0,
  showSizeChanger: true,
  showTotal: (total) => `Total ${total} certificates`,
}));

const filterOption = (input, option) => {
  return option.children[0].children.toLowerCase().indexOf(input.toLowerCase()) >= 0;
};

const formatDate = (dateString) => {
  if (!dateString) return '—';
  return dayjs(dateString).format('YYYY-MM-DD HH:mm');
};

const truncateText = (text, length) => {
  if (!text) return '—';
  return text.length > length ? text.substring(0, length) + '...' : text;
};

const handleSearch = () => {
  router.get('/dashboard/certificates/search', filters.value, {
    preserveState: true,
    preserveScroll: true,
  });
};

const handleResetFilters = () => {
  filters.value = {};
  dateRange.value = null;
  handleSearch();
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

  router.get('/dashboard/certificates/search', { ...filters.value, ...params }, {
    preserveState: true,
    preserveScroll: true,
  });
};

const handlePaginationChange = ({ current, pageSize }) => {
  const params = {
    page: current,
    per_page: pageSize,
  };

  router.get('/dashboard/certificates/search', { ...filters.value, ...params }, {
    preserveState: true,
    preserveScroll: true,
  });
};

const handlePageSizeChange = ({ current, pageSize }) => {
  const params = {
    page: current,
    per_page: pageSize,
  };

  router.get('/dashboard/certificates/search', { ...filters.value, ...params }, {
    preserveState: true,
    preserveScroll: true,
  });
};

const onSelectChange = (keys) => {
  selectedRowKeys.value = keys;
};

const handleCreate = () => {
  router.visit('/dashboard/certificates/create');
};

const handleView = (record) => {
  router.visit(`/dashboard/certificates/${record.id}`);
};

const handleEdit = (record) => {
  router.visit(`/dashboard/certificates/${record.id}/edit`);
};

const handleDelete = (record) => {
  router.delete(`/dashboard/certificates/${record.id}`, {
    preserveScroll: true,
    onSuccess: () => {
      // Success handled by Inertia
    },
  });
};

const handleDownload = (record) => {
  router.post(`/dashboard/certificates/${record.id}/download`, {}, {
    preserveScroll: true,
  });
};

const handleBulkAction = (action) => {
  if (selectedRowKeys.value.length === 0) return;

  router.post(
    '/dashboard/certificates/bulk-action',
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
  // Reset all filters
  filters.value = {};
  dateRange.value = null;
  selectedRowKeys.value = [];
  
  // Reload page without filters
  router.visit('/dashboard/certificates', {
    preserveState: false,
    preserveScroll: false,
  });
};

const handleExportPDF = () => {
  const tableData = certificates.value.data || [];
  const columns = ['Certificate Number', 'Event', 'User', 'Issued At', 'Downloaded At', 'Verification Code'];
  
  let content = `
    <html>
    <head>
      <title>Certificates Export</title>
      <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        h1 { color: #333; }
      </style>
    </head>
    <body>
      <h1>Certificates List</h1>
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
              <td>${row.certificate_number || ''}</td>
              <td>${row.event?.title || '—'}</td>
              <td>${row.user ? `${row.user.first_name} ${row.user.last_name}` : '—'}</td>
              <td>${row.issued_at ? formatDate(row.issued_at) : '—'}</td>
              <td>${row.downloaded_at ? formatDate(row.downloaded_at) : 'Not Downloaded'}</td>
              <td>${row.verification_code || '—'}</td>
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
  const tableData = certificates.value.data || [];
  
  // Create CSV content
  const headers = ['Certificate Number', 'Event', 'User', 'Issued At', 'Downloaded At', 'Verification Code', 'Created At'];
  const rows = tableData.map(row => [
    row.certificate_number || '',
    row.event?.title || '',
    row.user ? `${row.user.first_name} ${row.user.last_name}` : '',
    row.issued_at ? formatDate(row.issued_at) : '',
    row.downloaded_at ? formatDate(row.downloaded_at) : 'Not Downloaded',
    row.verification_code || '',
    row.created_at ? dayjs(row.created_at).format('YYYY-MM-DD HH:mm:ss') : '',
  ]);
  
  const csvContent = [
    headers.join(','),
    ...rows.map(row => row.map(cell => `"${String(cell).replace(/"/g, '""')}"`).join(','))
  ].join('\n');
  
  // Create blob and download
  const blob = new Blob(['\ufeff' + csvContent], { type: 'text/csv;charset=utf-8;' });
  const link = document.createElement('a');
  const url = URL.createObjectURL(blob);
  link.setAttribute('href', url);
  link.setAttribute('download', `certificates_${dayjs().format('YYYY-MM-DD')}.csv`);
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

.certificates-card {
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

.certificate-number-cell {
  display: flex;
  align-items: center;
}

.certificate-number {
  font-weight: 500;
  font-family: monospace;
}

.verification-code {
  font-family: monospace;
  font-size: 12px;
}

.text-muted {
  color: var(--text-tertiary, #8c8c8c);
}

[data-theme="dark"] .text-muted {
  color: rgba(255, 255, 255, 0.45);
}
</style>

