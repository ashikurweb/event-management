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

    <a-card class="reviews-card">
      <template #title>
        <div class="card-header">
          <h2 class="card-title">Reviews & Ratings</h2>
        </div>
      </template>

      <!-- Filters -->
      <div class="filters-section">
        <a-row :gutter="16">
          <a-col :xs="24" :sm="12" :md="6">
            <a-input
              v-model:value="filters.search"
              placeholder="Search reviews..."
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
              show-search
              :filter-option="filterOption"
              style="width: 100%"
              @change="handleSearch"
            >
              <a-select-option v-for="event in events" :key="event.id" :value="event.id">
                {{ event.title }}
              </a-select-option>
            </a-select>
          </a-col>
          <a-col :xs="24" :sm="12" :md="6">
            <a-select
              v-model:value="filters.status"
              placeholder="Filter by status"
              allow-clear
              style="width: 100%"
              @change="handleSearch"
            >
              <a-select-option value="pending">Pending</a-select-option>
              <a-select-option value="approved">Approved</a-select-option>
              <a-select-option value="rejected">Rejected</a-select-option>
            </a-select>
          </a-col>
          <a-col :xs="24" :sm="12" :md="6">
            <a-select
              v-model:value="filters.rating"
              placeholder="Filter by rating"
              allow-clear
              style="width: 100%"
              @change="handleSearch"
            >
              <a-select-option value="5">5 Stars</a-select-option>
              <a-select-option value="4">4 Stars</a-select-option>
              <a-select-option value="3">3 Stars</a-select-option>
              <a-select-option value="2">2 Stars</a-select-option>
              <a-select-option value="1">1 Star</a-select-option>
            </a-select>
          </a-col>
        </a-row>
        <a-row :gutter="16" style="margin-top: 12px;">
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
              v-model:value="filters.is_verified_attendee"
              placeholder="Verified Attendee"
              allow-clear
              style="width: 100%"
              @change="handleSearch"
            >
              <a-select-option value="1">Yes</a-select-option>
              <a-select-option value="0">No</a-select-option>
            </a-select>
          </a-col>
          <a-col :xs="24" :sm="12" :md="8">
            <a-button type="primary" @click="handleSearch" block>
              <template #icon><SearchOutlined /></template>
              Search
            </a-button>
          </a-col>
        </a-row>
      </div>

      <!-- Bulk Actions -->
      <div v-if="selectedRowKeys.length > 0" class="bulk-actions">
        <span class="selected-count">
          {{ selectedRowKeys.length }} selected
        </span>
        <a-space>
          <a-button @click="handleBulkAction('approve')">Approve</a-button>
          <a-button @click="handleBulkAction('reject')">Reject</a-button>
          <a-button danger @click="handleBulkAction('delete')">Delete</a-button>
        </a-space>
      </div>

      <!-- Table -->
      <a-table
        :columns="columns"
        :data-source="reviews.data"
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
          <template v-if="column.key === 'event'">
            <a-typography-link @click="handleViewEvent(record.event)">
              {{ record.event?.title || '—' }}
            </a-typography-link>
          </template>

          <template v-if="column.key === 'user'">
            <div class="user-cell">
              <a-avatar :src="getAvatarUrl(record.user?.avatar)" :size="32">
                {{ getUserInitials(record.user) }}
              </a-avatar>
              <div class="user-info">
                <div class="user-name">{{ record.user?.name || '—' }}</div>
                <div class="user-email">{{ record.user?.email || '' }}</div>
              </div>
            </div>
          </template>

          <template v-if="column.key === 'rating'">
            <a-rate :value="record.rating" disabled allow-half />
            <span class="rating-text">({{ record.rating }})</span>
          </template>

          <template v-if="column.key === 'status'">
            <a-tag :color="getStatusColor(record.status)">
              {{ record.status?.charAt(0).toUpperCase() + record.status?.slice(1) }}
            </a-tag>
          </template>

          <template v-if="column.key === 'is_verified_attendee'">
            <a-tag v-if="record.is_verified_attendee" color="green">
              <template #icon><CheckCircleOutlined /></template>
              Verified
            </a-tag>
            <span v-else class="text-muted">—</span>
          </template>

          <template v-if="column.key === 'stats'">
            <div class="stats-cell">
              <a-tooltip title="Helpful Count">
                <span class="stat-item">
                  <LikeOutlined /> {{ record.helpful_count || 0 }}
                </span>
              </a-tooltip>
              <a-tooltip title="Replies Count">
                <span class="stat-item">
                  <MessageOutlined /> {{ record.replies_count || 0 }}
                </span>
              </a-tooltip>
              <a-tooltip title="Reported Count">
                <span class="stat-item" :class="{ 'has-reports': record.reported_count > 0 }">
                  <FlagOutlined /> {{ record.reported_count || 0 }}
                </span>
              </a-tooltip>
            </div>
          </template>

          <template v-if="column.key === 'actions'">
            <a-space>
              <a-button type="link" size="small" @click="handleView(record)">
                <template #icon><EyeOutlined /></template>
              </a-button>
              <a-button
                v-if="record.status === 'pending'"
                type="link"
                size="small"
                @click="handleApprove(record)"
              >
                <template #icon><CheckCircleOutlined /></template>
              </a-button>
              <a-button
                v-if="record.status === 'pending'"
                type="link"
                size="small"
                danger
                @click="handleReject(record)"
              >
                <template #icon><CloseCircleOutlined /></template>
              </a-button>
              <a-popconfirm
                title="Are you sure you want to delete this review?"
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
import { message } from 'ant-design-vue';
import DashboardLayout from '../../../Layouts/DashboardLayout.vue';
import Breadcrumb from '../../../Components/Breadcrumb.vue';
import Pagination from '../../../Components/Pagination.vue';
import {
  SearchOutlined,
  EyeOutlined,
  DeleteOutlined,
  ReloadOutlined,
  FilePdfOutlined,
  FileExcelOutlined,
  CheckCircleOutlined,
  CloseCircleOutlined,
  LikeOutlined,
  MessageOutlined,
  FlagOutlined,
} from '@ant-design/icons-vue';
import dayjs from 'dayjs';

