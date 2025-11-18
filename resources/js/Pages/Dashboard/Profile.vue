<template>
  <DashboardLayout>
    <div class="profile-page">
      <!-- Profile Header -->
      <a-card class="profile-header-card" :bordered="false">
        <template #extra>
          <a-space>
            <a-button v-if="!isEditing" type="primary" @click="startEditing">
              <template #icon><EditOutlined /></template>
              Edit Profile
            </a-button>
            <a-space v-else>
              <a-button @click="cancelEditing">Cancel</a-button>
              <a-button type="primary" :loading="saving" @click="handleSave">
                <template #icon><SaveOutlined /></template>
                Save Changes
              </a-button>
            </a-space>
          </a-space>
        </template>
        <div class="profile-header">
          <div class="profile-avatar-section">
            <a-avatar 
              :src="avatarUrl" 
              :size="120"
              class="profile-avatar"
            >
              {{ userInitials }}
            </a-avatar>
            <a-upload
              v-if="isEditing"
              :custom-request="handleAvatarUpload"
              :show-upload-list="false"
              :before-upload="beforeUpload"
              accept="image/*"
              class="avatar-uploader"
            >
              <div class="avatar-upload-overlay">
                <CameraOutlined />
                <div class="upload-text">Change Photo</div>
              </div>
            </a-upload>
          </div>
          
          <div class="profile-info-header">
            <h1 class="profile-name">
              {{ fullName }}
            </h1>
            <p class="profile-email">
              <MailOutlined /> {{ user?.email }}
            </p>
            <div class="profile-meta">
              <a-tag :color="getStatusColor(user?.status)" class="status-tag">
                {{ user?.status ? user.status.charAt(0).toUpperCase() + user.status.slice(1) : 'Active' }}
              </a-tag>
              <span class="meta-item" v-if="user?.email_verified_at">
                <CheckCircleOutlined style="color: #52c41a; margin-right: 4px;" />
                Email Verified
              </span>
              <span class="meta-item" v-if="user?.last_login_at">
                <ClockCircleOutlined style="margin-right: 4px;" />
                Last login: {{ $formatDateTime(user.last_login_at) }}
              </span>
            </div>
          </div>
        </div>
      </a-card>

      <!-- Profile Content Tabs -->
      <a-card class="profile-content-card" :bordered="false">
        <a-tabs v-model:activeKey="activeTab" class="profile-tabs">
          <!-- Personal Information Tab -->
          <a-tab-pane key="personal" tab="Personal Information">
            <div class="tab-content">
              <a-form
                v-if="isEditing"
                :model="formData"
                layout="vertical"
                ref="personalFormRef"
              >
                <a-row :gutter="16">
                  <a-col :xs="24" :md="12">
                    <a-form-item label="First Name" required>
                      <a-input v-model:value="formData.first_name" placeholder="First Name" />
                    </a-form-item>
                  </a-col>
                  <a-col :xs="24" :md="12">
                    <a-form-item label="Last Name" required>
                      <a-input v-model:value="formData.last_name" placeholder="Last Name" />
                    </a-form-item>
                  </a-col>
                </a-row>

                <a-row :gutter="16">
                  <a-col :xs="24" :md="12">
                    <a-form-item label="Email" required>
                      <a-input v-model:value="formData.email" type="email" placeholder="Email" />
                    </a-form-item>
                  </a-col>
                  <a-col :xs="24" :md="12">
                    <a-form-item label="Phone">
                      <a-input v-model:value="formData.phone" placeholder="Phone Number" />
                    </a-form-item>
                  </a-col>
                </a-row>

                <a-row :gutter="16">
                  <a-col :xs="24" :md="12">
                    <a-form-item label="Date of Birth">
                      <a-date-picker
                        v-model:value="formData.date_of_birth"
                        style="width: 100%"
                        format="YYYY-MM-DD"
                        placeholder="Select date of birth"
                      />
                    </a-form-item>
                  </a-col>
                  <a-col :xs="24" :md="12">
                    <a-form-item label="Gender">
                      <a-select v-model:value="formData.gender" placeholder="Select gender" allow-clear>
                        <a-select-option value="male">Male</a-select-option>
                        <a-select-option value="female">Female</a-select-option>
                        <a-select-option value="other">Other</a-select-option>
                        <a-select-option value="prefer_not_to_say">Prefer not to say</a-select-option>
                      </a-select>
                    </a-form-item>
                  </a-col>
                </a-row>

                <a-form-item label="Bio">
                  <a-textarea
                    v-model:value="formData.bio"
                    placeholder="Tell us about yourself..."
                    :rows="4"
                    :maxlength="500"
                    show-count
                  />
                </a-form-item>

                <a-descriptions :column="2" bordered class="info-descriptions read-only-info">
                  <a-descriptions-item label="User ID" :span="1">
                    <span class="info-value text-muted">{{ user?.uuid }}</span>
                  </a-descriptions-item>
                  <a-descriptions-item label="Member Since" :span="1">
                    <span class="info-value">{{ $formatDate(user?.created_at) }}</span>
                  </a-descriptions-item>
                </a-descriptions>
              </a-form>

              <a-descriptions v-else :column="2" bordered class="info-descriptions">
                <a-descriptions-item label="First Name" :span="1">
                  <span class="info-value">{{ user?.first_name || '—' }}</span>
                </a-descriptions-item>
                
                <a-descriptions-item label="Last Name" :span="1">
                  <span class="info-value">{{ user?.last_name || '—' }}</span>
                </a-descriptions-item>
                
                <a-descriptions-item label="Email" :span="1">
                  <span class="info-value">
                    <a :href="`mailto:${user?.email}`">{{ user?.email }}</a>
                  </span>
                </a-descriptions-item>
                
                <a-descriptions-item label="Phone" :span="1">
                  <span class="info-value" v-if="user?.phone">
                    <a :href="`tel:${user?.phone}`">{{ user?.phone }}</a>
                  </span>
                  <span class="text-muted" v-else>—</span>
                </a-descriptions-item>
                
                <a-descriptions-item label="Date of Birth" :span="1">
                  <span class="info-value" v-if="user?.date_of_birth">
                    {{ $formatDate(user.date_of_birth) }}
                  </span>
                  <span class="text-muted" v-else>—</span>
                </a-descriptions-item>
                
                <a-descriptions-item label="Gender" :span="1">
                  <span class="info-value" v-if="user?.gender">
                    {{ formatGender(user.gender) }}
                  </span>
                  <span class="text-muted" v-else>—</span>
                </a-descriptions-item>
                
                <a-descriptions-item label="Bio" :span="2">
                  <span class="info-value" v-if="user?.bio">
                    {{ user.bio }}
                  </span>
                  <span class="text-muted" v-else>—</span>
                </a-descriptions-item>
                
                <a-descriptions-item label="User ID" :span="1">
                  <span class="info-value text-muted">{{ user?.uuid }}</span>
                </a-descriptions-item>
                
                <a-descriptions-item label="Member Since" :span="1">
                  <span class="info-value">{{ $formatDate(user?.created_at) }}</span>
                </a-descriptions-item>
              </a-descriptions>
            </div>
          </a-tab-pane>

          <!-- Profile Details Tab -->
          <a-tab-pane key="profile" tab="Profile Details">
            <div class="tab-content">
              <a-form
                v-if="isEditing"
                :model="formData"
                layout="vertical"
                ref="profileFormRef"
              >
                <a-form-item label="Company Name">
                  <a-input v-model:value="formData.company_name" placeholder="Company Name" />
                </a-form-item>

                <a-form-item label="Website">
                  <a-input v-model:value="formData.website" type="url" placeholder="https://example.com" />
                </a-form-item>

                <a-form-item label="Address Line 1">
                  <a-input v-model:value="formData.address_line1" placeholder="Address Line 1" />
                </a-form-item>

                <a-form-item label="Address Line 2">
                  <a-input v-model:value="formData.address_line2" placeholder="Address Line 2" />
                </a-form-item>

                <a-row :gutter="16">
                  <a-col :xs="24" :md="12">
                    <a-form-item label="City">
                      <a-input v-model:value="formData.city" placeholder="City" />
                    </a-form-item>
                  </a-col>
                  <a-col :xs="24" :md="12">
                    <a-form-item label="State">
                      <a-input v-model:value="formData.state" placeholder="State" />
                    </a-form-item>
                  </a-col>
                </a-row>

                <a-row :gutter="16">
                  <a-col :xs="24" :md="12">
                    <a-form-item label="Country">
                      <a-input v-model:value="formData.country" placeholder="Country" />
                    </a-form-item>
                  </a-col>
                  <a-col :xs="24" :md="12">
                    <a-form-item label="Postal Code">
                      <a-input v-model:value="formData.postal_code" placeholder="Postal Code" />
                    </a-form-item>
                  </a-col>
                </a-row>

                <a-row :gutter="16">
                  <a-col :xs="24" :md="12">
                    <a-form-item label="Timezone">
                      <a-input v-model:value="formData.timezone" placeholder="UTC" />
                    </a-form-item>
                  </a-col>
                  <a-col :xs="24" :md="12">
                    <a-form-item label="Language">
                      <a-input v-model:value="formData.language" placeholder="en" />
                    </a-form-item>
                  </a-col>
                </a-row>
              </a-form>

              <div v-else>
                <div v-if="user?.profile" class="profile-details">
                  <a-descriptions :column="2" bordered class="info-descriptions">
                    <a-descriptions-item label="Company Name" :span="2">
                      <span class="info-value" v-if="user.profile.company_name">
                        {{ user.profile.company_name }}
                      </span>
                      <span class="text-muted" v-else>—</span>
                    </a-descriptions-item>
                    
                    <a-descriptions-item label="Website" :span="2">
                      <span class="info-value" v-if="user.profile.website">
                        <a :href="user.profile.website" target="_blank" rel="noopener noreferrer">
                          <LinkOutlined style="margin-right: 4px" />
                          {{ user.profile.website }}
                        </a>
                      </span>
                      <span class="text-muted" v-else>—</span>
                    </a-descriptions-item>
                    
                    <a-descriptions-item label="Address Line 1" :span="2">
                      <span class="info-value" v-if="user.profile.address_line1">
                        {{ user.profile.address_line1 }}
                      </span>
                      <span class="text-muted" v-else>—</span>
                    </a-descriptions-item>
                    
                    <a-descriptions-item label="Address Line 2" :span="2">
                      <span class="info-value" v-if="user.profile.address_line2">
                        {{ user.profile.address_line2 }}
                      </span>
                      <span class="text-muted" v-else>—</span>
                    </a-descriptions-item>
                    
                    <a-descriptions-item label="City" :span="1">
                      <span class="info-value" v-if="user.profile.city">
                        {{ user.profile.city }}
                      </span>
                      <span class="text-muted" v-else>—</span>
                    </a-descriptions-item>
                    
                    <a-descriptions-item label="State" :span="1">
                      <span class="info-value" v-if="user.profile.state">
                        {{ user.profile.state }}
                      </span>
                      <span class="text-muted" v-else>—</span>
                    </a-descriptions-item>
                    
                    <a-descriptions-item label="Country" :span="1">
                      <span class="info-value" v-if="user.profile.country">
                        {{ user.profile.country }}
                      </span>
                      <span class="text-muted" v-else>—</span>
                    </a-descriptions-item>
                    
                    <a-descriptions-item label="Postal Code" :span="1">
                      <span class="info-value" v-if="user.profile.postal_code">
                        {{ user.profile.postal_code }}
                      </span>
                      <span class="text-muted" v-else>—</span>
                    </a-descriptions-item>
                    
                    <a-descriptions-item label="Full Address" :span="2">
                      <span class="info-value" v-if="user.profile.full_address">
                        {{ user.profile.full_address }}
                      </span>
                      <span class="text-muted" v-else>—</span>
                    </a-descriptions-item>
                    
                    <a-descriptions-item label="Timezone" :span="1">
                      <span class="info-value">{{ user.profile.timezone || 'UTC' }}</span>
                    </a-descriptions-item>
                    
                    <a-descriptions-item label="Language" :span="1">
                      <span class="info-value">{{ user.profile.language || 'en' }}</span>
                    </a-descriptions-item>
                    
                    <a-descriptions-item label="Preferences" :span="2" v-if="user.profile.preferences">
                      <div class="preferences-display">
                        <a-tag v-for="(value, key) in user.profile.preferences" :key="key" class="preference-tag">
                          {{ key }}: {{ value }}
                        </a-tag>
                      </div>
                    </a-descriptions-item>
                  </a-descriptions>
                </div>
                <a-empty v-else description="No profile details available" />
              </div>
            </div>
          </a-tab-pane>

          <!-- Social Accounts Tab -->
          <a-tab-pane key="social" tab="Social Accounts">
            <div class="tab-content">
              <div v-if="user?.social_accounts && user.social_accounts.length > 0" class="social-accounts">
                <a-list
                  :data-source="user.social_accounts"
                  :grid="{ gutter: 16, xs: 1, sm: 2, md: 2, lg: 3, xl: 3, xxl: 3 }"
                >
                  <template #renderItem="{ item }">
                    <a-list-item>
                      <a-card class="social-account-card" :hoverable="true">
                        <div class="social-account-content">
                          <div class="social-account-header">
                            <a-avatar 
                              :src="item.avatar" 
                              :size="48"
                              class="social-avatar"
                            >
                              {{ getProviderInitial(item.provider) }}
                            </a-avatar>
                            <div class="social-provider-info">
                              <h3 class="provider-name">{{ formatProviderName(item.provider) }}</h3>
                              <p class="provider-email" v-if="item.email">{{ item.email }}</p>
                            </div>
                          </div>
                          <div class="social-account-footer">
                            <a-tag :color="getProviderColor(item.provider)" class="provider-tag">
                              {{ item.provider }}
                            </a-tag>
                            <span class="connected-date">
                              Connected {{ $formatDate(item.created_at) }}
                            </span>
                          </div>
                        </div>
                      </a-card>
                    </a-list-item>
                  </template>
                </a-list>
              </div>
              <a-empty v-else description="No social accounts connected" />
            </div>
          </a-tab-pane>
        </a-tabs>
      </a-card>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { ref, computed, reactive } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import DashboardLayout from '../../Layouts/DashboardLayout.vue';
