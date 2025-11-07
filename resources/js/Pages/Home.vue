<template>
  <FrontendLayout>
    <HeroSection />
    <CategoriesSection :categories="categories" />
    <FeaturedEventsSection :events="featuredEvents" />
    <UpcomingEventsSection :events="upcomingEvents" />
    <CTASection />
  </FrontendLayout>
</template>

<script setup>
import { ref } from 'vue';
import FrontendLayout from '../Layouts/FrontendLayout.vue';
import HeroSection from '../components/frontend/HeroSection.vue';
import CategoriesSection from '../components/frontend/CategoriesSection.vue';
import FeaturedEventsSection from '../components/frontend/FeaturedEventsSection.vue';
import UpcomingEventsSection from '../components/frontend/UpcomingEventsSection.vue';
import CTASection from '../components/frontend/CTASection.vue';
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

// Mock data - will be replaced with API calls
const categories = ref([
  { id: 1, name: 'Conference', slug: 'conference', icon: FolderOutlined, color: '#1890ff', count: 234 },
  { id: 2, name: 'Workshop', slug: 'workshop', icon: SettingOutlined, color: '#52c41a', count: 156 },
  { id: 3, name: 'Concert', slug: 'concert', icon: SoundOutlined, color: '#faad14', count: 89 },
  { id: 4, name: 'Sports', slug: 'sports', icon: TrophyFilled, color: '#ff4d4f', count: 67 },
  { id: 5, name: 'Festival', slug: 'festival', icon: StarOutlined, color: '#722ed1', count: 45 },
  { id: 6, name: 'Networking', slug: 'networking', icon: UserOutlined, color: '#13c2c2', count: 123 },
  { id: 7, name: 'Webinar', slug: 'webinar', icon: PlayCircleOutlined, color: '#eb2f96', count: 278 },
  { id: 8, name: 'Exhibition', slug: 'exhibition', icon: AppstoreOutlined, color: '#f5222d', count: 34 },
]);

const featuredEvents = ref([
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
    is_featured: true,
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
    is_featured: true,
    featured_image: null,
  },
]);

const upcomingEvents = ref([
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
    featured_image: null,
  },
  {
    id: 7,
    title: 'Digital Marketing Webinar',
    slug: 'digital-marketing-webinar',
    short_description: 'Learn advanced digital marketing strategies',
    category: 'Webinar',
    event_type: 'online',
    start_date: '2024-03-22T11:00:00',
    venue_city: null,
    price: 0,
    featured_image: null,
  },
]);
</script>
