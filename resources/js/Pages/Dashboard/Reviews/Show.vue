<template>
  <DashboardLayout>
    <Breadcrumb :items="breadcrumbItems" />

    <a-card class="review-show-card" v-if="review">
      <template #title>
        <div class="card-header">
          <h2 class="card-title">Review Details</h2>
          <a-space>
            <a-button
              v-if="review.status === 'pending'"
              type="primary"
              @click="handleApprove"
            >
              <template #icon><CheckCircleOutlined /></template>
              Approve
            </a-button>
            <a-button
              v-if="review.status === 'pending'"
              danger
              @click="handleReject"
            >
              <template #icon><CloseCircleOutlined /></template>
              Reject
            </a-button>
            <a-button @click="handleBack">
              <template #icon><ArrowLeftOutlined /></template>
              Back
            </a-button>
          </a-space>
        </div>
      </template>

      <!-- Review Information -->
      <div class="review-info-section">
        <a-descriptions :column="2" bordered>
          <a-descriptions-item label="Event" :span="2">
            <a-typography-link @click="handleViewEvent">
              {{ review.event?.title || '—' }}
            </a-typography-link>
          </a-descriptions-item>

          <a-descriptions-item label="User">
            <div class="user-info">
              <a-avatar :src="getAvatarUrl(review.user?.avatar)" :size="40">
                {{ getUserInitials(review.user) }}
              </a-avatar>
              <div class="user-details">
                <div class="user-name">{{ review.user?.name || '—' }}</div>
                <div class="user-email">{{ review.user?.email || '' }}</div>
              </div>
            </div>
          </a-descriptions-item>

          <a-descriptions-item label="Rating">
            <a-rate :value="review.rating" disabled allow-half />
            <span class="rating-text">({{ review.rating }}/5)</span>
          </a-descriptions-item>

          <a-descriptions-item label="Status">
            <a-tag :color="getStatusColor(review.status)">
              {{ review.status?.charAt(0).toUpperCase() + review.status?.slice(1) }}
            </a-tag>
          </a-descriptions-item>

          <a-descriptions-item label="Verified Attendee">
            <a-tag v-if="review.is_verified_attendee" color="green">
              <template #icon><CheckCircleOutlined /></template>
              Verified
            </a-tag>
            <span v-else class="text-muted">Not Verified</span>
          </a-descriptions-item>

          <a-descriptions-item label="Title" :span="2">
            <span class="info-value">{{ review.title || '—' }}</span>
          </a-descriptions-item>

          <a-descriptions-item label="Comment" :span="2">
            <div class="comment-text">{{ review.comment || '—' }}</div>
          </a-descriptions-item>

          <a-descriptions-item label="Helpful Count">
            <span class="info-value">
              <LikeOutlined /> {{ review.helpful_count || 0 }}
            </span>
          </a-descriptions-item>

          <a-descriptions-item label="Reported Count">
            <span class="info-value" :class="{ 'has-reports': review.reported_count > 0 }">
              <FlagOutlined /> {{ review.reported_count || 0 }}
            </span>
          </a-descriptions-item>

          <a-descriptions-item label="Created At">
            <span class="info-value">{{ formatDate(review.created_at) }}</span>
          </a-descriptions-item>

          <a-descriptions-item label="Updated At">
            <span class="info-value">{{ formatDate(review.updated_at) }}</span>
          </a-descriptions-item>
        </a-descriptions>
      </div>

      <!-- Replies Section -->
      <div class="section-divider"></div>
      <div class="replies-section">
        <div class="section-header">
          <h3 class="section-title">Replies ({{ review.replies?.length || 0 }})</h3>
          <a-button type="primary" @click="showReplyModal = true">
            <template #icon><PlusOutlined /></template>
            Add Reply
          </a-button>
        </div>

        <div v-if="review.replies && review.replies.length > 0" class="replies-list">
          <a-card
            v-for="reply in review.replies"
            :key="reply.id"
            class="reply-card"
            :bordered="true"
          >
            <template #actions>
              <a-button type="link" size="small" @click="handleEditReply(reply)">
                <template #icon><EditOutlined /></template>
                Edit
              </a-button>
              <a-popconfirm
                title="Are you sure you want to delete this reply?"
                ok-text="Yes"
                cancel-text="No"
                @confirm="handleDeleteReply(reply)"
              >
                <a-button type="link" size="small" danger>
                  <template #icon><DeleteOutlined /></template>
                  Delete
                </a-button>
              </a-popconfirm>
            </template>
            <a-card-meta>
              <template #avatar>
                <a-avatar :src="getAvatarUrl(reply.user?.avatar)" :size="40">
                  {{ getUserInitials(reply.user) }}
                </a-avatar>
              </template>
              <template #title>
                <div class="reply-user-info">
                  <span class="reply-user-name">{{ reply.user?.name || '—' }}</span>
                  <span class="reply-date">{{ formatDate(reply.created_at) }}</span>
                </div>
              </template>
              <template #description>
                <div class="reply-comment">{{ reply.comment }}</div>
              </template>
            </a-card-meta>
          </a-card>
        </div>
        <a-empty v-else description="No replies yet" />
      </div>
    </a-card>

    <!-- Reply Modal -->
    <a-modal
      v-model:open="showReplyModal"
      title="Add Reply"
      :confirm-loading="replying"
      @ok="handleSubmitReply"
      @cancel="handleCancelReply"
    >
      <a-form :model="replyForm" layout="vertical">
        <a-form-item
          label="Comment"
          name="comment"
          :rules="[{ required: true, message: 'Please enter a comment' }]"
        >
          <a-textarea
            v-model:value="replyForm.comment"
            :rows="4"
            placeholder="Enter your reply..."
            :maxlength="1000"
            show-count
          />
        </a-form-item>
      </a-form>
    </a-modal>

    <!-- Edit Reply Modal -->
    <a-modal
      v-model:open="showEditReplyModal"
      title="Edit Reply"
      :confirm-loading="replying"
      @ok="handleUpdateReply"
      @cancel="handleCancelEditReply"
    >
      <a-form :model="editReplyForm" layout="vertical">
        <a-form-item
          label="Comment"
          name="comment"
          :rules="[{ required: true, message: 'Please enter a comment' }]"
        >
          <a-textarea
            v-model:value="editReplyForm.comment"
            :rows="4"
            placeholder="Enter your reply..."
            :maxlength="1000"
            show-count
          />
        </a-form-item>
      </a-form>
    </a-modal>
  </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { message } from 'ant-design-vue';
