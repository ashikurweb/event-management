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

    <a-card class="team-invitations-card">
      <template #title>
        <div class="card-header">
          <h2 class="card-title">Team Invitations</h2>
          <a-button type="primary" @click="handleCreate">
            <template #icon><PlusOutlined /></template>
            Send Invitation
          </a-button>
        </div>
      </template>

      <!-- Filters -->
      <div class="filters-section">
        <a-row :gutter="16">
          <a-col :xs="24" :sm="12" :md="8">
            <a-input
              v-model:value="filters.search"
              placeholder="Search by email, token, or team..."
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
              v-model:value="filters.status"
              placeholder="Filter by status"
              allow-clear
              style="width: 100%"
              @change="handleSearch"
            >
              <a-select-option value="pending">Pending</a-select-option>
              <a-select-option value="accepted">Accepted</a-select-option>
              <a-select-option value="rejected">Rejected</a-select-option>
              <a-select-option value="expired">Expired</a-select-option>
            </a-select>
          </a-col>
        </a-row>
        <a-row :gutter="16" style="margin-top: 12px">
          <a-col :xs="24" :sm="12" :md="8">
            <a-select
              v-model:value="filters.team_id"
              placeholder="Filter by team"
              allow-clear
              show-search
              :filter-option="filterTeamOption"
              style="width: 100%"
              @change="handleSearch"
            >
              <a-select-option
                v-for="team in teams"
                :key="team.id"
                :value="team.id"
              >
                {{ team.name }}
              </a-select-option>
            </a-select>
          </a-col>
          <a-col :xs="24" :sm="12" :md="8">
            <a-select
              v-model:value="filters.role"
              placeholder="Filter by role"
              allow-clear
              style="width: 100%"
              @change="handleSearch"
            >
              <a-select-option value="admin">Admin</a-select-option>
              <a-select-option value="manager">Manager</a-select-option>
              <a-select-option value="member">Member</a-select-option>
            </a-select>
          </a-col>
          <a-col :xs="24" :sm="12" :md="8">
            <a-select
              v-model:value="filters.expired"
              placeholder="Filter by expiration"
              allow-clear
              style="width: 100%"
              @change="handleSearch"
            >
              <a-select-option value="1">Expired</a-select-option>
              <a-select-option value="0">Not Expired</a-select-option>
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
          <a-button @click="handleBulkAction('resend')">Resend Selected</a-button>
          <a-button danger @click="handleBulkAction('delete')">Delete Selected</a-button>
        </a-space>
      </div>

      <!-- Table -->
      <a-table
        :columns="columns"
        :data-source="teamInvitations.data"
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
          <template v-if="column.key === 'email'">
            <div class="email-cell">
              <span>{{ record.email }}</span>
            </div>
          </template>

          <template v-if="column.key === 'team'">
            <a-tag v-if="record.team" color="blue">
              {{ record.team.name }}
            </a-tag>
            <span v-else class="text-muted">—</span>
          </template>

          <template v-if="column.key === 'role'">
            <a-tag :color="getRoleColor(record.role)">
              {{ record.role }}
            </a-tag>
          </template>

          <template v-if="column.key === 'status'">
            <a-tag :color="getStatusColor(record.status)">
              {{ record.status }}
            </a-tag>
          </template>

          <template v-if="column.key === 'invited_by'">
            <span v-if="record.invited_by">
              {{ `${record.invited_by.first_name || ''} ${record.invited_by.last_name || ''}`.trim() || 'N/A' }}
            </span>
            <span v-else class="text-muted">—</span>
          </template>

          <template v-if="column.key === 'expires_at'">
            <span :class="{ 'expired-text': isExpired(record.expires_at) }">
              {{ formatDate(record.expires_at) }}
            </span>
          </template>

          <template v-if="column.key === 'actions'">
            <a-space>
              <a-button type="link" size="small" @click="handleView(record)">
                <template #icon><EyeOutlined /></template>
              </a-button>
              <a-button type="link" size="small" @click="handleEdit(record)">
                <template #icon><EditOutlined /></template>
              </a-button>
              <a-button 
                v-if="record.status === 'pending' && !isExpired(record.expires_at)"
                type="link" 
                size="small" 
                @click="handleResend(record)"
              >
                <template #icon><SendOutlined /></template>
              </a-button>
              <a-popconfirm
                title="Are you sure you want to delete this invitation?"
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
  SendOutlined,
} from '@ant-design/icons-vue';
import dayjs from 'dayjs';

const page = usePage();

const teamInvitations = computed(() => page.props.teamInvitations || { data: [] });
const teams = computed(() => page.props.teams || []);
const users = computed(() => page.props.users || []);
const initialFilters = page.props.filters || {};
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
  { label: 'Team Invitations' },
]);

const columns = [
  {
    title: 'Email',
    key: 'email',
    dataIndex: 'email',
  },
  {
    title: 'Team',
    key: 'team',
    dataIndex: 'team',
  },
  {
    title: 'Role',
    key: 'role',
    dataIndex: 'role',
    align: 'center',
  },
  {
    title: 'Status',
    key: 'status',
    dataIndex: 'status',
    align: 'center',
  },
  {
    title: 'Invited By',
    key: 'invited_by',
    dataIndex: 'invited_by',
  },
  {
    title: 'Expires At',
    key: 'expires_at',
    dataIndex: 'expires_at',
  },
  {
    title: 'Actions',
    key: 'actions',
    align: 'right',
    width: 200,
  },
];

