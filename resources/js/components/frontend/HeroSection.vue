<template>
  <section class="relative min-h-[600px] hero-gradient hero-pattern overflow-hidden">
    <div class="absolute inset-0 grid-pattern opacity-20"></div>
    <div class="relative section-container py-20 lg:py-32">
      <div class="grid lg:grid-cols-2 gap-12 items-center">
        <!-- Content -->
        <div class="text-center lg:text-left space-y-8">
          <div class="space-y-4">
            <h1 class="hero-title text-5xl lg:text-6xl xl:text-7xl font-bold leading-tight">
              Discover Amazing
              <span class="block bg-gradient-to-r from-yellow-300 to-pink-300 bg-clip-text text-transparent">
                Events Near You
              </span>
            </h1>
            <p class="hero-description text-xl lg:text-2xl max-w-2xl mx-auto lg:mx-0">
              Join thousands of events, connect with like-minded people, and create
              unforgettable memories. From conferences to concerts, we have it all.
            </p>
          </div>

          <!-- Stats -->
          <div class="flex flex-wrap justify-center lg:justify-start gap-8 pt-4">
            <div class="text-center lg:text-left">
              <div class="hero-stat-number text-4xl font-bold mb-1">10K+</div>
              <div class="hero-stat-label">Events</div>
            </div>
            <div class="text-center lg:text-left">
              <div class="hero-stat-number text-4xl font-bold mb-1">50K+</div>
              <div class="hero-stat-label">Attendees</div>
            </div>
            <div class="text-center lg:text-left">
              <div class="hero-stat-number text-4xl font-bold mb-1">500+</div>
              <div class="hero-stat-label">Organizers</div>
            </div>
          </div>
        </div>

        <!-- Image/Illustration -->
        <div class="hidden lg:block">
          <div class="relative">
            <div class="absolute inset-0 bg-gradient-to-r from-blue-400 to-purple-400 rounded-3xl blur-3xl opacity-50 animate-float"></div>
            <div class="relative bg-white/10 backdrop-blur-xl rounded-3xl p-12 border border-white/20 shadow-2xl">
              <CalendarOutlined class="text-9xl text-white/30" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { CalendarOutlined } from '@ant-design/icons-vue';
import { useTheme } from '../../composables/useTheme';

const { isDark } = useTheme();
</script>

