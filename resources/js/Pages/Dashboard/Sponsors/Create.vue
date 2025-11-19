<template>
  <DashboardLayout>
    <Breadcrumb :items="breadcrumbItems" />

    <a-card class="sponsor-form-card">
      <template #title>
        <h2 class="card-title">Create Sponsor</h2>
      </template>

      <a-form
        :model="form"
        :rules="rules"
        layout="vertical"
        ref="formRef"
        @finish="handleSubmit"
      >
        <a-row :gutter="16">
          <a-col :xs="24" :md="12">
            <a-form-item label="Name" name="name" required>
              <Input
                v-model="form.name"
                placeholder="Enter sponsor name"
                :maxlength="255"
              />
            </a-form-item>
          </a-col>

          <a-col :xs="24" :md="12">
            <a-form-item label="Tier" name="tier" required>
              <a-select
                v-model:value="form.tier"
                placeholder="Select sponsor tier"
                style="width: 100%"
              >
                <a-select-option value="platinum">Platinum</a-select-option>
                <a-select-option value="gold">Gold</a-select-option>
                <a-select-option value="silver">Silver</a-select-option>
                <a-select-option value="bronze">Bronze</a-select-option>
                <a-select-option value="partner">Partner</a-select-option>
              </a-select>
            </a-form-item>
          </a-col>
        </a-row>

        <a-form-item label="Logo" name="logo">
          <FileUploader
            v-model="logoFile"
            :multiple="false"
            :accept="['image/*']"
            :max-size="5 * 1024 * 1024"
            :max-files="1"
            drag-text="Drag & drop logo here"
            subtitle="or click to browse"
            @error="handleFileError"
          />
          <template #extra>
            <span class="form-extra-text">Max 5MB. Supported formats: JPG, PNG, GIF, WebP</span>
          </template>
        </a-form-item>

        <a-form-item label="Description" name="description">
          <a-textarea
            v-model:value="form.description"
            placeholder="Enter sponsor description"
            :rows="4"
            show-count
          />
        </a-form-item>

        <a-row :gutter="16">
          <a-col :xs="24" :md="12">
            <a-form-item label="Website" name="website">
              <Input
                v-model="form.website"
                type="url"
                placeholder="https://example.com"
                :maxlength="255"
              />
            </a-form-item>
          </a-col>

          <a-col :xs="24" :md="12">
            <a-form-item label="Display Order" name="display_order">
              <a-input-number
                v-model:value="form.display_order"
                :min="0"
                placeholder="0"
                style="width: 100%"
              />
              <template #extra>
                <span class="form-extra-text">Lower numbers appear first</span>
              </template>
            </a-form-item>
          </a-col>
        </a-row>

        <a-form-item label="Active" name="is_active">
          <a-switch v-model:checked="form.is_active" />
          <template #extra>
            <span class="form-extra-text">Active sponsors are visible on the website</span>
          </template>
        </a-form-item>

        <a-form-item>
          <a-space>
            <a-button type="primary" html-type="submit" :loading="saving">
              Create Sponsor
            </a-button>
            <a-button @click="handleCancel">Cancel</a-button>
          </a-space>
        </a-form-item>
      </a-form>
    </a-card>
  </DashboardLayout>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import DashboardLayout from '../../../Layouts/DashboardLayout.vue';
import Breadcrumb from '../../../Components/Breadcrumb.vue';
import Input from '../../../Components/Input.vue';
import FileUploader from '../../../Components/FileUploader.vue';
import { message } from 'ant-design-vue';

const formRef = ref(null);
const saving = ref(false);
const logoFile = ref([]);

const breadcrumbItems = ref([
  { label: 'Dashboard', href: '/dashboard' },
  { label: 'Sponsors', href: '/dashboard/sponsors' },
  { label: 'Create' },
]);

const form = reactive({
  name: '',
  tier: 'partner',
  description: '',
  website: '',
  display_order: 0,
  is_active: true,
});

const rules = {
  name: [
    { required: true, message: 'Please enter sponsor name', trigger: 'blur' },
    { max: 255, message: 'Name cannot exceed 255 characters', trigger: 'blur' },
  ],
  tier: [
    { required: true, message: 'Please select sponsor tier', trigger: 'change' },
  ],
  website: [
    { type: 'url', message: 'Please enter a valid URL', trigger: 'blur' },
  ],
  display_order: [
    { type: 'number', min: 0, message: 'Display order cannot be less than 0', trigger: 'blur' },
  ],
};

const handleFileError = (error) => {
  message.error(error.message || 'File upload error');
};

const handleSubmit = async () => {
  try {
    await formRef.value.validate();
    
    saving.value = true;

    // Create FormData for file upload
    const formData = new FormData();
    
    // Add all form fields
    Object.keys(form).forEach((key) => {
      if (form[key] !== null && form[key] !== '' && form[key] !== false) {
        formData.append(key, form[key]);
      } else if (form[key] === false) {
        formData.append(key, '0');
      } else if (form[key] === true) {
        formData.append(key, '1');
      }
    });

    // Add logo file
    if (logoFile.value.length > 0 && logoFile.value[0].file) {
      formData.append('logo', logoFile.value[0].file);
    }

    router.post('/dashboard/sponsors', formData, {
      preserveScroll: true,
      forceFormData: true,
      onSuccess: () => {
        message.success('Sponsor created successfully');
      },
      onError: (errors) => {
        saving.value = false;
        if (errors.logo) {
          message.error(Array.isArray(errors.logo) ? errors.logo.join(', ') : errors.logo);
        }
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
  router.visit('/dashboard/sponsors');
};
</script>

<style scoped>
.sponsor-form-card {
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

