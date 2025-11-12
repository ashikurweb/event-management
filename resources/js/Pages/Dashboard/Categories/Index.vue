<template>
  <DashboardLayout>
    <Breadcrumb :items="breadcrumbItems" />

    <a-card class="categories-card">
      <template #title>
        <div class="card-header">
          <h2 class="card-title">Categories</h2>
          <a-button type="primary" @click="handleCreate">
            <template #icon><PlusOutlined /></template>
            Create Category
          </a-button>
        </div>
      </template>

      <!-- Filters -->
      <div class="filters-section">
        <a-row :gutter="16">
          <a-col :xs="24" :sm="12" :md="8">
            <a-input
              v-model:value="filters.search"
              placeholder="Search categories..."
              allow-clear
              @pressEnter="handleSearch"
            >
              <template #prefix><SearchOutlined /></template>
            </a-input>
          </a-col>
          <a-col :xs="24" :sm="12" :md="8">
            <a-select
              v-model:value="filters.parent_id"
              placeholder="Filter by parent"
              allow-clear
              style="width: 100%;"
              @change="handleSearch"
            >
              <a-select-option value="null">No Parent</a-select-option>
              <a-select-option
                v-for="cat in allCategories"
                :key="cat.id"
                :value="cat.id"
              >
                {{ cat.name }}
              </a-select-option>
            </a-select>
          </a-col>
          <a-col :xs="24" :sm="12" :md="8">
            <a-select
              v-model:value="filters.is_active"
              placeholder="Filter by status"
              allow-clear
              style="width: 100%"
              @change="handleSearch"
            >
              <a-select-option :value="true">Active</a-select-option>
              <a-select-option :value="false">Inactive</a-select-option>
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
        :data-source="categories.data"
        :row-key="(record) => record.id"
        :pagination="pagination"
        :loading="loading"
        :row-selection="{
          selectedRowKeys: selectedRowKeys,
          onChange: onSelectChange,
        }"
        @change="handleTableChange"
      >
        <template #bodyCell="{ column, record }">
          <template v-if="column.key === 'name'">
            <div class="category-name-cell">
              <span
                v-if="record.color"
                class="color-indicator"
                :style="{ backgroundColor: record.color }"
              ></span>
              <span v-if="record.icon" class="category-icon">{{ record.icon }}</span>
              <span class="category-name">{{ record.name }}</span>
            </div>
          </template>

          <template v-if="column.key === 'parent'">
            <span v-if="record.parent">{{ record.parent.name }}</span>
            <span v-else class="text-muted">—</span>
          </template>

          <template v-if="column.key === 'children_count'">
            <a-badge :count="record.children?.length || 0" :number-style="{ backgroundColor: '#52c41a' }" />
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
                title="Are you sure you want to delete this category?"
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
    </a-card>
  </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import DashboardLayout from '../../../Layouts/DashboardLayout.vue';
import Breadcrumb from '../../../Components/Breadcrumb.vue';
import {
  PlusOutlined,
  SearchOutlined,
  EyeOutlined,
  EditOutlined,
  DeleteOutlined,
} from '@ant-design/icons-vue';

const page = usePage();

const categories = computed(() => page.props.categories || { data: [] });
const allCategories = computed(() => page.props.allCategories || []);
const filters = ref(page.props.filters || {});
const selectedRowKeys = ref([]);
const loading = ref(false);

const breadcrumbItems = ref([
  { label: 'Dashboard', href: '/dashboard' },
  { label: 'Categories' },
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
    title: 'Parent',
    key: 'parent',
    dataIndex: 'parent',
  },
  {
    title: 'Children',
    key: 'children_count',
    align: 'center',
  },
  {
    title: 'Order',
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
  current: categories.value.current_page || 1,
  pageSize: categories.value.per_page || 15,
  total: categories.value.total || 0,
  showSizeChanger: true,
  showTotal: (total) => `Total ${total} categories`,
}));

