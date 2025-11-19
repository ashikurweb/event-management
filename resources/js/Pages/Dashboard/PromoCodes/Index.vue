<template>
  <DashboardLayout>
    <div class="breadcrumb-wrapper">
      <Breadcrumb :items="breadcrumbItems" />
      <div class="breadcrumb-actions">
        <a-button @click="handleRefresh" title="Refresh">
          <template #icon><ReloadOutlined /></template>
        </a-button>
      </div>
    </div>

    <a-card class="promo-codes-card">
      <template #title>
        <div class="card-header">
          <h2 class="card-title">Promo Codes</h2>
          <a-button type="primary" @click="handleCreate">
            <template #icon><PlusOutlined /></template>
            Create Promo Code
          </a-button>
        </div>
      </template>

      <!-- Filters -->
      <div class="filters-section">
        <a-row :gutter="16">
          <a-col :xs="24" :sm="12" :md="8">
            <a-input
              v-model:value="filters.search"
              placeholder="Search promo codes..."
              allow-clear
              @pressEnter="handleSearch"
            >
              <template #prefix><SearchOutlined /></template>
            </a-input>
          </a-col>
          <a-col :xs="24" :sm="12" :md="6">
            <a-select
              v-model:value="filters.discount_type"
              placeholder="Discount type"
              allow-clear
              style="width: 100%"
              @change="handleSearch"
            >
              <a-select-option value="percentage">Percentage</a-select-option>
              <a-select-option value="fixed">Fixed</a-select-option>
              <a-select-option value="free_ticket">Free Ticket</a-select-option>
            </a-select>
          </a-col>
          <a-col :xs="24" :sm="12" :md="6">
            <a-select
              v-model:value="filters.applicable_to"
              placeholder="Applicable to"
              allow-clear
              style="width: 100%"
              @change="handleSearch"
            >
              <a-select-option value="all">All Events</a-select-option>
              <a-select-option value="specific_events">Specific Events</a-select-option>
              <a-select-option value="specific_categories">Specific Categories</a-select-option>
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
        <a-row :gutter="16" style="margin-top: 12px">
          <a-col :xs="24" :sm="12" :md="12">
            <a-range-picker
              v-model:value="dateRange"
              :placeholder="['Valid From', 'Valid Until']"
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
          <a-button type="primary" @click="handleBulkAction('activate')">Activate</a-button>
          <a-button @click="handleBulkAction('deactivate')">Deactivate</a-button>
        </a-space>
      </div>

      <!-- Table -->
      <a-table
        :columns="columns"
        :data-source="promoCodes.data"
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
          <template v-if="column.key === 'code'">
            <div class="code-cell">
              <span class="code-value">{{ record.code }}</span>
              <a-tag v-if="record.is_active" color="success">Active</a-tag>
              <a-tag v-else color="default">Inactive</a-tag>
            </div>
          </template>

          <template v-if="column.key === 'discount'">
            <span class="discount-value">
              <span v-if="record.discount_type === 'percentage'">
                {{ record.discount_value }}%
              </span>
              <span v-else-if="record.discount_type === 'fixed'">
                ${{ record.discount_value }}
              </span>
              <span v-else>
                Free Ticket
              </span>
            </span>
          </template>

          <template v-if="column.key === 'applicable_to'">
            <a-tag :color="getApplicableColor(record.applicable_to)">
              {{ formatApplicableTo(record.applicable_to) }}
            </a-tag>
          </template>

          <template v-if="column.key === 'validity'">
            <div class="validity-cell">
              <div>From: {{ formatDate(record.valid_from) }}</div>
              <div>Until: {{ formatDate(record.valid_until) }}</div>
            </div>
          </template>

          <template v-if="column.key === 'usage'">
            <div class="usage-cell">
              <span>{{ record.current_uses || 0 }}</span>
              <span v-if="record.max_uses" class="text-muted">
                / {{ record.max_uses }}
              </span>
            </div>
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
                title="Are you sure you want to delete this promo code?"
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
} from '@ant-design/icons-vue';
import dayjs from 'dayjs';

