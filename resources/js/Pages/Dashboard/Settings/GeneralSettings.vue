<script setup>
import { ref, onMounted, h } from 'vue';
import DashboardLayout from '../../../Layouts/DashboardLayout.vue';
import {
  RightOutlined,
  SaveOutlined,
  WifiOutlined,
  MailOutlined,
  DatabaseOutlined,
  DownloadOutlined,
  DeleteOutlined,
  ClockCircleOutlined,
} from '@ant-design/icons-vue';
import { router, usePage } from '@inertiajs/vue3';
import { mailRules } from '../../../utils/validationRules';
import { useNotifications } from '../../../Composables/useNotifications';
import Input from '../../../Components/Input.vue';
import { Modal } from 'ant-design-vue';

const props = defineProps({
  mailConfig: {
    type: Object,
    default: () => ({
      mail_mailer: '',
      mail_host: '',
      mail_port: '2525',
      mail_username: '',
      mail_password: '',
      mail_encryption: 'tls',
      mail_from_address: '',
    }),
  },
  activeTab: {
    type: String,
    default: 'mail-config',
  },
  backups: {
    type: Array,
    default: () => [],
  },
  // backupConfig prop removed - scheduled backup not needed
});

const page = usePage();
const { notifySuccess, notifyError } = useNotifications();
const activeTab = ref(props.activeTab || 'mail-config');
const saving = ref(false);
const testing = ref(false);
const creatingBackup = ref(false);
const downloadingBackup = ref(false);
const downloadProgress = ref(0);
const showDownloadModal = ref(false);
const currentDownloadFile = ref(null);

const mailForm = ref({
  mail_mailer: props.mailConfig.mail_mailer || '',
  mail_host: props.mailConfig.mail_host || '',
  mail_port: props.mailConfig.mail_port || '2525',
  mail_username: props.mailConfig.mail_username || '',
  mail_password: props.mailConfig.mail_password || '',
  mail_encryption: props.mailConfig.mail_encryption || 'tls',
  mail_from_address: props.mailConfig.mail_from_address || '',
});

const settingsMenu = [
  { key: 'mail-config', label: 'Mail Configurations', icon: MailOutlined },
  { key: 'database-backup', label: 'Database Backup', icon: DatabaseOutlined },
];

// No backup form needed - only manual backup

const backups = ref(props.backups || []);

const getActiveTabLabel = () => {
  const item = settingsMenu.find(item => item.key === activeTab.value);
  return item ? item.label : 'Settings';
};

const handleSaveMailConfig = async (values) => {
  saving.value = true;
  
  router.post('/dashboard/settings/mail-config', values, {
    preserveScroll: true,
    onSuccess: (page) => {
      notifySuccess('Success', 'Mail configuration saved successfully!');
      // Update form with saved values
      if (page.props.mailConfig) {
        mailForm.value = { ...page.props.mailConfig };
      }
    },
    onError: (errors) => {
      if (errors.message) {
        notifyError('Error', errors.message);
      } else {
        const firstError = Object.values(errors)[0];
        notifyError('Error', Array.isArray(firstError) ? firstError[0] : 'Failed to save mail configuration');
      }
    },
    onFinish: () => {
      saving.value = false;
    },
  });
};

const handleTestConnection = async () => {
  // Validate form before testing
  if (!mailForm.value.mail_mailer || 
      !mailForm.value.mail_host || 
      !mailForm.value.mail_port || 
      !mailForm.value.mail_username || 
      !mailForm.value.mail_password || 
      !mailForm.value.mail_encryption || 
      !mailForm.value.mail_from_address) {
    notifyError('Warning', 'Please fill in all mail configuration fields before testing');
    return;
  }

  testing.value = true;
  
  router.post('/dashboard/settings/mail-config/test', mailForm.value, {
    preserveScroll: true,
    onSuccess: () => {
      // Flash message will be handled automatically by NotificationContainer
    },
    onError: (errors) => {
      if (errors.message) {
        notifyError('Error', errors.message);
      } else {
        const firstError = Object.values(errors)[0];
        notifyError('Error', Array.isArray(firstError) ? firstError[0] : 'Test connection failed. Please check your mail configuration.');
      }
    },
    onFinish: () => {
      testing.value = false;
    },
  });
};