const handleSearch = () => {
  router.get('/dashboard/categories/search', filters.value, {
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

  router.get('/dashboard/categories/search', { ...filters.value, ...params }, {
    preserveState: true,
    preserveScroll: true,
  });
};

const onSelectChange = (keys) => {
  selectedRowKeys.value = keys;
};

const handleCreate = () => {
  router.visit('/dashboard/categories/create');
};

const handleView = (record) => {
  router.visit(`/dashboard/categories/${record.id}`);
};

const handleEdit = (record) => {
  router.visit(`/dashboard/categories/${record.id}/edit`);
};

const handleDelete = (record) => {
  router.delete(`/dashboard/categories/${record.id}`, {
    preserveScroll: true,
    onSuccess: () => {
      // Success handled by Inertia
    },
  });
};

const handleBulkAction = (action) => {
  if (selectedRowKeys.value.length === 0) return;

  router.post(
    '/dashboard/categories/bulk-action',
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
</script>

<style scoped>
.categories-card {
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

.category-name-cell {
  display: flex;
  align-items: center;
  gap: 8px;
}

.color-indicator {
  width: 16px;
  height: 16px;
  border-radius: 4px;
  border: 1px solid var(--border-color, #d9d9d9);
  flex-shrink: 0;
}

.category-icon {
  font-size: 18px;
}

.category-name {
  font-weight: 500;
}

.text-muted {
  color: var(--text-tertiary, #8c8c8c);
}

[data-theme="dark"] .text-muted {
  color: rgba(255, 255, 255, 0.45);
}

:deep(.ant-table) {
  background: var(--bg-primary, #fff);
}

[data-theme="dark"] :deep(.ant-table) {
  background: var(--bg-primary, #1f1f1f);
}

:deep(.ant-table-thead > tr > th) {
  background: var(--bg-elevated, #fafafa);
  color: var(--text-primary, #262626);
}

[data-theme="dark"] :deep(.ant-table-thead > tr > th) {
  background: var(--bg-elevated, #262626);
  color: rgba(255, 255, 255, 0.85);
}

:deep(.ant-table-tbody > tr > td) {
  color: var(--text-primary, #262626);
}

[data-theme="dark"] :deep(.ant-table-tbody > tr > td) {
  color: rgba(255, 255, 255, 0.85);
}

/* Search Input Dark Mode */
[data-theme="dark"] .filters-section :deep(.ant-input),
[data-theme="dark"] .filters-section :deep(.ant-input-affix-wrapper) {
  background-color: #262626 !important;
  border-color: #434343 !important;
  color: rgba(255, 255, 255, 0.85) !important;
}

[data-theme="dark"] .filters-section :deep(.ant-input-affix-wrapper) {
  background-color: #262626 !important;
}

[data-theme="dark"] .filters-section :deep(.ant-input-affix-wrapper:hover) {
  border-color: #595959 !important;
}

[data-theme="dark"] .filters-section :deep(.ant-input-affix-wrapper-focused) {
  border-color: #40a9ff !important;
  box-shadow: 0 0 0 2px rgba(64, 169, 255, 0.2) !important;
}

[data-theme="dark"] .filters-section :deep(.ant-input) {
  background-color: transparent !important;
  color: rgba(255, 255, 255, 0.85) !important;
}

[data-theme="dark"] .filters-section :deep(.ant-input::placeholder) {
  color: rgba(255, 255, 255, 0.25) !important;
}

[data-theme="dark"] .filters-section :deep(.ant-input:hover) {
  border-color: transparent !important;
}

[data-theme="dark"] .filters-section :deep(.ant-input:focus),
[data-theme="dark"] .filters-section :deep(.ant-input-focused) {
  border-color: transparent !important;
  box-shadow: none !important;
}

[data-theme="dark"] .filters-section :deep(.ant-input-prefix),
[data-theme="dark"] .filters-section :deep(.ant-input-affix-wrapper .anticon) {
  color: rgba(255, 255, 255, 0.65) !important;
}

[data-theme="dark"] .filters-section :deep(.ant-input-clear-icon),
[data-theme="dark"] .filters-section :deep(.ant-input-affix-wrapper .ant-input-clear-icon) {
  color: rgba(255, 255, 255, 0.45) !important;
}

[data-theme="dark"] .filters-section :deep(.ant-input-clear-icon:hover),
[data-theme="dark"] .filters-section :deep(.ant-input-affix-wrapper .ant-input-clear-icon:hover) {
  color: rgba(255, 255, 255, 0.85) !important;
}

/* Select Dropdown Dark Mode */
[data-theme="dark"] .filters-section :deep(.ant-select) {
  color: rgba(255, 255, 255, 0.85) !important;
}

[data-theme="dark"] .filters-section :deep(.ant-select-selector) {
  background-color: #262626 !important;
  border-color: #434343 !important;
  color: rgba(255, 255, 255, 0.85) !important;
}

[data-theme="dark"] .filters-section :deep(.ant-select-selection-placeholder) {
  color: rgba(255, 255, 255, 0.25) !important;
}

[data-theme="dark"] .filters-section :deep(.ant-select-selection-item) {
  color: rgba(255, 255, 255, 0.85) !important;
}

[data-theme="dark"] .filters-section :deep(.ant-select-arrow) {
  color: rgba(255, 255, 255, 0.45) !important;
}

[data-theme="dark"] .filters-section :deep(.ant-select:hover .ant-select-selector) {
  border-color: #595959 !important;
}

[data-theme="dark"] .filters-section :deep(.ant-select-focused .ant-select-selector) {
  border-color: #40a9ff !important;
  box-shadow: 0 0 0 2px rgba(64, 169, 255, 0.2) !important;
}

[data-theme="dark"] .filters-section :deep(.ant-select-clear) {
  color: rgba(255, 255, 255, 0.45) !important;
  background-color: transparent !important;
}

[data-theme="dark"] .filters-section :deep(.ant-select-clear:hover) {
  color: rgba(255, 255, 255, 0.85) !important;
}

/* Select Dropdown Menu Dark Mode - Global (works for all dropdowns) */
[data-theme="dark"] :deep(.ant-select-dropdown),
[data-theme="dark"] :deep(.ant-select-dropdown.ant-select-dropdown-placement-bottomLeft),
[data-theme="dark"] :deep(.ant-select-dropdown.ant-select-dropdown-placement-topLeft) {
  background-color: #1f1f1f !important;
  background: #1f1f1f !important;
  border-color: #434343 !important;
  border: 1px solid #434343 !important;
  box-shadow: 0 6px 16px 0 rgba(0, 0, 0, 0.48), 0 3px 6px -4px rgba(0, 0, 0, 0.36), 0 9px 28px 8px rgba(0, 0, 0, 0.15) !important;
}

[data-theme="dark"] :deep(.ant-select-item),
[data-theme="dark"] :deep(.ant-select-item-option) {
  color: rgba(255, 255, 255, 0.85) !important;
  background-color: transparent !important;
  background: transparent !important;
}

[data-theme="dark"] :deep(.ant-select-item:hover),
[data-theme="dark"] :deep(.ant-select-item-option:hover) {
  background-color: rgba(255, 255, 255, 0.08) !important;
  background: rgba(255, 255, 255, 0.08) !important;
}

[data-theme="dark"] :deep(.ant-select-item-option-selected:not(.ant-select-item-option-disabled)),
[data-theme="dark"] :deep(.ant-select-item-option-selected) {
  background-color: rgba(24, 144, 255, 0.2) !important;
  background: rgba(24, 144, 255, 0.2) !important;
  color: rgba(255, 255, 255, 0.85) !important;
  font-weight: 600 !important;
}

[data-theme="dark"] :deep(.ant-select-item-option-active:not(.ant-select-item-option-disabled)),
[data-theme="dark"] :deep(.ant-select-item-option-active) {
  background-color: rgba(255, 255, 255, 0.08) !important;
  background: rgba(255, 255, 255, 0.08) !important;
}

[data-theme="dark"] :deep(.ant-select-item-option-content) {
  color: rgba(255, 255, 255, 0.85) !important;
}

[data-theme="dark"] :deep(.ant-select-item-empty) {
  color: rgba(255, 255, 255, 0.45) !important;
}
</style>

