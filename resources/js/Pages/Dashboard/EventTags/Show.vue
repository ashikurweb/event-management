<template>
  <DashboardLayout>
    <Breadcrumb :items="breadcrumbItems" />

    <a-card class="tag-show-card" v-if="tag">
      <template #title>
        <div class="card-header">
          <h2 class="card-title">{{ tag.name }}</h2>
          <a-space>
            <a-button @click="handleEdit">
              <template #icon><EditOutlined /></template>
              Edit
            </a-button>
            <a-button @click="handleBack">
              <template #icon><ArrowLeftOutlined /></template>
              Back
            </a-button>
          </a-space>
        </div>
      </template>

      <a-descriptions :column="1" bordered>
        <a-descriptions-item label="Name">
          <span class="info-value">{{ tag.name }}</span>
        </a-descriptions-item>
        
        <a-descriptions-item label="Slug">
          <span class="info-value tag-slug">{{ tag.slug }}</span>
        </a-descriptions-item>
        
        <a-descriptions-item label="Usage Count">
          <a-tag color="blue">{{ tag.events?.length || 0 }} events</a-tag>
        </a-descriptions-item>
        
        <a-descriptions-item label="Created At">
          <span class="info-value">{{ formatDate(tag.created_at) }}</span>
        </a-descriptions-item>
        
        <a-descriptions-item label="Updated At">
          <span class="info-value">{{ formatDate(tag.updated_at) }}</span>
        </a-descriptions-item>
      </a-descriptions>

      <!-- Events using this tag -->
      <a-divider>Events using this tag</a-divider>
      <a-list
        v-if="tag.events && tag.events.length > 0"
        :data-source="tag.events"
        :pagination="{ pageSize: 10 }"
      >
        <template #renderItem="{ item }">
          <a-list-item>
            <a-list-item-meta>
              <template #title>
                <a @click="handleViewEvent(item.id)">{{ item.title }}</a>
              </template>
              <template #description>
                <span class="text-muted">
                  {{ formatDate(item.start_date) }} - {{ formatDate(item.end_date) }}
                </span>
              </template>
            </a-list-item-meta>
          </a-list-item>
        </template>
      </a-list>
      <a-empty v-else description="No events using this tag" />
    </a-card>

    <!-- Activity Log Card -->
    <a-card class="activity-log-card" v-if="tag">
      <template #title>
        <h2 class="card-title">Activity Log</h2>
      </template>
      <a-list
        :data-source="activities.data"
        :pagination="{
          current: activities.current_page,
          pageSize: activities.per_page,
          total: activities.total,
          onChange: handlePageChange,
        }"
      >
        <template #renderItem="{ item }">
          <a-list-item>
            <a-list-item-meta>
              <template #title>
                <span>{{ item.description }}</span>
              </template>
              <template #description>
                <span class="text-muted">
                  {{ formatDate(item.created_at) }} by {{ item.user?.first_name }} {{ item.user?.last_name }}
                </span>
              </template>
            </a-list-item-meta>
          </a-list-item>
        </template>
      </a-list>
    </a-card>
  </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import DashboardLayout from '../../../Layouts/DashboardLayout.vue';
import Breadcrumb from '../../../Components/Breadcrumb.vue';
import { EditOutlined, ArrowLeftOutlined } from '@ant-design/icons-vue';
import dayjs from 'dayjs';

const page = usePage();

const tag = computed(() => page.props.tag);
const activities = computed(() => page.props.activities || { data: [] });

const breadcrumbItems = ref([
  { label: 'Dashboard', href: '/dashboard' },
  { label: 'Event Tags', href: '/dashboard/event-tags' },
  { label: tag.value?.name || 'Tag Details' },
]);

const formatDate = (dateString) => {
  if (!dateString) return '—';
  return dayjs(dateString).format('YYYY-MM-DD HH:mm:ss');
};

const handleEdit = () => {
  router.visit(`/dashboard/event-tags/${tag.value.id}/edit`);
};

const handleBack = () => {
  router.visit('/dashboard/event-tags');
};

const handleViewEvent = (eventId) => {
  router.visit(`/dashboard/events/${eventId}`);
};

const handlePageChange = (page) => {
  router.visit(`/dashboard/event-tags/${tag.value.id}/activities?page=${page}`, {
    preserveState: true,
    preserveScroll: true,
  });
};
</script>

<style scoped>
.tag-show-card,
.activity-log-card {
  border-radius: 8px;
  box-shadow: var(--card-shadow, 0 2px 8px rgba(0, 0, 0, 0.06));
  margin-bottom: 16px;
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

.info-value {
  font-weight: 500;
  color: var(--text-primary, #262626);
}

[data-theme="dark"] .info-value {
  color: rgba(255, 255, 255, 0.85);
}

.tag-slug {
  font-family: monospace;
  font-size: 14px;
}

.text-muted {
  color: var(--text-tertiary, #8c8c8c);
}

[data-theme="dark"] .text-muted {
  color: rgba(255, 255, 255, 0.45);
}
</style>

