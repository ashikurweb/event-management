<template>
  <div class="modern-pagination" v-if="total > 0">
    <div class="pagination-info">
      <span class="pagination-text">
        Showing {{ startRecord }} to {{ endRecord }} of {{ total }} entries
      </span>
    </div>
    
    <div class="pagination-controls">
      <button
        class="pagination-btn"
        :class="{ disabled: currentPage === 1 }"
        :disabled="currentPage === 1"
        @click="goToPage(1)"
        title="First page"
      >
        <DoubleLeftOutlined />
      </button>
      
      <button
        class="pagination-btn"
        :class="{ disabled: currentPage === 1 }"
        :disabled="currentPage === 1"
        @click="goToPage(currentPage - 1)"
        title="Previous page"
      >
        <LeftOutlined />
      </button>
      
      <div class="pagination-pages">
        <button
          v-for="page in visiblePages"
          :key="page"
          class="pagination-page-btn"
          :class="{ active: page === currentPage, ellipsis: page === '...' }"
          :disabled="page === '...'"
          @click="page !== '...' && goToPage(page)"
        >
          {{ page }}
        </button>
      </div>
      
      <button
        class="pagination-btn"
        :class="{ disabled: currentPage === totalPages }"
        :disabled="currentPage === totalPages"
        @click="goToPage(currentPage + 1)"
        title="Next page"
      >
        <RightOutlined />
      </button>
      
      <button
        class="pagination-btn"
        :class="{ disabled: currentPage === totalPages }"
        :disabled="currentPage === totalPages"
        @click="goToPage(totalPages)"
        title="Last page"
      >
        <DoubleRightOutlined />
      </button>
    </div>
    
    <div class="pagination-size">
      <span class="pagination-text">Show:</span>
      <select
        class="pagination-select"
        :value="pageSize"
        @change="handlePageSizeChange($event.target.value)"
      >
        <option v-for="size in pageSizeOptions" :key="size" :value="size">
          {{ size }}
        </option>
      </select>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import {
  LeftOutlined,
  RightOutlined,
  DoubleLeftOutlined,
  DoubleRightOutlined,
} from '@ant-design/icons-vue';

const props = defineProps({
  current: {
    type: Number,
    default: 1,
  },
  pageSize: {
    type: Number,
    default: 15,
  },
  total: {
    type: Number,
    default: 0,
  },
  pageSizeOptions: {
    type: Array,
    default: () => [10, 15, 20, 50, 100],
  },
});

const emit = defineEmits(['change', 'pageSizeChange']);

const currentPage = computed(() => props.current);
const totalPages = computed(() => Math.ceil(props.total / props.pageSize));

const startRecord = computed(() => {
  if (props.total === 0) return 0;
  return (props.current - 1) * props.pageSize + 1;
});

const endRecord = computed(() => {
  const end = props.current * props.pageSize;
  return end > props.total ? props.total : end;
});

const visiblePages = computed(() => {
  const pages = [];
  const total = totalPages.value;
  const current = currentPage.value;
  
  if (total <= 7) {
    // Show all pages if total is 7 or less
    for (let i = 1; i <= total; i++) {
      pages.push(i);
    }
  } else {
    // Always show first page
    pages.push(1);
    
    if (current <= 4) {
      // Near the start
      for (let i = 2; i <= 5; i++) {
        pages.push(i);
      }
      pages.push('...');
      pages.push(total);
    } else if (current >= total - 3) {
      // Near the end
      pages.push('...');
      for (let i = total - 4; i <= total; i++) {
        pages.push(i);
      }
    } else {
      // In the middle
      pages.push('...');
      for (let i = current - 1; i <= current + 1; i++) {
        pages.push(i);
      }
      pages.push('...');
      pages.push(total);
    }
  }
  
  return pages;
});

const goToPage = (page) => {
  if (page >= 1 && page <= totalPages.value && page !== currentPage.value) {
    emit('change', {
      current: page,
      pageSize: props.pageSize,
    });
  }
};

const handlePageSizeChange = (newSize) => {
  emit('pageSizeChange', {
    current: 1,
    pageSize: parseInt(newSize),
  });
};
</script>

<style scoped>
.modern-pagination {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  padding: 16px 0;
  flex-wrap: wrap;
}

.pagination-info {
  display: flex;
  align-items: center;
}

