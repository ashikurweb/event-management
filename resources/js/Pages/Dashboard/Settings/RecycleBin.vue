<template>
  <DashboardLayout>
    <div class="recycle-bin-container">
      <!-- Header -->
      <div class="page-header">
        <div class="header-content">
          <div class="header-left">
            <h1 class="page-title">
              <DeleteOutlined class="title-icon" />
              Recycle Bin
            </h1>
            <p class="page-description">
              View and manage deleted items. Restore or permanently delete items.
            </p>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <a-card class="filters-card">
        <a-row :gutter="16">
          <a-col :xs="24" :sm="12" :md="8">
            <a-input
              v-model:value="filters.search"
              placeholder="Search deleted items..."
              allow-clear
              @pressEnter="handleSearch"
            >
              <template #prefix><SearchOutlined /></template>
            </a-input>
          </a-col>
          <a-col :xs="24" :sm="12" :md="8">
            <a-select
              v-model:value="filters.type"
              placeholder="Filter by type"
              allow-clear
              style="width: 100%"
              @change="handleSearch"
            >
              <a-select-option value="all">All Types</a-select-option>
              <a-select-option value="categories">Categories</a-select-option>
              <a-select-option value="teams">Teams</a-select-option>
              <a-select-option value="events">Events</a-select-option>
              <a-select-option value="venues">Venues</a-select-option>
              <a-select-option value="sponsors">Sponsors</a-select-option>
              <a-select-option value="speakers">Speakers</a-select-option>
              <a-select-option value="vendors">Vendors</a-select-option>
              <a-select-option value="pages">Pages</a-select-option>
            </a-select>
          </a-col>
          <a-col :xs="24" :sm="12" :md="8">
            <a-space>
              <a-button type="primary" @click="handleSearch">
                <template #icon><SearchOutlined /></template>
                Search
              </a-button>
              <a-button @click="handleReset">
                <template #icon><ReloadOutlined /></template>
                Reset
              </a-button>
            </a-space>
          </a-col>
        </a-row>
      </a-card>

      <!-- Bulk Actions -->
      <div v-if="selectedRowKeys.length > 0" class="bulk-actions">
        <span class="selected-count">
          {{ selectedRowKeys.length }} item(s) selected
        </span>
        <a-space>
          <a-button @click="handleBulkRestore">
            <template #icon><UndoOutlined /></template>
            Restore Selected
          </a-button>
          <a-popconfirm
            title="Are you sure you want to permanently delete these items?"
            ok-text="Yes, Delete"
            cancel-text="Cancel"
            @confirm="handleBulkForceDelete"
          >
            <a-button danger>
              <template #icon><DeleteOutlined /></template>
              Delete Permanently
            </a-button>
          </a-popconfirm>
        </a-space>
      </div>

      <!-- Items Table -->
      <a-card class="items-card">
        <a-table
          :columns="columns"
          :data-source="items"
          :row-key="(record) => `${record.type}-${record.id}`"
          :pagination="false"
          :loading="loading"
          :row-selection="{
            selectedRowKeys: selectedRowKeys,
            onChange: onSelectChange,
          }"
        >
          <template #bodyCell="{ column, record }">
            <template v-if="column.key === 'type'">
              <a-tag :color="getTypeColor(record.type)">
                {{ getTypeLabel(record.type) }}
              </a-tag>
            </template>

            <template v-if="column.key === 'name'">
              <div class="item-name-cell">
                <span class="item-name">{{ record.name }}</span>
                <span v-if="record.slug" class="item-slug">/{{ record.slug }}</span>
              </div>
            </template>

            <template v-if="column.key === 'deleted_by'">
              <div v-if="record.deleted_by" class="deleted-by-cell">
                <UserOutlined class="user-icon" />
                <div class="user-info">
                  <div class="user-name">{{ record.deleted_by.name }}</div>
                  <div class="user-email">{{ record.deleted_by.email }}</div>
                </div>
              </div>
              <span v-else class="text-muted">—</span>
            </template>

            <template v-if="column.key === 'deleted_at'">
              <span>{{ formatDate(record.deleted_at) }}</span>
            </template>

            <template v-if="column.key === 'actions'">
              <a-space>
                <a-button type="link" size="small" @click="handleRestore(record)">
                  <template #icon><UndoOutlined /></template>
                  Restore
                </a-button>
                <a-popconfirm
                  title="Are you sure you want to permanently delete this item?"
                  ok-text="Yes"
                  cancel-text="No"
                  @confirm="handleForceDelete(record)"
                >
                  <a-button type="link" size="small" danger>
                    <template #icon><DeleteOutlined /></template>
                    Delete
                  </a-button>
                </a-popconfirm>
              </a-space>
            </template>
          </template>
        </a-table>

        <!-- Pagination -->
        <div class="pagination-wrapper">
          <a-pagination
            v-model:current="pagination.current_page"
            :total="pagination.total"
            :page-size="pagination.per_page"
            :page-size-options="['10', '15', '20', '50', '100']"
            show-size-changer
            show-total
            @change="handlePaginationChange"
            @showSizeChange="handlePageSizeChange"
          />
        </div>
      </a-card>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import DashboardLayout from '../../../Layouts/DashboardLayout.vue';
