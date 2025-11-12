<template>
  <DashboardLayout>
    <Breadcrumb :items="breadcrumbItems" />

    <a-card class="category-form-card">
      <template #title>
        <h2 class="card-title">Create Category</h2>
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
            <a-form-item label="Name" name="name" required>
              <Input
                v-model="form.name"
                placeholder="Enter category name"
                :maxlength="100"
              />
            </a-form-item>
          </a-col>

          <a-col :xs="24" :md="12">
            <a-form-item label="Slug" name="slug">
              <Input
                v-model="form.slug"
                placeholder="Auto-generated from name"
                :maxlength="100"
              />
              <template #extra>
                <span class="form-extra-text">Leave empty to auto-generate</span>
              </template>
            </a-form-item>
          </a-col>
        </a-row>

        <a-form-item label="Description" name="description">
          <a-textarea
            v-model:value="form.description"
            placeholder="Enter category description"
            :rows="4"
            :maxlength="500"
          />
        </a-form-item>

        <a-row :gutter="12">
          <a-col :xs="24" :md="8">
            <a-form-item label="Parent Category" name="parent_id">
              <a-select
                v-model:value="form.parent_id"
                placeholder="Select parent category (optional)"
                allow-clear
              >
                <a-select-option
                  v-for="category in parentCategories"
                  :key="category.id"
                  :value="category.id"
                >
                  {{ category.name }}
                </a-select-option>
              </a-select>
            </a-form-item>
          </a-col>

          <a-col :xs="24" :md="8">
            <a-form-item label="Display Order" name="display_order">
              <Input
                v-model="form.display_order"
                type="number"
                placeholder="Display order"
                :min="0"
                style="width: 100%"
              />
              <template #extra>
                <span class="form-extra-text">Lower numbers appear first</span>
              </template>
            </a-form-item>
          </a-col>

          <a-col :xs="24" :md="8">
            <a-form-item label="Status" name="is_active">
              <a-select
                v-model:value="form.is_active"
                placeholder="Select status"
                style="width: 100%"
              >
                <a-select-option :value="true">Active</a-select-option>
                <a-select-option :value="false">Inactive</a-select-option>
              </a-select>
              <template #extra>
                <span class="form-extra-text">Inactive categories won't be displayed</span>
              </template>
            </a-form-item>
          </a-col>
        </a-row>

        <a-form-item>
          <a-space>
            <a-button type="primary" html-type="submit" :loading="saving">
              Create Category
            </a-button>
            <a-button @click="handleCancel">Cancel</a-button>
          </a-space>
        </a-form-item>
      </a-form>
    </a-card>
  </DashboardLayout>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import DashboardLayout from '../../../Layouts/DashboardLayout.vue';
import Breadcrumb from '../../../Components/Breadcrumb.vue';
import Input from '../../../Components/Input.vue';

const page = usePage();
const formRef = ref(null);
const saving = ref(false);
const isSlugManuallyEdited = ref(false);

const parentCategories = computed(() => page.props.parentCategories || []);

const breadcrumbItems = ref([
  { label: 'Dashboard', href: '/dashboard' },
  { label: 'Categories', href: '/dashboard/categories' },
  { label: 'Create' },
]);

const form = reactive({
  name: '',
  slug: '',
  description: '',
  parent_id: null,
  display_order: 0,
  is_active: true,
});

// Generate slug from name
const generateSlug = (text) => {
  return text
    .toLowerCase()
    .trim()
    .replace(/[^\w\s-]/g, '') // Remove special characters
    .replace(/[\s_-]+/g, '-') // Replace spaces and underscores with hyphens
    .replace(/^-+|-+$/g, ''); // Remove leading/trailing hyphens
};

// Watch name field and auto-generate slug
watch(() => form.name, (newName) => {
  if (!isSlugManuallyEdited.value && newName) {
    form.slug = generateSlug(newName);
  }
});

// Track if slug is manually edited
watch(() => form.slug, () => {
  if (form.slug && form.slug !== generateSlug(form.name)) {
    isSlugManuallyEdited.value = true;
  }
});

const rules = {
  name: [
    { required: true, message: 'Please enter category name', trigger: 'blur' },
    { max: 100, message: 'Name cannot exceed 100 characters', trigger: 'blur' },
  ],
  slug: [
    { max: 100, message: 'Slug cannot exceed 100 characters', trigger: 'blur' },
  ],
};

const handleSubmit = async () => {
  try {
    await formRef.value.validate();
    saving.value = true;

    router.post('/dashboard/categories', form, {
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
  router.visit('/dashboard/categories');
};
</script>

<style scoped>
.category-form-card {
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