.pagination-text {
  font-size: 14px;
  color: var(--text-secondary, #8c8c8c);
  transition: color 0.1s ease-out;
}

[data-theme="dark"] .pagination-text {
  color: rgba(255, 255, 255, 0.45);
}

.pagination-controls {
  display: flex;
  align-items: center;
  gap: 4px;
}

.pagination-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border: 1px solid var(--border-color, #d9d9d9);
  background: var(--bg-primary, #fff);
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s ease;
  color: var(--text-primary, #262626);
  font-size: 12px;
}

.pagination-btn:hover:not(.disabled) {
  border-color: var(--color-primary, #1890ff);
  color: var(--color-primary, #1890ff);
  background: var(--bg-hover, #f5f5f5);
}

.pagination-btn.disabled {
  opacity: 0.4;
  cursor: not-allowed;
  background: var(--bg-disabled, #f5f5f5);
}

[data-theme="dark"] .pagination-btn {
  background: #1f1f1f;
  border-color: #434343;
  color: rgba(255, 255, 255, 0.85);
}

[data-theme="dark"] .pagination-btn:hover:not(.disabled) {
  border-color: #40a9ff;
  color: #40a9ff;
  background: #262626;
}

[data-theme="dark"] .pagination-btn.disabled {
  background: #141414;
  border-color: #2d2d2d;
}

.pagination-pages {
  display: flex;
  align-items: center;
  gap: 4px;
  margin: 0 8px;
}

.pagination-page-btn {
  min-width: 32px;
  height: 32px;
  padding: 0 8px;
  border: 1px solid var(--border-color, #d9d9d9);
  background: var(--bg-primary, #fff);
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s ease;
  color: var(--text-primary, #262626);
  font-size: 14px;
  font-weight: 500;
}

.pagination-page-btn:hover:not(.disabled):not(.active) {
  border-color: var(--color-primary, #1890ff);
  color: var(--color-primary, #1890ff);
  background: var(--bg-hover, #f5f5f5);
}

.pagination-page-btn.active {
  background: var(--color-primary, #1890ff);
  border-color: var(--color-primary, #1890ff);
  color: #fff;
  font-weight: 600;
}

.pagination-page-btn.ellipsis {
  border: none;
  background: transparent;
  cursor: default;
  color: var(--text-secondary, #8c8c8c);
}

.pagination-page-btn.ellipsis:hover {
  background: transparent;
  border: none;
}

[data-theme="dark"] .pagination-page-btn {
  background: #1f1f1f;
  border-color: #434343;
  color: rgba(255, 255, 255, 0.85);
}

[data-theme="dark"] .pagination-page-btn:hover:not(.disabled):not(.active) {
  border-color: #40a9ff;
  color: #40a9ff;
  background: #262626;
}

[data-theme="dark"] .pagination-page-btn.active {
  background: #1890ff;
  border-color: #1890ff;
  color: #fff;
}

[data-theme="dark"] .pagination-page-btn.ellipsis {
  color: rgba(255, 255, 255, 0.45);
}

.pagination-size {
  display: flex;
  align-items: center;
  gap: 8px;
}

.pagination-select {
  padding: 4px 8px;
  border: 1px solid var(--border-color, #d9d9d9);
  background: var(--bg-primary, #fff);
  border-radius: 6px;
  color: var(--text-primary, #262626);
  font-size: 14px;
  cursor: pointer;
  transition: all 0.2s ease;
  outline: none;
}

.pagination-select:hover {
  border-color: var(--color-primary, #1890ff);
}

.pagination-select:focus {
  border-color: var(--color-primary, #1890ff);
  box-shadow: 0 0 0 2px rgba(24, 144, 255, 0.2);
}

[data-theme="dark"] .pagination-select {
  background: #1f1f1f;
  border-color: #434343;
  color: rgba(255, 255, 255, 0.85);
}

[data-theme="dark"] .pagination-select:hover {
  border-color: #40a9ff;
}

[data-theme="dark"] .pagination-select:focus {
  border-color: #40a9ff;
  box-shadow: 0 0 0 2px rgba(64, 169, 255, 0.2);
}

/* Responsive */
@media (max-width: 768px) {
  .modern-pagination {
    flex-direction: column;
    align-items: stretch;
    gap: 12px;
  }
  
  .pagination-info,
  .pagination-controls,
  .pagination-size {
    justify-content: center;
  }
  
  .pagination-pages {
    margin: 0 4px;
  }
}
</style>

