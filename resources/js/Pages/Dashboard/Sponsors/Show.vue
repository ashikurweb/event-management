<template>
  <DashboardLayout>
    <Breadcrumb :items="breadcrumbItems" />

    <a-card class="sponsor-show-card" v-if="sponsor">
      <template #title>
        <div class="card-header">
          <h2 class="card-title">Sponsor Details</h2>
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

      <!-- Sponsor Information -->
      <div class="sponsor-info-section">
        <a-row :gutter="24">
          <a-col :xs="24" :md="8">
            <div class="logo-section">
              <a-avatar
                v-if="sponsor.logo_url"
                :src="sponsor.logo_url"
                :size="200"
                shape="square"
                class="sponsor-logo"
              />
              <a-avatar v-else :size="200" shape="square" class="sponsor-logo">
                <template #icon><StarOutlined /></template>
              </a-avatar>
            </div>
          </a-col>
          <a-col :xs="24" :md="16">
            <a-descriptions :column="1" bordered>
              <a-descriptions-item label="Name">
                <span class="info-value">{{ sponsor.name }}</span>
              </a-descriptions-item>
              
              <a-descriptions-item label="Tier">
                <a-tag :color="getTierColor(sponsor.tier)" style="font-size: 14px; padding: 4px 12px;">
                  {{ sponsor.tier ? sponsor.tier.toUpperCase() : '' }}
                </a-tag>
              </a-descriptions-item>
              
              <a-descriptions-item label="Display Order">
                <span class="info-value">{{ sponsor.display_order || 0 }}</span>
              </a-descriptions-item>
              
              <a-descriptions-item label="Status">
                <a-tag :color="sponsor.is_active ? 'green' : 'red'">
                  {{ sponsor.is_active ? 'Active' : 'Inactive' }}
                </a-tag>
              </a-descriptions-item>
              
              <a-descriptions-item label="Website" v-if="sponsor.website">
                <span class="info-value">
                  <a :href="sponsor.website" target="_blank" rel="noopener noreferrer">
                    {{ sponsor.website }}
                  </a>
                </span>
              </a-descriptions-item>
            </a-descriptions>
          </a-col>
        </a-row>
      </div>

      <!-- Description Section -->
      <div class="section-divider"></div>
      <div class="description-section">
        <h3 class="section-title">Description</h3>
        <p class="description-text" v-if="sponsor.description">{{ sponsor.description }}</p>
        <a-empty description="No description available" v-else />
      </div>

      <!-- Events Section -->
      <div class="section-divider" v-if="sponsor.events && sponsor.events.length > 0"></div>
      <div class="events-section" v-if="sponsor.events && sponsor.events.length > 0">
        <h3 class="section-title">Associated Events</h3>
        <a-list
          :data-source="sponsor.events"
          :pagination="false"
        >
          <template #renderItem="{ item }">
            <a-list-item>
              <a-list-item-meta>
                <template #title>
                  <a :href="`/events/${item.id}`" target="_blank">{{ item.title }}</a>
                </template>
                <template #description>
                  <span v-if="item.pivot.tier">Tier: {{ item.pivot.tier.toUpperCase() }}</span>
                  <span v-if="item.pivot.contribution_amount"> | Contribution: ${{ item.pivot.contribution_amount }}</span>
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
  StarOutlined,
} from '@ant-design/icons-vue';
import dayjs from 'dayjs';

const page = usePage();
const loading = ref(false);

const sponsor = computed(() => page.props.sponsor || {});
const activities = computed(() => page.props.activities || { data: [] });

const breadcrumbItems = ref([
  { label: 'Dashboard', href: '/dashboard' },
  { label: 'Sponsors', href: '/dashboard/sponsors' },
  { label: 'Details' },
]);

const getTierColor = (tier) => {
  const colors = {
    platinum: 'purple',
    gold: 'gold',
    silver: 'default',
    bronze: 'orange',
    partner: 'blue',
  };
  return colors[tier] || 'default';
};

const formatDate = (date) => {
  return dayjs(date).format('YYYY-MM-DD HH:mm:ss');
};

const handleEdit = () => {
  router.visit(`/dashboard/sponsors/${sponsor.value.id}/edit`);
};

const handleBack = () => {
  router.visit('/dashboard/sponsors');
};

const handleActivityPageChange = ({ current, pageSize }) => {
  router.visit(`/dashboard/sponsors/${sponsor.value.id}/activities`, {
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
.sponsor-show-card {
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

.sponsor-info-section {
  margin-bottom: 24px;
}

.logo-section {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.sponsor-logo {
  border: 2px solid var(--border-color, #f0f0f0);
  border-radius: 8px;
}

[data-theme="dark"] .sponsor-logo {
  border-color: var(--border-color, #434343);
}

.info-value {
  font-weight: 500;
  color: var(--text-primary, #262626);
}

[data-theme="dark"] .info-value {
  color: rgba(255, 255, 255, 0.85);
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
.events-section,
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

@media (max-width: 768px) {
  .card-header {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>

