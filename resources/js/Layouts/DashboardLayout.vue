<template>
  <div class="dashboard-layout">
    <!-- Mobile Overlay -->
    <div 
      v-if="isMobile && sidebarOpen"
      class="sidebar-overlay"
      @click="closeSidebar"
    ></div>

    <!-- Sidebar -->
    <DashboardSidebar 
      :collapsed="sidebarCollapsed"
      :mobile-open="isMobile ? sidebarOpen : true"
      @toggle="sidebarCollapsed = !sidebarCollapsed"
    />

    <!-- Main Content Area -->
    <div class="dashboard-main" :class="{ 'sidebar-collapsed': sidebarCollapsed }">
      <!-- Header -->
      <DashboardHeader 
        :collapsed="sidebarCollapsed"
        :mobile-open="isMobile ? sidebarOpen : false"
        @toggle-sidebar="toggleSidebar"
      />

      <!-- Content -->
      <div class="dashboard-content">
        <slot />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import DashboardSidebar from '../Pages/Dashboard/DashboardSidebar.vue';
import DashboardHeader from '../Pages/Dashboard/DashboardHeader.vue';

const sidebarCollapsed = ref(false);
const sidebarOpen = ref(false);
const isMobile = ref(false);

const checkMobile = () => {
  isMobile.value = window.innerWidth <= 768;
  if (!isMobile.value) {
    sidebarOpen.value = true; // Always open on desktop
  } else {
    sidebarOpen.value = false; // Closed by default on mobile
  }
};

const toggleSidebar = () => {
  if (isMobile.value) {
    sidebarOpen.value = !sidebarOpen.value;
  } else {
    sidebarCollapsed.value = !sidebarCollapsed.value;
  }
};

const closeSidebar = () => {
  if (isMobile.value) {
    sidebarOpen.value = false;
  }
};

onMounted(() => {
  checkMobile();
  window.addEventListener('resize', checkMobile);
});

onUnmounted(() => {
  window.removeEventListener('resize', checkMobile);
});
</script>

<style scoped>
.dashboard-layout {
  display: flex;
  min-height: 100vh;
  background-color: var(--bg-primary, #f0f2f5);
  transition: background-color 0.1s ease-out;
}

.dashboard-main {
  flex: 1;
  margin-left: 256px;
  transition: margin-left 0.2s;
  display: flex;
  flex-direction: column;
  margin-top: 64px; /* Account for fixed header */
}

.dashboard-main.sidebar-collapsed {
  margin-left: 80px;
}

/* Header positioning is handled in DashboardHeader.vue */

.dashboard-content {
  flex: 1;
  padding: 24px;
  overflow-y: auto;
}

/* Responsive Content Padding */
@media (max-width: 768px) {
  .dashboard-content {
    padding: 16px;
  }
}

@media (max-width: 480px) {
  .dashboard-content {
    padding: 12px;
  }
}

.sidebar-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.45);
  z-index: 999;
  animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

@media (max-width: 768px) {
  .dashboard-main {
    margin-left: 0;
    margin-top: 56px; /* Account for fixed header on mobile */
  }
  
  .dashboard-main.sidebar-collapsed {
    margin-left: 0;
  }
}

@media (max-width: 480px) {
  .dashboard-main {
    margin-top: 52px; /* Account for fixed header on small mobile */
  }
}
</style>