import {
  MailOutlined,
  CheckCircleOutlined,
  ClockCircleOutlined,
  CameraOutlined,
  LinkOutlined,
  EditOutlined,
  SaveOutlined,
} from '@ant-design/icons-vue';
import { message } from 'ant-design-vue';

const page = usePage();
const user = computed(() => page.props.user || null);
const activeTab = ref('personal');
const isEditing = ref(false);
const saving = ref(false);
const personalFormRef = ref(null);
const profileFormRef = ref(null);

const formData = reactive({
  first_name: '',
  last_name: '',
  email: '',
  phone: '',
  date_of_birth: null,
  gender: null,
  bio: '',
  company_name: '',
  website: '',
  address_line1: '',
  address_line2: '',
  city: '',
  state: '',
  country: '',
  postal_code: '',
  timezone: 'UTC',
  language: 'en',
});

const fullName = computed(() => {
  if (!user.value) return 'User';
  return `${user.value.first_name || ''} ${user.value.last_name || ''}`.trim() || 'User';
});

const userInitials = computed(() => {
  if (!user.value) return 'U';
  const firstName = formData.first_name || user.value.first_name || '';
  const lastName = formData.last_name || user.value.last_name || '';
  const initials = (firstName[0] || '') + (lastName[0] || '');
  return initials.toUpperCase() || 'U';
});

