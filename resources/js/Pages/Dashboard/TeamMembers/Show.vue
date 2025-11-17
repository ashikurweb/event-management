<template>
  <DashboardLayout>
    <Breadcrumb :items="breadcrumbItems" />

    <a-card class="team-member-show-card" v-if="teamMember">
      <template #title>
        <div class="card-header">
          <h2 class="card-title">Team Member Details</h2>
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

      <!-- Team Member Information -->
      <div class="team-member-info-section">
        <a-descriptions :column="1" bordered>
          <a-descriptions-item label="User">
            <span class="info-value" v-if="teamMember.user">
              {{ `${teamMember.user.first_name || ''} ${teamMember.user.last_name || ''}`.trim() || 'N/A' }}
              <span class="user-email">({{ teamMember.user.email }})</span>
            </span>
            <span class="text-muted" v-else>—</span>
          </a-descriptions-item>
          
          <a-descriptions-item label="Team">
            <a-tag v-if="teamMember.team" color="blue">
              {{ teamMember.team.name }}
            </a-tag>
            <span class="text-muted" v-else>—</span>
          </a-descriptions-item>
          
          <a-descriptions-item label="Role">
            <a-tag :color="getRoleColor(teamMember.role)">
              {{ teamMember.role }}
            </a-tag>
          </a-descriptions-item>
          
          <a-descriptions-item label="Invited By">
            <span class="info-value" v-if="teamMember.invited_by">
              {{ `${teamMember.invited_by.first_name || ''} ${teamMember.invited_by.last_name || ''}`.trim() || 'N/A' }}
              <span class="user-email">({{ teamMember.invited_by.email }})</span>
            </span>
            <span class="text-muted" v-else>—</span>
          </a-descriptions-item>
          
          <a-descriptions-item label="Joined At">
            <span class="info-value">{{ formatDate(teamMember.joined_at) }}</span>
          </a-descriptions-item>
          
          <a-descriptions-item label="Permissions">
            <div v-if="teamMember.permissions && teamMember.permissions.length > 0" class="permissions-list">
              <a-tag v-for="(permission, index) in teamMember.permissions" :key="index" style="margin-bottom: 4px;">
                {{ permission }}
              </a-tag>
            </div>
            <span class="text-muted" v-else>—</span>
          </a-descriptions-item>
          
          <a-descriptions-item label="Created At">
            <span class="info-value">{{ formatDate(teamMember.created_at) }}</span>
          </a-descriptions-item>
          
          <a-descriptions-item label="Updated At">
            <span class="info-value">{{ formatDate(teamMember.updated_at) }}</span>
          </a-descriptions-item>
        </a-descriptions>
      </div>
    </a-card>

    <!-- Activity Log Card -->
    <a-card class="activity-log-card" v-if="teamMember">
      <template #title>
        <h2 class="card-title">Activity Log</h2>
      </template>
      <ActivityLog
        :activities="activities"
        :loading="false"
        :team-member-id="teamMember.id"
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
} from '@ant-design/icons-vue';
import dayjs from 'dayjs';

const page = usePage();

const teamMember = computed(() => page.props.teamMember || null);
const activities = computed(() => page.props.activities || { data: [], current_page: 1, per_page: 15, total: 0 });

const breadcrumbItems = ref([
  { label: 'Dashboard', href: '/dashboard' },
  { label: 'Team Members', href: '/dashboard/team-members' },
  { label: teamMember.value?.user ? `${teamMember.value.user.first_name || ''} ${teamMember.value.user.last_name || ''}`.trim() || 'Member' : 'Team Member' },
]);

const formatDate = (dateString) => {
  if (!dateString) return '—';
  return dayjs(dateString).format('YYYY-MM-DD HH:mm:ss');
};

const getRoleColor = (role) => {
  const roleColors = {
    owner: 'red',
    admin: 'orange',
    manager: 'blue',
    member: 'default',
  };
  return roleColors[role] || 'default';
};

const handleBack = () => {
  router.visit('/dashboard/team-members');
};

const handleEdit = () => {
  if (teamMember.value) {
    router.visit(`/dashboard/team-members/${teamMember.value.id}/edit`);
  }
};
</script>

<style scoped>
.team-member-show-card,
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

.team-member-info-section {
  margin-bottom: 24px;
}

.info-value {
  font-weight: 500;
  color: var(--text-primary, #262626);
}

[data-theme="dark"] .info-value {
  color: rgba(255, 255, 255, 0.85);
}

.user-email {
  font-size: 12px;
  color: var(--text-tertiary, #8c8c8c);
  margin-left: 4px;
}

[data-theme="dark"] .user-email {
  color: rgba(255, 255, 255, 0.45);
}

.text-muted {
  color: var(--text-tertiary, #8c8c8c);
  font-style: italic;
}

[data-theme="dark"] .text-muted {
  color: rgba(255, 255, 255, 0.45);
}

.permissions-list {
  display: flex;
  flex-wrap: wrap;
  gap: 4px;
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

