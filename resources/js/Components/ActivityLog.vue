<template>
  <div class="activity-log-section">
    <!-- Search and Actions Bar -->
    <div class="activity-actions-bar">
      <a-input
        v-model:value="searchQuery"
        placeholder="Search activities..."
        allow-clear
        class="activity-search"
      >
        <template #prefix><SearchOutlined /></template>
      </a-input>
      <a-button @click="handlePrint" type="default">
        <template #icon><PrinterOutlined /></template>
        Print
      </a-button>
    </div>

    <div v-if="loading" class="loading-container">
      <a-spin />
    </div>
    
    <div v-else-if="filteredActivities && filteredActivities.length > 0">
      <div class="activity-list" id="activity-print-content">
        <div
          v-for="activity in filteredActivities"
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
        v-if="!searchQuery"
        :current="pagination.current"
        :page-size="pagination.pageSize"
        :total="pagination.total"
        :page-size-options="[10, 15, 20, 50]"
        @change="handlePageChange"
        @page-size-change="handlePageSizeChange"
      />
      <div v-else class="search-results-info">
        Showing {{ filteredActivities.length }} result(s) for "{{ searchQuery }}"
      </div>
    </div>
    
    <a-empty v-else description="No activity logs found" />
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';
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
  SearchOutlined,
  PrinterOutlined,
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

const searchQuery = ref('');

const pagination = computed(() => ({
  current: props.activities.current_page || 1,
  pageSize: props.activities.per_page || 15,
  total: props.activities.total || 0,
}));

const filteredActivities = computed(() => {
  if (!searchQuery.value.trim()) {
    return props.activities.data || [];
  }
  
  const query = searchQuery.value.toLowerCase().trim();
  return (props.activities.data || []).filter(activity => {
    const description = (activity.description || '').toLowerCase();
    const action = getActivityLabel(activity.action).toLowerCase();
    const userName = activity.user 
      ? `${activity.user.first_name} ${activity.user.last_name}`.toLowerCase()
      : '';
    const ip = formatIpAddress(activity.ip_address || '').toLowerCase();
    const time = formatTime(activity.created_at).toLowerCase();
    
    return description.includes(query) ||
           action.includes(query) ||
           userName.includes(query) ||
           ip.includes(query) ||
           time.includes(query);
  });
});

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

// Search is handled automatically by computed filteredActivities
// No need for handleSearch function as it's reactive

const handlePrint = () => {
  const printContent = document.getElementById('activity-print-content');
  if (!printContent) return;

  const printWindow = window.open('', '_blank');
  // Try to get category name from the page
  const categoryNameElement = document.querySelector('.activity-log-card .card-title') || 
                               document.querySelector('.category-show-card .card-title');
  const categoryName = categoryNameElement?.textContent?.replace('Activity Log', '').trim() || 
                       document.querySelector('h2.card-title')?.textContent || 
                       'Category';
  const currentDate = new Date().toLocaleString();
  
  printWindow.document.write(`
    <!DOCTYPE html>
    <html>
    <head>
      <title>Activity Log - ${categoryName}</title>
      <style>
        body {
          font-family: Arial, sans-serif;
          padding: 20px;
          color: #000;
        }
        .print-header {
          margin-bottom: 20px;
          border-bottom: 2px solid #000;
          padding-bottom: 10px;
        }
        .print-title {
          font-size: 24px;
          font-weight: bold;
          margin-bottom: 5px;
        }
        .print-date {
          font-size: 12px;
          color: #666;
        }
        .activity-item {
          margin-bottom: 15px;
          padding: 10px;
          border: 1px solid #ddd;
          border-radius: 4px;
        }
        .activity-header {
          display: flex;
          justify-content: space-between;
          margin-bottom: 8px;
        }
        .activity-action {
          font-weight: bold;
          font-size: 16px;
        }
        .activity-time {
          font-size: 12px;
          color: #666;
        }
        .activity-description {
          margin: 8px 0;
          font-size: 14px;
        }
        .activity-meta {
          font-size: 12px;
          color: #666;
          margin-top: 8px;
        }
        @media print {
          body { margin: 0; padding: 15px; }
          .activity-item { page-break-inside: avoid; }
        }
      </style>
    </head>
    <body>
      <div class="print-header">
        <div class="print-title">Activity Log - ${categoryName}</div>
        <div class="print-date">Generated on: ${currentDate}</div>
      </div>
      ${printContent.innerHTML}
    </body>
    </html>
  `);
  
  printWindow.document.close();
  setTimeout(() => {
    printWindow.print();
  }, 250);
};
</script>

<style scoped>

.activity-actions-bar {
  display: flex;
  gap: 12px;
  margin-bottom: 16px;
  align-items: center;
  flex-wrap: wrap;
}

.activity-search {
  flex: 1;
  min-width: 200px;
}

.search-results-info {
  padding: 12px;
  background: var(--bg-secondary, #f5f5f5);
  border-radius: 6px;
  text-align: center;
  color: var(--text-secondary, #595959);
  font-size: 14px;
  margin-top: 16px;
}

[data-theme="dark"] .search-results-info {
  background: rgba(255, 255, 255, 0.08);
  color: rgba(255, 255, 255, 0.65);
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

