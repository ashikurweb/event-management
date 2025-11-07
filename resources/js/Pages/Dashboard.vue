<template>
  <DashboardLayout>
    <!-- Stats Cards -->
    <div class="stats-grid">
      <a-card class="stat-card">
        <div class="stat-content">
          <div class="stat-icon" style="background: #e6f7ff; color: #1890ff;">
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
          <div class="stat-icon" style="background: #f6ffed; color: #52c41a;">
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
          <div class="stat-icon" style="background: #fff7e6; color: #faad14;">
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
          <div class="stat-icon" style="background: #fff1f0; color: #ff4d4f;">
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
          <BarChartOutlined style="font-size: 48px; color: #1890ff; opacity: 0.3;" />
          <p>Revenue Chart</p>
        </div>
      </a-card>

      <a-card title="Event Statistics" class="chart-card">
        <div class="chart-placeholder">
          <BarChartOutlined style="font-size: 48px; color: #52c41a; opacity: 0.3;" />
          <p>Event Stats Chart</p>
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
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
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
}

.stat-info {
  flex: 1;
}

.stat-value {
  font-size: 24px;
  font-weight: 600;
  color: #262626;
  line-height: 1.2;
  margin-bottom: 4px;
}

.stat-label {
  font-size: 14px;
  color: #8c8c8c;
}

.stat-trend {
  margin-top: 12px;
  font-size: 12px;
  font-weight: 500;
}

.stat-trend.up {
  color: #52c41a;
}

.charts-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
  gap: 24px;
  margin-bottom: 24px;
}

.chart-card {
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

.chart-placeholder {
  height: 300px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: #8c8c8c;
}

.content-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
  gap: 24px;
}

.content-card {
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

.amount {
  font-weight: 600;
  color: #52c41a;
}

.event-meta {
  display: flex;
  gap: 16px;
  font-size: 12px;
  color: #8c8c8c;
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

