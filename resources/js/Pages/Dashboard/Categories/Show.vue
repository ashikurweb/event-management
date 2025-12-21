<template>
  <DashboardLayout>
    <Breadcrumb :items="breadcrumbItems" />

    <a-card class="category-show-card" v-if="category">
      <template #title>
        <div class="card-header">
          <h2 class="card-title">Category Details</h2>
          <a-space>
            <a-button @click="handleEdit">
              <template #icon>
                <EditOutlined />
              </template>
              Edit
            </a-button>
            <a-button @click="handleBack">
              <template #icon>
                <ArrowLeftOutlined />
              </template>
              Back
            </a-button>
          </a-space>
        </div>
      </template>

      <!-- Category Information -->
      <div class="category-info-section">
        <a-descriptions :column="1" bordered>
          <a-descriptions-item label="Name">
            <span class="info-value">{{ category.name }}</span>
          </a-descriptions-item>

          <a-descriptions-item label="Slug">
            <span class="info-value">{{ category.slug }}</span>
          </a-descriptions-item>

          <a-descriptions-item label="Description">
            <span class="info-value" v-if="category.description">
              {{ category.description }}
            </span>
            <span class="text-muted" v-else>—</span>
          </a-descriptions-item>

          <a-descriptions-item label="Parent Category">
            <a-tag v-if="category.parent" color="blue">
              {{ category.parent.name }}
            </a-tag>
            <span class="text-muted" v-else>No parent category</span>
          </a-descriptions-item>

          <a-descriptions-item label="Display Order">
            <span class="info-value">{{ category.display_order || 0 }}</span>
          </a-descriptions-item>

          <a-descriptions-item label="Status">
            <a-tag :color="category.is_active ? 'green' : 'red'">
              {{ category.is_active ? 'Active' : 'Inactive' }}
            </a-tag>
          </a-descriptions-item>

          <a-descriptions-item label="Created At">
            <span class="info-value">{{ formatDate(category.created_at) }}</span>
          </a-descriptions-item>

          <a-descriptions-item label="Updated At">
            <span class="info-value">{{ formatDate(category.updated_at) }}</span>
          </a-descriptions-item>
        </a-descriptions>
      </div>

      <!-- Child Categories -->
      <div class="section-divider"></div>
      <div class="related-section" v-if="category.children && category.children.length > 0">
        <h3 class="section-title">Child Categories ({{ category.children.length }})</h3>
        <a-table :columns="childColumns" :data-source="category.children" :row-key="(record) => record.id"
          :pagination="false" size="small">
          <template #bodyCell="{ column, record }">
            <template v-if="column.key === 'name'">
              <a @click="handleViewChild(record)" class="category-link">
                {{ record.name }}
              </a>
            </template>
            <template v-if="column.key === 'is_active'">
              <a-tag :color="record.is_active ? 'green' : 'red'">
                {{ record.is_active ? 'Active' : 'Inactive' }}
              </a-tag>
            </template>
            <template v-if="column.key === 'actions'">
              <a-space>
                <a-button type="link" size="small" @click="handleViewChild(record)">
                  <template #icon>
                    <EyeOutlined />
                  </template>
                </a-button>
                <a-button type="link" size="small" @click="handleEditChild(record)">
                  <template #icon>
                    <EditOutlined />
                  </template>
                </a-button>
              </a-space>
            </template>
          </template>
        </a-table>
      </div>
      <div class="related-section" v-else>
        <h3 class="section-title">Child Categories</h3>
        <a-empty description="No child categories" />
      </div>

      <!-- Events -->
      <div class="section-divider"></div>
      <div class="related-section" v-if="category.events && category.events.length > 0">
        <h3 class="section-title">Events ({{ category.events.length }})</h3>
        <a-table :columns="eventColumns" :data-source="category.events" :row-key="(record) => record.id"
          :pagination="false" size="small">
          <template #bodyCell="{ column, record }">
            <template v-if="column.key === 'title'">
              <a @click="handleViewEvent(record.id)" class="event-link">
                {{ record.title }}
              </a>
            </template>
            <template v-if="column.key === 'status'">
              <a-tag :color="getStatusColor(record.status)">
                {{ record.status }}
              </a-tag>
            </template>
            <template v-if="column.key === 'start_date'">
              <span>{{ formatDate(record.start_date) }}</span>
            </template>
          </template>
        </a-table>
      </div>
      <div class="related-section" v-else>
        <h3 class="section-title">Events</h3>
        <a-empty description="No events in this category" />
      </div>

    </a-card>

    <!-- Activity Log Card -->
    <a-card class="activity-log-card" v-if="category">
      <template #title>
        <h2 class="card-title">Activity Log</h2>
      </template>
      <ActivityLog :activities="activities" :loading="false" :category-id="category.id" />
    </a-card>
  </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import DashboardLayout from '../../../Layouts/DashboardLayout.vue';
