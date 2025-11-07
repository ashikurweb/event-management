<template>
  <section class="cta-section relative overflow-hidden" :class="{ 'cta-section-dark': isDark }">
    <div class="cta-background"></div>
    <div class="cta-pattern"></div>
    <div class="cta-shapes">
      <div class="cta-shape cta-shape-1"></div>
      <div class="cta-shape cta-shape-2"></div>
      <div class="cta-shape cta-shape-3"></div>
    </div>
    
    <div class="section-container relative z-10">
      <div class="max-w-4xl mx-auto text-center space-y-8">
        <!-- Badge -->
        <div class="cta-badge" :class="isDark ? 'cta-badge-dark' : 'cta-badge-light'">
          <StarFilled class="text-yellow-300" />
          <span class="text-sm font-medium text-white">Join 10,000+ Event Organizers</span>
        </div>

        <!-- Main Heading -->
        <div class="space-y-4">
          <h2 class="cta-title">
            Ready to Create Your
            <span class="cta-title-highlight">Amazing Event?</span>
          </h2>
          <p class="cta-description">
            Join thousands of organizers and start hosting unforgettable events today. 
            From conferences to concerts, we've got everything you need.
          </p>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-3 gap-6 max-w-2xl mx-auto pt-4">
          <div class="cta-stat">
            <div class="cta-stat-number">10K+</div>
            <div class="cta-stat-label">Events</div>
          </div>
          <div class="cta-stat">
            <div class="cta-stat-number">50K+</div>
            <div class="cta-stat-label">Attendees</div>
          </div>
          <div class="cta-stat">
            <div class="cta-stat-number">98%</div>
            <div class="cta-stat-label">Satisfaction</div>
          </div>
        </div>

        <!-- CTA Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center pt-6">
          <a-button 
            type="primary" 
            size="large" 
            class="cta-button-primary"
            @click="handleCreateEvent"
          >
            <template #icon><PlusCircleOutlined /></template>
            Create Event
          </a-button>
          <a-button 
            size="large" 
            class="cta-button-secondary"
            @click="handleLearnMore"
          >
            <template #icon><PlayCircleOutlined /></template>
            Watch Demo
          </a-button>
        </div>

        <!-- Trust Indicators -->
        <div class="flex flex-wrap items-center justify-center gap-6 pt-8 text-white/60 text-sm">
          <div class="flex items-center gap-2">
            <CheckCircleOutlined class="text-green-400" />
            <span>Free to Start</span>
          </div>
          <div class="flex items-center gap-2">
            <CheckCircleOutlined class="text-green-400" />
            <span>No Credit Card</span>
          </div>
          <div class="flex items-center gap-2">
            <CheckCircleOutlined class="text-green-400" />
            <span>24/7 Support</span>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { router } from '@inertiajs/vue3';
import { useTheme } from '../../Composables/useTheme';
import {
  StarFilled,
  PlusCircleOutlined,
  PlayCircleOutlined,
  CheckCircleOutlined,
} from '@ant-design/icons-vue';

const { isDark } = useTheme();

const handleCreateEvent = () => {
  router.visit('/events/create');
};

const handleLearnMore = () => {
  router.visit('/about');
};
</script>

<style scoped>
.cta-section {
  position: relative;
  padding: 80px 0;
  min-height: 600px;
  display: flex;
  align-items: center;
}

.cta-background {
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 25%, #f093fb 50%, #4facfe 75%, #00f2fe 100%);
  background-size: 400% 400%;
  animation: gradientShift 15s ease infinite;
}

