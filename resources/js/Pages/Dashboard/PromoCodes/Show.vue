<template>
  <DashboardLayout>
    <Breadcrumb :items="breadcrumbItems" />

    <a-card class="promo-code-show-card" v-if="promoCode">
      <template #title>
        <div class="card-header">
          <h2 class="card-title">{{ promoCode.code }}</h2>
          <a-space>
            <a-button @click="handleEdit">
              <template #icon><EditOutlined /></template>
              Edit
            </a-button>
            <a-button @click="handleBack">
              <template #icon><ArrowLeftOutlined /></template>
              Back
            </a-button>
          </a-space>
        </div>
      </template>

      <a-descriptions :column="2" bordered>
        <a-descriptions-item label="Code" :span="2">
          <span class="info-value code-value">{{ promoCode.code }}</span>
          <a-tag v-if="promoCode.is_active" color="success" style="margin-left: 8px">Active</a-tag>
          <a-tag v-else color="default" style="margin-left: 8px">Inactive</a-tag>
        </a-descriptions-item>
        
        <a-descriptions-item label="Discount Type">
          <a-tag :color="getDiscountTypeColor(promoCode.discount_type)">
            {{ formatDiscountType(promoCode.discount_type) }}
          </a-tag>
        </a-descriptions-item>
        
        <a-descriptions-item label="Discount Value">
          <span class="info-value">
            <span v-if="promoCode.discount_type === 'percentage'">
              {{ promoCode.discount_value }}%
            </span>
            <span v-else-if="promoCode.discount_type === 'fixed'">
              ${{ promoCode.discount_value }}
            </span>
            <span v-else>
              Free Ticket
            </span>
          </span>
        </a-descriptions-item>
        
        <a-descriptions-item label="Applicable To">
          <a-tag :color="getApplicableColor(promoCode.applicable_to)">
            {{ formatApplicableTo(promoCode.applicable_to) }}
          </a-tag>
        </a-descriptions-item>
        
        <a-descriptions-item label="Minimum Order Amount">
          <span class="info-value" v-if="promoCode.min_order_amount">
            ${{ promoCode.min_order_amount }}
          </span>
          <span class="text-muted" v-else>—</span>
        </a-descriptions-item>
        
        <a-descriptions-item label="Valid From" :span="2">
          <span class="info-value">{{ formatDate(promoCode.valid_from) }}</span>
        </a-descriptions-item>
        
        <a-descriptions-item label="Valid Until" :span="2">
          <span class="info-value">{{ formatDate(promoCode.valid_until) }}</span>
        </a-descriptions-item>
        
        <a-descriptions-item label="Max Uses">
          <span class="info-value" v-if="promoCode.max_uses">
            {{ promoCode.max_uses }}
          </span>
          <span class="text-muted" v-else>Unlimited</span>
        </a-descriptions-item>
        
        <a-descriptions-item label="Max Uses Per User">
          <span class="info-value">{{ promoCode.max_uses_per_user }}</span>
        </a-descriptions-item>
        
        <a-descriptions-item label="Current Uses">
          <span class="info-value">{{ promoCode.current_uses || 0 }}</span>
        </a-descriptions-item>
        
        <a-descriptions-item label="Created By">
          <span class="info-value" v-if="promoCode.creator">
            {{ promoCode.creator.first_name }} {{ promoCode.creator.last_name }}
          </span>
          <span class="text-muted" v-else>—</span>
        </a-descriptions-item>
        
        <a-descriptions-item label="Description" :span="2" v-if="promoCode.description">
          <div class="info-value">{{ promoCode.description }}</div>
        </a-descriptions-item>
        
        <a-descriptions-item label="Events" :span="2" v-if="promoCode.applicable_to === 'specific_events'">
          <a-space v-if="promoCode.events && promoCode.events.length > 0" size="small" wrap>
            <a-tag v-for="event in promoCode.events" :key="event.id" color="blue">
              {{ event.title }}
            </a-tag>
          </a-space>
          <span class="text-muted" v-else>—</span>
        </a-descriptions-item>
      </a-descriptions>
    </a-card>

    <!-- Usage History Card -->
    <a-card class="usage-history-card" v-if="promoCode">
      <template #title>
        <h2 class="card-title">Usage History</h2>
      </template>
      <a-table
        :columns="usageColumns"
        :data-source="usages.data"
        :pagination="{
          current: usages.current_page,
          pageSize: usages.per_page,
          total: usages.total,
          onChange: handleUsagePageChange,
        }"
        :row-key="(record) => record.id"
      >
        <template #bodyCell="{ column, record }">
          <template v-if="column.key === 'user'">
            <span v-if="record.user">
              {{ record.user.first_name }} {{ record.user.last_name }}
            </span>
            <span class="text-muted" v-else>—</span>
          </template>
          
          <template v-if="column.key === 'order'">
            <a v-if="record.order" @click="handleViewOrder(record.order.id)">
              Order #{{ record.order.id }}
            </a>
            <span class="text-muted" v-else>—</span>
          </template>
          
          <template v-if="column.key === 'discount_applied'">
            <span class="info-value">${{ record.discount_applied }}</span>
          </template>
          
          <template v-if="column.key === 'used_at'">
            <span>{{ formatDate(record.created_at) }}</span>
          </template>
        </template>
      </a-table>
    </a-card>

    <!-- Activity Log Card -->
    <a-card class="activity-log-card" v-if="promoCode">
      <template #title>
        <h2 class="card-title">Activity Log</h2>
      </template>
      <a-list
        :data-source="activities.data"
        :pagination="{
          current: activities.current_page,
          pageSize: activities.per_page,
          total: activities.total,
          onChange: handleActivityPageChange,
        }"
      >
        <template #renderItem="{ item }">
          <a-list-item>
            <a-list-item-meta>
              <template #title>
                <span>{{ item.description }}</span>
              </template>
              <template #description>
                <span class="text-muted">
                  {{ formatDate(item.created_at) }} by {{ item.user?.first_name }} {{ item.user?.last_name }}
                </span>
              </template>
            </a-list-item-meta>
          </a-list-item>
        </template>
      </a-list>
    </a-card>
  </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import DashboardLayout from '../../../Layouts/DashboardLayout.vue';
