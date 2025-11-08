<template>
  <FrontendLayout>
    <!-- Page Header -->
    <section class="page-header">
      <div class="header-container">
        <h1 class="page-title">Browse Events</h1>
        <p class="page-description">
          Discover amazing events happening near you
        </p>
      </div>
    </section>

    <!-- Filters & Search -->
    <section class="filters-section">
      <div class="section-container">
        <div class="filters-bar">
          <a-input-search
            v-model:value="filters.search"
            placeholder="Search events..."
            class="search-filter"
            @search="handleSearch"
            allow-clear
            enter-button
          />
          <a-select
            v-model:value="filters.category"
            placeholder="All Categories"
            class="category-filter"
            allow-clear
            @change="handleFilterChange"
          >
            <a-select-option value="">All Categories</a-select-option>
            <a-select-option
              v-for="category in categories"
              :key="category.id"
              :value="category.slug"
            >
              {{ category.name }}
            </a-select-option>
          </a-select>
          <a-select
            v-model:value="filters.event_type"
            placeholder="Event Type"
            class="type-filter"
            allow-clear
            @change="handleFilterChange"
          >
            <a-select-option value="">All Types</a-select-option>
            <a-select-option value="online">Online</a-select-option>
            <a-select-option value="offline">Offline</a-select-option>
            <a-select-option value="hybrid">Hybrid</a-select-option>
          </a-select>
          <a-select
            v-model:value="filters.sort"
            placeholder="Sort By"
            class="sort-filter"
            @change="handleFilterChange"
          >
            <a-select-option value="date_asc">Date: Earliest First</a-select-option>
            <a-select-option value="date_desc">Date: Latest First</a-select-option>
            <a-select-option value="price_asc">Price: Low to High</a-select-option>
            <a-select-option value="price_desc">Price: High to Low</a-select-option>
            <a-select-option value="popular">Most Popular</a-select-option>
          </a-select>
          <a-button
            type="default"
            @click="toggleViewMode"
            class="view-toggle"
          >
            <AppstoreOutlined v-if="viewMode === 'list'" />
            <UnorderedListOutlined v-else />
          </a-button>
        </div>
      </div>
    </section>

    <!-- Events Grid/List -->
    <section class="events-section">
      <div class="section-container">
        <!-- Loading State -->
        <div v-if="loading" class="loading-state">
          <a-spin size="large" />
          <p>Loading events...</p>
        </div>

        <!-- Events Display -->
        <div v-else>
          <!-- Grid View -->
          <div v-if="viewMode === 'grid'" class="events-grid">
            <div
              v-for="event in events"
              :key="event.id"
              class="event-card"
              @click="handleEventClick(event.slug)"
            >
              <div class="event-image">
                <img
                  v-if="event.featured_image"
                  :src="event.featured_image"
                  :alt="event.title"
                />
                <div v-else class="event-image-placeholder">
                  <CalendarOutlined />
                </div>
                <div class="event-badge" v-if="event.is_featured">
                  <StarFilled /> Featured
                </div>
                <div class="event-type-badge" :class="event.event_type">
                  {{ event.event_type }}
                </div>
              </div>
              <div class="event-content">
                <div class="event-category">{{ event.category }}</div>
                <h3 class="event-title">{{ event.title }}</h3>
                <p class="event-description">{{ event.short_description }}</p>
                <div class="event-meta">
                  <div class="meta-item">
                    <CalendarOutlined />
                    <span>{{ formatDate(event.start_date) }}</span>
                  </div>
                  <div class="meta-item" v-if="event.event_type !== 'online'">
                    <EnvironmentOutlined />
                    <span>{{ event.venue_city || 'TBA' }}</span>
                  </div>
                  <div class="meta-item" v-else>
                    <VideoCameraOutlined />
                    <span>Online</span>
                  </div>
                </div>
                <div class="event-footer">
                  <div class="event-price">
                    <span v-if="event.price === 0" class="price-free">Free</span>
                    <span v-else class="price-paid">${{ event.price }}</span>
                  </div>
                  <a-button type="primary" size="small">
                    View Details
                  </a-button>
                </div>
              </div>
            </div>
          </div>

          <!-- List View -->
          <div v-else class="events-list">
            <div
              v-for="event in events"
              :key="event.id"
              class="event-list-item"
              @click="handleEventClick(event.slug)"
            >
              <div class="event-list-image">
                <img
                  v-if="event.featured_image"
                  :src="event.featured_image"
                  :alt="event.title"
                />
                <div v-else class="event-image-placeholder">
                  <CalendarOutlined />
                </div>
              </div>
              <div class="event-list-content">
                <div class="event-list-header">
                  <div class="event-category-small">{{ event.category }}</div>
                  <div class="event-type-badge-small" :class="event.event_type">
                    {{ event.event_type }}
                  </div>
                  <div class="event-badge-small" v-if="event.is_featured">
                    <StarFilled /> Featured
                  </div>
                </div>
                <h3 class="event-list-title">{{ event.title }}</h3>
                <p class="event-list-description">{{ event.short_description }}</p>
                <div class="event-list-meta">
                  <div class="meta-item">
                    <CalendarOutlined />
                    <span>{{ formatDate(event.start_date) }}</span>
                  </div>
                  <div class="meta-item">
                    <ClockCircleOutlined />
                    <span>{{ formatTime(event.start_date) }}</span>
                  </div>
                  <div class="meta-item" v-if="event.event_type !== 'online'">
                    <EnvironmentOutlined />
                    <span>{{ event.venue_city || 'TBA' }}</span>
                  </div>
                  <div class="meta-item" v-else>
                    <VideoCameraOutlined />
                    <span>Online</span>
                  </div>
                </div>
              </div>
              <div class="event-list-action">
                <div class="event-price">
                  <span v-if="event.price === 0" class="price-free">Free</span>
                  <span v-else class="price-paid">${{ event.price }}</span>
                </div>
                <a-button type="primary">Book Now</a-button>
              </div>
            </div>
          </div>

          <!-- Empty State -->
          <div v-if="events.length === 0" class="empty-state">
            <CalendarOutlined class="empty-icon" />
            <h3>No events found</h3>
            <p>Try adjusting your filters or search terms</p>
            <a-button type="primary" @click="clearFilters">
              Clear Filters
            </a-button>
          </div>

          <!-- Pagination -->
          <div v-if="events.length > 0" class="pagination-wrapper">
            <a-pagination
              v-model:current="currentPage"
              :total="totalEvents"
              :page-size="pageSize"
              show-size-changer
              :page-size-options="['12', '24', '48', '96']"
              show-total
              @change="handlePageChange"
              @show-size-change="handlePageSizeChange"
            />
          </div>
        </div>
      </div>
    </section>
  </FrontendLayout>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import FrontendLayout from '../../Layouts/FrontendLayout.vue';
