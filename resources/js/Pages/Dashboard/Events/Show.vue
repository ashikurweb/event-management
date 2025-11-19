<template>
  <DashboardLayout>
    <Breadcrumb :items="breadcrumbItems" />

    <a-card class="event-show-card" v-if="event">
      <template #title>
        <div class="card-header">
          <h2 class="card-title">{{ event.title }}</h2>
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

      <a-descriptions :column="2" bordered>
        <a-descriptions-item label="UUID" :span="2">
          <span class="info-value">{{ event.uuid }}</span>
        </a-descriptions-item>
        
        <a-descriptions-item label="Organizer">
          <span class="info-value" v-if="event.organizer">
            {{ event.organizer.first_name }} {{ event.organizer.last_name }}
          </span>
          <span class="text-muted" v-else>—</span>
        </a-descriptions-item>
        
        <a-descriptions-item label="Category">
          <a-tag v-if="event.category" color="blue">{{ event.category.name }}</a-tag>
          <span class="text-muted" v-else>—</span>
        </a-descriptions-item>
        
        <a-descriptions-item label="Event Type">
          <a-tag :color="getEventTypeColor(event.event_type)">{{ event.event_type }}</a-tag>
        </a-descriptions-item>
        
        <a-descriptions-item label="Status">
          <a-tag :color="getStatusColor(event.status)">{{ event.status }}</a-tag>
        </a-descriptions-item>
        
        <a-descriptions-item label="Visibility">
          <a-tag :color="getVisibilityColor(event.visibility)">{{ event.visibility }}</a-tag>
        </a-descriptions-item>
        
        <a-descriptions-item label="Featured">
          <a-tag v-if="event.is_featured" color="gold">Yes</a-tag>
          <a-tag v-else color="default">No</a-tag>
        </a-descriptions-item>
        
        <a-descriptions-item label="Start Date" :span="2">
          <span class="info-value">{{ formatDate(event.start_date) }}</span>
        </a-descriptions-item>
        
        <a-descriptions-item label="End Date" :span="2">
          <span class="info-value">{{ formatDate(event.end_date) }}</span>
        </a-descriptions-item>
        
        <a-descriptions-item label="Timezone">
          <span class="info-value">{{ event.timezone }}</span>
        </a-descriptions-item>
        
        <a-descriptions-item label="Tags">
          <a-space v-if="event.tags && event.tags.length > 0" size="small" wrap>
            <a-tag v-for="tag in event.tags" :key="tag.id" color="blue">
              {{ tag.name }}
            </a-tag>
          </a-space>
          <span class="text-muted" v-else>—</span>
        </a-descriptions-item>
        
        <a-descriptions-item label="Short Description" :span="2" v-if="event.short_description">
          <span class="info-value">{{ event.short_description }}</span>
        </a-descriptions-item>
        
        <a-descriptions-item label="Description" :span="2" v-if="event.description">
          <div class="info-value" v-html="event.description.replace(/\n/g, '<br>')"></div>
        </a-descriptions-item>
      </a-descriptions>
    </a-card>

    <!-- Activity Log Card -->
    <a-card class="activity-log-card" v-if="event">
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

const event = computed(() => page.props.event);
const activities = computed(() => page.props.activities || { data: [] });

const breadcrumbItems = ref([
  { label: 'Dashboard', href: '/dashboard' },
  { label: 'Events', href: '/dashboard/events' },
  { label: event.value?.title || 'Event Details' },
]);

const formatDate = (dateString) => {
  if (!dateString) return '—';
  return dayjs(dateString).format('YYYY-MM-DD HH:mm:ss');
};

const getEventTypeColor = (type) => {
  const colors = { online: 'blue', offline: 'green', hybrid: 'orange' };
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
  const colors = { public: 'success', private: 'error', unlisted: 'warning' };
  return colors[visibility] || 'default';
};

const handleEdit = () => {
  router.visit(`/dashboard/events/${event.value.id}/edit`);
};

const handleBack = () => {
  router.visit('/dashboard/events');
};

const handlePageChange = (page) => {
  router.visit(`/dashboard/events/${event.value.id}/activities?page=${page}`, {
    preserveState: true,
    preserveScroll: true,
  });
};
</script>

<style scoped>
.event-show-card,
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

.text-muted {
  color: var(--text-tertiary, #8c8c8c);
}

[data-theme="dark"] .text-muted {
  color: rgba(255, 255, 255, 0.45);
}
</style>

