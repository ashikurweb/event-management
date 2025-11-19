<template>
  <DashboardLayout>
    <Breadcrumb :items="breadcrumbItems" />

    <a-card class="venue-show-card" v-if="venue">
      <template #title>
        <div class="card-header">
          <h2 class="card-title">Venue Details</h2>
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

      <!-- Venue Information -->
      <div class="venue-info-section">
        <a-row :gutter="24">
          <a-col :xs="24" :md="8">
            <div class="image-section">
              <div v-if="venue.image_urls && venue.image_urls.length > 0" class="image-gallery">
                <a-image
                  :src="venue.image_urls[0]"
                  :width="300"
                  :preview="true"
                  class="main-image"
                />
                <div v-if="venue.image_urls.length > 1" class="thumbnail-grid">
                  <a-image
                    v-for="(image, index) in venue.image_urls.slice(1, 5)"
                    :key="index"
                    :src="image"
                    :width="60"
                    :preview="true"
                    class="thumbnail"
                  />
                  <div v-if="venue.image_urls.length > 5" class="more-images">
                    +{{ venue.image_urls.length - 5 }} more
                  </div>
                </div>
              </div>
              <a-empty v-else description="No images available" />
            </div>
          </a-col>
          <a-col :xs="24" :md="16">
            <a-descriptions :column="1" bordered>
              <a-descriptions-item label="Name">
                <span class="info-value">{{ venue.name }}</span>
              </a-descriptions-item>
              
              <a-descriptions-item label="Slug">
                <span class="info-value">{{ venue.slug }}</span>
              </a-descriptions-item>
              
              <a-descriptions-item label="Address">
                <span class="info-value">{{ venue.address }}</span>
              </a-descriptions-item>
              
              <a-descriptions-item label="Location">
                <span class="info-value">
                  {{ venue.city }}{{ venue.state ? ', ' + venue.state : '' }}, {{ venue.country }}
                  <span v-if="venue.postal_code"> ({{ venue.postal_code }})</span>
                </span>
              </a-descriptions-item>
              
              <a-descriptions-item label="Coordinates" v-if="venue.latitude && venue.longitude">
                <span class="info-value">
                  {{ venue.latitude }}, {{ venue.longitude }}
                  <a
                    :href="`https://www.google.com/maps?q=${venue.latitude},${venue.longitude}`"
                    target="_blank"
                    rel="noopener noreferrer"
                    style="margin-left: 8px;"
                  >
                    View on Map
                  </a>
                </span>
              </a-descriptions-item>
              
              <a-descriptions-item label="Capacity">
                <span class="info-value" v-if="venue.capacity">{{ venue.capacity.toLocaleString() }}</span>
                <span class="text-muted" v-else>—</span>
              </a-descriptions-item>
              
              <a-descriptions-item label="Rating">
                <a-rate :value="venue.rating" disabled allow-half :count="5" />
                <span class="rating-text">({{ venue.rating }})</span>
              </a-descriptions-item>
              
              <a-descriptions-item label="Verified">
                <a-tag :color="venue.is_verified ? 'green' : 'default'">
                  {{ venue.is_verified ? 'Verified' : 'Not Verified' }}
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
        <p class="description-text" v-if="venue.description">{{ venue.description }}</p>
        <a-empty description="No description available" v-else />
      </div>

      <!-- Facilities Section -->
      <div class="section-divider" v-if="venue.facilities && venue.facilities.length > 0"></div>
      <div class="facilities-section" v-if="venue.facilities && venue.facilities.length > 0">
        <h3 class="section-title">Facilities</h3>
        <a-space wrap>
          <a-tag v-for="(facility, index) in venue.facilities" :key="index" color="blue">
            {{ facility }}
          </a-tag>
        </a-space>
      </div>

      <!-- Contact Information Section -->
      <div class="section-divider"></div>
      <div class="contact-section">
        <h3 class="section-title">Contact Information</h3>
        <a-descriptions :column="2" bordered>
          <a-descriptions-item label="Contact Name">
            <span class="info-value" v-if="venue.contact_name">{{ venue.contact_name }}</span>
            <span class="text-muted" v-else>—</span>
          </a-descriptions-item>
          
          <a-descriptions-item label="Contact Email">
            <span class="info-value" v-if="venue.contact_email">
              <a :href="`mailto:${venue.contact_email}`">{{ venue.contact_email }}</a>
            </span>
            <span class="text-muted" v-else>—</span>
          </a-descriptions-item>
          
          <a-descriptions-item label="Contact Phone">
            <span class="info-value" v-if="venue.contact_phone">
              <a :href="`tel:${venue.contact_phone}`">{{ venue.contact_phone }}</a>
            </span>
            <span class="text-muted" v-else>—</span>
          </a-descriptions-item>
          
          <a-descriptions-item label="Website">
            <span class="info-value" v-if="venue.website">
              <a :href="venue.website" target="_blank" rel="noopener noreferrer">
                {{ venue.website }}
              </a>
            </span>
            <span class="text-muted" v-else>—</span>
          </a-descriptions-item>
        </a-descriptions>
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

const venue = computed(() => page.props.venue || {});
const activities = computed(() => page.props.activities || { data: [] });

const breadcrumbItems = ref([
  { label: 'Dashboard', href: '/dashboard' },
  { label: 'Venues', href: '/dashboard/venues' },
  { label: 'Details' },
]);

const formatDate = (date) => {
  return dayjs(date).format('YYYY-MM-DD HH:mm:ss');
};

const handleEdit = () => {
  router.visit(`/dashboard/venues/${venue.value.id}/edit`);
};

const handleBack = () => {
  router.visit('/dashboard/venues');
};

const handleActivityPageChange = ({ current, pageSize }) => {
  router.visit(`/dashboard/venues/${venue.value.id}/activities`, {
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
.venue-show-card {
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

.venue-info-section {
  margin-bottom: 24px;
}

.image-section {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.image-gallery {
  width: 100%;
}

.main-image {
  width: 100%;
  max-width: 300px;
  border-radius: 8px;
  margin-bottom: 12px;
}

.thumbnail-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  justify-content: center;
}

.thumbnail {
  border-radius: 4px;
  cursor: pointer;
}

.more-images {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 60px;
  height: 60px;
  background: var(--bg-hover, #f5f5f5);
  border-radius: 4px;
  font-size: 12px;
  color: var(--text-secondary, #8c8c8c);
  cursor: pointer;
}

[data-theme="dark"] .more-images {
  background: rgba(255, 255, 255, 0.08);
  color: rgba(255, 255, 255, 0.65);
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
.facilities-section,
.contact-section,
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
  
  .thumbnail-grid {
    justify-content: flex-start;
  }
}
</style>

