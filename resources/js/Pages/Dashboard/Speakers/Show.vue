<template>
  <DashboardLayout>
    <Breadcrumb :items="breadcrumbItems" />

    <a-card class="speaker-show-card" v-if="speaker">
      <template #title>
        <div class="card-header">
          <h2 class="card-title">Speaker Details</h2>
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

      <!-- Speaker Information -->
      <div class="speaker-info-section">
        <a-row :gutter="24">
          <a-col :xs="24" :md="8">
            <div class="photo-section">
              <a-avatar
                v-if="speaker.photo_urls && speaker.photo_urls.length > 0"
                :src="speaker.photo_urls[0]"
                :size="200"
                shape="square"
                class="speaker-photo"
              />
              <a-avatar v-else :size="200" shape="square" class="speaker-photo">
                <template #icon><UserOutlined /></template>
              </a-avatar>
              <!-- Show all photos if more than one -->
              <div v-if="speaker.photo_urls && speaker.photo_urls.length > 1" class="additional-photos">
                <a-badge :count="speaker.photo_urls.length - 1" :number-style="{ backgroundColor: '#1890ff' }">
                  <span class="more-photos-text">+{{ speaker.photo_urls.length - 1 }} more</span>
                </a-badge>
              </div>
            </div>
          </a-col>
          <a-col :xs="24" :md="16">
            <a-descriptions :column="1" bordered>
              <a-descriptions-item label="Name">
                <span class="info-value">{{ speaker.name }}</span>
              </a-descriptions-item>
              
              <a-descriptions-item label="Title">
                <span class="info-value" v-if="speaker.title">{{ speaker.title }}</span>
                <span class="text-muted" v-else>—</span>
              </a-descriptions-item>
              
              <a-descriptions-item label="Company">
                <span class="info-value" v-if="speaker.company">{{ speaker.company }}</span>
                <span class="text-muted" v-else>—</span>
              </a-descriptions-item>
              
              <a-descriptions-item label="Email">
                <span class="info-value" v-if="speaker.email">
                  <a :href="`mailto:${speaker.email}`">{{ speaker.email }}</a>
                </span>
                <span class="text-muted" v-else>—</span>
              </a-descriptions-item>
              
              <a-descriptions-item label="Phone">
                <span class="info-value" v-if="speaker.phone">
                  <a :href="`tel:${speaker.phone}`">{{ speaker.phone }}</a>
                </span>
                <span class="text-muted" v-else>—</span>
              </a-descriptions-item>
              
              <a-descriptions-item label="Website">
                <span class="info-value" v-if="speaker.website">
                  <a :href="speaker.website" target="_blank" rel="noopener noreferrer">
                    {{ speaker.website }}
                  </a>
                </span>
                <span class="text-muted" v-else>—</span>
              </a-descriptions-item>
              
              <a-descriptions-item label="Featured">
                <a-tag :color="speaker.is_featured ? 'gold' : 'default'">
                  {{ speaker.is_featured ? 'Yes' : 'No' }}
                </a-tag>
              </a-descriptions-item>
              
              <a-descriptions-item label="User Account" v-if="speaker.user">
                <a-tag color="blue">
                  {{ speaker.user.first_name }} {{ speaker.user.last_name }}
                </a-tag>
              </a-descriptions-item>
            </a-descriptions>
          </a-col>
        </a-row>
      </div>

      <!-- Bio Section -->
      <div class="section-divider"></div>
      <div class="bio-section">
        <h3 class="section-title">Biography</h3>
        <p class="bio-text" v-if="speaker.bio">{{ speaker.bio }}</p>
        <a-empty description="No biography available" v-else />
      </div>

      <!-- Social Links -->
      <div class="section-divider" v-if="speaker.social_links && hasSocialLinks"></div>
      <div class="social-section" v-if="speaker.social_links && hasSocialLinks">
        <h3 class="section-title">Social Links</h3>
        <a-space wrap>
          <a-button
            v-if="speaker.social_links.linkedin"
            type="link"
            :href="speaker.social_links.linkedin"
            target="_blank"
            rel="noopener noreferrer"
          >
            <template #icon><LinkedinOutlined /></template>
            LinkedIn
          </a-button>
          <a-button
            v-if="speaker.social_links.twitter"
            type="link"
            :href="speaker.social_links.twitter"
            target="_blank"
            rel="noopener noreferrer"
          >
            <template #icon><TwitterOutlined /></template>
            Twitter
          </a-button>
          <a-button
            v-if="speaker.social_links.facebook"
            type="link"
            :href="speaker.social_links.facebook"
            target="_blank"
            rel="noopener noreferrer"
          >
            <template #icon><FacebookOutlined /></template>
            Facebook
          </a-button>
          <a-button
            v-if="speaker.social_links.instagram"
            type="link"
            :href="speaker.social_links.instagram"
            target="_blank"
            rel="noopener noreferrer"
          >
            <template #icon><InstagramOutlined /></template>
            Instagram
          </a-button>
        </a-space>
      </div>

      <!-- Specialties -->
      <div class="section-divider" v-if="speaker.specialties && speaker.specialties.length > 0"></div>
      <div class="specialties-section" v-if="speaker.specialties && speaker.specialties.length > 0">
        <h3 class="section-title">Specialties</h3>
        <a-space wrap>
          <a-tag
            v-for="(specialty, index) in speaker.specialties"
            :key="index"
            color="blue"
          >
            {{ specialty }}
          </a-tag>
        </a-space>
      </div>

      <!-- Events -->
      <div class="section-divider"></div>
      <div class="related-section" v-if="speaker.events && speaker.events.length > 0">
        <h3 class="section-title">Events ({{ speaker.events.length }})</h3>
        <a-table
          :columns="eventColumns"
          :data-source="speaker.events"
          :row-key="(record) => record.id"
          :pagination="false"
          size="small"
        >
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
        <a-empty description="No events for this speaker" />
      </div>

      <!-- Metadata -->
      <div class="section-divider"></div>
      <div class="metadata-section">
        <a-descriptions :column="2" bordered size="small">
          <a-descriptions-item label="Created At">
            <span class="info-value">{{ formatDate(speaker.created_at) }}</span>
          </a-descriptions-item>
          <a-descriptions-item label="Updated At">
            <span class="info-value">{{ formatDate(speaker.updated_at) }}</span>
          </a-descriptions-item>
        </a-descriptions>
      </div>
    </a-card>

    <!-- Activity Log Card -->
    <a-card class="activity-log-card" v-if="speaker">
      <template #title>
        <h2 class="card-title">Activity Log</h2>
      </template>
      <ActivityLog
        :activities="activities"
        :loading="false"
        :speaker-id="speaker.id"
        @refresh="handleRefreshActivities"
      />
    </a-card>
  </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import DashboardLayout from '../../../Layouts/DashboardLayout.vue';