import DashboardLayout from '../../../Layouts/DashboardLayout.vue';
import Breadcrumb from '../../../Components/Breadcrumb.vue';
import {
  ArrowLeftOutlined,
  CheckCircleOutlined,
  CloseCircleOutlined,
  LikeOutlined,
  FlagOutlined,
  PlusOutlined,
  EditOutlined,
  DeleteOutlined,
} from '@ant-design/icons-vue';
import dayjs from 'dayjs';

const page = usePage();
const review = computed(() => page.props.review || null);

const showReplyModal = ref(false);
const showEditReplyModal = ref(false);
const replying = ref(false);
const replyForm = ref({
  comment: '',
});
const editReplyForm = ref({
  id: null,
  comment: '',
});

const breadcrumbItems = ref([
  { label: 'Dashboard', href: '/dashboard' },
  { label: 'Reviews', href: '/dashboard/reviews' },
  { label: 'Review Details' },
]);

const getStatusColor = (status) => {
  const colors = {
    pending: 'orange',
    approved: 'green',
    rejected: 'red',
  };
  return colors[status] || 'default';
};

const getAvatarUrl = (avatar) => {
  if (!avatar) return null;
  if (avatar.startsWith('http')) return avatar;
  return `/storage/${avatar}`;
};

const getUserInitials = (user) => {
  if (!user || !user.name) return 'U';
  const names = user.name.split(' ');
  if (names.length >= 2) {
    return (names[0][0] + names[1][0]).toUpperCase();
  }
  return names[0][0].toUpperCase();
};

const formatDate = (date) => {
  if (!date) return '—';
  return dayjs(date).format('YYYY-MM-DD HH:mm:ss');
};

const handleBack = () => {
  router.visit('/dashboard/reviews');
};

