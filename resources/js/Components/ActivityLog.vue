<template>
  <div class="activity-log-section">
    <div v-if="loading" class="loading-container">
      <a-spin />
    </div>
    
    <div v-else-if="activities && activities.data && activities.data.length > 0">
      <div class="activity-list">
        <div
          v-for="activity in activities.data"
          :key="activity.id"
          class="activity-item"
        >
          <div class="activity-icon">
            <component :is="getActivityIcon(activity.action)" />
          </div>
          <div class="activity-content">
            <div class="activity-header">
              <span class="activity-action">{{ getActivityLabel(activity.action) }}</span>
              <span class="activity-time">{{ formatTime(activity.created_at) }}</span>
            </div>
            <div class="activity-description" v-if="activity.description">
              {{ activity.description }}
            </div>
            <div class="activity-meta">
              <span v-if="activity.user" class="activity-user">
                <UserOutlined /> {{ activity.user.first_name }} {{ activity.user.last_name }}
              </span>
              <span v-if="activity.ip_address" class="activity-ip">
                <GlobalOutlined /> {{ formatIpAddress(activity.ip_address) }}
              </span>
            </div>
          </div>
        </div>
      </div>
      
      <Pagination
        :current="pagination.current"
        :page-size="pagination.pageSize"
        :total="pagination.total"
        :page-size-options="[10, 15, 20, 50]"
        @change="handlePageChange"
        @page-size-change="handlePageSizeChange"
      />
    </div>
    
    <a-empty v-else description="No activity logs found" />
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import Pagination from './Pagination.vue';
import {
  PlusCircleOutlined,
  DeleteOutlined,
  EditOutlined,
  CheckCircleOutlined,
  CloseCircleOutlined,
  UserOutlined,
  GlobalOutlined,
} from '@ant-design/icons-vue';

const props = defineProps({
  activities: {
    type: Object,
    default: () => ({ data: [], current_page: 1, per_page: 15, total: 0 }),
  },
  loading: {
    type: Boolean,
    default: false,
  },
  categoryId: {
    type: [Number, String],
    required: true,
  },
});

const pagination = computed(() => ({
  current: props.activities.current_page || 1,
  pageSize: props.activities.per_page || 15,
  total: props.activities.total || 0,
}));

const getActivityIcon = (action) => {
  const iconMap = {
    'category.created': PlusCircleOutlined,
    'category.updated': EditOutlined,
    'category.deleted': DeleteOutlined,
    'category.activated': CheckCircleOutlined,
    'category.deactivated': CloseCircleOutlined,
  };
  return iconMap[action] || CheckCircleOutlined;
};

const getActivityLabel = (action) => {
  const labelMap = {
    'category.created': 'Category Created',
    'category.updated': 'Category Updated',
    'category.deleted': 'Category Deleted',
    'category.activated': 'Category Activated',
    'category.deactivated': 'Category Deactivated',
  };
  return labelMap[action] || action;
};

const formatTime = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  const now = new Date();
  const diff = now - date;
  
  // Less than 1 minute
  if (diff < 60000) {
    return 'Just now';
  }
  
  // Less than 1 hour
  if (diff < 3600000) {
    const minutes = Math.floor(diff / 60000);
    return `${minutes} minute${minutes > 1 ? 's' : ''} ago`;
  }
  
  // Less than 24 hours
  if (diff < 86400000) {
    const hours = Math.floor(diff / 3600000);
    return `${hours} hour${hours > 1 ? 's' : ''} ago`;
  }
  
  // Less than 7 days
  if (diff < 604800000) {
    const days = Math.floor(diff / 86400000);
    return `${days} day${days > 1 ? 's' : ''} ago`;
  }
  
  // Format as date
  return date.toLocaleString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};

const formatIpAddress = (ip) => {
  if (!ip) return '';
  
  // If it's a URL, extract just the hostname/IP
  try {
    const url = new URL(ip);
    const hostname = url.hostname;
    // If hostname is localhost or 127.0.0.1, show as is
    if (hostname === '127.0.0.1' || hostname === '::1' || hostname === 'localhost') {
      return '127.0.0.1';
    }
    return hostname;
  } catch (e) {
    // If it's not a URL, it's probably just an IP
    // Normalize localhost variants
    if (ip === '::1' || ip === 'localhost') {
      return '127.0.0.1';
    }
    // Return the IP as is
    return ip;
  }
};

const handlePageChange = ({ current, pageSize }) => {
  router.get(
    window.location.pathname,
    { page: current, per_page: pageSize },
    {
      preserveState: true,
      preserveScroll: true,
      only: ['activities'],
    }
  );
};

const handlePageSizeChange = ({ current, pageSize }) => {
  router.get(
    window.location.pathname,
    { page: 1, per_page: pageSize },
    {
      preserveState: true,
      preserveScroll: true,
      only: ['activities'],
    }
  );
};
</script>

<style scoped>
.activity-log-section {
  /* Removed margin-top as it's now in a separate card */
}

.loading-container {
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 40px;
}

.activity-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.activity-item {
  display: flex;
  gap: 12px;
  padding: 16px;
  background: var(--bg-secondary, #fafafa);
  border-radius: 8px;
  border: 1px solid var(--border-color, #f0f0f0);
  transition: all 0.2s;
}

[data-theme="dark"] .activity-item {
  background: rgba(255, 255, 255, 0.04);
  border-color: var(--border-color, #434343);
}

.activity-item:hover {
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.activity-icon {
  display: flex;
  align-items: flex-start;
  padding-top: 2px;
  color: var(--primary-color, #1890ff);
  font-size: 18px;
}

.activity-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.activity-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 8px;
}

.activity-action {
  font-weight: 600;
  font-size: 15px;
  color: var(--text-primary, #262626);
}

[data-theme="dark"] .activity-action {
  color: rgba(255, 255, 255, 0.85);
}

.activity-time {
  font-size: 13px;
  color: var(--text-tertiary, #8c8c8c);
}

[data-theme="dark"] .activity-time {
  color: rgba(255, 255, 255, 0.45);
}

.activity-description {
  font-size: 14px;
  color: var(--text-secondary, #595959);
  line-height: 1.5;
}

[data-theme="dark"] .activity-description {
  color: rgba(255, 255, 255, 0.65);
}

.activity-meta {
  display: flex;
  gap: 16px;
  flex-wrap: wrap;
  font-size: 12px;
  color: var(--text-tertiary, #8c8c8c);
}

[data-theme="dark"] .activity-meta {
  color: rgba(255, 255, 255, 0.45);
}

.activity-user,
.activity-ip {
  display: flex;
  align-items: center;
  gap: 4px;
}

@media (max-width: 768px) {
  .activity-item {
    padding: 12px;
  }
  
  .activity-header {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>

