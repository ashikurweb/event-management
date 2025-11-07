<template>
  <DashboardLayout>
    <!-- Stats Cards -->
    <div class="stats-grid">
      <a-card class="stat-card">
        <div class="stat-content">
          <div class="stat-icon stat-icon-primary">
            <CalendarOutlined />
          </div>
          <div class="stat-info">
            <div class="stat-value">1,234</div>
            <div class="stat-label">Total Events</div>
          </div>
        </div>
        <div class="stat-trend up">
          <ArrowUpOutlined /> +12.5%
        </div>
      </a-card>

      <a-card class="stat-card">
        <div class="stat-content">
          <div class="stat-icon stat-icon-success">
            <IdcardOutlined />
          </div>
          <div class="stat-info">
            <div class="stat-value">5,678</div>
            <div class="stat-label">Total Tickets Sold</div>
          </div>
        </div>
        <div class="stat-trend up">
          <ArrowUpOutlined /> +8.2%
        </div>
      </a-card>

      <a-card class="stat-card">
        <div class="stat-content">
          <div class="stat-icon stat-icon-warning">
            <MoneyCollectOutlined />
          </div>
          <div class="stat-info">
            <div class="stat-value">$125,430</div>
            <div class="stat-label">Total Revenue</div>
          </div>
        </div>
        <div class="stat-trend up">
          <ArrowUpOutlined /> +15.3%
        </div>
      </a-card>

      <a-card class="stat-card">
        <div class="stat-content">
          <div class="stat-icon stat-icon-error">
            <UserOutlined />
          </div>
          <div class="stat-info">
            <div class="stat-value">8,901</div>
            <div class="stat-label">Total Users</div>
          </div>
        </div>
        <div class="stat-trend up">
          <ArrowUpOutlined /> +5.7%
        </div>
      </a-card>
    </div>

    <!-- Charts Row -->
    <div class="charts-row">
      <a-card title="Revenue Overview" class="chart-card">
        <div class="chart-placeholder">
          <BarChartOutlined class="chart-icon chart-icon-primary" />
          <p class="chart-text">Revenue Chart</p>
        </div>
      </a-card>

      <a-card title="Event Statistics" class="chart-card">
        <div class="chart-placeholder">
          <BarChartOutlined class="chart-icon chart-icon-success" />
          <p class="chart-text">Event Stats Chart</p>
        </div>
      </a-card>
    </div>

    <!-- Recent Activity & Upcoming Events -->
    <div class="content-row">
      <a-card title="Recent Orders" class="content-card" :bordered="false">
        <a-table
          :columns="orderColumns"
          :data-source="recentOrders"
          :pagination="false"
          size="small"
        >
          <template #bodyCell="{ column, record }">
            <template v-if="column.key === 'status'">
              <a-tag :color="getStatusColor(record.status)">
                {{ record.status }}
              </a-tag>
            </template>
            <template v-if="column.key === 'amount'">
              <span class="amount">${{ record.amount }}</span>
            </template>
          </template>
        </a-table>
      </a-card>

      <a-card title="Upcoming Events" class="content-card" :bordered="false">
        <a-list
          :data-source="upcomingEvents"
          item-layout="horizontal"
        >
          <template #renderItem="{ item }">
            <a-list-item>
              <a-list-item-meta>
                <template #avatar>
                  <a-avatar :src="item.image" shape="square" />
                </template>
                <template #title>
                  <a>{{ item.title }}</a>
                </template>
                <template #description>
                  <div class="event-meta">
                    <span><CalendarOutlined /> {{ item.date }}</span>
                    <span><UserOutlined /> {{ item.attendees }} attendees</span>
                  </div>
                </template>
              </a-list-item-meta>
            </a-list-item>
          </template>
        </a-list>
      </a-card>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { ref } from 'vue';
import DashboardLayout from '../Layouts/DashboardLayout.vue';
import {
  CalendarOutlined,
  IdcardOutlined,
  MoneyCollectOutlined,
  UserOutlined,
  ArrowUpOutlined,
  BarChartOutlined,
} from '@ant-design/icons-vue';

// Table columns
const orderColumns = [
  { title: 'Order ID', dataIndex: 'orderId', key: 'orderId' },
  { title: 'Event', dataIndex: 'event', key: 'event' },
  { title: 'Customer', dataIndex: 'customer', key: 'customer' },
  { title: 'Amount', key: 'amount' },
  { title: 'Status', key: 'status' },
  { title: 'Date', dataIndex: 'date', key: 'date' },
];