const handleViewEvent = () => {
  if (review.value?.event?.id) {
    router.visit(`/dashboard/events/${review.value.event.id}`);
  }
};

const handleApprove = () => {
  router.post(`/dashboard/reviews/${review.value.id}/approve`, {}, {
    preserveScroll: true,
    onSuccess: () => {
      message.success('Review approved successfully');
    },
  });
};

const handleReject = () => {
  router.post(`/dashboard/reviews/${review.value.id}/reject`, {}, {
    preserveScroll: true,
    onSuccess: () => {
      message.success('Review rejected successfully');
    },
  });
};

const handleSubmitReply = () => {
  if (!replyForm.value.comment.trim()) {
    message.error('Please enter a comment');
    return;
  }

  replying.value = true;
  router.post('/dashboard/review-replies', {
    review_id: review.value.id,
    comment: replyForm.value.comment,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      message.success('Reply added successfully');
      showReplyModal.value = false;
      replyForm.value.comment = '';
      replying.value = false;
    },
    onError: () => {
      replying.value = false;
    },
  });
};

const handleCancelReply = () => {
  showReplyModal.value = false;
  replyForm.value.comment = '';
};

const handleEditReply = (reply) => {
  editReplyForm.value = {
    id: reply.id,
    comment: reply.comment,
  };
  showEditReplyModal.value = true;
};

const handleUpdateReply = () => {
  if (!editReplyForm.value.comment.trim()) {
    message.error('Please enter a comment');
    return;
  }

  replying.value = true;
  router.put(`/dashboard/review-replies/${editReplyForm.value.id}`, {
    comment: editReplyForm.value.comment,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      message.success('Reply updated successfully');
      showEditReplyModal.value = false;
      editReplyForm.value = { id: null, comment: '' };
      replying.value = false;
    },
    onError: () => {
      replying.value = false;
    },
  });
};

const handleCancelEditReply = () => {
  showEditReplyModal.value = false;
  editReplyForm.value = { id: null, comment: '' };
};

const handleDeleteReply = (reply) => {
  router.delete(`/dashboard/review-replies/${reply.id}`, {
    preserveScroll: true,
    onSuccess: () => {
      message.success('Reply deleted successfully');
    },
  });
};
</script>

<style scoped>
.review-show-card {
  border-radius: 8px;
  box-shadow: var(--card-shadow, 0 2px 8px rgba(0, 0, 0, 0.06));
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

.review-info-section {
  margin-bottom: 24px;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.user-details {
  display: flex;
  flex-direction: column;
}

.user-name {
  font-weight: 500;
  font-size: 14px;
}

.user-email {
  font-size: 12px;
  color: var(--text-secondary, #8c8c8c);
}

.rating-text {
  margin-left: 8px;
  font-size: 14px;
  color: var(--text-secondary, #8c8c8c);
}

.info-value {
  color: var(--text-primary, #262626);
  font-size: 14px;
}

.info-value.has-reports {
  color: #ff4d4f;
}

.comment-text {
  color: var(--text-primary, #262626);
  font-size: 14px;
  line-height: 1.6;
  white-space: pre-wrap;
}

.text-muted {
  color: var(--text-tertiary, #8c8c8c);
}

.section-divider {
  height: 1px;
  background: var(--border-color, #f0f0f0);
  margin: 24px 0;
}

[data-theme="dark"] .section-divider {
  background: var(--border-color, #434343);
}

.replies-section {
  margin-top: 24px;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.section-title {
  margin: 0;
  font-size: 18px;
  font-weight: 600;
  color: var(--text-primary, #262626);
}

[data-theme="dark"] .section-title {
  color: rgba(255, 255, 255, 0.85);
}

.replies-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.reply-card {
  border-radius: 8px;
}

.reply-user-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.reply-user-name {
  font-weight: 500;
  font-size: 14px;
}

.reply-date {
  font-size: 12px;
  color: var(--text-secondary, #8c8c8c);
}

.reply-comment {
  color: var(--text-primary, #262626);
  font-size: 14px;
  line-height: 1.6;
  white-space: pre-wrap;
  margin-top: 8px;
}

@media (max-width: 768px) {
  .card-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
  }

  .section-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
  }
}
</style>

