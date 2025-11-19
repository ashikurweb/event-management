<template>
  <DashboardLayout>
    <Breadcrumb :items="breadcrumbItems" />

    <a-card class="certificate-form-card">
      <template #title>
        <h2 class="card-title">Edit Certificate</h2>
      </template>

      <a-form
        :model="form"
        :rules="rules"
        layout="vertical"
        ref="formRef"
        @finish="handleSubmit"
      >
        <a-row :gutter="12">
          <a-col :xs="24" :md="12">
            <a-form-item label="Event" name="event_id" required>
              <a-select
                v-model:value="form.event_id"
                placeholder="Select an event"
                show-search
                :filter-option="filterOption"
                style="width: 100%"
              >
                <a-select-option
                  v-for="event in events"
                  :key="event.id"
                  :value="event.id"
                >
                  {{ event.title }}
                </a-select-option>
              </a-select>
            </a-form-item>
          </a-col>

          <a-col :xs="24" :md="12">
            <a-form-item label="User" name="user_id" required>
              <a-select
                v-model:value="form.user_id"
                placeholder="Select a user"
                show-search
                :filter-option="filterOption"
                style="width: 100%"
              >
                <a-select-option
                  v-for="user in users"
                  :key="user.id"
                  :value="user.id"
                >
                  {{ user.first_name }} {{ user.last_name }} ({{ user.email }})
                </a-select-option>
              </a-select>
            </a-form-item>
          </a-col>
        </a-row>

        <a-row :gutter="12">
          <a-col :xs="24" :md="12">
            <a-form-item label="Certificate Number" name="certificate_number">
              <Input
                v-model="form.certificate_number"
                placeholder="Auto-generated if left empty"
                :maxlength="100"
              />
              <template #extra>
                <span class="form-extra-text">Leave empty to auto-generate</span>
              </template>
            </a-form-item>
          </a-col>

          <a-col :xs="24" :md="12">
            <a-form-item label="Verification Code" name="verification_code">
              <Input
                v-model="form.verification_code"
                placeholder="Auto-generated if left empty"
                :maxlength="100"
              />
              <template #extra>
                <span class="form-extra-text">Leave empty to auto-generate</span>
              </template>
            </a-form-item>
          </a-col>
        </a-row>

        <a-row :gutter="12">
          <a-col :xs="24" :md="12">
            <a-form-item label="Template Path" name="template_path">
              <Input
                v-model="form.template_path"
                placeholder="Enter template path (optional)"
                :maxlength="255"
              />
            </a-form-item>
          </a-col>

          <a-col :xs="24" :md="12">
            <a-form-item label="File Path" name="file_path">
              <Input
                v-model="form.file_path"
                placeholder="Enter file path (optional)"
                :maxlength="255"
              />
            </a-form-item>
          </a-col>
        </a-row>

        <a-row :gutter="12">
          <a-col :xs="24" :md="12">
            <a-form-item label="Issued At" name="issued_at">
              <a-date-picker
                v-model:value="form.issued_at"
                show-time
                format="YYYY-MM-DD HH:mm:ss"
                style="width: 100%"
                placeholder="Select issued date"
              />
            </a-form-item>
          </a-col>

          <a-col :xs="24" :md="12">
            <a-form-item label="Downloaded At" name="downloaded_at">
              <a-date-picker
                v-model:value="form.downloaded_at"
                show-time
                format="YYYY-MM-DD HH:mm:ss"
                style="width: 100%"
                placeholder="Select downloaded date (optional)"
              />
            </a-form-item>
          </a-col>
        </a-row>

        <a-form-item>
          <a-space>
            <a-button type="primary" html-type="submit" :loading="saving">
              Update Certificate
            </a-button>
            <a-button @click="handleCancel">Cancel</a-button>
          </a-space>
        </a-form-item>
      </a-form>
    </a-card>
  </DashboardLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import DashboardLayout from '../../../Layouts/DashboardLayout.vue';
import Breadcrumb from '../../../Components/Breadcrumb.vue';
import Input from '../../../Components/Input.vue';
import dayjs from 'dayjs';

const page = usePage();
const formRef = ref(null);
const saving = ref(false);

const certificate = computed(() => page.props.certificate || {});
const events = computed(() => page.props.events || []);
const users = computed(() => page.props.users || []);

const breadcrumbItems = ref([
  { label: 'Dashboard', href: '/dashboard' },
  { label: 'Certificates', href: '/dashboard/certificates' },
  { label: 'Edit' },
]);

const form = reactive({
  event_id: null,
  user_id: null,
  certificate_number: '',
  template_path: '',
  file_path: '',
  issued_at: null,
  downloaded_at: null,
  verification_code: '',
});

const filterOption = (input, option) => {
  return option.children[0].children.toLowerCase().indexOf(input.toLowerCase()) >= 0;
};

const rules = {
  event_id: [
    { required: true, message: 'Please select an event', trigger: 'change' },
  ],
  user_id: [
    { required: true, message: 'Please select a user', trigger: 'change' },
  ],
  certificate_number: [
    { max: 100, message: 'Certificate number cannot exceed 100 characters', trigger: 'blur' },
  ],
  template_path: [
    { max: 255, message: 'Template path cannot exceed 255 characters', trigger: 'blur' },
  ],
  file_path: [
    { max: 255, message: 'File path cannot exceed 255 characters', trigger: 'blur' },
  ],
  verification_code: [
    { max: 100, message: 'Verification code cannot exceed 100 characters', trigger: 'blur' },
  ],
};

onMounted(() => {
  if (certificate.value) {
    Object.assign(form, {
      event_id: certificate.value.event_id || null,
      user_id: certificate.value.user_id || null,
      certificate_number: certificate.value.certificate_number || '',
      template_path: certificate.value.template_path || '',
      file_path: certificate.value.file_path || '',
      issued_at: certificate.value.issued_at ? dayjs(certificate.value.issued_at) : null,
      downloaded_at: certificate.value.downloaded_at ? dayjs(certificate.value.downloaded_at) : null,
      verification_code: certificate.value.verification_code || '',
    });
  }
});

const handleSubmit = async () => {
  try {
    await formRef.value.validate();
    saving.value = true;

    const submitData = {
      ...form,
      issued_at: form.issued_at ? form.issued_at.format('YYYY-MM-DD HH:mm:ss') : null,
      downloaded_at: form.downloaded_at ? form.downloaded_at.format('YYYY-MM-DD HH:mm:ss') : null,
    };

    router.put(`/dashboard/certificates/${certificate.value.id}`, submitData, {
      preserveScroll: true,
      onSuccess: () => {
        // Success handled by Inertia
      },
      onError: () => {
        saving.value = false;
      },
      onFinish: () => {
        saving.value = false;
      },
    });
  } catch (error) {
    console.error('Validation failed:', error);
  }
};

const handleCancel = () => {
  router.visit('/dashboard/certificates');
};
</script>

<style scoped>
.certificate-form-card {
  border-radius: 8px;
  box-shadow: var(--card-shadow, 0 2px 8px rgba(0, 0, 0, 0.06));
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

.form-extra-text {
  font-size: 12px;
  color: var(--text-tertiary, #8c8c8c);
}

[data-theme="dark"] .form-extra-text {
  color: rgba(255, 255, 255, 0.45);
}

:deep(.ant-form-item) {
  margin-bottom: 12px;
}

:deep(.ant-form-item-label > label) {
  color: var(--text-primary, #262626);
  font-weight: 500;
}

[data-theme="dark"] :deep(.ant-form-item-label > label) {
  color: rgba(255, 255, 255, 0.85);
}
</style>