import {
  DeleteOutlined,
  SearchOutlined,
  ReloadOutlined,
  UndoOutlined,
  UserOutlined,
} from '@ant-design/icons-vue';
import dayjs from 'dayjs';

const page = usePage();

const items = computed(() => page.props.items || []);
const pagination = computed(() => page.props.pagination || {
  current_page: 1,
  per_page: 15,
  total: 0,
  last_page: 1,
});
const initialFilters = page.props.filters || {};
const filters = ref({
  type: initialFilters.type || 'all',
  search: initialFilters.search || '',
});
const selectedRowKeys = ref([]);
const loading = ref(false);

const columns = [
  {
    title: 'Type',
    key: 'type',
    dataIndex: 'type',
    width: 120,
  },
  {
    title: 'Name',
    key: 'name',
    dataIndex: 'name',
  },
  {
    title: 'Deleted By',
    key: 'deleted_by',
    dataIndex: 'deleted_by',
    width: 200,
  },
  {
    title: 'Deleted At',
    key: 'deleted_at',
    dataIndex: 'deleted_at',
    width: 180,
  },
  {
    title: 'Actions',
    key: 'actions',
    align: 'right',
    width: 150,
  },
];

const getTypeColor = (type) => {
  const colors = {
    category: 'blue',
    team: 'green',
    event: 'orange',
    venue: 'purple',
    sponsor: 'gold',
    speaker: 'cyan',
    vendor: 'magenta',
    page: 'geekblue',
  };
  return colors[type] || 'default';
};

const getTypeLabel = (type) => {
  const labels = {
    category: 'Category',
    team: 'Team',
    event: 'Event',
    venue: 'Venue',
    sponsor: 'Sponsor',
    speaker: 'Speaker',
    vendor: 'Vendor',
    page: 'Page',
  };
  return labels[type] || type;
};

const formatDate = (dateString) => {
  if (!dateString) return '—';
  return dayjs(dateString).format('MMM DD, YYYY HH:mm');
};

const handleSearch = () => {
  loading.value = true;
  router.get('/dashboard/settings/recycle-bin', {
    ...filters.value,
    page: 1,
  }, {
    preserveState: true,
    preserveScroll: true,
    onFinish: () => {
      loading.value = false;
    },
  });
};

const handleReset = () => {
  filters.value = {
    type: 'all',
    search: '',
  };
  selectedRowKeys.value = [];
  handleSearch();
};

const handlePaginationChange = (page, pageSize) => {
  loading.value = true;
  router.get('/dashboard/settings/recycle-bin', {
    ...filters.value,
    page,
    per_page: pageSize,
  }, {
    preserveState: true,
    preserveScroll: true,
    onFinish: () => {
      loading.value = false;
    },
  });
};

const handlePageSizeChange = (current, size) => {
  loading.value = true;
  router.get('/dashboard/settings/recycle-bin', {
    ...filters.value,
    page: current,
    per_page: size,
  }, {
    preserveState: true,
    preserveScroll: true,
    onFinish: () => {
      loading.value = false;
    },
  });
};

const onSelectChange = (keys) => {
  selectedRowKeys.value = keys;
};

