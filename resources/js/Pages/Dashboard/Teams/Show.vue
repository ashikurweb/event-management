<template>
  <DashboardLayout>
    <Breadcrumb :items="breadcrumbItems" />

    <a-card class="team-show-card" v-if="team">
      <template #title>
        <div class="card-header">
          <h2 class="card-title">Team Details</h2>
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

      <!-- Team Information -->
      <div class="team-info-section">
        <a-descriptions :column="1" bordered>
          <a-descriptions-item label="Name">
            <span class="info-value">{{ team.name }}</span>
          </a-descriptions-item>
          
          <a-descriptions-item label="Slug">
            <span class="info-value">{{ team.slug }}</span>
          </a-descriptions-item>
          
          <a-descriptions-item label="Description">
            <span class="info-value" v-if="team.description">
              {{ team.description }}
            </span>
            <span class="text-muted" v-else>—</span>
          </a-descriptions-item>
          
          <a-descriptions-item label="Owner">
            <a-tag v-if="team.owner" color="blue">
              {{ `${team.owner.first_name || ''} ${team.owner.last_name || ''}`.trim() || 'N/A' }} ({{ team.owner.email }})
            </a-tag>
            <span class="text-muted" v-else>—</span>
          </a-descriptions-item>
          
          <a-descriptions-item label="Email">
            <span class="info-value" v-if="team.email">
              <a :href="`mailto:${team.email}`">{{ team.email }}</a>
            </span>
            <span class="text-muted" v-else>—</span>
          </a-descriptions-item>
          
          <a-descriptions-item label="Phone">
            <span class="info-value" v-if="team.phone">
              <a :href="`tel:${team.phone}`">{{ team.phone }}</a>
            </span>
            <span class="text-muted" v-else>—</span>
          </a-descriptions-item>
          
          <a-descriptions-item label="Website">
            <span class="info-value" v-if="team.website">
              <a :href="team.website" target="_blank" rel="noopener noreferrer">
                <LinkOutlined style="margin-right: 4px" />
                {{ team.website }}
              </a>
            </span>
            <span class="text-muted" v-else>—</span>
          </a-descriptions-item>
          
          <a-descriptions-item label="Logo">
            <span class="info-value" v-if="team.logo">
              <a :href="team.logo" target="_blank" rel="noopener noreferrer">
                {{ team.logo }}
              </a>
            </span>
            <span class="text-muted" v-else>—</span>
          </a-descriptions-item>
          
          <a-descriptions-item label="Verification Status">
            <a-tag :color="team.is_verified ? 'green' : 'orange'">
              {{ team.is_verified ? 'Verified' : 'Unverified' }}
            </a-tag>
          </a-descriptions-item>
          
          <a-descriptions-item label="Created At">
            <span class="info-value">{{ formatDate(team.created_at) }}</span>
          </a-descriptions-item>
          
          <a-descriptions-item label="Updated At">
            <span class="info-value">{{ formatDate(team.updated_at) }}</span>
          </a-descriptions-item>
        </a-descriptions>
      </div>

      <!-- Team Members -->
      <div class="section-divider"></div>
      <div class="related-section" v-if="team.members && team.members.length > 0">
        <h3 class="section-title">Team Members ({{ team.members.length }})</h3>
        <a-table
          :columns="memberColumns"
          :data-source="team.members"
          :row-key="(record) => record.id"
          :pagination="false"
          size="small"
        >
          <template #bodyCell="{ column, record }">
            <template v-if="column.key === 'user'">
              <span v-if="record.user">
                {{ `${record.user.first_name || ''} ${record.user.last_name || ''}`.trim() || 'N/A' }} ({{ record.user.email }})
              </span>
              <span v-else class="text-muted">—</span>
            </template>
            <template v-if="column.key === 'role'">
              <a-tag :color="getRoleColor(record.role)">
                {{ record.role }}
              </a-tag>
            </template>
            <template v-if="column.key === 'joined_at'">
              <span>{{ formatDate(record.joined_at) }}</span>
            </template>
          </template>
        </a-table>
      </div>
      <div class="related-section" v-else>
        <h3 class="section-title">Team Members</h3>
        <a-empty description="No members in this team" />
      </div>

      <!-- Team Invitations -->
      <div class="section-divider"></div>
      <div class="related-section" v-if="team.invitations && team.invitations.length > 0">
        <h3 class="section-title">Pending Invitations ({{ team.invitations.length }})</h3>
        <a-table
          :columns="invitationColumns"
          :data-source="team.invitations"
          :row-key="(record) => record.id"
          :pagination="false"
          size="small"
        >
          <template #bodyCell="{ column, record }">
            <template v-if="column.key === 'status'">
              <a-tag :color="getInvitationStatusColor(record.status)">
                {{ record.status }}
              </a-tag>
            </template>
            <template v-if="column.key === 'role'">
              <a-tag :color="getRoleColor(record.role)">
                {{ record.role }}
              </a-tag>
            </template>
            <template v-if="column.key === 'expires_at'">
              <span>{{ formatDate(record.expires_at) }}</span>
            </template>
          </template>
        </a-table>
      </div>
      <div class="related-section" v-else>
        <h3 class="section-title">Team Invitations</h3>
        <a-empty description="No pending invitations" />
      </div>

    </a-card>

    <!-- Activity Log Card -->
    <a-card class="activity-log-card" v-if="team">
      <template #title>
        <h2 class="card-title">Activity Log</h2>
      </template>
      <ActivityLog
        :activities="activities"
        :loading="false"
        :team-id="team.id"
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
  LinkOutlined,
} from '@ant-design/icons-vue';

const page = usePage();

const team = computed(() => page.props.team || null);
const activities = computed(() => page.props.activities || { data: [], current_page: 1, per_page: 15, total: 0 });

const breadcrumbItems = ref([
  { label: 'Dashboard', href: '/dashboard' },
  { label: 'Teams', href: '/dashboard/teams' },
  { label: team.value?.name || 'Team' },
]);

const memberColumns = [
  {
    title: 'User',
    key: 'user',
    dataIndex: 'user',
  },
  {
    title: 'Role',
    key: 'role',
    dataIndex: 'role',
    align: 'center',
  },
  {
    title: 'Joined At',
    key: 'joined_at',
    dataIndex: 'joined_at',
  },
];

const invitationColumns = [
  {
    title: 'Email',
    key: 'email',
    dataIndex: 'email',
  },
  {
    title: 'Role',
    key: 'role',
    dataIndex: 'role',
    align: 'center',
  },
  {
    title: 'Status',
    key: 'status',
    dataIndex: 'status',
    align: 'center',
  },
  {
    title: 'Expires At',
    key: 'expires_at',
    dataIndex: 'expires_at',
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

const getRoleColor = (role) => {
  const roleColors = {
    owner: 'red',
    admin: 'orange',
    manager: 'blue',
    member: 'default',
  };
  return roleColors[role] || 'default';
};

const getInvitationStatusColor = (status) => {
  const statusColors = {
    pending: 'orange',
    accepted: 'green',
    rejected: 'red',
    expired: 'default',
  };
  return statusColors[status] || 'default';
};

const handleBack = () => {
  router.visit('/dashboard/teams');
};

const handleEdit = () => {
  if (team.value) {
    router.visit(`/dashboard/teams/${team.value.id}/edit`);
  }
};
</script>

<style scoped>
.team-show-card,
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

.team-info-section {
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

/* Lighten borders in dark mode for descriptions */
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