const avatarUrl = computed(() => {
  if (!user.value) return null;
  const avatar = user.value.avatar;
  if (!avatar) return null;
  if (avatar.startsWith('http')) return avatar;
  return `/storage/${avatar}`;
});

const startEditing = () => {
  isEditing.value = true;
  // Populate form with current user data
  formData.first_name = user.value?.first_name || '';
  formData.last_name = user.value?.last_name || '';
  formData.email = user.value?.email || '';
  formData.phone = user.value?.phone || '';
  formData.date_of_birth = user.value?.date_of_birth ? dayjs(user.value.date_of_birth) : null;
  formData.gender = user.value?.gender || null;
  formData.bio = user.value?.bio || '';
  
  if (user.value?.profile) {
    formData.company_name = user.value.profile.company_name || '';
    formData.website = user.value.profile.website || '';
    formData.address_line1 = user.value.profile.address_line1 || '';
    formData.address_line2 = user.value.profile.address_line2 || '';
    formData.city = user.value.profile.city || '';
    formData.state = user.value.profile.state || '';
    formData.country = user.value.profile.country || '';
    formData.postal_code = user.value.profile.postal_code || '';
    formData.timezone = user.value.profile.timezone || 'UTC';
    formData.language = user.value.profile.language || 'en';
  }
};

