<template>
  <DashboardLayout>
    <Breadcrumb :items="breadcrumbItems" />

    <a-card class="team-invitation-show-card" v-if="teamInvitation">
      <template #title>
        <div class="card-header">
          <h2 class="card-title">Team Invitation Details</h2>
          <a-space>
            <a-button 
              v-if="teamInvitation.status === 'pending' && !isExpired(teamInvitation.expires_at)"
              @click="handleResend"
            >
              <template #icon><SendOutlined /></template>
              Resend
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

      <!-- Team Invitation Information -->
      <div class="team-invitation-info-section">
        <a-descriptions :column="1" bordered>
          <a-descriptions-item label="Email">
            <span class="info-value">{{ teamInvitation.email }}</span>
          </a-descriptions-item>
          
          <a-descriptions-item label="Team">
            <a-tag v-if="teamInvitation.team" color="blue">
              {{ teamInvitation.team.name }}
            </a-tag>
            <span class="text-muted" v-else>—</span>
          </a-descriptions-item>
          
          <a-descriptions-item label="Role">
            <a-tag :color="getRoleColor(teamInvitation.role)">
              {{ teamInvitation.role }}
            </a-tag>
          </a-descriptions-item>
          
          <a-descriptions-item label="Status">
            <a-tag :color="getStatusColor(teamInvitation.status)">
              {{ teamInvitation.status }}
            </a-tag>
          </a-descriptions-item>
          
          <a-descriptions-item label="Token">
            <span class="info-value token-value">{{ teamInvitation.token }}</span>
          </a-descriptions-item>
          
          <a-descriptions-item label="Invited By">
            <span class="info-value" v-if="teamInvitation.invited_by">
              {{ `${teamInvitation.invited_by.first_name || ''} ${teamInvitation.invited_by.last_name || ''}`.trim() || 'N/A' }}
              <span class="user-email">({{ teamInvitation.invited_by.email }})</span>
            </span>
            <span class="text-muted" v-else>—</span>
          </a-descriptions-item>
          
          <a-descriptions-item label="Expires At">
            <span class="info-value" :class="{ 'expired-text': isExpired(teamInvitation.expires_at) }">
              {{ formatDate(teamInvitation.expires_at) }}
            </span>
          </a-descriptions-item>
          
          <a-descriptions-item label="Created At">
            <span class="info-value">{{ formatDate(teamInvitation.created_at) }}</span>
          </a-descriptions-item>
          
          <a-descriptions-item label="Updated At">
            <span class="info-value">{{ formatDate(teamInvitation.updated_at) }}</span>
          </a-descriptions-item>
        </a-descriptions>
      </div>
    </a-card>

    <!-- Activity Log Card -->
    <a-card class="activity-log-card" v-if="teamInvitation">
      <template #title>
        <h2 class="card-title">Activity Log</h2>
      </template>
      <ActivityLog
        :activities="activities"
        :loading="false"
        :team-invitation-id="teamInvitation.id"
      />
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
  SendOutlined,
} from '@ant-design/icons-vue';
import dayjs from 'dayjs';

const page = usePage();

const teamInvitation = computed(() => page.props.teamInvitation || null);
const activities = computed(() => page.props.activities || { data: [], current_page: 1, per_page: 15, total: 0 });

const breadcrumbItems = ref([
  { label: 'Dashboard', href: '/dashboard' },
  { label: 'Team Invitations', href: '/dashboard/team-invitations' },
  { label: teamInvitation.value?.email || 'Invitation' },
]);

const formatDate = (dateString) => {
  if (!dateString) return '—';
  return dayjs(dateString).format('YYYY-MM-DD HH:mm:ss');
};

const isExpired = (expiresAt) => {
  if (!expiresAt) return false;
  return dayjs(expiresAt).isBefore(dayjs());
};

const getRoleColor = (role) => {
  const roleColors = {
    admin: 'orange',
    manager: 'blue',
    member: 'default',
  };
  return roleColors[role] || 'default';
};

const getStatusColor = (status) => {
  const statusColors = {
    pending: 'orange',
    accepted: 'green',
    rejected: 'red',
    expired: 'default',
  };
  return statusColors[status] || 'default';
};

const handleBack = () => {
  router.visit('/dashboard/team-invitations');
};

const handleEdit = () => {
  if (teamInvitation.value) {
    router.visit(`/dashboard/team-invitations/${teamInvitation.value.id}/edit`);
  }
};

const handleResend = () => {
  if (teamInvitation.value) {
    router.post(`/dashboard/team-invitations/${teamInvitation.value.id}/resend`, {}, {
      preserveScroll: true,
      onSuccess: () => {
        // Success handled by Inertia
      },
    });
  }
};
</script>

<style scoped>
.team-invitation-show-card,
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

.team-invitation-info-section {
  margin-bottom: 24px;
}

.info-value {
  font-weight: 500;
  color: var(--text-primary, #262626);
}

[data-theme="dark"] .info-value {
  color: rgba(255, 255, 255, 0.85);
}

.token-value {
  font-family: monospace;
  font-size: 12px;
  word-break: break-all;
}

.user-email {
  font-size: 12px;
  color: var(--text-tertiary, #8c8c8c);
  margin-left: 4px;
}

[data-theme="dark"] .user-email {
  color: rgba(255, 255, 255, 0.45);
}

.expired-text {
  color: var(--text-tertiary, #8c8c8c);
  text-decoration: line-through;
}

[data-theme="dark"] .expired-text {
  color: rgba(255, 255, 255, 0.45);
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

[data-theme="dark"] :deep(.ant-descriptions-bordered) {
  border: 1px solid rgba(255, 255, 255, 0.15) !important;
}

[data-theme="dark"] :deep(.ant-descriptions-bordered .ant-descriptions-item-label),
[data-theme="dark"] :deep(.ant-descriptions-bordered .ant-descriptions-item-content) {
  border-right: 1px solid rgba(255, 255, 255, 0.15) !important;
  border-bottom: 1px solid rgba(255, 255, 255, 0.15) !important;
}

[data-theme="dark"] :deep(.ant-descriptions-bordered .ant-descriptions-row:last-child .ant-descriptions-item-label),
[data-theme="dark"] :deep(.ant-descriptions-bordered .ant-descriptions-row:last-child .ant-descriptions-item-content) {
  border-bottom: none !important;
}

[data-theme="dark"] :deep(.ant-descriptions-bordered .ant-descriptions-item-label:last-child),
[data-theme="dark"] :deep(.ant-descriptions-bordered .ant-descriptions-item-content:last-child) {
  border-right: none !important;
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

