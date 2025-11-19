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

    <a-card class="sponsors-card">
      <template #title>
        <div class="card-title-wrapper">
          <h2 class="card-title">All Sponsors</h2>
          <a-button type="primary" @click="handleCreate">
            <template #icon><PlusOutlined /></template>
            Add Sponsor
          </a-button>
        </div>
      </template>

      <!-- Filters -->
      <div class="filters-section">
        <a-row :gutter="16">
          <a-col :xs="24" :sm="12" :md="8">
            <a-input
              v-model:value="filters.search"
              placeholder="Search sponsors..."
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
              v-model:value="filters.tier"
              placeholder="Filter by tier"
              allow-clear
              style="width: 100%"
              @change="handleSearch"
            >
              <a-select-option value="platinum">Platinum</a-select-option>
              <a-select-option value="gold">Gold</a-select-option>
              <a-select-option value="silver">Silver</a-select-option>
              <a-select-option value="bronze">Bronze</a-select-option>
              <a-select-option value="partner">Partner</a-select-option>
            </a-select>
          </a-col>
          <a-col :xs="24" :sm="12" :md="4">
            <a-select
              v-model:value="filters.is_active"
              placeholder="Status"
              allow-clear
              style="width: 100%"
              @change="handleSearch"
            >
              <a-select-option value="1">Active</a-select-option>
              <a-select-option value="0">Inactive</a-select-option>
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
          <a-button @click="handleBulkAction('deactivate')">Deactivate</a-button>
          <a-button danger @click="handleBulkAction('delete')">Delete</a-button>
        </a-space>
      </div>

      <!-- Table -->
      <a-table
        :columns="columns"
        :data-source="sponsors.data"
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
          <template v-if="column.key === 'logo'">
            <a-avatar
              v-if="record.logo_url"
              :src="record.logo_url"
              :size="50"
              shape="square"
              class="sponsor-logo-avatar"
            />
            <a-avatar v-else :size="50" shape="square">
              <template #icon><StarOutlined /></template>
            </a-avatar>
          </template>

          <template v-if="column.key === 'name'">
            <div class="sponsor-name-cell">
              <span class="sponsor-name">{{ record.name }}</span>
            </div>
          </template>

          <template v-if="column.key === 'tier'">
            <a-tag :color="getTierColor(record.tier)">
              {{ record.tier.toUpperCase() }}
            </a-tag>
          </template>

          <template v-if="column.key === 'is_active'">
            <a-tag :color="record.is_active ? 'green' : 'red'">
              {{ record.is_active ? 'Active' : 'Inactive' }}
            </a-tag>
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
                title="Are you sure you want to delete this sponsor?"
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
  StarOutlined,
} from '@ant-design/icons-vue';
import dayjs from 'dayjs';

const page = usePage();

const sponsors = computed(() => page.props.sponsors || { data: [] });
const initialFilters = page.props.filters || {};
if (initialFilters.is_active !== undefined && initialFilters.is_active !== null && initialFilters.is_active !== '') {
  initialFilters.is_active = initialFilters.is_active ? '1' : '0';
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
  { label: 'Sponsors' },
]);

const getTierColor = (tier) => {
  const colors = {
    platinum: 'purple',
    gold: 'gold',
    silver: 'default',
    bronze: 'orange',
    partner: 'blue',
  };
  return colors[tier] || 'default';
};

const columns = [
  {
    title: 'Logo',
    key: 'logo',
    width: 80,
    align: 'center',
  },
  {
    title: 'Name',
    key: 'name',
    dataIndex: 'name',
    sorter: true,
  },
  {
    title: 'Tier',
    key: 'tier',
    dataIndex: 'tier',
    align: 'center',
    sorter: true,
  },
  {
    title: 'Display Order',
    dataIndex: 'display_order',
    key: 'display_order',
    align: 'center',
    sorter: true,
  },
  {
    title: 'Status',
    key: 'is_active',
    dataIndex: 'is_active',
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
  current: sponsors.value.current_page || 1,
  pageSize: sponsors.value.per_page || 15,
  total: sponsors.value.total || 0,
  showSizeChanger: true,
  showTotal: (total) => `Total ${total} sponsors`,
}));

const handleCreate = () => {
  router.visit('/dashboard/sponsors/create');
};

const handleSearch = () => {
  router.get('/dashboard/sponsors/search', filters.value, {
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

  router.get('/dashboard/sponsors/search', { ...filters.value, ...params }, {
    preserveState: true,
    preserveScroll: true,
  });
};

const handlePaginationChange = ({ current, pageSize }) => {
  const params = {
    page: current,
    per_page: pageSize,
  };

  router.get('/dashboard/sponsors/search', { ...filters.value, ...params }, {
    preserveState: true,
    preserveScroll: true,
  });
};

const handlePageSizeChange = ({ current, pageSize }) => {
  const params = {
    page: current,
    per_page: pageSize,
  };

  router.get('/dashboard/sponsors/search', { ...filters.value, ...params }, {
    preserveState: true,
    preserveScroll: true,
  });
};

const onSelectChange = (keys) => {
  selectedRowKeys.value = keys;
};

const handleView = (record) => {
  router.visit(`/dashboard/sponsors/${record.id}`);
};

const handleEdit = (record) => {
  router.visit(`/dashboard/sponsors/${record.id}/edit`);
};

const handleDelete = (record) => {
  router.delete(`/dashboard/sponsors/${record.id}`, {
    preserveScroll: true,
    onSuccess: () => {
      // Success handled by Inertia
    },
  });
};

const handleBulkAction = (action) => {
  if (selectedRowKeys.value.length === 0) return;

  router.post(
    '/dashboard/sponsors/bulk-action',
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
    tier: '',
    is_active: '',
  };
  dateRange.value = null;
  selectedRowKeys.value = [];
  
  router.visit('/dashboard/sponsors', {
    preserveState: false,
    preserveScroll: false,
  });
};

const handleExportPDF = () => {
  const tableData = sponsors.value.data || [];
  const columns = ['Name', 'Tier', 'Display Order', 'Status', 'Website'];
  
  let content = `
    <html>
    <head>
      <title>Sponsors Export</title>
      <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        h1 { color: #333; }
      </style>
    </head>
    <body>
      <h1>Sponsors List</h1>
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
              <td>${row.name || ''}</td>
              <td>${row.tier ? row.tier.toUpperCase() : ''}</td>
              <td>${row.display_order || 0}</td>
              <td>${row.is_active ? 'Active' : 'Inactive'}</td>
              <td>${row.website || ''}</td>
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
  const tableData = sponsors.value.data || [];
  
  const headers = ['Name', 'Tier', 'Display Order', 'Status', 'Website', 'Description', 'Created At'];
  const rows = tableData.map(row => [
    row.name || '',
    row.tier ? row.tier.toUpperCase() : '',
    row.display_order || 0,
    row.is_active ? 'Active' : 'Inactive',
    row.website || '',
    row.description || '',
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
  link.setAttribute('download', `sponsors_${dayjs().format('YYYY-MM-DD')}.csv`);
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

.sponsors-card {
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

.sponsor-name-cell {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.sponsor-name {
  font-weight: 500;
}

.sponsor-logo-avatar {
  cursor: pointer;
}
</style>