const page = usePage();

const reviews = computed(() => page.props.reviews || { data: [] });
const events = computed(() => page.props.events || []);
const users = computed(() => page.props.users || []);
const initialFilters = page.props.filters || {};
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
  { label: 'Reviews & Ratings' },
]);

const columns = [
  {
    title: 'Event',
    key: 'event',
    dataIndex: ['event', 'title'],
    width: 200,
  },
  {
    title: 'User',
    key: 'user',
    dataIndex: ['user', 'name'],
    width: 200,
  },
  {
    title: 'Rating',
    key: 'rating',
    dataIndex: 'rating',
    align: 'center',
    width: 150,
  },
  {
    title: 'Title',
    key: 'title',
    dataIndex: 'title',
    ellipsis: true,
  },
  {
    title: 'Status',
    key: 'status',
    dataIndex: 'status',
    align: 'center',
    width: 120,
  },
  {
    title: 'Verified',
    key: 'is_verified_attendee',
    align: 'center',
    width: 100,
  },
  {
    title: 'Stats',
    key: 'stats',
    align: 'center',
    width: 150,
  },
  {
    title: 'Created',
    dataIndex: 'created_at',
    key: 'created_at',
    width: 180,
  },
  {
    title: 'Actions',
    key: 'actions',
    align: 'right',
    width: 200,
    fixed: 'right',
  },
];

const pagination = computed(() => ({
  current: reviews.value.current_page || 1,
  pageSize: reviews.value.per_page || 15,
  total: reviews.value.total || 0,
  showSizeChanger: true,
  showTotal: (total) => `Total ${total} reviews`,
}));

const filterOption = (input, option) => {
  return option.children[0].children.toLowerCase().indexOf(input.toLowerCase()) >= 0;
};

const getStatusColor = (status) => {
  const colors = {
    pending: 'orange',
    approved: 'green',
    rejected: 'red',
  };
  return colors[status] || 'default';
};

const getAvatarUrl = (avatar) => {
  if (!avatar) return null;
  if (avatar.startsWith('http')) return avatar;
  return `/storage/${avatar}`;
};

const getUserInitials = (user) => {
  if (!user || !user.name) return 'U';
  const names = user.name.split(' ');
  if (names.length >= 2) {
    return (names[0][0] + names[1][0]).toUpperCase();
  }
  return names[0][0].toUpperCase();
};

