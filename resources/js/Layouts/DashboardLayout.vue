<template>
  <div class="dashboard-layout">
    <!-- Sidebar -->
    <DashboardSidebar 
      :collapsed="sidebarCollapsed"
      @toggle="sidebarCollapsed = !sidebarCollapsed"
    />

    <!-- Main Content Area -->
    <div class="dashboard-main" :class="{ 'sidebar-collapsed': sidebarCollapsed }">
      <!-- Header -->
      <DashboardHeader 
        :collapsed="sidebarCollapsed"
        @toggle-sidebar="sidebarCollapsed = !sidebarCollapsed"
      />

      <!-- Content -->
      <div class="dashboard-content">
        <slot />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import DashboardSidebar from '../components/dashboard/DashboardSidebar.vue';
import DashboardHeader from '../components/dashboard/DashboardHeader.vue';

const sidebarCollapsed = ref(false);
</script>

<style scoped>
.dashboard-layout {
  display: flex;
  min-height: 100vh;
  background-color: var(--bg-primary, #f0f2f5);
  transition: background-color 0.3s;
}

.dashboard-main {
  flex: 1;
  margin-left: 256px;
  transition: margin-left 0.2s;
  display: flex;
  flex-direction: column;
}

.dashboard-main.sidebar-collapsed {
  margin-left: 80px;
}

.dashboard-content {
  flex: 1;
  padding: 24px;
  overflow-y: auto;
}

@media (max-width: 768px) {
  .dashboard-main {
    margin-left: 0;
  }
  
  .dashboard-main.sidebar-collapsed {
    margin-left: 0;
  }
}
</style>

