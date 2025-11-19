<template>
  <DashboardLayout>
    <Breadcrumb :items="breadcrumbItems" />

    <a-card class="vendor-form-card">
      <template #title>
        <h2 class="card-title">Create Vendor</h2>
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
                placeholder="Enter vendor name"
                :maxlength="255"
              />
            </a-form-item>
          </a-col>

          <a-col :xs="24" :md="12">
            <a-form-item label="Company" name="company">
              <Input
                v-model="form.company"
                placeholder="Enter company name"
                :maxlength="255"
              />
            </a-form-item>
          </a-col>
        </a-row>

        <a-row :gutter="16">
          <a-col :xs="24" :md="12">
            <a-form-item label="Email" name="email" required>
              <Input
                v-model="form.email"
                type="email"
                placeholder="Enter email address"
                :maxlength="255"
              />
            </a-form-item>
          </a-col>

          <a-col :xs="24" :md="12">
            <a-form-item label="Phone" name="phone">
              <Input
                v-model="form.phone"
                placeholder="Enter phone number"
                :maxlength="20"
              />
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
            placeholder="Enter vendor description"
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
            <a-form-item label="Category" name="category">
              <Input
                v-model="form.category"
                placeholder="e.g., Catering, Photography, Decor"
                :maxlength="100"
              />
            </a-form-item>
          </a-col>
        </a-row>

        <a-row :gutter="16">
          <a-col :xs="24" :md="12">
            <a-form-item label="Rating" name="rating">
              <a-input-number
                v-model:value="form.rating"
                :min="0"
                :max="5"
                :step="0.1"
                :precision="2"
                placeholder="0.00"
                style="width: 100%"
              />
              <template #extra>
                <span class="form-extra-text">Rating from 0.00 to 5.00</span>
              </template>
            </a-form-item>
          </a-col>

          <a-col :xs="24" :md="12">
            <a-form-item label="Verified" name="is_verified">
              <a-switch v-model:checked="form.is_verified" />
              <template #extra>
                <span class="form-extra-text">Mark vendor as verified</span>
              </template>
            </a-form-item>
          </a-col>
        </a-row>

        <a-form-item>
          <a-space>
            <a-button type="primary" html-type="submit" :loading="saving">
              Create Vendor
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
  { label: 'Vendors', href: '/dashboard/vendors' },
  { label: 'Create' },
]);

const form = reactive({
  name: '',
  company: '',
  email: '',
  phone: '',
  description: '',
  website: '',
  category: '',
  rating: 0.00,
  is_verified: false,
});

const rules = {
  name: [
    { required: true, message: 'Please enter vendor name', trigger: 'blur' },
    { max: 255, message: 'Name cannot exceed 255 characters', trigger: 'blur' },
  ],
  email: [
    { required: true, message: 'Please enter email address', trigger: 'blur' },
    { type: 'email', message: 'Please enter a valid email address', trigger: 'blur' },
    { max: 255, message: 'Email cannot exceed 255 characters', trigger: 'blur' },
  ],
  phone: [
    { max: 20, message: 'Phone cannot exceed 20 characters', trigger: 'blur' },
  ],
  website: [
    { type: 'url', message: 'Please enter a valid URL', trigger: 'blur' },
  ],
  category: [
    { max: 100, message: 'Category cannot exceed 100 characters', trigger: 'blur' },
  ],
  rating: [
    { type: 'number', min: 0, max: 5, message: 'Rating must be between 0 and 5', trigger: 'blur' },
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

    router.post('/dashboard/vendors', formData, {
      preserveScroll: true,
      forceFormData: true,
      onSuccess: () => {
        message.success('Vendor created successfully');
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
  router.visit('/dashboard/vendors');
};
</script>

<style scoped>
.vendor-form-card {
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

