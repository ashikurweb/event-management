<template>
  <FrontendLayout>
    <!-- Event Hero Section -->
    <section class="event-hero" v-if="event">
      <div class="hero-image-wrapper">
        <img
          v-if="event.banner_image"
          :src="event.banner_image"
          :alt="event.title"
          class="hero-banner"
        />
        <div v-else class="hero-banner-placeholder">
          <CalendarOutlined />
        </div>
        <div class="hero-overlay"></div>
        <div class="hero-content">
          <div class="hero-container">
            <div class="hero-info">
              <div class="event-badges">
                <a-tag v-if="event.is_featured" color="warning">
                  <StarFilled /> Featured
                </a-tag>
                <a-tag :color="getEventTypeColor(event.event_type)">
                  {{ event.event_type }}
                </a-tag>
                <a-tag v-if="event.status === 'published'" color="success">
                  Published
                </a-tag>
              </div>
              <h1 class="event-title">{{ event.title }}</h1>
              <p class="event-short-description">{{ event.short_description }}</p>
              <div class="event-quick-info">
                <div class="quick-info-item">
                  <CalendarOutlined />
                  <div>
                    <div class="info-label">Date</div>
                    <div class="info-value">{{ formatDateRange(event.start_date, event.end_date) }}</div>
                  </div>
                </div>
                <div class="quick-info-item" v-if="event.event_type !== 'online'">
                  <EnvironmentOutlined />
                  <div>
                    <div class="info-label">Location</div>
                    <div class="info-value">{{ event.venue_name || event.venue_city || 'TBA' }}</div>
                  </div>
                </div>
                <div class="quick-info-item" v-else>
                  <VideoCameraOutlined />
                  <div>
                    <div class="info-label">Platform</div>
                    <div class="info-value">Online Event</div>
                  </div>
                </div>
                <div class="quick-info-item">
                  <UserOutlined />
                  <div>
                    <div class="info-label">Attendees</div>
                    <div class="info-value">{{ event.current_attendees }} / {{ event.max_attendees || '∞' }}</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Main Content -->
    <div class="event-main" v-if="event">
      <div class="main-container">
        <!-- Left Column: Event Details -->
        <div class="event-details-column">
          <!-- About Section -->
          <section class="detail-section">
            <h2 class="section-title">About This Event</h2>
            <div class="event-description" v-html="event.description"></div>
          </section>

          <!-- Event Schedule -->
          <section class="detail-section" v-if="event.schedules && event.schedules.length > 0">
            <h2 class="section-title">Schedule</h2>
            <div class="schedule-list">
              <div
                v-for="schedule in event.schedules"
                :key="schedule.id"
                class="schedule-item"
              >
                <div class="schedule-time">
                  <div class="time-start">{{ formatTime(schedule.start_time) }}</div>
                  <div class="time-end">{{ formatTime(schedule.end_time) }}</div>
                </div>
                <div class="schedule-content">
                  <h3 class="schedule-title">{{ schedule.title }}</h3>
                  <p class="schedule-description" v-if="schedule.description">
                    {{ schedule.description }}
                  </p>
                  <div class="schedule-meta" v-if="schedule.location || schedule.speaker">
                    <span v-if="schedule.location">
                      <EnvironmentOutlined /> {{ schedule.location }}
                    </span>
                    <span v-if="schedule.speaker">
                      <UserOutlined /> {{ schedule.speaker.name }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </section>

          <!-- Speakers -->
          <section class="detail-section" v-if="event.speakers && event.speakers.length > 0">
            <h2 class="section-title">Speakers</h2>
            <div class="speakers-grid">
              <div
                v-for="speaker in event.speakers"
                :key="speaker.id"
                class="speaker-card"
              >
                <div class="speaker-avatar">
                  <img
                    v-if="speaker.photo"
                    :src="speaker.photo"
                    :alt="speaker.name"
                  />
                  <div v-else class="speaker-avatar-placeholder">
                    <UserOutlined />
                  </div>
                </div>
                <h3 class="speaker-name">{{ speaker.name }}</h3>
                <p class="speaker-title" v-if="speaker.title">{{ speaker.title }}</p>
                <p class="speaker-company" v-if="speaker.company">{{ speaker.company }}</p>
              </div>
            </div>
          </section>

          <!-- FAQs -->
          <section class="detail-section" v-if="event.faqs && event.faqs.length > 0">
            <h2 class="section-title">Frequently Asked Questions</h2>
            <a-collapse v-model:activeKey="activeFaqKeys" class="faq-collapse">
              <a-collapse-panel
                v-for="faq in event.faqs"
                :key="faq.id"
                :header="faq.question"
              >
                <p>{{ faq.answer }}</p>
              </a-collapse-panel>
            </a-collapse>
          </section>

          <!-- Reviews -->
          <section class="detail-section" v-if="event.reviews && event.reviews.length > 0">
            <h2 class="section-title">Reviews</h2>
            <div class="reviews-summary">
              <div class="rating-overview">
                <div class="rating-score">{{ event.average_rating || 0 }}</div>
                <div class="rating-stars">
                  <a-rate :value="event.average_rating || 0" disabled allow-half />
                </div>
                <div class="rating-count">Based on {{ event.reviews.length }} reviews</div>
              </div>
            </div>
            <div class="reviews-list">
              <div
                v-for="review in event.reviews"
                :key="review.id"
                class="review-item"
              >
                <div class="review-header">
                  <div class="reviewer-info">
                    <a-avatar :src="review.user.avatar">{{ review.user.first_name[0] }}</a-avatar>
                    <div>
                      <div class="reviewer-name">{{ review.user.first_name }} {{ review.user.last_name }}</div>
                      <div class="review-date">{{ formatDate(review.created_at) }}</div>
                    </div>
                  </div>
                  <a-rate :value="review.rating" disabled allow-half />
                </div>
                <h4 class="review-title" v-if="review.title">{{ review.title }}</h4>
                <p class="review-comment">{{ review.comment }}</p>
              </div>
            </div>
          </section>
        </div>

        <!-- Right Column: Booking Card -->
        <div class="booking-column">
          <div class="booking-card">
            <div class="booking-header">
              <div class="booking-price">
                <span v-if="minPrice === 0" class="price-free">Free</span>
                <span v-else class="price-paid">From ${{ minPrice }}</span>
              </div>
              <div class="booking-status" v-if="event.status === 'published'">
                <a-tag color="success">Available</a-tag>
              </div>
            </div>

            <div class="booking-details">
              <div class="detail-row">
                <CalendarOutlined />
                <div>
                  <div class="detail-label">Date & Time</div>
                  <div class="detail-value">{{ formatDateRange(event.start_date, event.end_date) }}</div>
                  <div class="detail-value-small">{{ formatTime(event.start_date) }} - {{ formatTime(event.end_date) }}</div>
                </div>
              </div>
              <div class="detail-row" v-if="event.event_type !== 'online'">
                <EnvironmentOutlined />
                <div>
                  <div class="detail-label">Location</div>
                  <div class="detail-value">{{ event.venue_name }}</div>
                  <div class="detail-value-small">{{ event.venue_address }}, {{ event.venue_city }}</div>
                </div>
              </div>
              <div class="detail-row" v-else>
                <VideoCameraOutlined />
                <div>
                  <div class="detail-label">Online Event</div>
                  <div class="detail-value">Join via {{ event.meeting_platform || 'Online Platform' }}</div>
                </div>
              </div>
            </div>

            <!-- Ticket Selection -->
            <div class="ticket-selection" v-if="event.ticket_types && event.ticket_types.length > 0">
              <h3 class="ticket-selection-title">Select Tickets</h3>
              <div class="ticket-types">
                <div
                  v-for="ticketType in event.ticket_types"
                  :key="ticketType.id"
                  class="ticket-type-item"
                  :class="{ 'selected': selectedTickets[ticketType.id] > 0 }"
                >
                  <div class="ticket-type-info">
                    <h4 class="ticket-type-name">{{ ticketType.name }}</h4>
                    <p class="ticket-type-description" v-if="ticketType.description">
                      {{ ticketType.description }}
                    </p>
                    <div class="ticket-type-price">
                      <span v-if="ticketType.price === 0" class="price-free">Free</span>
                      <span v-else>${{ ticketType.price }}</span>
                    </div>
                    <div class="ticket-type-availability" v-if="ticketType.quantity_total">
                      {{ ticketType.quantity_available }} available
                    </div>
                  </div>
                  <div class="ticket-type-controls">
                    <a-input-number
                      v-model:value="selectedTickets[ticketType.id]"
                      :min="ticketType.min_per_order || 0"
                      :max="Math.min(ticketType.max_per_order || 10, ticketType.quantity_available || 10)"
                      :disabled="ticketType.quantity_available === 0"
                      @change="updateTotal"
                    />
                  </div>
                </div>
              </div>

              <!-- Order Summary -->
              <div class="order-summary" v-if="totalTickets > 0">
                <div class="summary-row">
                  <span>Subtotal</span>
                  <span>${{ subtotal.toFixed(2) }}</span>
                </div>
                <div class="summary-row" v-if="serviceFee > 0">
                  <span>Service Fee</span>
                  <span>${{ serviceFee.toFixed(2) }}</span>
                </div>
                <div class="summary-row total">
                  <span>Total</span>
                  <span>${{ total.toFixed(2) }}</span>
                </div>
              </div>

              <!-- Booking Button -->
              <a-button
                type="primary"
                size="large"
                block
                class="book-button"
                :disabled="totalTickets === 0 || event.status !== 'published'"
                :loading="booking"
                @click="handleBooking"
              >
                {{ totalTickets > 0 ? `Book ${totalTickets} Ticket${totalTickets > 1 ? 's' : ''}` : 'Select Tickets' }}
              </a-button>
            </div>

            <!-- Share & Favorite -->
            <div class="event-actions">
              <a-button @click="handleShare">
                <ShareAltOutlined /> Share
              </a-button>
              <a-button @click="handleFavorite">
                <HeartOutlined v-if="!isFavorited" />
                <HeartFilled v-else />
                {{ isFavorited ? 'Favorited' : 'Favorite' }}
              </a-button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-else class="loading-state">
      <a-spin size="large" />
      <p>Loading event details...</p>
    </div>
  </FrontendLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import FrontendLayout from '../Layouts/FrontendLayout.vue';
import {
  CalendarOutlined,
  StarFilled,
  EnvironmentOutlined,
  VideoCameraOutlined,
  UserOutlined,
  ShareAltOutlined,
  HeartOutlined,
  HeartFilled,
} from '@ant-design/icons-vue';

const page = usePage();
const booking = ref(false);
const isFavorited = ref(false);
const activeFaqKeys = ref([]);
const selectedTickets = reactive({});
const serviceFeeRate = 0.05; // 5% service fee

// Mock event data - will be replaced with API call
const event = ref({
  id: 1,
  title: 'Tech Conference 2024',
  slug: 'tech-conference-2024',
  short_description: 'Join industry leaders for the biggest tech event of the year',
  description: '<p>This is a comprehensive tech conference featuring the latest innovations in technology, artificial intelligence, and software development. Join us for three days of inspiring talks, hands-on workshops, and networking opportunities.</p><p>Our lineup includes industry experts, startup founders, and thought leaders who will share their insights and experiences.</p>',
  category: 'Conference',
  event_type: 'hybrid',
  status: 'published',
  is_featured: true,
  start_date: '2024-03-15T10:00:00',
  end_date: '2024-03-17T18:00:00',
  venue_name: 'San Francisco Convention Center',
  venue_address: '747 Howard St',
  venue_city: 'San Francisco, CA',
  meeting_platform: 'Zoom',
  current_attendees: 245,
  max_attendees: 500,
  average_rating: 4.5,
  ticket_types: [
    {
      id: 1,
      name: 'Early Bird',
      description: 'Limited early bird pricing',
      price: 199,
      quantity_total: 100,
      quantity_sold: 45,
      quantity_available: 55,
      min_per_order: 1,
      max_per_order: 5,
    },
    {
      id: 2,
      name: 'Regular',
      description: 'Standard ticket',
      price: 299,
      quantity_total: 300,
      quantity_sold: 150,
      quantity_available: 150,
      min_per_order: 1,
      max_per_order: 10,
    },
    {
      id: 3,
      name: 'VIP',
      description: 'VIP access with premium benefits',
      price: 499,
      quantity_total: 50,
      quantity_sold: 20,
      quantity_available: 30,
      min_per_order: 1,
      max_per_order: 3,
    },
  ],
  schedules: [
    {
      id: 1,
      title: 'Opening Keynote',
      description: 'Welcome and opening remarks',
      start_time: '2024-03-15T10:00:00',
      end_time: '2024-03-15T11:00:00',
      location: 'Main Hall',
      speaker: { name: 'John Doe' },
    },
    {
      id: 2,
      title: 'AI & Machine Learning Panel',
      description: 'Discussion on the future of AI',
      start_time: '2024-03-15T14:00:00',
      end_time: '2024-03-15T15:30:00',
      location: 'Hall A',
      speaker: { name: 'Jane Smith' },
    },
  ],
  speakers: [
    { id: 1, name: 'John Doe', title: 'CEO', company: 'Tech Corp', photo: null },
    { id: 2, name: 'Jane Smith', title: 'CTO', company: 'Innovate Inc', photo: null },
  ],
  faqs: [
    { id: 1, question: 'What is included in the ticket?', answer: 'Access to all sessions, lunch, and networking events.' },
    { id: 2, question: 'Can I get a refund?', answer: 'Refunds are available up to 7 days before the event.' },
  ],
  reviews: [
    {
      id: 1,
      rating: 5,
      title: 'Amazing Experience',
      comment: 'This was one of the best conferences I\'ve attended. Great speakers and networking opportunities.',
      user: { first_name: 'Alice', last_name: 'Johnson', avatar: null },
      created_at: '2024-01-15',
    },
  ],
});

const minPrice = computed(() => {
  if (!event.value.ticket_types || event.value.ticket_types.length === 0) return 0;
  return Math.min(...event.value.ticket_types.map(t => t.price));
});

const totalTickets = computed(() => {
  return Object.values(selectedTickets).reduce((sum, count) => sum + (count || 0), 0);
});

const subtotal = computed(() => {
  let total = 0;
  Object.entries(selectedTickets).forEach(([ticketTypeId, quantity]) => {
    if (quantity > 0) {
      const ticketType = event.value.ticket_types.find(t => t.id === parseInt(ticketTypeId));
      if (ticketType) {
        total += ticketType.price * quantity;
      }
    }
  });
  return total;
});

const serviceFee = computed(() => {
  return subtotal.value * serviceFeeRate;
});

const total = computed(() => {
  return subtotal.value + serviceFee.value;
});

onMounted(() => {
  // Initialize selected tickets
  if (event.value.ticket_types) {
    event.value.ticket_types.forEach(ticketType => {
      selectedTickets[ticketType.id] = 0;
    });
  }
  // Load event data from API
  // loadEvent();
});

const updateTotal = () => {
  // Recalculate totals when ticket selection changes
};

const handleBooking = async () => {
  booking.value = true;
  // TODO: Implement booking logic
  setTimeout(() => {
    booking.value = false;
    router.visit('/booking/checkout');
  }, 1000);
};

const handleShare = () => {
  // TODO: Implement share functionality
  if (navigator.share) {
    navigator.share({
      title: event.value.title,
      text: event.value.short_description,
      url: window.location.href,
    });
  }
};

const handleFavorite = () => {
  isFavorited.value = !isFavorited.value;
  // TODO: Implement favorite API call
};

const getEventTypeColor = (type) => {
  const colors = {
    online: 'blue',
    offline: 'green',
    hybrid: 'orange',
  };
  return colors[type] || 'default';
};

const formatDateRange = (start, end) => {
  const startDate = new Date(start);
  const endDate = new Date(end);
  const startFormatted = startDate.toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
  });
  const endFormatted = endDate.toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  });
  return `${startFormatted} - ${endFormatted}`;
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
/* Event Hero */
.event-hero {
  position: relative;
  margin-bottom: 48px;
}