.cta-section-dark .cta-background {
  background: linear-gradient(135deg, #1a1a2e 0%, #16213e 25%, #0f3460 50%, #533483 75%, #6a2c70 100%);
  background-size: 400% 400%;
  animation: gradientShift 15s ease infinite;
}

.cta-pattern {
  position: absolute;
  inset: 0;
  background-image: 
    radial-gradient(circle at 20% 30%, rgba(255, 255, 255, 0.15) 0%, transparent 50%),
    radial-gradient(circle at 80% 70%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
    radial-gradient(circle at 50% 50%, rgba(255, 255, 255, 0.05) 0%, transparent 70%);
  opacity: 0.8;
}

.cta-section-dark .cta-pattern {
  background-image: 
    radial-gradient(circle at 20% 30%, rgba(255, 255, 255, 0.08) 0%, transparent 50%),
    radial-gradient(circle at 80% 70%, rgba(255, 255, 255, 0.05) 0%, transparent 50%),
    radial-gradient(circle at 50% 50%, rgba(255, 255, 255, 0.03) 0%, transparent 70%);
  opacity: 0.6;
}

.cta-shapes {
  position: absolute;
  inset: 0;
  overflow: hidden;
}

.cta-shape {
  position: absolute;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  animation: float 20s ease-in-out infinite;
}

.cta-shape-1 {
  width: 300px;
  height: 300px;
  top: -150px;
  left: -150px;
  animation-delay: 0s;
}

.cta-shape-2 {
  width: 200px;
  height: 200px;
  bottom: -100px;
  right: -100px;
  animation-delay: 5s;
}

.cta-shape-3 {
  width: 150px;
  height: 150px;
  top: 50%;
  right: 10%;
  animation-delay: 10s;
}

.cta-title {
  font-size: 3rem;
  line-height: 1.2;
  font-weight: 800;
  color: white;
  text-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
}

.cta-title-highlight {
  background: linear-gradient(120deg, #fbbf24, #f59e0b, #f97316);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  display: inline-block;
  animation: shimmer 3s ease-in-out infinite;
}

.cta-description {
  font-size: 1.25rem;
  line-height: 1.7;
  color: rgba(255, 255, 255, 0.9);
  max-width: 700px;
  margin: 0 auto;
}

.cta-stat {
  text-align: center;
  padding: 1.5rem;
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border-radius: 16px;
  border: 1px solid rgba(255, 255, 255, 0.2);
  transition: all 0.3s ease;
}

.cta-stat:hover {
  background: rgba(255, 255, 255, 0.15);
  transform: translateY(-5px);
}

.cta-section-dark .cta-stat {
  background: rgba(255, 255, 255, 0.05);
  border-color: rgba(255, 255, 255, 0.1);
}

.cta-section-dark .cta-stat:hover {
  background: rgba(255, 255, 255, 0.1);
}

.cta-stat-number {
  font-size: 2rem;
  font-weight: 700;
  color: white;
  margin-bottom: 0.5rem;
}

.cta-stat-label {
  font-size: 0.875rem;
  color: rgba(255, 255, 255, 0.8);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.cta-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  border-radius: 9999px;
  backdrop-filter: blur(8px);
  border: 1px solid;
}

.cta-badge-light {
  background: rgba(255, 255, 255, 0.1);
  border-color: rgba(255, 255, 255, 0.2);
}

.cta-badge-dark {
  background: rgba(255, 255, 255, 0.05);
  border-color: rgba(255, 255, 255, 0.1);
}

.cta-button-primary {
  background: white !important;
  color: #667eea !important;
  border: none !important;
  font-weight: 600 !important;
  padding: 12px 32px !important;
  height: auto !important;
  font-size: 1rem !important;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15) !important;
  transition: all 0.3s ease !important;
}

.cta-button-primary:hover {
  background: #f8f9fa !important;
  transform: translateY(-2px) !important;
  box-shadow: 0 6px 25px rgba(0, 0, 0, 0.2) !important;
}

.cta-section-dark .cta-button-primary {
  background: #40a9ff !important;
  color: white !important;
}

.cta-section-dark .cta-button-primary:hover {
  background: #69c0ff !important;
  box-shadow: 0 6px 25px rgba(64, 169, 255, 0.4) !important;
}

.cta-button-secondary {
  background: rgba(255, 255, 255, 0.15) !important;
  color: white !important;
  border: 2px solid rgba(255, 255, 255, 0.3) !important;
  backdrop-filter: blur(10px) !important;
  font-weight: 600 !important;
  padding: 12px 32px !important;
  height: auto !important;
  font-size: 1rem !important;
  transition: all 0.3s ease !important;
}

.cta-button-secondary:hover {
  background: rgba(255, 255, 255, 0.25) !important;
  border-color: rgba(255, 255, 255, 0.5) !important;
  transform: translateY(-2px) !important;
}

.cta-section-dark .cta-button-secondary {
  background: rgba(255, 255, 255, 0.08) !important;
  border-color: rgba(255, 255, 255, 0.2) !important;
}

.cta-section-dark .cta-button-secondary:hover {
  background: rgba(255, 255, 255, 0.15) !important;
  border-color: rgba(255, 255, 255, 0.3) !important;
}

@keyframes gradientShift {
  0% {
    background-position: 0% 50%;
  }
  50% {
    background-position: 100% 50%;
  }
  100% {
    background-position: 0% 50%;
  }
}

@keyframes shimmer {
  0%, 100% {
    background-position: 0% 50%;
  }
  50% {
    background-position: 100% 50%;
  }
}

@keyframes float {
  0%, 100% {
    transform: translate(0, 0) rotate(0deg);
  }
  33% {
    transform: translate(30px, -30px) rotate(120deg);
  }
  66% {
    transform: translate(-20px, 20px) rotate(240deg);
  }
}

@media (max-width: 768px) {
  .cta-section {
    padding: 60px 0;
    min-height: auto;
  }

  .cta-title {
    font-size: 2rem;
  }

  .cta-description {
    font-size: 1rem;
  }

  .cta-stat-number {
    font-size: 1.5rem;
  }

  .cta-shape-1,
  .cta-shape-2,
  .cta-shape-3 {
    display: none;
  }
}
</style>