const cancelEditing = () => {
  isEditing.value = false;
  // Reset form data
  Object.keys(formData).forEach(key => {
    if (key === 'timezone') {
      formData[key] = 'UTC';
    } else if (key === 'language') {
      formData[key] = 'en';
    } else {
      formData[key] = key === 'date_of_birth' ? null : '';
    }
  });
};

const handleSave = () => {
  saving.value = true;
  
  const data = {
    ...formData,
    date_of_birth: formData.date_of_birth ? formData.date_of_birth.format('YYYY-MM-DD') : null,
  };

  router.put('/dashboard/profile', data, {
    preserveScroll: true,
    onSuccess: () => {
      message.success('Profile updated successfully!');
      isEditing.value = false;
      saving.value = false;
      // Reload auth and user data to update header with new profile info
      router.reload({ only: ['auth', 'user'] });
    },
    onError: (errors) => {
      saving.value = false;
      if (errors.message) {
        message.error(errors.message);
      } else {
        message.error('Failed to update profile. Please check the form for errors.');
      }
    },
  });
};

const handleAvatarUpload = async ({ file, onSuccess, onError }) => {
  const formData = new FormData();
  formData.append('avatar', file);

  // Use Inertia.js router for file upload - automatically handles CSRF token
  router.post('/dashboard/profile/avatar', formData, {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: (page) => {
      // Flash message will be shown automatically by NotificationContainer
      onSuccess({ success: true, file }, file);
      // Reload auth and user data to update avatar in header
      router.reload({ only: ['auth', 'user'] });
    },
    onError: (errors) => {
      let errorMessage = 'Failed to upload avatar';
      
      if (errors.message) {
        errorMessage = errors.message;
      } else if (errors.errors && errors.errors.avatar) {
        errorMessage = Array.isArray(errors.errors.avatar) 
          ? errors.errors.avatar[0] 
          : errors.errors.avatar;
      } else if (typeof errors === 'string') {
        errorMessage = errors;
      }
      
      message.error(errorMessage);
      onError(new Error(errorMessage));
    },
  });
};