const handleCreateBackup = async () => {
  creatingBackup.value = true;
  
  try {
    const response = await fetch('/dashboard/settings/database-backup/create', {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
      },
      credentials: 'same-origin',
    });

    const result = await response.json();

    if (result.success) {
      notifySuccess('Success', 'Database backup created successfully!');
      // Add new backup to the list instantly
      if (result.backup) {
        backups.value.unshift({
          filename: result.backup.filename,
          path: result.backup.path,
          size: result.backup.size,
          created_at: result.backup.created_at,
        });
      } else {
        // Reload backups list
        router.reload({ only: ['backups'] });
      }
    } else {
      notifyError('Error', result.message || 'Failed to create backup');
    }
  } catch (error) {
    notifyError('Error', 'Failed to create backup. Please try again.');
  } finally {
    creatingBackup.value = false;
  }
};

const handleDownloadBackup = async (filename) => {
  currentDownloadFile.value = filename;
  downloadingBackup.value = true;
  downloadProgress.value = 0;
  showDownloadModal.value = true;
  
  try {
    // Simulate progress
    const progressInterval = setInterval(() => {
      if (downloadProgress.value < 90) {
        downloadProgress.value += 10;
      }
    }, 200);
    
    // Create a link and trigger download
    const link = document.createElement('a');
    link.href = `/dashboard/settings/database-backup/download/${filename}`;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    // Wait a bit for download to start
    setTimeout(() => {
      clearInterval(progressInterval);
      downloadProgress.value = 100;
      
      setTimeout(() => {
        showDownloadModal.value = false;
        downloadingBackup.value = false;
        downloadProgress.value = 0;
        currentDownloadFile.value = null;
        notifySuccess('Success', 'Backup download started!');
      }, 500);
    }, 1000);
  } catch (error) {
    showDownloadModal.value = false;
    downloadingBackup.value = false;
    downloadProgress.value = 0;
    currentDownloadFile.value = null;
    notifyError('Error', 'Failed to download backup. Please try again.');
  }
};

const handleDeleteBackup = (filename) => {
  Modal.confirm({
    title: 'Delete Backup',
    content: 'Are you sure you want to delete this backup? This action cannot be undone.',
    okText: 'Yes, Delete',
    okType: 'danger',
    cancelText: 'Cancel',
    onOk: async () => {
      try {
        const response = await fetch(`/dashboard/settings/database-backup/delete/${filename}`, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
          },
          credentials: 'same-origin',
        });

        const result = await response.json();

        if (result.success) {
          notifySuccess('Success', 'Backup deleted successfully!');
          // Remove from list instantly
          const index = backups.value.findIndex(b => b.filename === filename);
          if (index > -1) {
            backups.value.splice(index, 1);
          }
        } else {
          notifyError('Error', result.message || 'Failed to delete backup');
        }
      } catch (error) {
        notifyError('Error', 'Failed to delete backup. Please try again.');
      }
    },
  });
};

// Removed handleSaveBackupConfig - scheduled backup not needed

onMounted(() => {
  if (!mailForm.value.mail_mailer) {
    mailForm.value.mail_mailer = 'smtp';
  }
  if (!mailForm.value.mail_encryption) {
    mailForm.value.mail_encryption = 'tls';
  }
  if (!mailForm.value.mail_port) {
    mailForm.value.mail_port = '2525';
  }
  
  // Update backups list if props changed
  if (props.backups) {
    backups.value = props.backups;
  }
});
</script>

