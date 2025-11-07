<template>
  <section class="py-20 bg-gray-50" :class="{ 'bg-[#141414]': isDark }">
    <div class="section-container">
      <div class="section-header">
        <h2 class="section-title">Browse by Category</h2>
        <p class="section-description">
          Explore events by your interests
        </p>
      </div>
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-4 gap-6">
        <div
          v-for="category in categories"
          :key="category.id"
          class="category-card-base"
          @click="handleCategoryClick(category.slug)"
        >
          <div class="category-icon-base" :style="{ background: category.color }">
            <component :is="category.icon" />
          </div>
          <h3 class="text-lg font-semibold mb-1" :class="isDark ? 'text-white' : 'text-gray-900'">
            {{ category.name }}
          </h3>
          <p class="text-sm" :class="isDark ? 'text-gray-400' : 'text-gray-600'">
            {{ category.count }} events
          </p>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { router } from '@inertiajs/vue3';
import { useTheme } from '../../composables/useTheme';
import {
  FolderOutlined,
  SettingOutlined,
  SoundOutlined,
  TrophyFilled,
  StarOutlined,
  UserOutlined,
  PlayCircleOutlined,
  AppstoreOutlined,
} from '@ant-design/icons-vue';

const { isDark } = useTheme();

const props = defineProps({
  categories: {
    type: Array,
    required: true,
  },
});

const handleCategoryClick = (slug) => {
  router.visit(`/events?category=${slug}`);
};
</script>