.hero-image-wrapper {
  position: relative;
  width: 100%;
  height: 500px;
  overflow: hidden;
}

.hero-banner {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.hero-banner-placeholder {
  width: 100%;
  height: 100%;
  background: linear-gradient(135deg, var(--color-primary, #1890ff) 0%, var(--color-primary-400, #40a9ff) 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 120px;
  opacity: 0.3;
}

.hero-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(to bottom, rgba(0, 0, 0, 0.3) 0%, rgba(0, 0, 0, 0.7) 100%);
}

.hero-content {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  padding: 48px 24px;
  color: white;
}

.hero-container {
  max-width: 1400px;
  margin: 0 auto;
}

.event-badges {
  display: flex;
  gap: 8px;
  margin-bottom: 16px;
}

.event-title {
  font-size: 48px;
  font-weight: 700;
  margin: 0 0 16px;
  color: white;
}

.event-short-description {
  font-size: 18px;
  opacity: 0.95;
  margin: 0 0 24px;
}

.event-quick-info {
  display: flex;
  gap: 32px;
  flex-wrap: wrap;
}

.quick-info-item {
  display: flex;
  align-items: flex-start;
  gap: 12px;
}

.quick-info-item .anticon {
  font-size: 20px;
  margin-top: 4px;
}

.info-label {
  font-size: 12px;
  opacity: 0.8;
  margin-bottom: 4px;
}

.info-value {
  font-size: 16px;
  font-weight: 600;
}

/* Main Content */
.event-main {
  padding: 0 24px 48px;
}

.main-container {
  max-width: 1400px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: 1fr 400px;
  gap: 32px;
}

/* Event Details Column */
.event-details-column {
  display: flex;
  flex-direction: column;
  gap: 32px;
}

.detail-section {
  background: var(--card-bg, #fff);
  border: 1px solid var(--card-border, #f0f0f0);
  border-radius: var(--radius-lg, 16px);
  padding: 32px;
  transition: background-color var(--transition-theme), border-color var(--transition-theme);
}

.section-title {
  font-size: 24px;
  font-weight: 600;
  color: var(--text-primary, #262626);
  margin: 0 0 24px;
  transition: color var(--transition-theme);
}

.event-description {
  font-size: 16px;
  line-height: 1.8;
  color: var(--text-primary, #262626);
  transition: color var(--transition-theme);
}

.event-description :deep(p) {
  margin: 0 0 16px;
}

.event-description :deep(p:last-child) {
  margin-bottom: 0;
}

/* Schedule */
.schedule-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.schedule-item {
  display: grid;
  grid-template-columns: 120px 1fr;
  gap: 24px;
  padding: 16px;
  background: var(--bg-elevated, #fafafa);
  border-radius: var(--radius-base, 8px);
  transition: background-color var(--transition-theme);
}

.schedule-time {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.time-start,
.time-end {
  font-size: 14px;
  color: var(--text-secondary, #8c8c8c);
}

.schedule-title {
  font-size: 18px;
  font-weight: 600;
  color: var(--text-primary, #262626);
  margin: 0 0 8px;
  transition: color var(--transition-theme);
}

.schedule-description {
  font-size: 14px;
  color: var(--text-secondary, #8c8c8c);
  margin: 0 0 8px;
  transition: color var(--transition-theme);
}

.schedule-meta {
  display: flex;
  gap: 16px;
  font-size: 13px;
  color: var(--text-tertiary, #bfbfbf);
}

/* Speakers */
.speakers-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 24px;
}

.speaker-card {
  text-align: center;
}

.speaker-avatar {
  width: 120px;
  height: 120px;
  border-radius: var(--radius-full, 9999px);
  margin: 0 auto 16px;
  overflow: hidden;
  background: var(--bg-elevated, #fafafa);
}

.speaker-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.speaker-avatar-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 48px;
  color: var(--text-tertiary, #bfbfbf);
}

.speaker-name {
  font-size: 18px;
  font-weight: 600;
  color: var(--text-primary, #262626);
  margin: 0 0 4px;
  transition: color var(--transition-theme);
}

.speaker-title {
  font-size: 14px;
  color: var(--text-secondary, #8c8c8c);
  margin: 0 0 4px;
  transition: color var(--transition-theme);
}

.speaker-company {
  font-size: 13px;
  color: var(--text-tertiary, #bfbfbf);
  margin: 0;
  transition: color var(--transition-theme);
}

/* FAQs */
.faq-collapse {
  background: transparent;
}

/* Reviews */
.reviews-summary {
  margin-bottom: 32px;
}

.rating-overview {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 24px;
  background: var(--bg-elevated, #fafafa);
  border-radius: var(--radius-base, 8px);
  transition: background-color var(--transition-theme);
}

.rating-score {
  font-size: 48px;
  font-weight: 700;
  color: var(--text-primary, #262626);
  transition: color var(--transition-theme);
}

.rating-count {
  font-size: 14px;
  color: var(--text-secondary, #8c8c8c);
  transition: color var(--transition-theme);
}

.reviews-list {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.review-item {
  padding: 20px;
  background: var(--bg-elevated, #fafafa);
  border-radius: var(--radius-base, 8px);
  transition: background-color var(--transition-theme);
}

.review-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 12px;
}

.reviewer-info {
  display: flex;
  gap: 12px;
  align-items: center;
}

.reviewer-name {
  font-size: 16px;
  font-weight: 600;
  color: var(--text-primary, #262626);
  margin: 0 0 4px;
  transition: color var(--transition-theme);
}

.review-date {
  font-size: 12px;
  color: var(--text-secondary, #8c8c8c);
  transition: color var(--transition-theme);
}

.review-title {
  font-size: 18px;
  font-weight: 600;
  color: var(--text-primary, #262626);
  margin: 0 0 8px;
  transition: color var(--transition-theme);
}

.review-comment {
  font-size: 14px;
  line-height: 1.6;
  color: var(--text-secondary, #8c8c8c);
  margin: 0;
  transition: color var(--transition-theme);
}

/* Booking Column */
.booking-column {
  position: sticky;
  top: 100px;
  height: fit-content;
}

.booking-card {
  background: var(--card-bg, #fff);
  border: 1px solid var(--card-border, #f0f0f0);
  border-radius: var(--radius-lg, 16px);
  padding: 24px;
  transition: background-color var(--transition-theme), border-color var(--transition-theme);
}

.booking-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  padding-bottom: 24px;
  border-bottom: 1px solid var(--card-border, #f0f0f0);
  transition: border-color var(--transition-theme);
}

.booking-price {
  font-size: 32px;
  font-weight: 700;
}

.price-free {
  color: var(--color-success, #52c41a);
}

.price-paid {
  color: var(--text-primary, #262626);
  transition: color var(--transition-theme);
}

.booking-details {
  display: flex;
  flex-direction: column;
  gap: 20px;
  margin-bottom: 24px;
  padding-bottom: 24px;
  border-bottom: 1px solid var(--card-border, #f0f0f0);
  transition: border-color var(--transition-theme);
}

.detail-row {
  display: flex;
  gap: 12px;
}

.detail-row .anticon {
  font-size: 20px;
  color: var(--color-primary, #1890ff);
  margin-top: 4px;
}

.detail-label {
  font-size: 12px;
  color: var(--text-secondary, #8c8c8c);
  margin-bottom: 4px;
  transition: color var(--transition-theme);
}

.detail-value {
  font-size: 16px;
  font-weight: 600;
  color: var(--text-primary, #262626);
  transition: color var(--transition-theme);
}

.detail-value-small {
  font-size: 14px;
  color: var(--text-secondary, #8c8c8c);
  margin-top: 4px;
  transition: color var(--transition-theme);
}

/* Ticket Selection */
.ticket-selection {
  margin-bottom: 24px;
}

.ticket-selection-title {
  font-size: 18px;
  font-weight: 600;
  color: var(--text-primary, #262626);
  margin: 0 0 16px;
  transition: color var(--transition-theme);
}

.ticket-types {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin-bottom: 24px;
}

.ticket-type-item {
  padding: 16px;
  border: 2px solid var(--card-border, #f0f0f0);
  border-radius: var(--radius-base, 8px);
  display: flex;
  justify-content: space-between;
  align-items: center;
  transition: all var(--transition-base);
  transition: background-color var(--transition-theme), border-color var(--transition-theme);
}

.ticket-type-item.selected {
  border-color: var(--color-primary, #1890ff);
  background: var(--color-primary-50, #e6f7ff);
}

.ticket-type-info {
  flex: 1;
}

.ticket-type-name {
  font-size: 16px;
  font-weight: 600;
  color: var(--text-primary, #262626);
  margin: 0 0 4px;
  transition: color var(--transition-theme);
}

.ticket-type-description {
  font-size: 13px;
  color: var(--text-secondary, #8c8c8c);
  margin: 0 0 8px;
  transition: color var(--transition-theme);
}

.ticket-type-price {
  font-size: 18px;
  font-weight: 600;
  color: var(--text-primary, #262626);
  margin-bottom: 4px;
  transition: color var(--transition-theme);
}

.ticket-type-availability {
  font-size: 12px;
  color: var(--text-tertiary, #bfbfbf);
  transition: color var(--transition-theme);
}

/* Order Summary */
.order-summary {
  padding: 16px;
  background: var(--bg-elevated, #fafafa);
  border-radius: var(--radius-base, 8px);
  margin-bottom: 16px;
  transition: background-color var(--transition-theme);
}

.summary-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 12px;
  font-size: 14px;
  color: var(--text-secondary, #8c8c8c);
  transition: color var(--transition-theme);
}

.summary-row:last-child {
  margin-bottom: 0;
}

.summary-row.total {
  font-size: 18px;
  font-weight: 600;
  color: var(--text-primary, #262626);
  padding-top: 12px;
  border-top: 1px solid var(--card-border, #f0f0f0);
  transition: color var(--transition-theme), border-color var(--transition-theme);
}

.book-button {
  margin-bottom: 16px;
}

/* Event Actions */
.event-actions {
  display: flex;
  gap: 12px;
}

.event-actions .ant-btn {
  flex: 1;
}

/* Loading State */
.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 80px 24px;
  gap: 16px;
  color: var(--text-secondary, #8c8c8c);
}

/* Mobile Responsive */
@media (max-width: 1024px) {
  .main-container {
    grid-template-columns: 1fr;
  }

  .booking-column {
    position: static;
  }

  .hero-image-wrapper {
    height: 400px;
  }

  .event-title {
    font-size: 36px;
  }
}

@media (max-width: 768px) {
  .event-hero {
    margin-bottom: 24px;
  }

  .hero-image-wrapper {
    height: 300px;
  }

  .hero-content {
    padding: 24px 16px;
  }

  .event-title {
    font-size: 28px;
  }

  .event-short-description {
    font-size: 16px;
  }

  .event-quick-info {
    flex-direction: column;
    gap: 16px;
  }

  .event-main {
    padding: 0 16px 32px;
  }

  .detail-section {
    padding: 24px;
  }

  .speakers-grid {
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
  }

  .schedule-item {
    grid-template-columns: 1fr;
  }

  .schedule-time {
    flex-direction: row;
    gap: 16px;
  }
}
</style>