const pagination = computed(() => ({
  current: teamInvitations.value.current_page || 1,
  pageSize: teamInvitations.value.per_page || 15,
  total: teamInvitations.value.total || 0,
  showSizeChanger: true,
  showTotal: (total) => `Total ${total} invitations`,
}));

const filterTeamOption = (input, option) => {
  return option.children[0].children.toLowerCase().indexOf(input.toLowerCase()) >= 0;
};

const getRoleColor = (role) => {
  const roleColors = {
    admin: 'orange',
    manager: 'blue',
    member: 'default',
  };
  return roleColors[role] || 'default';
};

const getStatusColor = (status) => {
  const statusColors = {
    pending: 'orange',
    accepted: 'green',
    rejected: 'red',
    expired: 'default',
  };
  return statusColors[status] || 'default';
};

const isExpired = (expiresAt) => {
  if (!expiresAt) return false;
  return dayjs(expiresAt).isBefore(dayjs());
};

const formatDate = (dateString) => {
  if (!dateString) return '—';
  return dayjs(dateString).format('YYYY-MM-DD HH:mm');
};

const handleSearch = () => {
  router.get('/dashboard/team-invitations/search', filters.value, {
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

  router.get('/dashboard/team-invitations/search', { ...filters.value, ...params }, {
    preserveState: true,
    preserveScroll: true,
  });
};

const handlePaginationChange = ({ current, pageSize }) => {
  const params = {
    page: current,
    per_page: pageSize,
  };

  router.get('/dashboard/team-invitations/search', { ...filters.value, ...params }, {
    preserveState: true,
    preserveScroll: true,
  });
};

const handlePageSizeChange = ({ current, pageSize }) => {
  const params = {
    page: current,
    per_page: pageSize,
  };

  router.get('/dashboard/team-invitations/search', { ...filters.value, ...params }, {
    preserveState: true,
    preserveScroll: true,
  });
};

const onSelectChange = (keys) => {
  selectedRowKeys.value = keys;
};

const handleCreate = () => {
  router.visit('/dashboard/team-invitations/create');
};

const handleView = (record) => {
  router.visit(`/dashboard/team-invitations/${record.id}`);
};

const handleEdit = (record) => {
  router.visit(`/dashboard/team-invitations/${record.id}/edit`);
};

const handleDelete = (record) => {
  router.delete(`/dashboard/team-invitations/${record.id}`, {
    preserveScroll: true,
    onSuccess: () => {
      // Success handled by Inertia
    },
  });
};

const handleResend = (record) => {
  router.post(`/dashboard/team-invitations/${record.id}/resend`, {}, {
    preserveScroll: true,
    onSuccess: () => {
      // Success handled by Inertia
    },
  });
};

const handleBulkAction = (action) => {
  if (selectedRowKeys.value.length === 0) return;

  router.post(
    '/dashboard/team-invitations/bulk-action',
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
    team_id: null,
    status: null,
    role: null,
    expired: null,
  };
  dateRange.value = null;
  selectedRowKeys.value = [];
  
  // Reload page without filters
  router.visit('/dashboard/team-invitations', {
    preserveState: false,
    preserveScroll: false,
  });
};

const handleExportPDF = () => {
  const tableData = teamInvitations.value.data || [];
  const columns = ['Email', 'Team', 'Role', 'Status', 'Invited By', 'Expires At'];
  
  let content = `
    <html>
    <head>
      <title>Team Invitations Export</title>
      <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        h1 { color: #333; }
      </style>
    </head>
    <body>
      <h1>Team Invitations List</h1>
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
              <td>${row.email || '—'}</td>
              <td>${row.team?.name || '—'}</td>
              <td>${row.role || '—'}</td>
              <td>${row.status || '—'}</td>
              <td>${row.invited_by ? `${row.invited_by.first_name || ''} ${row.invited_by.last_name || ''}`.trim() : '—'}</td>
              <td>${row.expires_at ? dayjs(row.expires_at).format('YYYY-MM-DD HH:mm') : '—'}</td>
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
  const tableData = teamInvitations.value.data || [];
  
  // Create CSV content
  const headers = ['Email', 'Team', 'Role', 'Status', 'Invited By', 'Expires At', 'Created At'];
  const rows = tableData.map(row => [
    row.email || '',
    row.team?.name || '',
    row.role || '',
    row.status || '',
    row.invited_by ? `${row.invited_by.first_name || ''} ${row.invited_by.last_name || ''}`.trim() : '',
    row.expires_at ? dayjs(row.expires_at).format('YYYY-MM-DD HH:mm:ss') : '',
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
  link.setAttribute('download', `team_invitations_${dayjs().format('YYYY-MM-DD')}.csv`);
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

.team-invitations-card {
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

.email-cell {
  font-weight: 500;
}

.expired-text {
  color: var(--text-tertiary, #8c8c8c);
  text-decoration: line-through;
}

[data-theme="dark"] .expired-text {
  color: rgba(255, 255, 255, 0.45);
}

.text-muted {
  color: var(--text-tertiary, #8c8c8c);
}

[data-theme="dark"] .text-muted {
  color: rgba(255, 255, 255, 0.45);
}
</style>