// Mock data
const recentOrders = ref([
  {
    key: '1',
    orderId: '#ORD-001',
    event: 'Tech Conference 2024',
    customer: 'John Doe',
    amount: 150,
    status: 'completed',
    date: '2024-01-15',
  },
  {
    key: '2',
    orderId: '#ORD-002',
    event: 'Web Development Workshop',
    customer: 'Jane Smith',
    amount: 75,
    status: 'pending',
    date: '2024-01-16',
  },
  {
    key: '3',
    orderId: '#ORD-003',
    event: 'Design Summit',
    customer: 'Bob Johnson',
    amount: 200,
    status: 'completed',
    date: '2024-01-17',
  },
]);

const upcomingEvents = ref([
  {
    id: 1,
    title: 'Tech Conference 2024',
    date: 'Jan 25, 2024',
    attendees: 250,
    image: null,
  },
  {
    id: 2,
    title: 'Web Development Workshop',
    date: 'Feb 1, 2024',
    attendees: 120,
    image: null,
  },
  {
    id: 3,
    title: 'Design Summit',
    date: 'Feb 10, 2024',
    attendees: 180,
    image: null,
  },
]);

const getStatusColor = (status) => {
  const colors = {
    completed: 'green',
    pending: 'orange',
    cancelled: 'red',
  };
  return colors[status] || 'default';
};
</script>

<style scoped>
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 24px;
  margin-bottom: 24px;
}

