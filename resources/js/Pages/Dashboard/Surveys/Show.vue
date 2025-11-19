<template>
  <DashboardLayout>
    <Breadcrumb :items="breadcrumbItems" />

    <a-card class="survey-show-card" v-if="survey">
      <template #title>
        <div class="card-header">
          <h2 class="card-title">Survey Details</h2>
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

      <!-- Survey Information -->
      <div class="survey-info-section">
        <a-descriptions :column="2" bordered>
          <a-descriptions-item label="Title" :span="2">
            <span class="info-value">{{ survey.title }}</span>
          </a-descriptions-item>
          
          <a-descriptions-item label="Event">
            <span class="info-value" v-if="survey.event">
              <a :href="`/events/${survey.event.id}`" target="_blank">{{ survey.event.title }}</a>
            </span>
            <span class="text-muted" v-else>—</span>
          </a-descriptions-item>
          
          <a-descriptions-item label="Status">
            <a-tag :color="getStatusColor(survey.status)">
              {{ survey.status ? survey.status.toUpperCase() : '' }}
            </a-tag>
          </a-descriptions-item>
          
          <a-descriptions-item label="Required">
            <a-tag :color="survey.is_required ? 'red' : 'default'">
              {{ survey.is_required ? 'Required' : 'Optional' }}
            </a-tag>
          </a-descriptions-item>
          
          <a-descriptions-item label="Opens At">
            <span class="info-value" v-if="survey.opens_at">
              {{ formatDate(survey.opens_at) }}
            </span>
            <span class="text-muted" v-else>—</span>
          </a-descriptions-item>
          
          <a-descriptions-item label="Closes At">
            <span class="info-value" v-if="survey.closes_at">
              {{ formatDate(survey.closes_at) }}
            </span>
            <span class="text-muted" v-else>—</span>
          </a-descriptions-item>
        </a-descriptions>
      </div>

      <!-- Description Section -->
      <div class="section-divider"></div>
      <div class="description-section">
        <h3 class="section-title">Description</h3>
        <p class="description-text" v-if="survey.description">{{ survey.description }}</p>
        <a-empty description="No description available" v-else />
      </div>

      <!-- Questions Section -->
      <div class="section-divider" v-if="survey.questions && survey.questions.length > 0"></div>
      <div class="questions-section" v-if="survey.questions && survey.questions.length > 0">
        <h3 class="section-title">Questions ({{ survey.questions.length }})</h3>
        <a-list
          :data-source="survey.questions"
          :pagination="false"
        >
          <template #renderItem="{ item, index }">
            <a-list-item>
              <a-list-item-meta>
                <template #title>
                  <span class="question-number">Q{{ index + 1 }}:</span>
                  <span>{{ item.question }}</span>
                  <a-tag v-if="item.is_required" color="red" style="margin-left: 8px;">Required</a-tag>
                </template>
                <template #description>
                  <span class="question-type">Type: {{ item.type.replace('_', ' ').toUpperCase() }}</span>
                  <span v-if="item.options && item.options.length > 0" class="question-options">
                    | Options: {{ item.options.join(', ') }}
                  </span>
                </template>
              </a-list-item-meta>
            </a-list-item>
          </template>
        </a-list>
      </div>

      <!-- Responses Section -->
      <div class="section-divider" v-if="survey.responses && survey.responses.length > 0"></div>
      <div class="responses-section" v-if="survey.responses && survey.responses.length > 0">
        <h3 class="section-title">Responses ({{ survey.responses.length }})</h3>
        <a-list
          :data-source="survey.responses"
          :pagination="false"
        >
          <template #renderItem="{ item }">
            <a-list-item>
              <a-list-item-meta>
                <template #title>
                  <span v-if="item.user">
                    {{ item.user.first_name }} {{ item.user.last_name }}
                    <span v-if="item.user.email">({{ item.user.email }})</span>
                  </span>
                  <span v-else>Anonymous</span>
                </template>
                <template #description>
                  <span>Submitted: {{ formatDate(item.created_at) }}</span>
                </template>
              </a-list-item-meta>
            </a-list-item>
          </template>
        </a-list>
      </div>

      <!-- Activity Logs Section -->
      <div class="section-divider"></div>
      <div class="activities-section">
        <h3 class="section-title">Activity Logs</h3>
        <a-list
          :data-source="activities.data"
          :pagination="false"
          :loading="loading"
        >
          <template #renderItem="{ item }">
            <a-list-item>
              <a-list-item-meta>
                <template #title>
                  <span>{{ item.description }}</span>
                </template>
                <template #description>
                  <span v-if="item.user">
                    By {{ item.user.first_name }} {{ item.user.last_name }}
                  </span>
                  <span v-else>System</span>
                  <span> • {{ formatDate(item.created_at) }}</span>
                </template>
              </a-list-item-meta>
            </a-list-item>
          </template>
        </a-list>
        
        <Pagination
          v-if="activities.total > 0"
          :current="activities.current_page"
          :page-size="activities.per_page"
          :total="activities.total"
          @change="handleActivityPageChange"
          style="margin-top: 16px;"
        />
      </div>
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
  EditOutlined,
  ArrowLeftOutlined,
} from '@ant-design/icons-vue';
import dayjs from 'dayjs';

const page = usePage();
const loading = ref(false);

const survey = computed(() => page.props.survey || {});
const activities = computed(() => page.props.activities || { data: [] });

const breadcrumbItems = ref([
  { label: 'Dashboard', href: '/dashboard' },
  { label: 'Surveys', href: '/dashboard/surveys' },
  { label: 'Details' },
]);

const getStatusColor = (status) => {
  const colors = {
    draft: 'default',
    active: 'green',
    closed: 'red',
  };
  return colors[status] || 'default';
};

const formatDate = (date) => {
  return dayjs(date).format('YYYY-MM-DD HH:mm:ss');
};

const handleEdit = () => {
  router.visit(`/dashboard/surveys/${survey.value.id}/edit`);
};

const handleBack = () => {
  router.visit('/dashboard/surveys');
};

const handleActivityPageChange = ({ current, pageSize }) => {
  router.visit(`/dashboard/surveys/${survey.value.id}/activities`, {
    data: {
      page: current,
      per_page: pageSize,
    },
    preserveState: true,
    preserveScroll: true,
  });
};
</script>

<style scoped>
.survey-show-card {
  border-radius: 8px;
  box-shadow: var(--card-shadow, 0 2px 8px rgba(0, 0, 0, 0.06));
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

.survey-info-section {
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

.description-section,
.questions-section,
.responses-section,
.activities-section {
  margin-bottom: 24px;
}

.description-text {
  color: var(--text-primary, #262626);
  line-height: 1.6;
  white-space: pre-wrap;
}

[data-theme="dark"] .description-text {
  color: rgba(255, 255, 255, 0.85);
}

.question-number {
  font-weight: 600;
  color: var(--text-primary, #262626);
  margin-right: 8px;
}

[data-theme="dark"] .question-number {
  color: rgba(255, 255, 255, 0.85);
}

.question-type {
  font-size: 12px;
  color: var(--text-secondary, #8c8c8c);
}

[data-theme="dark"] .question-type {
  color: rgba(255, 255, 255, 0.65);
}

.question-options {
  font-size: 12px;
  color: var(--text-secondary, #8c8c8c);
}

[data-theme="dark"] .question-options {
  color: rgba(255, 255, 255, 0.65);
}

@media (max-width: 768px) {
  .card-header {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>

