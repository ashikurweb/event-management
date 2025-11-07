<template>
  <section class="py-20 bg-white" :class="{ 'bg-[#1f1f1f]': isDark }">
    <div class="section-container">
      <div class="flex justify-between items-center mb-12">
        <div>
          <h2 class="section-title">Featured Events</h2>
          <p class="section-description">Handpicked events you don't want to miss</p>
        </div>
        <a-button type="link" size="large" @click="handleViewAll" class="hidden sm:flex">
          View All <ArrowRightOutlined class="ml-2" />
        </a-button>
      </div>
      <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <div
          v-for="event in events"
          :key="event.id"
          class="event-card-base"
          @click="handleEventClick(event.slug)"
        >
          <div class="event-card-image">
            <img
              v-if="event.featured_image"
              :src="event.featured_image"
              :alt="event.title"
              class="w-full h-full object-cover"
            />
            <div v-else class="w-full h-full flex items-center justify-center" :class="isDark ? 'text-gray-600' : 'text-gray-400'">
              <CalendarOutlined class="text-5xl" />
            </div>
            <div class="badge-featured" v-if="event.is_featured">
              <StarFilled /> Featured
            </div>
            <div class="badge-type">
              {{ event.event_type }}
            </div>
          </div>
          <div class="event-card-content">
            <div class="event-card-category">{{ event.category }}</div>
            <h3 class="event-card-title">{{ event.title }}</h3>
            <p class="event-card-description">{{ event.short_description }}</p>
            <div class="event-card-meta">
              <span class="flex items-center gap-1">
                <CalendarOutlined />
                {{ formatDate(event.start_date) }}
              </span>
              <span v-if="event.event_type !== 'online'" class="flex items-center gap-1">
                <EnvironmentOutlined />
                {{ event.venue_city || 'TBA' }}
              </span>
              <span v-else class="flex items-center gap-1">
                <VideoCameraOutlined />
                Online
              </span>
            </div>
            <div class="event-card-footer">
              <div>
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
    </div>
  </section>
</template>

<script setup>
import { router } from '@inertiajs/vue3';
import { useTheme } from '../../composables/useTheme';
import {
  CalendarOutlined,
  ArrowRightOutlined,
  StarFilled,
  EnvironmentOutlined,
  VideoCameraOutlined,
} from '@ant-design/icons-vue';

const { isDark } = useTheme();

const props = defineProps({
  events: {
    type: Array,
    required: true,
  },
});

const handleEventClick = (slug) => {
  router.visit(`/events/${slug}`);
};

const handleViewAll = () => {
  router.visit('/events');
};

const formatDate = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  });
};
</script>