<template>
  <DashboardLayout>
    <div class="general-settings-container">
      <!-- Left Panel: Settings Menu -->
      <div class="settings-sidebar">
        <div class="settings-header">
          <h2 class="settings-title">Settings</h2>
        </div>
        
        <div class="settings-submenu">
          <div 
            v-for="item in settingsMenu" 
            :key="item.key"
            class="submenu-item"
            :class="{ active: activeTab === item.key }"
            @click="activeTab = item.key"
          >
            <span class="submenu-label">{{ item.label }}</span>
            <RightOutlined v-if="activeTab === item.key" class="submenu-icon" />
          </div>
        </div>
      </div>

      <!-- Right Panel: Content Area -->
      <div class="settings-content">
        <!-- Mail Configuration -->
        <div v-if="activeTab === 'mail-config'" class="config-panel">
          <h2 class="config-title">Mail Configuration</h2>
          
          <a-form
            :model="mailForm"
            :rules="mailRules"
            layout="vertical"
            @finish="handleSaveMailConfig"
            class="mail-config-form"
          >
            <a-form-item label="MAIL MAILER" name="mail_mailer" required>
              <Input
                v-model="mailForm.mail_mailer"
                placeholder="MAIL MAILER"
                size="large"
              />
            </a-form-item>

            <a-form-item label="MAIL HOST" name="mail_host" required>
              <Input
                v-model="mailForm.mail_host"
                placeholder="MAIL HOST"
                size="large"
              />
            </a-form-item>

            <a-form-item label="MAIL PORT" name="mail_port" required>
              <Input
                v-model="mailForm.mail_port"
                placeholder="MAIL PORT"
                size="large"
              />
            </a-form-item>

            <a-form-item label="MAIL USERNAME" name="mail_username" required>
              <Input
                v-model="mailForm.mail_username"
                placeholder="MAIL USERNAME"
                size="large"
              />
            </a-form-item>

            <a-form-item label="MAIL PASSWORD" name="mail_password" required>
              <Input
                v-model="mailForm.mail_password"
                type="text"
                placeholder="MAIL PASSWORD"
                size="large"
              />
            </a-form-item>

            <a-form-item label="MAIL ENCRYPTION" name="mail_encryption" required>
              <Input
                v-model="mailForm.mail_encryption"
                placeholder="MAIL ENCRYPTION"
                size="large"
              />
            </a-form-item>

            <a-form-item label="MAIL FROM ADDRESS" name="mail_from_address" required>
              <Input
                v-model="mailForm.mail_from_address"
                placeholder="MAIL FROM ADDRESS"
                size="large"
              />
            </a-form-item>

            <a-form-item>
              <div class="form-actions">
                <a-button
                  type="primary"
                  size="large"
                  html-type="submit"
                  :loading="saving"
                  class="save-button"
                >
                  <template #icon><SaveOutlined /></template>
                  Save changes
                </a-button>
                
                <a-button
                  size="large"
                  @click="handleTestConnection"
                  :loading="testing"
                  class="test-button"
                >
                  <template #icon><WifiOutlined /></template>
                  Test Connection
                </a-button>
              </div>
            </a-form-item>
          </a-form>
        </div>

        <!-- Database Backup -->
        <div v-if="activeTab === 'database-backup'" class="config-panel">
          <h2 class="config-title">Database Backup</h2>
          
          <!-- Manual Backup and History in One Card -->
          <a-card class="backup-card" :bordered="false">
            <template #title>
              <span>Database Backup</span>
            </template>
            <template #extra>
              <a-button
                type="primary"
                :loading="creatingBackup"
                @click="handleCreateBackup"
                :icon="h(DownloadOutlined)"
              >
                Create Backup Now
              </a-button>
            </template>
            
            <p class="backup-description" style="margin-bottom: 24px;">
              Create a manual database backup. The backup will be saved as a SQL file that you can download.
            </p>

            <!-- Backup History -->
            <div class="backup-history-section">
              <h3 class="backup-history-title">Backup History</h3>
              <a-empty v-if="backups.length === 0" description="No backups created yet" />
              <a-list
                v-else
                :data-source="backups"
                item-layout="horizontal"
              >
                <template #renderItem="{ item }">
                  <a-list-item>
                    <a-list-item-meta>
                      <template #title>
                        <div style="display: flex; align-items: center; gap: 8px;">
                          <DatabaseOutlined />
                          <span>{{ item.filename }}</span>
                        </div>
                      </template>
                      <template #description>
                        <div style="display: flex; gap: 16px; align-items: center; margin-top: 8px;">
                          <span>
                            <ClockCircleOutlined style="margin-right: 4px;" />
                            {{ $formatDateTime(item.created_at) }}
                          </span>
                          <span>{{ item.size }}</span>
                        </div>
                      </template>
                    </a-list-item-meta>
                    <template #actions>
                      <a-button
                        type="link"
                        :icon="h(DownloadOutlined)"
                        @click="handleDownloadBackup(item.filename)"
                      >
                        Download
                      </a-button>
                      <a-button
                        type="link"
                        danger
                        :icon="h(DeleteOutlined)"
                        @click="handleDeleteBackup(item.filename)"
                      >
                        Delete
                      </a-button>
                    </template>
                  </a-list-item>
                </template>
              </a-list>
            </div>
          </a-card>
        </div>

        <!-- Download Progress Modal -->
        <a-modal
          v-model:open="showDownloadModal"
          title="Downloading Backup"
          :footer="null"
          :closable="false"
          :maskClosable="false"
        >
          <div class="download-progress-content">
            <p style="margin-bottom: 16px;">
              Downloading: <strong>{{ currentDownloadFile }}</strong>
            </p>
            <a-progress :percent="downloadProgress" :status="downloadProgress === 100 ? 'success' : 'active'" />
            <p v-if="downloadProgress === 100" style="margin-top: 16px; color: #52c41a; text-align: center;">
              Download completed!
            </p>
          </div>
        </a-modal>
      </div>
    </div>
  </DashboardLayout>
