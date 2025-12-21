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

    <a-card class="events-card">
      <template #title>
        <div class="card-header">
          <h2 class="card-title">Events</h2>
          <a-button type="primary" @click="handleCreate">
            <template #icon>
              <PlusOutlined />
            </template>
            Create Event
          </a-button>
        </div>
      </template>

      <!-- Filters -->
      <div class="filters-section">
        <a-row :gutter="16">
          <a-col :xs="24" :sm="12" :md="12">
            <Search v-model="filters.search" placeholder="Search events..." @search="handleSearch" />
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
          <a-button danger @click="handleBulkAction('delete')">Delete</a-button>
          <a-button type="primary" @click="handleBulkAction('publish')">Publish</a-button>
          <a-button @click="handleBulkAction('unpublish')">Unpublish</a-button>
          <a-button @click="handleBulkAction('feature')">Feature</a-button>
          <a-button @click="handleBulkAction('unfeature')">Unfeature</a-button>
        </a-space>
      </div>

      <!-- Table -->
      <a-table :columns="columns" :data-source="events.data" :row-key="(record) => record.id" :pagination="false"
        :loading="loading" :row-selection="{
          selectedRowKeys: selectedRowKeys,
          onChange: onSelectChange,
        }" @change="handleTableChange">
        <template #bodyCell="{ column, record }">
          <template v-if="column.key === 'title'">
            <div class="event-title-cell">
              <span class="event-title">{{ record.title }}</span>
              <a-tag v-if="record.is_featured" color="gold">Featured</a-tag>
            </div>
          </template>

          <template v-if="column.key === 'organizer'">
            <span v-if="record.organizer">
              {{ record.organizer.first_name }} {{ record.organizer.last_name }}
            </span>
            <span v-else class="text-muted">—</span>
          </template>

          <template v-if="column.key === 'category'">
            <span v-if="record.category">{{ record.category.name }}</span>
            <span v-else class="text-muted">—</span>
          </template>

          <template v-if="column.key === 'event_type'">
            <a-tag :color="getEventTypeColor(record.event_type)">
              {{ record.event_type }}
            </a-tag>
          </template>

          <template v-if="column.key === 'status'">
            <a-tag :color="getStatusColor(record.status)">
              {{ record.status }}
            </a-tag>
          </template>

          <template v-if="column.key === 'visibility'">
            <a-tag :color="getVisibilityColor(record.visibility)">
              {{ record.visibility }}
            </a-tag>
          </template>

          <template v-if="column.key === 'start_date'">
            <span>{{ formatDate(record.start_date) }}</span>
          </template>

          <template v-if="column.key === 'end_date'">
            <span>{{ formatDate(record.end_date) }}</span>
          </template>

          <template v-if="column.key === 'tags'">
            <a-space v-if="record.tags && record.tags.length > 0" size="small" wrap>
              <a-tag v-for="tag in record.tags.slice(0, 3)" :key="tag.id" color="blue">
                {{ tag.name }}
              </a-tag>
              <a-tag v-if="record.tags.length > 3" color="default">
                +{{ record.tags.length - 3 }}
              </a-tag>
            </a-space>
            <span v-else class="text-muted">—</span>
          </template>

          <template v-if="column.key === 'actions'">
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
              <a-popconfirm title="Are you sure you want to delete this event?" ok-text="Yes" cancel-text="No"
                @confirm="handleDelete(record)">
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
  EyeOutlined,
  EditOutlined,
  DeleteOutlined,
  ReloadOutlined,
  FilePdfOutlined,
  FileExcelOutlined,
} from '@ant-design/icons-vue';
import dayjs from 'dayjs';

const page = usePage();

const events = computed(() => page.props.events || { data: [] });
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
  { label: 'Events' },
]);

const columns = [
  {
    title: 'Title',
    key: 'title',
    dataIndex: 'title',
    sorter: true,
  },
  {
    title: 'Organizer',
    key: 'organizer',
    dataIndex: 'organizer',
  },
  {
    title: 'Category',
    key: 'category',
    dataIndex: 'category',
  },
  {
    title: 'Type',
    key: 'event_type',
    dataIndex: 'event_type',
  },
  {
    title: 'Status',
    key: 'status',
    dataIndex: 'status',
  },
  {
    title: 'Visibility',
    key: 'visibility',
    dataIndex: 'visibility',
  },
  {
    title: 'Start Date',
    key: 'start_date',
    dataIndex: 'start_date',
    sorter: true,
  },
  {
    title: 'End Date',
    key: 'end_date',
    dataIndex: 'end_date',
    sorter: true,
  },
  {
    title: 'Tags',
    key: 'tags',
    dataIndex: 'tags',
  },
  {
    title: 'Actions',
    key: 'actions',
    align: 'right',
    width: 150,
  },
];

