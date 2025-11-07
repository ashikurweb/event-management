<template>
  <section class="py-20 bg-gray-50" :class="{ 'bg-[#141414]': isDark }">
    <div class="section-container">
      <div class="flex justify-between items-center mb-12">
        <div>
          <h2 class="section-title">Upcoming Events</h2>
          <p class="section-description">Don't miss these exciting events</p>
        </div>
        <a-button type="link" size="large" @click="handleViewAll" class="hidden sm:flex">
          View All <ArrowRightOutlined class="ml-2" />
        </a-button>
      </div>
      <div class="space-y-4">
        <div
          v-for="event in events"
          :key="event.id"
          class="rounded-2xl p-6 grid md:grid-cols-[200px_1fr_auto] gap-6 cursor-pointer transition-all duration-300 hover:shadow-lg hover:-translate-x-1"
          :class="isDark ? 'bg-[#1f1f1f] border border-[#2d2d2d]' : 'bg-white border border-gray-200'"
          @click="handleEventClick(event.slug)"
        >
          <div class="w-full aspect-video rounded-xl overflow-hidden" :class="isDark ? 'bg-[#262626]' : 'bg-gray-100'">
            <img
              v-if="event.featured_image"
              :src="event.featured_image"
              :alt="event.title"
              class="w-full h-full object-cover"
            />
            <div v-else class="w-full h-full flex items-center justify-center" :class="isDark ? 'text-gray-600' : 'text-gray-400'">
              <CalendarOutlined class="text-4xl" />
            </div>
          </div>
          <div class="space-y-2">
            <div class="flex items-center gap-2 flex-wrap">
              <span class="text-xs font-semibold uppercase" :class="isDark ? 'text-blue-400' : 'text-blue-600'">
                {{ event.category }}
              </span>
              <span class="text-xs px-2 py-1 rounded capitalize" :class="isDark ? 'bg-[#262626] text-gray-400' : 'bg-gray-100 text-gray-600'">
                {{ event.event_type }}
              </span>
            </div>
            <h3 class="text-xl font-semibold" :class="isDark ? 'text-white' : 'text-gray-900'">
              {{ event.title }}
            </h3>
            <p class="text-sm line-clamp-2" :class="isDark ? 'text-gray-400' : 'text-gray-600'">
              {{ event.short_description }}
            </p>
            <div class="flex flex-wrap gap-4 text-sm" :class="isDark ? 'text-gray-400' : 'text-gray-500'">
              <span class="flex items-center gap-1">
                <CalendarOutlined />
                {{ formatDate(event.start_date) }}
              </span>
              <span class="flex items-center gap-1">
                <ClockCircleOutlined />
                {{ formatTime(event.start_date) }}
              </span>
              <span v-if="event.event_type !== 'online'" class="flex items-center gap-1">
                <EnvironmentOutlined />
                {{ event.venue_city || 'TBA' }}
              </span>
            </div>
          </div>
          <div class="flex flex-col justify-between items-end gap-4">
            <div>
              <span v-if="event.price === 0" class="price-free">Free</span>
              <span v-else class="price-paid">${{ event.price }}</span>
            </div>
            <a-button type="primary">Book Now</a-button>
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
  ClockCircleOutlined,
  EnvironmentOutlined,
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

const formatTime = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleTimeString('en-US', {
    hour: 'numeric',
    minute: '2-digit',
  });
};
</script>