</template>

<style scoped>
.general-settings-container {
  display: flex;
  gap: 24px;
  min-height: calc(100vh - 200px);
}

/* Left Sidebar */
.settings-sidebar {
  height: fit-content;
  width: 285px;
  position: sticky;
  background: var(--card-bg, #fff);
  border-radius: var(--radius-base, 8px);
  box-shadow: var(--card-shadow, 0 2px 8px rgba(0, 0, 0, 0.06));
  border: 1px solid var(--card-border, #f0f0f0);
  padding: 24px;
  transition: background-color 0.1s ease-out, border-color 0.1s ease-out;
}

[data-theme="dark"] .settings-sidebar {
  background: var(--card-bg, #1f1f1f);
  border-color: var(--card-border, #2d2d2d);
}

.settings-header {
  margin-bottom: 24px;
}

.settings-title {
  font-size: 18px;
  font-weight: 600;
  color: var(--text-primary, #262626);
  margin: 0;
  transition: color 0.1s ease-out;
}

[data-theme="dark"] .settings-title {
  color: rgba(255, 255, 255, 0.85);
}

.settings-submenu {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.submenu-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 16px;
  border-radius: var(--radius-base, 8px);
  cursor: pointer;
  transition: all 0.2s ease;
  color: var(--text-secondary, #8c8c8c);
}

.submenu-item:hover {
  background: var(--bg-hover, #fafafa);
  color: var(--text-primary, #262626);
}

[data-theme="dark"] .submenu-item:hover {
  background: var(--bg-hover, #2d2d2d);
  color: rgba(255, 255, 255, 0.85);
}

.submenu-item.active {
  background: #722ed1;
  color: #fff;
  font-weight: 500;
}

.submenu-item.active:hover {
  background: #9254de;
  color: #fff;
}

.submenu-label {
  font-size: 14px;
}

.submenu-icon {
  font-size: 12px;
}

/* Right Content Area */
.settings-content {
  flex: 1;
  background: var(--card-bg, #fff);
  border-radius: var(--radius-base, 8px);
  box-shadow: var(--card-shadow, 0 2px 8px rgba(0, 0, 0, 0.06));
  border: 1px solid var(--card-border, #f0f0f0);
  padding: 32px;
  transition: background-color 0.1s ease-out, border-color 0.1s ease-out;
}

:deep(.ant-form-item) {
  margin-bottom: 24px;
  width: 100%;
}

:deep(.ant-form-item-label) {
  padding: 0 0 8px 0;
  width: 100%;
}

:deep(.ant-form-item-control) {
  width: 100%;
  max-width: 100%;
}

[data-theme="dark"] .settings-content {
  background: var(--card-bg, #1f1f1f);
  border-color: var(--card-border, #2d2d2d);
}

.config-panel {
  width: 100%;
}

.config-title {
  font-size: 24px;
  font-weight: 600;
  color: var(--text-primary, #262626);
  margin: 0 0 32px 0;
  padding: 0;
  transition: color 0.1s ease-out;
}

[data-theme="dark"] .config-title {
  color: rgba(255, 255, 255, 0.85);
}

.mail-config-form {
  width: 100%;
}

:deep(.ant-form-item-label > label) {
  font-weight: 500;
  color: var(--text-primary, #262626);
  font-size: 14px;
}

[data-theme="dark"] :deep(.ant-form-item-label > label) {
  color: rgba(255, 255, 255, 0.85);
}

:deep(.ant-form-item-label > label::after) {
  content: ' *';
  color: var(--color-error, #ff4d4f);
}

/* Form item styles */
:deep(.ant-form-item-control-input) {
  width: 100%;
}

:deep(.ant-form-item-control-input-content) {
  width: 100%;
}

.form-actions {
  display: flex;
  gap: 12px;
  margin-top: 8px;
}

.save-button {
  background: #722ed1;
  border-color: #722ed1;
  border-radius: var(--radius-base, 8px);
  font-weight: 500;
}

.save-button:hover {
  background: #9254de;
  border-color: #9254de;
}

.test-button {
  border-radius: var(--radius-base, 8px);
  font-weight: 500;
}

.coming-soon {
  color: var(--text-secondary, #8c8c8c);
  font-size: 16px;
  margin-top: 24px;
}

[data-theme="dark"] .coming-soon {
  color: rgba(255, 255, 255, 0.45);
}

/* Backup Card Styles */
.backup-card {
  margin-bottom: 0;
}

/* Backup History Section */
.backup-history-section {
  margin-top: 24px;
  padding-top: 24px;
  border-top: 1px solid var(--border-color-light, #f0f0f0);
}

[data-theme="dark"] .backup-history-section {
  border-top-color: rgba(255, 255, 255, 0.12);
}

.backup-history-title {
  font-size: 16px;
  font-weight: 600;
  color: var(--text-primary, #262626);
  margin: 0 0 16px 0;
}

[data-theme="dark"] .backup-history-title {
  color: rgba(255, 255, 255, 0.85);
}

/* Download Progress Modal */
.download-progress-content {
  padding: 8px 0;
}

.backup-description {
  color: var(--text-secondary, #8c8c8c);
  margin: 0;
  line-height: 1.6;
}

[data-theme="dark"] .backup-description {
  color: rgba(255, 255, 255, 0.65);
}

.backup-config-form {
  margin-top: 16px;
}

.form-help-text {
  color: var(--text-tertiary, #8c8c8c);
  font-size: 13px;
}

[data-theme="dark"] .form-help-text {
  color: rgba(255, 255, 255, 0.45);
}

/* Responsive Design */
@media (max-width: 1024px) {
  .general-settings-container {
    flex-direction: column;
  }

  .settings-sidebar {
    width: 100%;
    position: relative;
    top: 0;
  }
}

@media (max-width: 768px) {
  .settings-content {
    padding: 24px;
  }

  .config-title {
    font-size: 20px;
    margin-bottom: 24px;
  }

  .form-actions {
    flex-direction: column;
  }

  .save-button,
  .test-button {
    width: 100%;
  }
}

@media (max-width: 480px) {
  .settings-sidebar {
    padding: 16px;
  }

  .settings-content {
    padding: 16px;
  }

  .config-title {
    font-size: 18px;
    margin-bottom: 20px;
  }
}
</style>

