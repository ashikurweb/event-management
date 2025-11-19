<template>
  <DashboardLayout>
    <Breadcrumb :items="breadcrumbItems" />

    <a-card class="vendor-show-card" v-if="vendor">
      <template #title>
        <div class="card-header">
          <h2 class="card-title">Vendor Details</h2>
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

      <!-- Vendor Information -->
      <div class="vendor-info-section">
        <a-row :gutter="24">
          <a-col :xs="24" :md="8">
            <div class="logo-section">
              <a-avatar
                v-if="vendor.logo_url"
                :src="vendor.logo_url"
                :size="200"
                shape="square"
                class="vendor-logo"
              />
              <a-avatar v-else :size="200" shape="square" class="vendor-logo">
                <template #icon><ShopOutlined /></template>
              </a-avatar>
            </div>
          </a-col>
          <a-col :xs="24" :md="16">
            <a-descriptions :column="1" bordered>
              <a-descriptions-item label="Name">
                <span class="info-value">{{ vendor.name }}</span>
              </a-descriptions-item>
              
              <a-descriptions-item label="Company">
                <span class="info-value" v-if="vendor.company">{{ vendor.company }}</span>
                <span class="text-muted" v-else>—</span>
              </a-descriptions-item>
              
              <a-descriptions-item label="Email">
                <span class="info-value" v-if="vendor.email">
                  <a :href="`mailto:${vendor.email}`">{{ vendor.email }}</a>
                </span>
                <span class="text-muted" v-else>—</span>
              </a-descriptions-item>
              
              <a-descriptions-item label="Phone">
                <span class="info-value" v-if="vendor.phone">
                  <a :href="`tel:${vendor.phone}`">{{ vendor.phone }}</a>
                </span>
                <span class="text-muted" v-else>—</span>
              </a-descriptions-item>
              
              <a-descriptions-item label="Website">
                <span class="info-value" v-if="vendor.website">
                  <a :href="vendor.website" target="_blank" rel="noopener noreferrer">
                    {{ vendor.website }}
                  </a>
                </span>
                <span class="text-muted" v-else>—</span>
              </a-descriptions-item>
              
              <a-descriptions-item label="Category">
                <span class="info-value" v-if="vendor.category">{{ vendor.category }}</span>
                <span class="text-muted" v-else>—</span>
              </a-descriptions-item>
              
              <a-descriptions-item label="Rating">
                <a-rate :value="vendor.rating" disabled allow-half :count="5" />
                <span class="rating-text">({{ vendor.rating }})</span>
              </a-descriptions-item>
              
              <a-descriptions-item label="Verified">
                <a-tag :color="vendor.is_verified ? 'green' : 'default'">
                  {{ vendor.is_verified ? 'Verified' : 'Not Verified' }}
                </a-tag>
              </a-descriptions-item>
            </a-descriptions>
          </a-col>
        </a-row>
      </div>

      <!-- Description Section -->
      <div class="section-divider"></div>
      <div class="description-section">
        <h3 class="section-title">Description</h3>
        <p class="description-text" v-if="vendor.description">{{ vendor.description }}</p>
        <a-empty description="No description available" v-else />
      </div>

      <!-- Events Section -->
      <div class="section-divider" v-if="vendor.events && vendor.events.length > 0"></div>
      <div class="events-section" v-if="vendor.events && vendor.events.length > 0">
        <h3 class="section-title">Associated Events</h3>
        <a-list
          :data-source="vendor.events"
          :pagination="false"
        >
          <template #renderItem="{ item }">
            <a-list-item>
              <a-list-item-meta>
                <template #title>
                  <a :href="`/events/${item.id}`" target="_blank">{{ item.title }}</a>
                </template>
                <template #description>
                  <span v-if="item.pivot.booth_number">Booth: {{ item.pivot.booth_number }}</span>
                  <span v-if="item.pivot.package_type"> | Package: {{ item.pivot.package_type }}</span>
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
  ShopOutlined,
} from '@ant-design/icons-vue';
import dayjs from 'dayjs';

const page = usePage();
const loading = ref(false);

const vendor = computed(() => page.props.vendor || {});
const activities = computed(() => page.props.activities || { data: [] });

const breadcrumbItems = ref([
  { label: 'Dashboard', href: '/dashboard' },
  { label: 'Vendors', href: '/dashboard/vendors' },
  { label: 'Details' },
]);

const formatDate = (date) => {
  return dayjs(date).format('YYYY-MM-DD HH:mm:ss');
};

const handleEdit = () => {
  router.visit(`/dashboard/vendors/${vendor.value.id}/edit`);
};

const handleBack = () => {
  router.visit('/dashboard/vendors');
};

const handleActivityPageChange = ({ current, pageSize }) => {
  router.visit(`/dashboard/vendors/${vendor.value.id}/activities`, {
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
.vendor-show-card {
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

.vendor-info-section {
  margin-bottom: 24px;
}

.logo-section {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.vendor-logo {
  border: 2px solid var(--border-color, #f0f0f0);
  border-radius: 8px;
}

[data-theme="dark"] .vendor-logo {
  border-color: var(--border-color, #434343);
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

.rating-text {
  margin-left: 8px;
  font-size: 14px;
  color: var(--text-secondary, #8c8c8c);
}

[data-theme="dark"] .rating-text {
  color: rgba(255, 255, 255, 0.65);
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