const beforeUpload = (file) => {
  const isJpgOrPng = file.type === 'image/jpeg' || file.type === 'image/png' || file.type === 'image/gif' || file.type === 'image/webp';
  if (!isJpgOrPng) {
    message.error('You can only upload JPG/PNG/GIF/WebP file!');
  }
  const isLt2M = file.size / 1024 / 1024 < 2;
  if (!isLt2M) {
    message.error('Image must smaller than 2MB!');
  }
  return isJpgOrPng && isLt2M;
};

const getStatusColor = (status) => {
  const colors = {
    active: 'green',
    inactive: 'default',
    suspended: 'orange',
    deleted: 'red',
  };
  return colors[status] || 'default';
};

const formatGender = (gender) => {
  if (!gender) return '—';
  return gender.charAt(0).toUpperCase() + gender.slice(1).replace('_', ' ');
};

const formatProviderName = (provider) => {
  if (!provider) return 'Unknown';
  return provider.charAt(0).toUpperCase() + provider.slice(1);
};

const getProviderInitial = (provider) => {
  if (!provider) return '?';
  return provider.charAt(0).toUpperCase();
};

const getProviderColor = (provider) => {
  const colors = {
    google: 'red',
    facebook: 'blue',
    github: 'default',
    twitter: 'cyan',
    linkedin: 'blue',
  };
  return colors[provider.toLowerCase()] || 'default';
};
</script>

<style scoped>
.profile-page {
  max-width: 1200px;
  margin: 0 auto;
}