const handleRestore = (record) => {
  router.post(`/dashboard/settings/recycle-bin/${record.type}/${record.id}/restore`, {}, {
    preserveScroll: true,
    onSuccess: () => {
      selectedRowKeys.value = selectedRowKeys.value.filter(
        key => key !== `${record.type}-${record.id}`
      );
    },
  });
};

const handleForceDelete = (record) => {
  router.delete(`/dashboard/settings/recycle-bin/${record.type}/${record.id}`, {
    preserveScroll: true,
    onSuccess: () => {
      selectedRowKeys.value = selectedRowKeys.value.filter(
        key => key !== `${record.type}-${record.id}`
      );
    },
  });
};

const handleBulkRestore = () => {
  if (selectedRowKeys.value.length === 0) return;

  const itemsToRestore = selectedRowKeys.value.map(key => {
    const [type, id] = key.split('-');
    return { type, id: parseInt(id) };
  });

  router.post('/dashboard/settings/recycle-bin/bulk-restore', {
    items: itemsToRestore,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      selectedRowKeys.value = [];
    },
  });
};

const handleBulkForceDelete = () => {
  if (selectedRowKeys.value.length === 0) return;

  const itemsToDelete = selectedRowKeys.value.map(key => {
    const [type, id] = key.split('-');
    return { type, id: parseInt(id) };
  });

  router.post('/dashboard/settings/recycle-bin/bulk-delete', {
    items: itemsToDelete,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      selectedRowKeys.value = [];
    },
  });
};

onMounted(() => {
  // Auto-load if filters are present
  if (filters.value.type !== 'all' || filters.value.search) {
    handleSearch();
  }
});
</script>

<style scoped>
.recycle-bin-container {
  padding: 24px;
}

.page-header {
  margin-bottom: 24px;
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  flex-wrap: wrap;
  gap: 16px;
}

.header-left {
  flex: 1;
}

.page-title {
  display: flex;
  align-items: center;
  gap: 12px;
  margin: 0 0 8px 0;
  font-size: 28px;
  font-weight: 700;
  color: var(--text-primary, #262626);
}

[data-theme="dark"] .page-title {
  color: rgba(255, 255, 255, 0.85);
}

.title-icon {
  font-size: 32px;
  color: var(--primary-color, #1890ff);
}

.page-description {
  margin: 0;
  font-size: 14px;
  color: var(--text-secondary, #8c8c8c);
}

[data-theme="dark"] .page-description {
  color: rgba(255, 255, 255, 0.65);
}

.filters-card,
.items-card {
  border-radius: 8px;
  box-shadow: var(--card-shadow, 0 2px 8px rgba(0, 0, 0, 0.06));
  margin-bottom: 24px;
}

.bulk-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px;
  margin-bottom: 16px;
  background: var(--bg-hover, #f5f5f5);
  border-radius: 8px;
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

.item-name-cell {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.item-name {
  font-weight: 500;
  color: var(--text-primary, #262626);
}

[data-theme="dark"] .item-name {
  color: rgba(255, 255, 255, 0.85);
}

.item-slug {
  font-size: 12px;
  color: var(--text-tertiary, #8c8c8c);
  font-family: monospace;
}

[data-theme="dark"] .item-slug {
  color: rgba(255, 255, 255, 0.45);
}

.deleted-by-cell {
  display: flex;
  align-items: center;
  gap: 8px;
}

.user-icon {
  color: var(--text-tertiary, #8c8c8c);
}

.user-info {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.user-name {
  font-size: 14px;
  font-weight: 500;
  color: var(--text-primary, #262626);
}

[data-theme="dark"] .user-name {
  color: rgba(255, 255, 255, 0.85);
}

.user-email {
  font-size: 12px;
  color: var(--text-tertiary, #8c8c8c);
}

[data-theme="dark"] .user-email {
  color: rgba(255, 255, 255, 0.45);
}

.text-muted {
  color: var(--text-tertiary, #8c8c8c);
  font-style: italic;
}

[data-theme="dark"] .text-muted {
  color: rgba(255, 255, 255, 0.45);
}

.pagination-wrapper {
  margin-top: 24px;
  display: flex;
  justify-content: center;
}

@media (max-width: 768px) {
  .recycle-bin-container {
    padding: 16px;
  }

  .page-title {
    font-size: 24px;
  }

  .bulk-actions {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
  }
}
</style>