const handleSearch = () => {
  router.get('/dashboard/reviews/search', filters.value, {
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

  router.get('/dashboard/reviews/search', { ...filters.value, ...params }, {
    preserveState: true,
    preserveScroll: true,
  });
};

const handlePaginationChange = ({ current, pageSize }) => {
  const params = {
    page: current,
    per_page: pageSize,
  };

  router.get('/dashboard/reviews/search', { ...filters.value, ...params }, {
    preserveState: true,
    preserveScroll: true,
  });
};

const handlePageSizeChange = ({ current, pageSize }) => {
  const params = {
    page: current,
    per_page: pageSize,
  };

  router.get('/dashboard/reviews/search', { ...filters.value, ...params }, {
    preserveState: true,
    preserveScroll: true,
  });
};

const onSelectChange = (keys) => {
  selectedRowKeys.value = keys;
};

const handleView = (record) => {
  router.visit(`/dashboard/reviews/${record.id}`);
};

const handleViewEvent = (event) => {
  if (event?.id) {
    router.visit(`/dashboard/events/${event.id}`);
  }
};

const handleApprove = (record) => {
  router.post(`/dashboard/reviews/${record.id}/approve`, {}, {
    preserveScroll: true,
    onSuccess: () => {
      message.success('Review approved successfully');
    },
  });
};

const handleReject = (record) => {
  router.post(`/dashboard/reviews/${record.id}/reject`, {}, {
    preserveScroll: true,
    onSuccess: () => {
      message.success('Review rejected successfully');
    },
  });
};

const handleDelete = (record) => {
  router.delete(`/dashboard/reviews/${record.id}`, {
    preserveScroll: true,
    onSuccess: () => {
      message.success('Review deleted successfully');
    },
  });
};

const handleBulkAction = (action) => {
  if (selectedRowKeys.value.length === 0) {
    message.warning('Please select at least one review');
    return;
  }

  const count = selectedRowKeys.value.length;
  router.post(
    '/dashboard/reviews/bulk-action',
    {
      action,
      ids: selectedRowKeys.value,
    },
    {
      preserveScroll: true,
      onSuccess: () => {
        selectedRowKeys.value = [];
        message.success(`${count} review(s) ${action}d successfully`);
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
  filters.value = {};
  dateRange.value = null;
  selectedRowKeys.value = [];
  
  router.visit('/dashboard/reviews', {
    preserveState: false,
    preserveScroll: false,
  });
};

const handleExportPDF = () => {
  const tableData = reviews.value.data || [];
  const columns = ['Event', 'User', 'Rating', 'Title', 'Status', 'Created At'];
  
  let content = `
    <html>
    <head>
      <title>Reviews Export</title>
      <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        h1 { color: #333; }
      </style>
    </head>
    <body>
      <h1>Reviews & Ratings List</h1>
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
              <td>${row.event?.title || ''}</td>
              <td>${row.user?.name || ''}</td>
              <td>${row.rating || ''}</td>
              <td>${row.title || ''}</td>
              <td>${row.status || ''}</td>
              <td>${row.created_at ? dayjs(row.created_at).format('YYYY-MM-DD HH:mm:ss') : ''}</td>
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
  const tableData = reviews.value.data || [];
  
  const headers = ['Event', 'User', 'Email', 'Rating', 'Title', 'Comment', 'Status', 'Verified', 'Helpful', 'Replies', 'Reported', 'Created At'];
  const rows = tableData.map(row => [
    row.event?.title || '',
    row.user?.name || '',
    row.user?.email || '',
    row.rating || '',
    row.title || '',
    row.comment || '',
    row.status || '',
    row.is_verified_attendee ? 'Yes' : 'No',
    row.helpful_count || 0,
    row.replies_count || 0,
    row.reported_count || 0,
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
  link.setAttribute('download', `reviews_${dayjs().format('YYYY-MM-DD')}.csv`);
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

.reviews-card {
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

.user-cell {
  display: flex;
  align-items: center;
  gap: 8px;
}

.user-info {
  display: flex;
  flex-direction: column;
}

.user-name {
  font-weight: 500;
  font-size: 14px;
}

.user-email {
  font-size: 12px;
  color: var(--text-secondary, #8c8c8c);
}

.rating-text {
  margin-left: 8px;
  font-size: 14px;
  color: var(--text-secondary, #8c8c8c);
}

.stats-cell {
  display: flex;
  gap: 12px;
  justify-content: center;
  align-items: center;
}

.stat-item {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 14px;
  color: var(--text-secondary, #8c8c8c);
}

.stat-item.has-reports {
  color: #ff4d4f;
}

.text-muted {
  color: var(--text-tertiary, #8c8c8c);
}

[data-theme="dark"] .text-muted {
  color: rgba(255, 255, 255, 0.45);
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
</style>