import Breadcrumb from '../../../Components/Breadcrumb.vue';
import ActivityLog from '../../../Components/ActivityLog.vue';
import {
  EditOutlined,
  ArrowLeftOutlined,
  UserOutlined,
  LinkedinOutlined,
  TwitterOutlined,
  FacebookOutlined,
  InstagramOutlined,
} from '@ant-design/icons-vue';
import dayjs from 'dayjs';

const page = usePage();

const speaker = computed(() => page.props.speaker || {});
const activities = computed(() => page.props.activities || { data: [] });

const breadcrumbItems = computed(() => [
  { label: 'Dashboard', href: '/dashboard' },
  { label: 'Speakers', href: '/dashboard/speakers' },
  { label: speaker.value.name || 'Speaker Details' },
]);

const hasSocialLinks = computed(() => {
  if (!speaker.value.social_links) return false;
  const links = speaker.value.social_links;
  return !!(links.linkedin || links.twitter || links.facebook || links.instagram);
});

// Photo URLs come directly from backend

const formatDate = (date) => {
  if (!date) return '—';
  return dayjs(date).format('MMMM DD, YYYY [at] HH:mm');
};

const getStatusColor = (status) => {
  const colors = {
    published: 'green',
    draft: 'orange',
    cancelled: 'red',
    completed: 'blue',
  };
  return colors[status?.toLowerCase()] || 'default';
};

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
  },
  {
    title: 'Start Date',
    key: 'start_date',
    dataIndex: 'start_date',
  },
];

const handleEdit = () => {
  router.visit(`/dashboard/speakers/${speaker.value.id}/edit`);
};

const handleBack = () => {
  router.visit('/dashboard/speakers');
};

const handleViewEvent = (eventId) => {
  router.visit(`/events/${eventId}`);
};

const handleRefreshActivities = () => {
  router.visit(`/dashboard/speakers/${speaker.value.id}/activities`, {
    preserveState: true,
    preserveScroll: true,
  });
};
</script>

<style scoped>
.speaker-show-card,
.activity-log-card {
  border-radius: 8px;
  box-shadow: var(--card-shadow, 0 2px 8px rgba(0, 0, 0, 0.06));
  margin-bottom: 24px;
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

.speaker-info-section {
  margin-bottom: 24px;
}

.photo-section {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  margin-bottom: 16px;
  gap: 12px;
}

.additional-photos {
  margin-top: 8px;
}

.more-photos-text {
  color: var(--color-primary, #1890ff);
  font-size: 12px;
  cursor: pointer;
}

.more-photos-text:hover {
  text-decoration: underline;
}

.speaker-photo {
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
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

.section-divider {
  height: 1px;
  background: var(--border-color, #f0f0f0);
  margin: 24px 0;
}

[data-theme="dark"] .section-divider {
  background: var(--border-color, #434343);
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

.bio-section,
.social-section,
.specialties-section,
.related-section,
.metadata-section {
  margin-bottom: 24px;
}

.bio-text {
  color: var(--text-primary, #262626);
  line-height: 1.8;
  white-space: pre-wrap;
}

[data-theme="dark"] .bio-text {
  color: rgba(255, 255, 255, 0.85);
}

.event-link {
  color: var(--color-primary, #1890ff);
  text-decoration: none;
}

.event-link:hover {
  text-decoration: underline;
}

@media (max-width: 768px) {
  .card-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
  }
  
  .photo-section {
    margin-bottom: 24px;
  }
}
</style>