.stat-card {
  border-radius: var(--radius-base, 8px);
  box-shadow: var(--card-shadow, 0 2px 8px rgba(0, 0, 0, 0.06));
  background: var(--card-bg, #fff);
  border: 1px solid var(--card-border, #f0f0f0);
  transition: background-color 0.3s, border-color 0.3s, box-shadow 0.3s;
}

:deep(.stat-card .ant-card-body) {
  background: var(--card-bg, #fff) !important;
  color: var(--text-primary, #262626) !important;
}

.stat-content {
  display: flex;
  align-items: center;
  gap: 16px;
}

.stat-icon {
  width: 56px;
  height: 56px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  transition: background-color 0.3s, color 0.3s;
}

.stat-icon-primary {
  background: var(--color-primary-50, #e6f7ff);
  color: var(--color-primary, #1890ff);
}

[data-theme="dark"] .stat-icon-primary {
  background: var(--color-primary-50, #111b26);
  color: var(--color-primary, #40a9ff);
}

.stat-icon-success {
  background: var(--color-success-50, #f6ffed);
  color: var(--color-success, #52c41a);
}

[data-theme="dark"] .stat-icon-success {
  background: var(--color-success-50, #0d1f0a);
  color: var(--color-success, #73d13d);
}

.stat-icon-warning {
  background: var(--color-warning-50, #fffbe6);
  color: var(--color-warning, #faad14);
}

[data-theme="dark"] .stat-icon-warning {
  background: var(--color-warning-50, #1f1a0a);
  color: var(--color-warning, #ffc53d);
}

.stat-icon-error {
  background: var(--color-error-50, #fff1f0);
  color: var(--color-error, #ff4d4f);
}

[data-theme="dark"] .stat-icon-error {
  background: var(--color-error-50, #1f0f0f);
  color: var(--color-error, #ff7875);
}

.stat-info {
  flex: 1;
}

.stat-value {
  font-size: 24px;
  font-weight: 600;
  color: var(--text-primary, #262626);
  line-height: 1.2;
  margin-bottom: 4px;
  transition: color 0.3s;
}

.stat-label {
  font-size: 14px;
  color: var(--text-secondary, #8c8c8c);
  transition: color 0.3s;
}

.stat-trend {
  margin-top: 12px;
  font-size: 12px;
  font-weight: 500;
  transition: color 0.3s;
}

.stat-trend.up {
  color: var(--color-success, #52c41a);
}

.charts-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
  gap: 24px;
  margin-bottom: 24px;
}

.chart-card {
  border-radius: var(--radius-base, 8px);
  box-shadow: var(--card-shadow, 0 2px 8px rgba(0, 0, 0, 0.06));
  background: var(--card-bg, #fff);
  border: 1px solid var(--card-border, #f0f0f0);
  transition: background-color 0.3s, border-color 0.3s, box-shadow 0.3s;
}

:deep(.chart-card .ant-card-head) {
  background: var(--card-bg, #fff) !important;
  border-bottom: 1px solid var(--card-border, #f0f0f0) !important;
}

:deep(.chart-card .ant-card-head-title) {
  color: var(--text-primary, #262626) !important;
}

:deep(.chart-card .ant-card-body) {
  background: var(--card-bg, #fff) !important;
  color: var(--text-primary, #262626) !important;
}

.chart-placeholder {
  height: 300px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.chart-icon {
  font-size: 48px;
  opacity: 0.3;
  transition: color 0.3s;
}

.chart-icon-primary {
  color: var(--color-primary, #1890ff);
}

.chart-icon-success {
  color: var(--color-success, #52c41a);
}

.chart-icon {
  font-size: 48px;
  opacity: 0.3;
  transition: color 0.3s;
}

.chart-icon-primary {
  color: var(--color-primary, #1890ff);
}

.chart-icon-success {
  color: var(--color-success, #52c41a);
}

.chart-text {
  color: var(--text-tertiary, #8c8c8c);
  margin-top: 12px;
  transition: color 0.3s;
}

.content-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
  gap: 24px;
}

.content-card {
  border-radius: var(--radius-base, 8px);
  box-shadow: var(--card-shadow, 0 2px 8px rgba(0, 0, 0, 0.06));
  background: var(--card-bg, #fff);
  border: 1px solid var(--card-border, #f0f0f0);
  transition: background-color 0.3s, border-color 0.3s, box-shadow 0.3s;
}

:deep(.content-card .ant-card-body) {
  background: var(--card-bg, #fff) !important;
  color: var(--text-primary, #262626) !important;
}

:deep(.content-card .ant-card-head) {
  background: var(--card-bg, #fff) !important;
  border-bottom: 1px solid var(--card-border, #f0f0f0) !important;
}

:deep(.content-card .ant-card-head-title) {
  color: var(--text-primary, #262626) !important;
}

:deep(.content-card .ant-table) {
  background: var(--card-bg, #fff) !important;
  color: var(--text-primary, #262626) !important;
}

:deep(.content-card .ant-table-thead > tr > th) {
  background: var(--bg-elevated, #fafafa) !important;
  color: var(--text-primary, #262626) !important;
  border-bottom: 1px solid var(--border-color-light, #f0f0f0) !important;
}

:deep(.content-card .ant-table-tbody > tr > td) {
  border-bottom: 1px solid var(--border-color-light, #f0f0f0) !important;
  color: var(--text-primary, #262626) !important;
}

:deep(.content-card .ant-table-tbody > tr:hover > td) {
  background: var(--bg-hover, #fafafa) !important;
}

:deep(.content-card .ant-list-item) {
  border-bottom: 1px solid var(--border-color-light, #f0f0f0) !important;
}

:deep(.content-card .ant-list-item-meta-title) {
  color: var(--text-primary, #262626) !important;
}

:deep(.content-card .ant-list-item-meta-title > a) {
  color: var(--text-link, #1890ff) !important;
}

:deep(.content-card .ant-list-item-meta-description) {
  color: var(--text-secondary, #595959) !important;
}

:deep(.content-card .ant-list-item) {
  border-bottom: 1px solid var(--border-color-light, #f0f0f0) !important;
}

:deep(.content-card .ant-list-item-meta-title) {
  color: var(--text-primary, #262626);
}

:deep(.content-card .ant-list-item-meta-title > a) {
  color: var(--text-primary, #262626);
}

:deep(.content-card .ant-list-item-meta-title > a:hover) {
  color: var(--color-primary, #1890ff);
}

:deep(.content-card .ant-list-item-meta-description) {
  color: var(--text-secondary, #8c8c8c);
}

.amount {
  font-weight: 600;
  color: var(--color-success, #52c41a);
  transition: color 0.3s;
}

.event-meta {
  display: flex;
  gap: 16px;
  font-size: 12px;
  color: var(--text-tertiary, #8c8c8c);
  transition: color 0.3s;
}

.event-meta span {
  display: flex;
  align-items: center;
  gap: 4px;
}

@media (max-width: 768px) {
  .stats-grid,
  .charts-row,
  .content-row {
    grid-template-columns: 1fr;
  }
}
</style>