import {
  CalendarOutlined,
  StarFilled,
  EnvironmentOutlined,
  VideoCameraOutlined,
  ClockCircleOutlined,
  AppstoreOutlined,
  UnorderedListOutlined,
} from '@ant-design/icons-vue';

const page = usePage();

const loading = ref(false);
const viewMode = ref('grid'); // 'grid' or 'list'
const currentPage = ref(1);
const pageSize = ref(12);
const totalEvents = ref(0);

const filters = reactive({
  search: '',
  category: '',
  event_type: '',
  sort: 'date_asc',
});

const categories = ref([
  { id: 1, name: 'Conference', slug: 'conference' },
  { id: 2, name: 'Workshop', slug: 'workshop' },
  { id: 3, name: 'Concert', slug: 'concert' },
  { id: 4, name: 'Sports', slug: 'sports' },
  { id: 5, name: 'Festival', slug: 'festival' },
  { id: 6, name: 'Networking', slug: 'networking' },
  { id: 7, name: 'Webinar', slug: 'webinar' },
  { id: 8, name: 'Exhibition', slug: 'exhibition' },
]);

// Mock events data - will be replaced with API call
const events = ref([
  {
    id: 1,
    title: 'Tech Conference 2024',
    slug: 'tech-conference-2024',
    short_description: 'Join industry leaders for the biggest tech event of the year',
    category: 'Conference',
    event_type: 'hybrid',
    start_date: '2024-03-15T10:00:00',
    venue_city: 'San Francisco',
    price: 299,
    is_featured: true,
    featured_image: null,
  },
  {
    id: 2,
    title: 'Web Development Masterclass',
    slug: 'web-development-masterclass',
    short_description: 'Learn modern web development from experts',
    category: 'Workshop',
    event_type: 'online',
    start_date: '2024-03-20T14:00:00',
    venue_city: null,
    price: 99,
    is_featured: false,
    featured_image: null,
  },
  {
    id: 3,
    title: 'Summer Music Festival',
    slug: 'summer-music-festival',
    short_description: 'Three days of amazing music and entertainment',
    category: 'Festival',
    event_type: 'offline',
    start_date: '2024-06-01T12:00:00',
    venue_city: 'New York',
    price: 149,
    is_featured: true,
    featured_image: null,
  },
  {
    id: 4,
    title: 'Design Summit 2024',
    slug: 'design-summit-2024',
    short_description: 'Explore the future of design and creativity',
    category: 'Conference',
    event_type: 'offline',
    start_date: '2024-04-10T09:00:00',
    venue_city: 'Los Angeles',
    price: 199,
    is_featured: false,
    featured_image: null,
  },
  {
    id: 5,
    title: 'AI & Machine Learning Workshop',
    slug: 'ai-ml-workshop',
    short_description: 'Hands-on workshop on AI and ML technologies',
    category: 'Workshop',
    event_type: 'online',
    start_date: '2024-03-25T15:00:00',
    venue_city: null,
    price: 79,
    is_featured: false,
    featured_image: null,
  },
  {
    id: 6,
    title: 'Startup Networking Night',
    slug: 'startup-networking-night',
    short_description: 'Connect with entrepreneurs and investors',
    category: 'Networking',
    event_type: 'offline',
    start_date: '2024-03-18T18:00:00',
    venue_city: 'Boston',
    price: 0,
    is_featured: false,
    featured_image: null,
  },
]);