const page = usePage();

const promoCodes = computed(() => page.props.promoCodes || { data: [] });
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
  { label: 'Promo Codes' },
]);

const columns = [
  {
    title: 'Code',
    key: 'code',
    dataIndex: 'code',
    sorter: true,
  },
  {
    title: 'Discount',
    key: 'discount',
    dataIndex: 'discount_type',
  },
  {
    title: 'Applicable To',
    key: 'applicable_to',
    dataIndex: 'applicable_to',
  },
  {
    title: 'Validity',
    key: 'validity',
    dataIndex: 'valid_from',
    sorter: true,
  },
  {
    title: 'Usage',
    key: 'usage',
    dataIndex: 'current_uses',
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
  current: promoCodes.value.current_page || 1,
  pageSize: promoCodes.value.per_page || 15,
  total: promoCodes.value.total || 0,
  showSizeChanger: true,
  showTotal: (total) => `Total ${total} promo codes`,
}));

const formatDate = (dateString) => {
  if (!dateString) return '—';
  return dayjs(dateString).format('YYYY-MM-DD');
};

const formatApplicableTo = (applicableTo) => {
  const labels = {
    all: 'All Events',
    specific_events: 'Specific Events',
    specific_categories: 'Specific Categories',
  };
  return labels[applicableTo] || applicableTo;
};

const getApplicableColor = (applicableTo) => {
  const colors = {
    all: 'blue',
    specific_events: 'green',
    specific_categories: 'orange',
  };
  return colors[applicableTo] || 'default';
};

const handleSearch = () => {
  router.get('/dashboard/promo-codes/search', filters.value, {
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

  router.get('/dashboard/promo-codes/search', { ...filters.value, ...params }, {
    preserveState: true,
    preserveScroll: true,
  });
};

const handlePaginationChange = ({ current, pageSize }) => {
  const params = {
    page: current,
    per_page: pageSize,
  };

  router.get('/dashboard/promo-codes/search', { ...filters.value, ...params }, {
    preserveState: true,
    preserveScroll: true,
  });
};

const handlePageSizeChange = ({ current, pageSize }) => {
  const params = {
    page: current,
    per_page: pageSize,
  };

  router.get('/dashboard/promo-codes/search', { ...filters.value, ...params }, {
    preserveState: true,
    preserveScroll: true,
  });
};

const onSelectChange = (keys) => {
  selectedRowKeys.value = keys;
};

const handleCreate = () => {
  router.visit('/dashboard/promo-codes/create');
};

const handleView = (record) => {
  router.visit(`/dashboard/promo-codes/${record.id}`);
};

const handleEdit = (record) => {
  router.visit(`/dashboard/promo-codes/${record.id}/edit`);
};

const handleDelete = (record) => {
  router.delete(`/dashboard/promo-codes/${record.id}`, {
    preserveScroll: true,
  });
};

const handleBulkAction = (action) => {
  if (selectedRowKeys.value.length === 0) return;

  router.post(
    '/dashboard/promo-codes/bulk-action',
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
  filters.value = {};
  dateRange.value = null;
  selectedRowKeys.value = [];
  
  router.visit('/dashboard/promo-codes', {
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

.promo-codes-card {
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

.code-cell {
  display: flex;
  align-items: center;
  gap: 8px;
}

.code-value {
  font-weight: 600;
  font-family: monospace;
  font-size: 14px;
}

.discount-value {
  font-weight: 500;
  color: var(--text-primary, #262626);
}

[data-theme="dark"] .discount-value {
  color: rgba(255, 255, 255, 0.85);
}

.validity-cell {
  font-size: 12px;
  line-height: 1.5;
}

.usage-cell {
  font-weight: 500;
}

.text-muted {
  color: var(--text-tertiary, #8c8c8c);
}

[data-theme="dark"] .text-muted {
  color: rgba(255, 255, 255, 0.45);
}
</style>