import Breadcrumb from '../../../Components/Breadcrumb.vue';
import ActivityLog from '../../../Components/ActivityLog.vue';
import {
  EditOutlined,
  ArrowLeftOutlined,
  EyeOutlined,
} from '@ant-design/icons-vue';

const page = usePage();

const category = computed(() => page.props.category || null);
const activities = computed(() => page.props.activities || { data: [], current_page: 1, per_page: 15, total: 0 });

const breadcrumbItems = ref([
  { label: 'Dashboard', href: '/dashboard' },
  { label: 'Categories', href: '/dashboard/categories' },
  { label: category.value?.name || 'Category' },
]);

const childColumns = [
  {
    title: 'Name',
    key: 'name',
    dataIndex: 'name',
  },
  {
    title: 'Slug',
    dataIndex: 'slug',
    key: 'slug',
  },
  {
    title: 'Display Order',
    dataIndex: 'display_order',
    key: 'display_order',
    align: 'center',
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
    width: 120,
  },
];

const eventColumns = [
  {
    title: 'Title',
    key: 'title',
    dataIndex: 'title',
  },
  {
    title: 'Status',
    key: 'status',
    dataIndex: 'status',
    align: 'center',
  },
  {
    title: 'Start Date',
    key: 'start_date',
    dataIndex: 'start_date',
  },
  {
    title: 'Event Type',
    key: 'event_type',
    dataIndex: 'event_type',
  },
];

const formatDate = (dateString) => {
  if (!dateString) return '—';
  const date = new Date(dateString);
  return date.toLocaleString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};

const getStatusColor = (status) => {
  const statusColors = {
    published: 'green',
    draft: 'orange',
    cancelled: 'red',
    completed: 'blue',
  };
  return statusColors[status] || 'default';
};

const handleBack = () => {
  router.visit('/dashboard/categories');
};

const handleEdit = () => {
  if (category.value) {
    router.visit(`/dashboard/categories/${category.value.id}/edit`);
  }
};

const handleViewChild = (childCategory) => {
  router.visit(`/dashboard/categories/${childCategory.slug || childCategory.id}`);
};

const handleEditChild = (childCategory) => {
  router.visit(`/dashboard/categories/${childCategory.id}/edit`);
};

const handleViewEvent = (id) => {
  router.visit(`/events/${id}`);
};
</script>

<style scoped>
.category-show-card,
.activity-log-card {
  border-radius: 8px;
  box-shadow: var(--card-shadow, 0 2px 8px rgba(0, 0, 0, 0.06));
  margin-bottom: 24px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 12px;
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

.category-info-section {
  margin-bottom: 24px;
}

.info-value {
  font-weight: 500;
  color: var(--text-primary, #262626);
}

[data-theme="dark"] .info-value {
  color: rgba(255, 255, 255, 0.85);
}

.text-muted {
  color: var(--text-tertiary, #8c8c8c);
  font-style: italic;
}

[data-theme="dark"] .text-muted {
  color: rgba(255, 255, 255, 0.45);
}

.section-divider {
  height: 1px;
  background: var(--border-color, #f0f0f0);
  margin: 24px 0;
}

[data-theme="dark"] .section-divider {
  background: var(--border-color, #434343);
}

.related-section {
  margin-bottom: 24px;
}

.section-title {
  font-size: 18px;
  font-weight: 600;
  margin-bottom: 16px;
  color: var(--text-primary, #262626);
}

[data-theme="dark"] .section-title {
  color: rgba(255, 255, 255, 0.85);
}

.category-link,
.event-link {
  color: var(--primary-color, #1890ff);
  cursor: pointer;
  text-decoration: none;
  transition: color 0.2s;
}

.category-link:hover,
.event-link:hover {
  color: var(--primary-hover-color, #40a9ff);
  text-decoration: underline;
}

:deep(.ant-descriptions-item-label) {
  font-weight: 600;
  color: var(--text-primary, #262626);
  width: 180px;
}

[data-theme="dark"] :deep(.ant-descriptions-item-label) {
  color: rgba(255, 255, 255, 0.85);
}

:deep(.ant-descriptions-bordered .ant-descriptions-item-label) {
  background: var(--bg-secondary, #fafafa);
}

[data-theme="dark"] :deep(.ant-descriptions-bordered .ant-descriptions-item-label) {
  background: rgba(255, 255, 255, 0.04);
}

@media (max-width: 768px) {
  .card-header {
    flex-direction: column;
    align-items: flex-start;
  }

  :deep(.ant-descriptions-item-label) {
    width: 120px;
  }
}
</style>