onMounted(() => {
  // Initialize filters from URL params
  const urlParams = new URLSearchParams(window.location.search);
  filters.search = urlParams.get('search') || '';
  filters.category = urlParams.get('category') || '';
  filters.event_type = urlParams.get('event_type') || '';
  
  // Load events
  loadEvents();
});

const loadEvents = async () => {
  loading.value = true;
  // TODO: Replace with actual API call
  // const response = await fetch(`/api/events?${buildQueryString()}`);
  // const data = await response.json();
  // events.value = data.events;
  // totalEvents.value = data.total;
  
  setTimeout(() => {
    loading.value = false;
    totalEvents.value = events.value.length;
  }, 500);
};

const buildQueryString = () => {
  const params = new URLSearchParams();
  if (filters.search) params.append('search', filters.search);
  if (filters.category) params.append('category', filters.category);
  if (filters.event_type) params.append('event_type', filters.event_type);
  if (filters.sort) params.append('sort', filters.sort);
  params.append('page', currentPage.value);
  params.append('per_page', pageSize.value);
  return params.toString();
};

const handleSearch = (value) => {
  filters.search = value;
  currentPage.value = 1;
  handleFilterChange();
};

const handleFilterChange = () => {
  currentPage.value = 1;
  loadEvents();
  // Update URL without page reload
  const queryString = buildQueryString();
  router.visit(`/events?${queryString}`, { preserveState: true, replace: true });
};

const toggleViewMode = () => {
  viewMode.value = viewMode.value === 'grid' ? 'list' : 'grid';
};

const handleEventClick = (slug) => {
  router.visit(`/events/${slug}`);
};

const clearFilters = () => {
  filters.search = '';
  filters.category = '';
  filters.event_type = '';
  filters.sort = 'date_asc';
  currentPage.value = 1;
  handleFilterChange();
};

const handlePageChange = (page) => {
  currentPage.value = page;
  loadEvents();
};

const handlePageSizeChange = (current, size) => {
  currentPage.value = 1;
  pageSize.value = size;
  loadEvents();
};

const formatDate = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  });
};

const formatTime = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleTimeString('en-US', {
    hour: 'numeric',
    minute: '2-digit',
  });
};
</script>