import Breadcrumb from '../../../Components/Breadcrumb.vue';
import { EditOutlined, ArrowLeftOutlined } from '@ant-design/icons-vue';
import dayjs from 'dayjs';

const page = usePage();

const promoCode = computed(() => page.props.promoCode);
const usages = computed(() => page.props.usages || { data: [] });
const activities = computed(() => page.props.activities || { data: [] });

const breadcrumbItems = ref([
  { label: 'Dashboard', href: '/dashboard' },
  { label: 'Promo Codes', href: '/dashboard/promo-codes' },
  { label: promoCode.value?.code || 'Promo Code Details' },
]);

const usageColumns = [
  {
    title: 'User',
    key: 'user',
    dataIndex: 'user',
  },
  {
    title: 'Order',
    key: 'order',
    dataIndex: 'order',
  },
  {
    title: 'Discount Applied',
    key: 'discount_applied',
    dataIndex: 'discount_applied',
  },
  {
    title: 'Used At',
    key: 'used_at',
    dataIndex: 'created_at',
  },
];

const formatDate = (dateString) => {
  if (!dateString) return '—';
  return dayjs(dateString).format('YYYY-MM-DD HH:mm:ss');
};

const formatDiscountType = (type) => {
  const labels = {
    percentage: 'Percentage',
    fixed: 'Fixed Amount',
    free_ticket: 'Free Ticket',
  };
  return labels[type] || type;
};

const formatApplicableTo = (applicableTo) => {
  const labels = {
    all: 'All Events',
    specific_events: 'Specific Events',
    specific_categories: 'Specific Categories',
  };
  return labels[applicableTo] || applicableTo;
};

const getDiscountTypeColor = (type) => {
  const colors = {
    percentage: 'blue',
    fixed: 'green',
    free_ticket: 'gold',
  };
  return colors[type] || 'default';
};

const getApplicableColor = (applicableTo) => {
  const colors = {
    all: 'blue',
    specific_events: 'green',
    specific_categories: 'orange',
  };
  return colors[applicableTo] || 'default';
};

const handleEdit = () => {
  router.visit(`/dashboard/promo-codes/${promoCode.value.id}/edit`);
};

const handleBack = () => {
  router.visit('/dashboard/promo-codes');
};

const handleViewOrder = (orderId) => {
  // Navigate to order details if route exists
  router.visit(`/dashboard/orders/${orderId}`);
};

const handleUsagePageChange = (page) => {
  router.visit(`/dashboard/promo-codes/${promoCode.value.id}?page=${page}`, {
    preserveState: true,
    preserveScroll: true,
  });
};

const handleActivityPageChange = (page) => {
  router.visit(`/dashboard/promo-codes/${promoCode.value.id}?page=${page}`, {
    preserveState: true,
    preserveScroll: true,
  });
};
</script>

<style scoped>
.promo-code-show-card,
.usage-history-card,
.activity-log-card {
  border-radius: 8px;
  box-shadow: var(--card-shadow, 0 2px 8px rgba(0, 0, 0, 0.06));
  margin-bottom: 16px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.card-title {
  margin: 0;
  font-size: 20px;
  font-weight: 600;
  color: var(--text-primary, #262626);
}

[data-theme="dark"] .card-title {
  color: rgba(255, 255, 255, 0.85);
}

.info-value {
  font-weight: 500;
  color: var(--text-primary, #262626);
}

[data-theme="dark"] .info-value {
  color: rgba(255, 255, 255, 0.85);
}

.code-value {
  font-family: monospace;
  font-size: 16px;
  font-weight: 600;
}

.text-muted {
  color: var(--text-tertiary, #8c8c8c);
}

[data-theme="dark"] .text-muted {
  color: rgba(255, 255, 255, 0.45);
}
</style>