const pagination = computed(() => ({
  current: events.value.current_page || 1,
  pageSize: events.value.per_page || 15,
  total: events.value.total || 0,
  showSizeChanger: true,
  showTotal: (total) => `Total ${total} events`,
}));

const formatDate = (dateString) => {
  if (!dateString) return '—';
  return dayjs(dateString).format('YYYY-MM-DD HH:mm');
};

const getEventTypeColor = (type) => {
  const colors = {
    online: 'blue',
    offline: 'green',
    hybrid: 'orange',
  };
  return colors[type] || 'default';
};

const getStatusColor = (status) => {
  const colors = {
    draft: 'default',
    published: 'success',
    cancelled: 'error',
    completed: 'processing',
    postponed: 'warning',
  };
  return colors[status] || 'default';
};

const getVisibilityColor = (visibility) => {
  const colors = {
    public: 'success',
    private: 'error',
    unlisted: 'warning',
  };
  return colors[visibility] || 'default';
};

const handleSearch = () => {
  router.get('/dashboard/events/search', filters.value, {
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

  router.get('/dashboard/events/search', { ...filters.value, ...params }, {
    preserveState: true,
    preserveScroll: true,
  });
};

const handlePaginationChange = ({ current, pageSize }) => {
  const params = {
    page: current,
    per_page: pageSize,
  };

  router.get('/dashboard/events/search', { ...filters.value, ...params }, {
    preserveState: true,
    preserveScroll: true,
  });
};

const handlePageSizeChange = ({ current, pageSize }) => {
  const params = {
    page: current,
    per_page: pageSize,
  };

  router.get('/dashboard/events/search', { ...filters.value, ...params }, {
    preserveState: true,
    preserveScroll: true,
  });
};

const onSelectChange = (keys) => {
  selectedRowKeys.value = keys;
};

const handleCreate = () => {
  router.visit('/dashboard/events/create');
};

const handleView = (record) => {
  router.visit(`/dashboard/events/${record.id}`);
};

const handleEdit = (record) => {
  router.visit(`/dashboard/events/${record.id}/edit`);
};

const handleDelete = (record) => {
  router.delete(`/dashboard/events/${record.id}`, {
    preserveScroll: true,
  });
};

const handleBulkAction = (action) => {
  if (selectedRowKeys.value.length === 0) return;

  router.post(
    '/dashboard/events/bulk-action',
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
  filters.value = { search: '', date_from: '', date_to: '' };
  dateRange.value = null;
  selectedRowKeys.value = [];

  router.visit('/dashboard/events', {
    preserveState: false,
    preserveScroll: false,
  });
};

const handleExportPDF = () => {
  const tableData = events.value.data || [];
  const columns = ['Title', 'Organizer', 'Category', 'Type', 'Status', 'Start Date', 'End Date'];

  let content = `
    <html>
    <head>
      <title>Events Export</title>
      <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        h1 { color: #333; }
      </style>
    </head>
    <body>
      <h1>Events List</h1>
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
              <td>${row.organizer ? `${row.organizer.first_name} ${row.organizer.last_name}` : '—'}</td>
              <td>${row.category?.name || '—'}</td>
              <td>${row.event_type || '—'}</td>
              <td>${row.status || '—'}</td>
              <td>${row.start_date ? formatDate(row.start_date) : '—'}</td>
              <td>${row.end_date ? formatDate(row.end_date) : '—'}</td>
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
  const tableData = events.value.data || [];

  const headers = ['Title', 'Organizer', 'Category', 'Type', 'Status', 'Visibility', 'Start Date', 'End Date', 'Created At'];
  const rows = tableData.map(row => [
    row.title || '',
    row.organizer ? `${row.organizer.first_name} ${row.organizer.last_name}` : '',
    row.category?.name || '',
    row.event_type || '',
    row.status || '',
    row.visibility || '',
    row.start_date ? formatDate(row.start_date) : '',
    row.end_date ? formatDate(row.end_date) : '',
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
  link.setAttribute('download', `events_${dayjs().format('YYYY-MM-DD')}.csv`);
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

.events-card {
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

.event-title-cell {
  display: flex;
  align-items: center;
  gap: 8px;
}

.event-title {
  font-weight: 500;
}

.text-muted {
  color: var(--text-tertiary, #8c8c8c);
}

[data-theme="dark"] .text-muted {
  color: rgba(255, 255, 255, 0.45);
}
</style>
