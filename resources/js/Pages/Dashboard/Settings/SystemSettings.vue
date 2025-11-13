<template>
  <DashboardLayout>
    <div class="system-settings-container">
      <!-- Header -->
      <div class="page-header">
        <h1 class="page-title">System Settings</h1>
        <p class="page-description">
          Manage system-wide settings and configurations
        </p>
      </div>

      <!-- Settings Cards Grid -->
      <div class="settings-grid">
        <!-- Recycle Bin Card -->
        <a-card class="settings-card recycle-bin-card" @click="handleRecycleBin">
          <div class="card-content">
            <div class="card-icon-wrapper">
              <DeleteOutlined class="card-icon" />
            </div>
            <div class="card-info">
              <h3 class="card-title">Recycle Bin</h3>
              <p class="card-description">
                View and manage deleted items. Restore or permanently delete items.
              </p>
            </div>
            <RightOutlined class="card-arrow" />
          </div>
        </a-card>

        <!-- Placeholder for other system settings -->
        <a-card class="settings-card coming-soon-card">
          <div class="card-content">
            <div class="card-icon-wrapper">
              <SettingOutlined class="card-icon" />
            </div>
            <div class="card-info">
              <h3 class="card-title">System Maintenance</h3>
              <p class="card-description">
                System maintenance tools and utilities
              </p>
            </div>
            <span class="coming-soon-badge">Coming Soon</span>
          </div>
        </a-card>

        <a-card class="settings-card coming-soon-card">
          <div class="card-content">
            <div class="card-icon-wrapper">
              <DatabaseOutlined class="card-icon" />
            </div>
            <div class="card-info">
              <h3 class="card-title">Database Backup</h3>
              <p class="card-description">
                Backup and restore database
              </p>
            </div>
            <span class="coming-soon-badge">Coming Soon</span>
          </div>
        </a-card>

        <a-card class="settings-card coming-soon-card">
          <div class="card-content">
            <div class="card-icon-wrapper">
              <SafetyOutlined class="card-icon" />
            </div>
            <div class="card-info">
              <h3 class="card-title">Security Settings</h3>
              <p class="card-description">
                Configure security and access controls
              </p>
            </div>
            <span class="coming-soon-badge">Coming Soon</span>
          </div>
        </a-card>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { router } from '@inertiajs/vue3';
import DashboardLayout from '../../../Layouts/DashboardLayout.vue';
import {
  DeleteOutlined,
  SettingOutlined,
  DatabaseOutlined,
  SafetyOutlined,
  RightOutlined,
} from '@ant-design/icons-vue';

const handleRecycleBin = () => {
  router.visit('/dashboard/settings/recycle-bin');
};
</script>

<style scoped>
.system-settings-container {
  padding: 24px;
}

.page-header {
  margin-bottom: 32px;
}

.page-title {
  margin: 0 0 8px 0;
  font-size: 28px;
  font-weight: 700;
  color: var(--text-primary, #262626);
}

[data-theme="dark"] .page-title {
  color: rgba(255, 255, 255, 0.85);
}

.page-description {
  margin: 0;
  font-size: 14px;
  color: var(--text-secondary, #8c8c8c);
}

[data-theme="dark"] .page-description {
  color: rgba(255, 255, 255, 0.65);
}

.settings-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 24px;
}

.settings-card {
  border-radius: 12px;
  box-shadow: var(--card-shadow, 0 2px 8px rgba(0, 0, 0, 0.06));
  transition: all 0.3s ease;
  cursor: pointer;
  border: 1px solid var(--card-border, #f0f0f0);
}

[data-theme="dark"] .settings-card {
  border-color: var(--card-border, #2d2d2d);
}

.recycle-bin-card {
  cursor: pointer;
}

.recycle-bin-card:hover {
  transform: translateY(-4px);
  box-shadow: var(--shadow-lg, 0 8px 24px rgba(0, 0, 0, 0.12));
  border-color: var(--primary-color, #1890ff);
}

[data-theme="dark"] .recycle-bin-card:hover {
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4);
  border-color: var(--primary-color, #1890ff);
}

.coming-soon-card {
  cursor: not-allowed;
  opacity: 0.7;
}

.card-content {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 8px;
}

.card-icon-wrapper {
  width: 56px;
  height: 56px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, var(--primary-color, #1890ff) 0%, #40a9ff 100%);
  flex-shrink: 0;
}

.recycle-bin-card .card-icon-wrapper {
  background: linear-gradient(135deg, #ff4d4f 0%, #ff7875 100%);
}

.card-icon {
  font-size: 24px;
  color: white;
}

.card-info {
  flex: 1;
  min-width: 0;
}

.card-title {
  margin: 0 0 4px 0;
  font-size: 18px;
  font-weight: 600;
  color: var(--text-primary, #262626);
}

[data-theme="dark"] .card-title {
  color: rgba(255, 255, 255, 0.85);
}

.card-description {
  margin: 0;
  font-size: 13px;
  color: var(--text-secondary, #8c8c8c);
  line-height: 1.5;
}

[data-theme="dark"] .card-description {
  color: rgba(255, 255, 255, 0.65);
}

.card-arrow {
  font-size: 16px;
  color: var(--text-tertiary, #bfbfbf);
  flex-shrink: 0;
}

.coming-soon-badge {
  padding: 4px 12px;
  background: var(--bg-hover, #f5f5f5);
  border-radius: 12px;
  font-size: 12px;
  font-weight: 500;
  color: var(--text-secondary, #8c8c8c);
  flex-shrink: 0;
}

[data-theme="dark"] .coming-soon-badge {
  background: rgba(255, 255, 255, 0.08);
  color: rgba(255, 255, 255, 0.65);
}

@media (max-width: 768px) {
  .system-settings-container {
    padding: 16px;
  }

  .settings-grid {
    grid-template-columns: 1fr;
    gap: 16px;
  }

  .page-title {
    font-size: 24px;
  }
}
</style>

