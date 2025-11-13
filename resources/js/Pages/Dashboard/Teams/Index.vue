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

    <a-card class="teams-card">
      <template #title>
        <div class="card-header">
          <h2 class="card-title">Teams</h2>
          <a-button type="primary" @click="handleCreate">
            <template #icon><PlusOutlined /></template>
            Create Team
          </a-button>
        </div>
      </template>

      <!-- Filters -->
      <div class="filters-section">
        <a-row :gutter="16">
          <a-col :xs="24" :sm="12" :md="8">
            <a-input
              v-model:value="filters.search"
              placeholder="Search teams..."
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
          <a-col :xs="24" :sm="12" :md="8">
            <a-select
              v-model:value="filters.is_verified"
              placeholder="Filter by verification"
              allow-clear
              style="width: 100%"
              @change="handleSearch"
            >
              <a-select-option value="1">Verified</a-select-option>
              <a-select-option value="0">Unverified</a-select-option>
            </a-select>
          </a-col>
        </a-row>
        <a-row :gutter="16" style="margin-top: 12px">
          <a-col :xs="24" :sm="12" :md="8">
            <a-select
              v-model:value="filters.owner_id"
              placeholder="Filter by owner"
              allow-clear
              show-search
              :filter-option="filterOption"
              style="width: 100%"
              @change="handleSearch"
            >
              <a-select-option
                v-for="user in users"
                :key="user.id"
                :value="user.id"
              >
                {{ `${user.first_name || ''} ${user.last_name || ''}`.trim() || 'N/A' }} ({{ user.email }})
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
          <a-button @click="handleBulkAction('verify')">Verify</a-button>
          <a-button @click="handleBulkAction('unverify')">Unverify</a-button>
          <a-button danger @click="handleBulkAction('delete')">Delete</a-button>
        </a-space>
      </div>

      <!-- Table -->
      <a-table
        :columns="columns"
        :data-source="teams.data"
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
          <template v-if="column.key === 'name'">
            <div class="team-name-cell">
              <span class="team-name">{{ record.name }}</span>
            </div>
          </template>

          <template v-if="column.key === 'owner'">
            <span v-if="record.owner">{{ `${record.owner.first_name || ''} ${record.owner.last_name || ''}`.trim() || 'N/A' }}</span>
            <span v-else class="text-muted">—</span>
          </template>

          <template v-if="column.key === 'is_verified'">
            <a-tag :color="record.is_verified ? 'green' : 'orange'">
              {{ record.is_verified ? 'Verified' : 'Unverified' }}
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
                title="Are you sure you want to delete this team?"
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

const teams = computed(() => page.props.teams || { data: [] });
const users = computed(() => page.props.users || []);
const initialFilters = page.props.filters || {};
// Convert boolean is_verified to string for select component
if (initialFilters.is_verified !== undefined && initialFilters.is_verified !== null && initialFilters.is_verified !== '') {
  initialFilters.is_verified = initialFilters.is_verified ? '1' : '0';
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
  { label: 'Teams' },
]);

const columns = [
  {
    title: 'Name',
    key: 'name',
    dataIndex: 'name',
    sorter: true,
  },
  {
    title: 'Slug',
    dataIndex: 'slug',
    key: 'slug',
  },
  {
    title: 'Owner',
    key: 'owner',
    dataIndex: 'owner',
  },
  {
    title: 'Email',
    dataIndex: 'email',
    key: 'email',
  },
  {
    title: 'Phone',
    dataIndex: 'phone',
    key: 'phone',
  },
  {
    title: 'Status',
    key: 'is_verified',
    dataIndex: 'is_verified',
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
  current: teams.value.current_page || 1,
  pageSize: teams.value.per_page || 15,
  total: teams.value.total || 0,
  showSizeChanger: true,
  showTotal: (total) => `Total ${total} teams`,
}));

const filterOption = (input, option) => {
  return option.children[0].children.toLowerCase().indexOf(input.toLowerCase()) >= 0;
};

const handleSearch = () => {
  router.get('/dashboard/teams/search', filters.value, {
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

  router.get('/dashboard/teams/search', { ...filters.value, ...params }, {
    preserveState: true,
    preserveScroll: true,
  });
};

const handlePaginationChange = ({ current, pageSize }) => {
  const params = {
    page: current,
    per_page: pageSize,
  };

  router.get('/dashboard/teams/search', { ...filters.value, ...params }, {
    preserveState: true,
    preserveScroll: true,
  });
};

const handlePageSizeChange = ({ current, pageSize }) => {
  const params = {
    page: current,
    per_page: pageSize,
  };

  router.get('/dashboard/teams/search', { ...filters.value, ...params }, {
    preserveState: true,
    preserveScroll: true,
  });
};

const onSelectChange = (keys) => {
  selectedRowKeys.value = keys;
};

const handleCreate = () => {
  router.visit('/dashboard/teams/create');
};

const handleView = (record) => {
  router.visit(`/dashboard/teams/${record.slug}`);
};

const handleEdit = (record) => {
  router.visit(`/dashboard/teams/${record.id}/edit`);
};

const handleDelete = (record) => {
  router.delete(`/dashboard/teams/${record.id}`, {
    preserveScroll: true,
    onSuccess: () => {
      // Success handled by Inertia
    },
  });
};

const handleBulkAction = (action) => {
  if (selectedRowKeys.value.length === 0) return;

  router.post(
    '/dashboard/teams/bulk-action',
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
  filters.value = {
    search: '',
    is_verified: '',
    owner_id: null,
  };
  dateRange.value = null;
  selectedRowKeys.value = [];
  
  // Reload page without filters
  router.visit('/dashboard/teams', {
    preserveState: false,
    preserveScroll: false,
  });
};

const handleExportPDF = () => {
  const tableData = teams.value.data || [];
  const columns = ['Name', 'Slug', 'Owner', 'Email', 'Phone', 'Status'];
  
  let content = `
    <html>
    <head>
      <title>Teams Export</title>
      <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        h1 { color: #333; }
      </style>
    </head>
    <body>
      <h1>Teams List</h1>
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
              <td>${row.slug || ''}</td>
              <td>${row.owner?.name || '—'}</td>
              <td>${row.email || '—'}</td>
              <td>${row.phone || '—'}</td>
              <td>${row.is_verified ? 'Verified' : 'Unverified'}</td>
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
  const tableData = teams.value.data || [];
  
  // Create CSV content
  const headers = ['Name', 'Slug', 'Owner', 'Email', 'Phone', 'Status', 'Created At'];
  const rows = tableData.map(row => [
    row.name || '',
    row.slug || '',
    (row.owner ? `${row.owner.first_name || ''} ${row.owner.last_name || ''}`.trim() : '') || '',
    row.email || '',
    row.phone || '',
    row.is_verified ? 'Verified' : 'Unverified',
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
  link.setAttribute('download', `teams_${dayjs().format('YYYY-MM-DD')}.csv`);
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

.teams-card {
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

.team-name-cell {
  display: flex;
  align-items: center;
}

.team-name {
  font-weight: 500;
}

.text-muted {
  color: var(--text-tertiary, #8c8c8c);
}

[data-theme="dark"] .text-muted {
  color: rgba(255, 255, 255, 0.45);
}
</style>