/* Profile Header Card */
.profile-header-card {
  margin-bottom: 24px;
  border-radius: 12px;
  box-shadow: var(--card-shadow, 0 2px 8px rgba(0, 0, 0, 0.06));
  background: var(--card-bg, #fff);
  border: 1px solid var(--card-border, #f0f0f0);
  overflow: hidden;
}

.profile-header {
  display: flex;
  align-items: center;
  gap: 32px;
  padding: 24px;
}

.profile-avatar-section {
  position: relative;
  flex-shrink: 0;
}

.profile-avatar {
  border: 4px solid var(--border-color-light, #f0f0f0);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.avatar-uploader {
  position: absolute;
  bottom: 0;
  right: 0;
  width: 120px;
  height: 120px;
}

.avatar-uploader :deep(.ant-upload) {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  overflow: hidden;
  background: transparent;
  border: none;
}

.avatar-upload-overlay {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  background: rgba(0, 0, 0, 0.6);
  color: white;
  padding: 8px;
  text-align: center;
  cursor: pointer;
  transition: background 0.3s;
  border-radius: 0 0 50% 50%;
}

.avatar-upload-overlay:hover {
  background: rgba(0, 0, 0, 0.8);
}

.upload-text {
  font-size: 11px;
  margin-top: 2px;
}

.profile-info-header {
  flex: 1;
}

.profile-name {
  font-size: 32px;
  font-weight: 600;
  margin: 0 0 8px 0;
  color: var(--text-primary, #262626);
}

.profile-email {
  font-size: 16px;
  color: var(--text-secondary, #595959);
  margin: 0 0 16px 0;
  display: flex;
  align-items: center;
  gap: 8px;
}

.profile-meta {
  display: flex;
  align-items: center;
  gap: 16px;
  flex-wrap: wrap;
}

.status-tag {
  font-weight: 500;
}

.meta-item {
  font-size: 14px;
  color: var(--text-secondary, #595959);
  display: flex;
  align-items: center;
}

/* Profile Content Card */
.profile-content-card {
  border-radius: 12px;
  box-shadow: var(--card-shadow, 0 2px 8px rgba(0, 0, 0, 0.06));
  background: var(--card-bg, #fff);
  border: 1px solid var(--card-border, #f0f0f0);
}

.profile-tabs :deep(.ant-tabs-nav) {
  margin-bottom: 24px;
  padding: 0 24px;
}

.profile-tabs :deep(.ant-tabs-content-holder) {
  padding: 0 24px 24px;
}

.tab-content {
  min-height: 400px;
}

.info-descriptions {
  background: var(--card-bg, #fff);
}

.info-descriptions :deep(.ant-descriptions-item-label) {
  font-weight: 600;
  color: var(--text-primary, #262626);
  background: var(--bg-hover, #fafafa);
  width: 200px;
}

.info-descriptions :deep(.ant-descriptions-item-content) {
  color: var(--text-primary, #262626);
}

.read-only-info {
  margin-top: 24px;
}

.info-value {
  color: var(--text-primary, #262626);
  font-size: 15px;
}

.info-value a {
  color: var(--primary-color, #1890ff);
  text-decoration: none;
}

.info-value a:hover {
  text-decoration: underline;
}

.text-muted {
  color: var(--text-tertiary, #8c8c8c);
  font-style: italic;
}

.preferences-display {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.preference-tag {
  margin: 0;
}

/* Social Accounts */
.social-accounts {
  margin-top: 16px;
}

.social-account-card {
  border-radius: 8px;
  transition: all 0.3s;
  height: 100%;
}

.social-account-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.social-account-content {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.social-account-header {
  display: flex;
  align-items: center;
  gap: 12px;
}

.social-avatar {
  flex-shrink: 0;
}

.social-provider-info {
  flex: 1;
  min-width: 0;
}

.provider-name {
  font-size: 16px;
  font-weight: 600;
  margin: 0 0 4px 0;
  color: var(--text-primary, #262626);
}

.provider-email {
  font-size: 13px;
  color: var(--text-secondary, #595959);
  margin: 0;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.social-account-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 12px;
  border-top: 1px solid var(--border-color-light, #f0f0f0);
}

.provider-tag {
  font-weight: 500;
}

.connected-date {
  font-size: 12px;
  color: var(--text-tertiary, #8c8c8c);
}

/* Dark Theme Support */
[data-theme="dark"] .profile-header-card,
[data-theme="dark"] .profile-content-card {
  background: var(--card-bg, #1f1f1f);
  border-color: var(--card-border, #2d2d2d);
}

[data-theme="dark"] .profile-name {
  color: rgba(255, 255, 255, 0.85);
}

[data-theme="dark"] .profile-email {
  color: rgba(255, 255, 255, 0.65);
}

[data-theme="dark"] .info-descriptions :deep(.ant-descriptions-item-label) {
  background: var(--bg-hover, #2d2d2d);
  color: rgba(255, 255, 255, 0.85);
}

[data-theme="dark"] .info-value {
  color: rgba(255, 255, 255, 0.85);
}

[data-theme="dark"] .provider-name {
  color: rgba(255, 255, 255, 0.85);
}

[data-theme="dark"] .social-account-footer {
  border-top-color: var(--border-color-light, #2d2d2d);
}

/* Dark Theme - Social Account Tags */
[data-theme="dark"] .social-account-card :deep(.ant-tag) {
  color: rgba(255, 255, 255, 0.85) !important;
  border-color: rgba(255, 255, 255, 0.2) !important;
  background: transparent !important;
}

[data-theme="dark"] .social-account-card :deep(.ant-tag.ant-tag-red) {
  color: rgba(255, 255, 255, 0.85) !important;
  border-color: rgba(255, 77, 79, 0.5) !important;
  background: rgba(255, 77, 79, 0.1) !important;
}

[data-theme="dark"] .social-account-card :deep(.ant-tag.ant-tag-blue) {
  color: rgba(255, 255, 255, 0.85) !important;
  border-color: rgba(24, 144, 255, 0.5) !important;
  background: rgba(24, 144, 255, 0.1) !important;
}

[data-theme="dark"] .social-account-card :deep(.ant-tag.ant-tag-cyan) {
  color: rgba(255, 255, 255, 0.85) !important;
  border-color: rgba(19, 194, 194, 0.5) !important;
  background: rgba(19, 194, 194, 0.1) !important;
}

[data-theme="dark"] .social-account-card :deep(.ant-tag.ant-tag-default) {
  color: rgba(255, 255, 255, 0.85) !important;
  border-color: rgba(255, 255, 255, 0.2) !important;
  background: transparent !important;
}

[data-theme="dark"] .provider-email {
  color: rgba(255, 255, 255, 0.65) !important;
}

[data-theme="dark"] .connected-date {
  color: rgba(255, 255, 255, 0.65) !important;
}

[data-theme="dark"] .social-account-card {
  background: var(--card-bg, #1f1f1f) !important;
  border-color: var(--card-border, #2d2d2d) !important;
}

[data-theme="dark"] .social-account-card :deep(.ant-card-body) {
  background: var(--card-bg, #1f1f1f) !important;
}

/* Dark Theme - Form Labels */
[data-theme="dark"] .profile-content-card :deep(.ant-form-item-label > label) {
  color: rgba(255, 255, 255, 0.85) !important;
}

[data-theme="dark"] .profile-content-card :deep(.ant-form-item-label > label.ant-form-item-required:not(.ant-form-item-required-mark-optional)::before) {
  color: rgba(255, 77, 79, 0.85) !important;
}

/* Dark Theme - Tabs */
[data-theme="dark"] .profile-tabs :deep(.ant-tabs-tab) {
  color: rgba(255, 255, 255, 0.65) !important;
}

[data-theme="dark"] .profile-tabs :deep(.ant-tabs-tab:hover) {
  color: rgba(255, 255, 255, 0.85) !important;
}

[data-theme="dark"] .profile-tabs :deep(.ant-tabs-tab.ant-tabs-tab-active .ant-tabs-tab-btn) {
  color: rgba(24, 144, 255, 1) !important;
}

[data-theme="dark"] .profile-tabs :deep(.ant-tabs-ink-bar) {
  background: rgba(24, 144, 255, 1) !important;
}

/* Responsive Design */
@media (max-width: 768px) {
  .profile-header {
    flex-direction: column;
    text-align: center;
    gap: 24px;
  }

  .profile-name {
    font-size: 24px;
  }

  .profile-meta {
    justify-content: center;
  }

  .profile-tabs :deep(.ant-tabs-nav) {
    padding: 0 16px;
  }

  .profile-tabs :deep(.ant-tabs-content-holder) {
    padding: 0 16px 16px;
  }

  .info-descriptions :deep(.ant-descriptions-item-label) {
    width: 120px;
  }

  .social-account-footer {
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
  }
}

@media (max-width: 480px) {
  .profile-page {
    padding: 0;
  }

  .profile-header {
    padding: 16px;
  }

  .profile-avatar {
    width: 100px !important;
    height: 100px !important;
  }

  .avatar-uploader {
    width: 100px;
    height: 100px;
  }

  .profile-name {
    font-size: 20px;
  }

  .profile-meta {
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
  }
}
</style>