<style scoped>
/* Page Header */
.page-header {
  background: linear-gradient(135deg, var(--color-primary, #1890ff) 0%, var(--color-primary-400, #40a9ff) 100%);
  padding: 64px 24px;
  color: white;
  text-align: center;
}

.header-container {
  max-width: 1400px;
  margin: 0 auto;
}

.page-title {
  font-size: 48px;
  font-weight: 700;
  margin: 0 0 16px;
}

.page-description {
  font-size: 18px;
  opacity: 0.95;
  margin: 0;
}

/* Filters Section */
.filters-section {
  background: var(--card-bg, #fff);
  border-bottom: 1px solid var(--card-border, #f0f0f0);
  padding: 24px;
  position: sticky;
  top: 72px;
  z-index: 100;
  transition: background-color var(--transition-theme), border-color var(--transition-theme);
}

.section-container {
  max-width: 1400px;
  margin: 0 auto;
}

.filters-bar {
  display: flex;
  gap: 16px;
  align-items: center;
  flex-wrap: wrap;
}

.search-filter {
  flex: 1;
  min-width: 250px;
}

.category-filter,
.type-filter,
.sort-filter {
  min-width: 150px;
}

.view-toggle {
  flex-shrink: 0;
}

/* Events Section */
.events-section {
  padding: 48px 24px;
  background: var(--bg-primary, #f0f2f5);
  min-height: 60vh;
  transition: background-color var(--transition-theme);
}

.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 80px 24px;
  gap: 16px;
  color: var(--text-secondary, #8c8c8c);
}

/* Events Grid */
.events-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 24px;
  margin-bottom: 32px;
}

.event-card {
  background: var(--card-bg, #fff);
  border: 1px solid var(--card-border, #f0f0f0);
  border-radius: var(--radius-lg, 16px);
  overflow: hidden;
  cursor: pointer;
  transition: all var(--transition-base);
  transition: background-color var(--transition-theme), border-color var(--transition-theme), transform var(--transition-base), box-shadow var(--transition-base);
}

.event-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
}

.event-image {
  position: relative;
  width: 100%;
  aspect-ratio: 16/9;
  overflow: hidden;
  background: var(--bg-elevated, #fafafa);
}

.event-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.event-image-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--text-tertiary, #bfbfbf);
  font-size: 48px;
}

.event-badge {
  position: absolute;
  top: 12px;
  left: 12px;
  background: rgba(255, 255, 255, 0.95);
  color: var(--color-warning, #faad14);
  padding: 4px 12px;
  border-radius: var(--radius-full, 9999px);
  font-size: 12px;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 4px;
}

.event-type-badge {
  position: absolute;
  top: 12px;
  right: 12px;
  background: rgba(0, 0, 0, 0.7);
  color: white;
  padding: 4px 12px;
  border-radius: var(--radius-full, 9999px);
  font-size: 12px;
  font-weight: 600;
  text-transform: capitalize;
}

.event-content {
  padding: 20px;
}

.event-category {
  font-size: 12px;
  color: var(--color-primary, #1890ff);
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 8px;
}

.event-title {
  font-size: 20px;
  font-weight: 600;
  color: var(--text-primary, #262626);
  margin: 0 0 8px;
  transition: color var(--transition-theme);
}

.event-description {
  font-size: 14px;
  color: var(--text-secondary, #8c8c8c);
  line-height: 1.5;
  margin: 0 0 16px;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  transition: color var(--transition-theme);
}

.event-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
  margin-bottom: 16px;
  font-size: 14px;
  color: var(--text-secondary, #8c8c8c);
}

.meta-item {
  display: flex;
  align-items: center;
  gap: 6px;
  transition: color var(--transition-theme);
}

.event-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 16px;
  border-top: 1px solid var(--card-border, #f0f0f0);
  transition: border-color var(--transition-theme);
}

.event-price {
  font-size: 18px;
  font-weight: 600;
}

.price-free {
  color: var(--color-success, #52c41a);
}

.price-paid {
  color: var(--text-primary, #262626);
  transition: color var(--transition-theme);
}

/* Events List */
.events-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
  margin-bottom: 32px;
}

.event-list-item {
  background: var(--card-bg, #fff);
  border: 1px solid var(--card-border, #f0f0f0);
  border-radius: var(--radius-lg, 16px);
  padding: 20px;
  display: grid;
  grid-template-columns: 200px 1fr auto;
  gap: 24px;
  cursor: pointer;
  transition: all var(--transition-base);
  transition: background-color var(--transition-theme), border-color var(--transition-theme), transform var(--transition-base), box-shadow var(--transition-base);
}

.event-list-item:hover {
  transform: translateX(4px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.event-list-image {
  width: 200px;
  aspect-ratio: 16/9;
  border-radius: var(--radius-base, 8px);
  overflow: hidden;
  background: var(--bg-elevated, #fafafa);
}

.event-list-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.event-list-content {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.event-list-header {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 4px;
  flex-wrap: wrap;
}

.event-category-small {
  font-size: 11px;
  color: var(--color-primary, #1890ff);
  font-weight: 600;
  text-transform: uppercase;
}

.event-type-badge-small {
  font-size: 11px;
  padding: 2px 8px;
  border-radius: var(--radius-sm, 4px);
  background: var(--bg-elevated, #fafafa);
  color: var(--text-secondary, #8c8c8c);
  text-transform: capitalize;
}

.event-badge-small {
  font-size: 11px;
  color: var(--color-warning, #faad14);
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 4px;
}

.event-list-title {
  font-size: 20px;
  font-weight: 600;
  color: var(--text-primary, #262626);
  margin: 0;
  transition: color var(--transition-theme);
}

.event-list-description {
  font-size: 14px;
  color: var(--text-secondary, #8c8c8c);
  line-height: 1.5;
  margin: 0;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  transition: color var(--transition-theme);
}

.event-list-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
  margin-top: 8px;
  font-size: 13px;
  color: var(--text-secondary, #8c8c8c);
}

.event-list-action {
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  align-items: flex-end;
  gap: 12px;
  min-width: 120px;
}

/* Empty State */
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 80px 24px;
  text-align: center;
}

.empty-icon {
  font-size: 64px;
  color: var(--text-tertiary, #bfbfbf);
  margin-bottom: 16px;
}

.empty-state h3 {
  font-size: 24px;
  color: var(--text-primary, #262626);
  margin: 0 0 8px;
}

.empty-state p {
  font-size: 16px;
  color: var(--text-secondary, #8c8c8c);
  margin: 0 0 24px;
}

/* Pagination */
.pagination-wrapper {
  display: flex;
  justify-content: center;
  margin-top: 32px;
}

/* Dark Theme Styles */
[data-theme="dark"] .page-header {
  background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
}

[data-theme="dark"] .event-badge {
  background: rgba(30, 30, 46, 0.95);
  color: #fadb14;
  border: 1px solid rgba(250, 219, 20, 0.3);
}

[data-theme="dark"] .event-badge-small {
  background: rgba(30, 30, 46, 0.95);
  color: #fadb14;
  border: 1px solid rgba(250, 219, 20, 0.3);
  padding: 2px 8px;
  border-radius: var(--radius-sm, 4px);
}

[data-theme="dark"] .event-type-badge-small {
  background: rgba(40, 40, 60, 0.8);
  color: rgba(255, 255, 255, 0.9);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

/* Dark theme for Ant Design components */
[data-theme="dark"] .search-filter :deep(.ant-input),
[data-theme="dark"] .search-filter :deep(.ant-input-search-button) {
  background: var(--card-bg, #1f1f1f) !important;
  border-color: var(--card-border, #2d2d2d) !important;
  color: var(--text-primary, #ffffff) !important;
}

[data-theme="dark"] .search-filter :deep(.ant-input::placeholder) {
  color: var(--text-secondary, #8c8c8c) !important;
}

[data-theme="dark"] .search-filter :deep(.ant-input-search-button) {
  background: var(--color-primary, #1890ff) !important;
  border-color: var(--color-primary, #1890ff) !important;
}

[data-theme="dark"] .category-filter :deep(.ant-select-selector),
[data-theme="dark"] .type-filter :deep(.ant-select-selector),
[data-theme="dark"] .sort-filter :deep(.ant-select-selector) {
  background: var(--card-bg, #1f1f1f) !important;
  border-color: var(--card-border, #2d2d2d) !important;
  color: var(--text-primary, #ffffff) !important;
}

[data-theme="dark"] .category-filter :deep(.ant-select-selection-placeholder),
[data-theme="dark"] .type-filter :deep(.ant-select-selection-placeholder),
[data-theme="dark"] .sort-filter :deep(.ant-select-selection-placeholder) {
  color: var(--text-secondary, #8c8c8c) !important;
}

[data-theme="dark"] .category-filter :deep(.ant-select-arrow),
[data-theme="dark"] .type-filter :deep(.ant-select-arrow),
[data-theme="dark"] .sort-filter :deep(.ant-select-arrow) {
  color: var(--text-secondary, #8c8c8c) !important;
}

/* Dark theme for pagination */
[data-theme="dark"] .pagination-wrapper :deep(.ant-pagination-item) {
  background: var(--card-bg, #1f1f1f) !important;
  border-color: var(--card-border, #2d2d2d) !important;
}

[data-theme="dark"] .pagination-wrapper :deep(.ant-pagination-item a) {
  color: var(--text-primary, #ffffff) !important;
}

[data-theme="dark"] .pagination-wrapper :deep(.ant-pagination-item-active) {
  background: var(--color-primary, #1890ff) !important;
  border-color: var(--color-primary, #1890ff) !important;
}

[data-theme="dark"] .pagination-wrapper :deep(.ant-pagination-item-active a) {
  color: #ffffff !important;
}

[data-theme="dark"] .pagination-wrapper :deep(.ant-pagination-prev),
[data-theme="dark"] .pagination-wrapper :deep(.ant-pagination-next) {
  background: var(--card-bg, #1f1f1f) !important;
  border-color: var(--card-border, #2d2d2d) !important;
}

[data-theme="dark"] .pagination-wrapper :deep(.ant-pagination-prev .ant-pagination-item-link),
[data-theme="dark"] .pagination-wrapper :deep(.ant-pagination-next .ant-pagination-item-link) {
  background: transparent !important;
  border-color: var(--card-border, #2d2d2d) !important;
  color: var(--text-primary, #ffffff) !important;
}

[data-theme="dark"] .pagination-wrapper :deep(.ant-pagination-prev:hover .ant-pagination-item-link),
[data-theme="dark"] .pagination-wrapper :deep(.ant-pagination-next:hover .ant-pagination-item-link) {
  border-color: var(--color-primary, #1890ff) !important;
  color: var(--color-primary, #1890ff) !important;
}

[data-theme="dark"] .pagination-wrapper :deep(.ant-pagination-prev.ant-pagination-disabled .ant-pagination-item-link),
[data-theme="dark"] .pagination-wrapper :deep(.ant-pagination-next.ant-pagination-disabled .ant-pagination-item-link) {
  background: transparent !important;
  border-color: var(--card-border, #2d2d2d) !important;
  color: var(--text-disabled, #595959) !important;
  cursor: not-allowed;
  opacity: 0.5;
}

[data-theme="dark"] .pagination-wrapper :deep(.ant-pagination-options) {
  color: var(--text-primary, #ffffff) !important;
}

[data-theme="dark"] .pagination-wrapper :deep(.ant-select-selector) {
  background: var(--card-bg, #1f1f1f) !important;
  border-color: var(--card-border, #2d2d2d) !important;
  color: var(--text-primary, #ffffff) !important;
}

[data-theme="dark"] .pagination-wrapper :deep(.ant-select-selection-item) {
  color: var(--text-primary, #ffffff) !important;
}

[data-theme="dark"] .pagination-wrapper :deep(.ant-select-selection-placeholder) {
  color: var(--text-secondary, #8c8c8c) !important;
}

[data-theme="dark"] .pagination-wrapper :deep(.ant-select-arrow) {
  color: var(--text-secondary, #8c8c8c) !important;
}

[data-theme="dark"] .pagination-wrapper :deep(.ant-select:hover .ant-select-selector) {
  border-color: var(--color-primary, #1890ff) !important;
}

[data-theme="dark"] .pagination-wrapper :deep(.ant-select-focused .ant-select-selector) {
  border-color: var(--color-primary, #1890ff) !important;
  box-shadow: 0 0 0 2px rgba(24, 144, 255, 0.2) !important;
}

[data-theme="dark"] .pagination-wrapper :deep(.ant-pagination-total-text) {
  color: var(--text-secondary, #8c8c8c) !important;
}

/* Dark theme for select dropdown options */
[data-theme="dark"] :deep(.ant-select-dropdown) {
  background: var(--card-bg, #1f1f1f) !important;
  border-color: var(--card-border, #2d2d2d) !important;
  box-shadow: 0 6px 16px 0 rgba(0, 0, 0, 0.48), 0 3px 6px -4px rgba(0, 0, 0, 0.32), 0 9px 28px 8px rgba(0, 0, 0, 0.2) !important;
}

[data-theme="dark"] :deep(.ant-select-item) {
  color: var(--text-primary, #ffffff) !important;
  background: transparent !important;
}

[data-theme="dark"] :deep(.ant-select-item:hover),
[data-theme="dark"] :deep(.ant-select-item-option-active) {
  background: rgba(255, 255, 255, 0.08) !important;
  color: var(--text-primary, #ffffff) !important;
}

/* Remove any white background from selected items */
[data-theme="dark"] :deep(.ant-select-item-option-selected) {
  background-color: rgba(24, 144, 255, 0.2) !important;
  background-image: none !important;
  background: rgba(24, 144, 255, 0.2) !important;
  color: var(--color-primary, #1890ff) !important;
  font-weight: 600 !important;
}

[data-theme="dark"] :deep(.ant-select-item-option-selected:hover) {
  background: rgba(24, 144, 255, 0.3) !important;
  background-color: rgba(24, 144, 255, 0.3) !important;
}

[data-theme="dark"] :deep(.ant-select-item-option-selected::after) {
  display: none !important;
}

[data-theme="dark"] :deep(.ant-select-item-option-selected .ant-select-item-option-content) {
  background: transparent !important;
  color: var(--color-primary, #1890ff) !important;
}

/* Override any white background that might come from Ant Design default styles */
[data-theme="dark"] :deep(.ant-select-item-option-selected) {
  background: rgba(24, 144, 255, 0.2) !important;
  background-color: rgba(24, 144, 255, 0.2) !important;
}

/* Specific styling for pagination dropdown */
[data-theme="dark"] .pagination-wrapper :deep(.ant-select-dropdown) {
  background: var(--card-bg, #1f1f1f) !important;
  border: 1px solid var(--card-border, #2d2d2d) !important;
}

[data-theme="dark"] .pagination-wrapper :deep(.ant-select-item) {
  color: var(--text-primary, #ffffff) !important;
  background: transparent !important;
}

[data-theme="dark"] .pagination-wrapper :deep(.ant-select-item:hover),
[data-theme="dark"] .pagination-wrapper :deep(.ant-select-item-option-active) {
  background: rgba(255, 255, 255, 0.08) !important;
  color: var(--text-primary, #ffffff) !important;
}

[data-theme="dark"] .pagination-wrapper :deep(.ant-select-item-option-selected) {
  background: rgba(24, 144, 255, 0.2) !important;
  color: var(--color-primary, #1890ff) !important;
  font-weight: 600 !important;
}

[data-theme="dark"] .pagination-wrapper :deep(.ant-select-item-option-selected:hover) {
  background: rgba(24, 144, 255, 0.3) !important;
}

[data-theme="dark"] .pagination-wrapper :deep(.ant-select-item-option-selected::after) {
  display: none !important;
}

/* Dark theme for view toggle button */
[data-theme="dark"] .view-toggle {
  background: var(--card-bg, #1f1f1f) !important;
  border-color: var(--card-border, #2d2d2d) !important;
  color: var(--text-primary, #ffffff) !important;
}

[data-theme="dark"] .view-toggle:hover {
  background: rgba(255, 255, 255, 0.08) !important;
  border-color: var(--color-primary, #1890ff) !important;
  color: var(--color-primary, #1890ff) !important;
}

/* Mobile Responsive */
@media (max-width: 1024px) {
  .events-grid {
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  }

  .event-list-item {
    grid-template-columns: 150px 1fr;
  }

  .event-list-action {
    grid-column: 1 / -1;
    flex-direction: row;
    justify-content: space-between;
    align-items: center;
  }
}

@media (max-width: 768px) {
  .page-header {
    padding: 48px 16px;
  }

  .page-title {
    font-size: 32px;
  }

  .filters-section {
    padding: 16px;
    top: 64px;
  }

  .filters-bar {
    flex-direction: column;
  }

  .search-filter,
  .category-filter,
  .type-filter,
  .sort-filter {
    width: 100%;
  }

  .events-section {
    padding: 32px 16px;
  }

  .events-grid {
    grid-template-columns: 1fr;
  }

  .event-list-item {
    grid-template-columns: 1fr;
  }

  .event-list-image {
    width: 100%;
  }
}
</style>

