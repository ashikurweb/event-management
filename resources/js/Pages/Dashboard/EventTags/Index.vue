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
      </div>
    </div>

    <a-card class="tags-card">
      <template #title>
        <div class="card-header">
          <h2 class="card-title">Event Tags</h2>
          <a-button type="primary" @click="handleCreate">
            <template #icon>
              <PlusOutlined />
            </template>
            Create Tag
          </a-button>
        </div>
      </template>

      <!-- Filters -->
      <div class="filters-section">
        <a-row :gutter="16">
          <a-col :xs="24" :md="12">
            <Search v-model="filters.search" placeholder="Search tags..." @search="handleSearch" />
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
      <a-table :columns="columns" :data-source="tags.data" :row-key="(record) => record.id" :pagination="false"
        :loading="loading" :row-selection="{
          selectedRowKeys: selectedRowKeys,
          onChange: onSelectChange,
        }" @change="handleTableChange">
        <template #bodyCell="{ column, record }">
          <template v-if="column.key === 'name'">
            <div class="tag-name-cell">
              <span class="tag-name">{{ record.name }}</span>
            </div>
          </template>

          <template v-if="column.key === 'slug'">
            <span class="tag-slug">{{ record.slug }}</span>
          </template>

          <template v-if="column.key === 'usage_count'">
            <a-tag color="blue">{{ record.events_count || 0 }}</a-tag>
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
              <a-popconfirm title="Are you sure you want to delete this tag?" ok-text="Yes" cancel-text="No"
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
import {
  PlusOutlined,
  EyeOutlined,
  EditOutlined,
  DeleteOutlined,
  ReloadOutlined,
} from '@ant-design/icons-vue';

const page = usePage();

const tags = computed(() => page.props.tags || { data: [] });
const initialFilters = page.props.filters || {};

const filters = ref(initialFilters);
const selectedRowKeys = ref([]);
const loading = ref(false);

const breadcrumbItems = ref([
  { label: 'Dashboard', href: '/dashboard' },
  { label: 'Event Tags' },
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
    key: 'slug',
    dataIndex: 'slug',
  },
  {
    title: 'Usage Count',
    key: 'usage_count',
    dataIndex: 'events_count',
    sorter: true,
  },
  {
    title: 'Actions',
    key: 'actions',
    align: 'right',
    width: 150,
  },
];

const pagination = computed(() => ({
  current: tags.value.current_page || 1,
  pageSize: tags.value.per_page || 15,
  total: tags.value.total || 0,
  showSizeChanger: true,
  showTotal: (total) => `Total ${total} tags`,
}));

const handleSearch = () => {
  router.get('/dashboard/event-tags/search', filters.value, {
    preserveState: true,
    preserveScroll: true,
  });
};

const handleResetFilters = () => {
  filters.value = {};
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

  router.get('/dashboard/event-tags/search', { ...filters.value, ...params }, {
    preserveState: true,
    preserveScroll: true,
  });
};

const handlePaginationChange = ({ current, pageSize }) => {
  const params = {
    page: current,
    per_page: pageSize,
  };

  router.get('/dashboard/event-tags/search', { ...filters.value, ...params }, {
    preserveState: true,
    preserveScroll: true,
  });
};

const handlePageSizeChange = ({ current, pageSize }) => {
  const params = {
    page: current,
    per_page: pageSize,
  };

  router.get('/dashboard/event-tags/search', { ...filters.value, ...params }, {
    preserveState: true,
    preserveScroll: true,
  });
};

const onSelectChange = (keys) => {
  selectedRowKeys.value = keys;
};

const handleCreate = () => {
  router.visit('/dashboard/event-tags/create');
};

const handleView = (record) => {
  router.visit(`/dashboard/event-tags/${record.id}`);
};

const handleEdit = (record) => {
  router.visit(`/dashboard/event-tags/${record.id}/edit`);
};

const handleDelete = (record) => {
  router.delete(`/dashboard/event-tags/${record.id}`, {
    preserveScroll: true,
  });
};

const handleBulkAction = (action) => {
  if (selectedRowKeys.value.length === 0) return;

  router.post(
    '/dashboard/event-tags/bulk-action',
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

const handleRefresh = () => {
  filters.value = {};
  selectedRowKeys.value = [];

  router.visit('/dashboard/event-tags', {
    preserveState: false,
    preserveScroll: false,
  });
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

.tags-card {
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

.tag-name-cell {
  display: flex;
  align-items: center;
}

.tag-name {
  font-weight: 500;
}

.tag-slug {
  font-family: monospace;
  font-size: 12px;
  color: var(--text-tertiary, #8c8c8c);
}

[data-theme="dark"] .tag-slug {
  color: rgba(255, 255, 255, 0.45);
}
</style>
