<template>
  <DashboardLayout>
    <Breadcrumb :items="breadcrumbItems" />

    <a-card class="certificate-show-card" v-if="certificate">
      <template #title>
        <div class="card-header">
          <h2 class="card-title">Certificate Details</h2>
          <a-space>
            <a-button @click="handleDownload">
              <template #icon><DownloadOutlined /></template>
              Download
            </a-button>
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

      <!-- Certificate Information -->
      <div class="certificate-info-section">
        <a-descriptions :column="1" bordered>
          <a-descriptions-item label="Certificate Number">
            <span class="info-value certificate-number">{{ certificate.certificate_number }}</span>
          </a-descriptions-item>
          
          <a-descriptions-item label="Event">
            <a-tag v-if="certificate.event" color="blue">
              {{ certificate.event.title }}
            </a-tag>
            <span class="text-muted" v-else>—</span>
          </a-descriptions-item>
          
          <a-descriptions-item label="User">
            <span class="info-value" v-if="certificate.user">
              {{ certificate.user.first_name }} {{ certificate.user.last_name }}
              <span class="text-muted">({{ certificate.user.email }})</span>
            </span>
            <span class="text-muted" v-else>—</span>
          </a-descriptions-item>
          
          <a-descriptions-item label="Verification Code">
            <span class="info-value verification-code">{{ certificate.verification_code }}</span>
            <a-button
              type="link"
              size="small"
              @click="copyToClipboard(certificate.verification_code)"
              style="margin-left: 8px"
            >
              <template #icon><CopyOutlined /></template>
              Copy
            </a-button>
          </a-descriptions-item>
          
          <a-descriptions-item label="Template Path">
            <span class="info-value" v-if="certificate.template_path">
              {{ certificate.template_path }}
            </span>
            <span class="text-muted" v-else>—</span>
          </a-descriptions-item>
          
          <a-descriptions-item label="File Path">
            <span class="info-value" v-if="certificate.file_path">
              {{ certificate.file_path }}
            </span>
            <span class="text-muted" v-else>—</span>
          </a-descriptions-item>
          
          <a-descriptions-item label="Issued At">
            <span class="info-value">{{ formatDate(certificate.issued_at) }}</span>
          </a-descriptions-item>
          
          <a-descriptions-item label="Downloaded At">
            <a-tag v-if="certificate.downloaded_at" color="green">
              {{ formatDate(certificate.downloaded_at) }}
            </a-tag>
            <a-tag v-else color="default">Not Downloaded</a-tag>
          </a-descriptions-item>
          
          <a-descriptions-item label="Created At">
            <span class="info-value">{{ formatDate(certificate.created_at) }}</span>
          </a-descriptions-item>
          
          <a-descriptions-item label="Updated At">
            <span class="info-value">{{ formatDate(certificate.updated_at) }}</span>
          </a-descriptions-item>
        </a-descriptions>
      </div>
    </a-card>

    <!-- Activity Log Card -->
    <a-card class="activity-log-card" v-if="certificate">
      <template #title>
        <h2 class="card-title">Activity Log</h2>
      </template>
      <ActivityLog
        :activities="activities"
        :loading="false"
        :certificate-id="certificate.id"
      />
    </a-card>
  </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { message } from 'ant-design-vue';
import DashboardLayout from '../../../Layouts/DashboardLayout.vue';
import Breadcrumb from '../../../Components/Breadcrumb.vue';
import ActivityLog from '../../../Components/ActivityLog.vue';
import {
  EditOutlined,
  ArrowLeftOutlined,
  DownloadOutlined,
  CopyOutlined,
} from '@ant-design/icons-vue';
import dayjs from 'dayjs';

const page = usePage();

const certificate = computed(() => page.props.certificate || null);
const activities = computed(() => page.props.activities || { data: [], current_page: 1, per_page: 15, total: 0 });

const breadcrumbItems = ref([
  { label: 'Dashboard', href: '/dashboard' },
  { label: 'Certificates', href: '/dashboard/certificates' },
  { label: certificate.value?.certificate_number || 'Certificate' },
]);

const formatDate = (dateString) => {
  if (!dateString) return '—';
  return dayjs(dateString).format('YYYY-MM-DD HH:mm:ss');
};

const copyToClipboard = async (text) => {
  try {
    await navigator.clipboard.writeText(text);
    message.success('Verification code copied to clipboard!');
  } catch (err) {
    message.error('Failed to copy to clipboard');
  }
};

const handleBack = () => {
  router.visit('/dashboard/certificates');
};

const handleEdit = () => {
  if (certificate.value) {
    router.visit(`/dashboard/certificates/${certificate.value.id}/edit`);
  }
};

const handleDownload = () => {
  if (certificate.value) {
    router.post(`/dashboard/certificates/${certificate.value.id}/download`, {}, {
      preserveScroll: true,
    });
  }
};
</script>

<style scoped>
.certificate-show-card,
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

.certificate-info-section {
  margin-bottom: 24px;
}

.info-value {
  font-weight: 500;
  color: var(--text-primary, #262626);
}

[data-theme="dark"] .info-value {
  color: rgba(255, 255, 255, 0.85);
}

.certificate-number {
  font-family: monospace;
  font-size: 16px;
  font-weight: 600;
}

.verification-code {
  font-family: monospace;
  font-size: 14px;
}

.text-muted {
  color: var(--text-tertiary, #8c8c8c);
  font-style: italic;
}

[data-theme="dark"] .text-muted {
  color: rgba(255, 255, 255, 0.45);
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

