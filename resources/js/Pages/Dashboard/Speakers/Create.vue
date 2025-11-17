<template>
  <DashboardLayout>
    <Breadcrumb :items="breadcrumbItems" />

    <a-card class="speaker-form-card">
      <template #title>
        <h2 class="card-title">Create Speaker</h2>
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
                placeholder="Enter speaker name"
                :maxlength="255"
              />
            </a-form-item>
          </a-col>

          <a-col :xs="24" :md="12">
            <a-form-item label="User" name="user_id">
              <a-select
                v-model:value="form.user_id"
                placeholder="Select user (optional)"
                allow-clear
                show-search
                :filter-option="filterUserOption"
              >
                <a-select-option
                  v-for="user in users"
                  :key="user.id"
                  :value="user.id"
                >
                  {{ user.name }} ({{ user.email }})
                </a-select-option>
              </a-select>
            </a-form-item>
          </a-col>
        </a-row>

        <a-row :gutter="16">
          <a-col :xs="24" :md="12">
            <a-form-item label="Email" name="email">
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

        <a-row :gutter="16">
          <a-col :xs="24" :md="12">
            <a-form-item label="Title" name="title">
              <Input
                v-model="form.title"
                placeholder="e.g., CEO, CTO, Director"
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

        <a-form-item label="Bio" name="bio">
          <a-textarea
            v-model:value="form.bio"
            placeholder="Enter speaker biography"
            :rows="4"
            :maxlength="1000"
            show-count
          />
        </a-form-item>

        <a-form-item label="Photos" name="photos">
          <FileUploader
            v-model="photoFiles"
            :multiple="true"
            :accept="['image/*']"
            :max-size="5 * 1024 * 1024"
            :max-files="10"
            drag-text="Drag & drop photos here"
            subtitle="or click to browse (multiple images allowed)"
            @error="handleFileError"
          />
          <template #extra>
            <span class="form-extra-text">Max 10 images, 5MB each. Supported formats: JPG, PNG, GIF, WebP</span>
          </template>
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
            <a-form-item label="Featured" name="is_featured">
              <a-switch v-model:checked="form.is_featured" />
              <template #extra>
                <span class="form-extra-text">Featured speakers appear prominently</span>
              </template>
            </a-form-item>
          </a-col>
        </a-row>

        <a-form-item label="Social Links" name="social_links">
          <div class="social-links-container">
            <a-row :gutter="12">
              <a-col :xs="24" :sm="12">
                <a-input
                  v-model:value="form.social_links.linkedin"
                  placeholder="LinkedIn URL"
                  addon-before="LinkedIn"
                />
              </a-col>
              <a-col :xs="24" :sm="12">
                <a-input
                  v-model:value="form.social_links.twitter"
                  placeholder="Twitter/X URL"
                  addon-before="Twitter"
                />
              </a-col>
            </a-row>
            <a-row :gutter="12" style="margin-top: 12px">
              <a-col :xs="24" :sm="12">
                <a-input
                  v-model:value="form.social_links.facebook"
                  placeholder="Facebook URL"
                  addon-before="Facebook"
                />
              </a-col>
              <a-col :xs="24" :sm="12">
                <a-input
                  v-model:value="form.social_links.instagram"
                  placeholder="Instagram URL"
                  addon-before="Instagram"
                />
              </a-col>
            </a-row>
          </div>
        </a-form-item>

        <a-form-item label="Specialties" name="specialties">
          <a-select
            v-model:value="form.specialties"
            mode="tags"
            placeholder="Add specialties (press Enter to add)"
            :max-tag-count="10"
            style="width: 100%"
          >
          </a-select>
          <template #extra>
            <span class="form-extra-text">Press Enter after each specialty</span>
          </template>
        </a-form-item>

        <a-form-item>
          <a-space>
            <a-button type="primary" html-type="submit" :loading="saving">
              Create Speaker
            </a-button>
            <a-button @click="handleCancel">Cancel</a-button>
          </a-space>
        </a-form-item>
      </a-form>
    </a-card>
  </DashboardLayout>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import DashboardLayout from '../../../Layouts/DashboardLayout.vue';
import Breadcrumb from '../../../Components/Breadcrumb.vue';
import Input from '../../../Components/Input.vue';
import FileUploader from '../../../Components/FileUploader.vue';
import { message } from 'ant-design-vue';

const page = usePage();
const formRef = ref(null);
const saving = ref(false);
const photoFiles = ref([]);

const users = computed(() => page.props.users || []);

const breadcrumbItems = ref([
  { label: 'Dashboard', href: '/dashboard' },
  { label: 'Speakers', href: '/dashboard/speakers' },
  { label: 'Create' },
]);

const form = reactive({
  user_id: null,
  name: '',
  email: '',
  phone: '',
  bio: '',
  title: '',
  company: '',
  website: '',
  social_links: {
    linkedin: '',
    twitter: '',
    facebook: '',
    instagram: '',
  },
  specialties: [],
  is_featured: false,
});

const rules = {
  name: [
    { required: true, message: 'Please enter speaker name', trigger: 'blur' },
    { max: 255, message: 'Name cannot exceed 255 characters', trigger: 'blur' },
  ],
  email: [
    { type: 'email', message: 'Please enter a valid email address', trigger: 'blur' },
    { max: 255, message: 'Email cannot exceed 255 characters', trigger: 'blur' },
  ],
  phone: [
    { max: 20, message: 'Phone cannot exceed 20 characters', trigger: 'blur' },
  ],
  website: [
    { type: 'url', message: 'Please enter a valid URL', trigger: 'blur' },
  ],
};

const filterUserOption = (input, option) => {
  const searchText = input.toLowerCase();
  const userName = option.children[0].children.toLowerCase();
  return userName.includes(searchText);
};

const handleFileError = (error) => {
  message.error(error.message || 'File upload error');
};

const handleSubmit = async () => {
  try {
    await formRef.value.validate();
    
    // Photos are optional

    saving.value = true;

    // Create FormData for file upload
    const formData = new FormData();
    
    // Add all form fields
    Object.keys(form).forEach((key) => {
      if (key === 'social_links') {
        // Filter out empty social links and append each as separate field
        Object.entries(form.social_links).forEach(([socialKey, value]) => {
          if (value && value.trim()) {
            formData.append(`social_links[${socialKey}]`, value);
          }
        });
      } else if (key === 'specialties') {
        // Append specialties as array items
        if (form.specialties && form.specialties.length > 0) {
          form.specialties.forEach((specialty, index) => {
            if (specialty && specialty.trim()) {
              formData.append(`specialties[${index}]`, specialty);
            }
          });
        }
      } else if (form[key] !== null && form[key] !== '' && form[key] !== false) {
        formData.append(key, form[key]);
      } else if (form[key] === false) {
        // Handle boolean false
        formData.append(key, '0');
      } else if (form[key] === true) {
        // Handle boolean true
        formData.append(key, '1');
      }
    });

    // Add photo files
    if (photoFiles.value.length > 0) {
      photoFiles.value.forEach((fileObj) => {
        if (fileObj.file) {
          formData.append('photos[]', fileObj.file);
        }
      });
    }

    router.post('/dashboard/speakers', formData, {
      preserveScroll: true,
      forceFormData: true,
      onSuccess: () => {
        message.success('Speaker created successfully');
      },
      onError: (errors) => {
        saving.value = false;
        if (errors.photos) {
          message.error(Array.isArray(errors.photos) ? errors.photos.join(', ') : errors.photos);
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
  router.visit('/dashboard/speakers');
};
</script>

<style scoped>
.speaker-form-card {
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

.social-links-container {
  width: 100%;
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

