<template>
  <DashboardLayout>
    <Breadcrumb :items="breadcrumbItems" />

    <a-card class="team-form-card">
      <template #title>
        <h2 class="card-title">Edit Team</h2>
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
                placeholder="Enter team name"
                :maxlength="255"
              />
            </a-form-item>
          </a-col>

          <a-col :xs="24" :md="12">
            <a-form-item label="Slug" name="slug">
              <Input
                v-model="form.slug"
                placeholder="Auto-generated from name"
                :maxlength="255"
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
            placeholder="Enter team description"
            :rows="4"
            :maxlength="1000"
          />
        </a-form-item>

        <a-row :gutter="12">
          <a-col :xs="24" :md="8">
            <a-form-item label="Owner" name="owner_id" required>
              <a-select
                v-model:value="form.owner_id"
                placeholder="Select team owner"
                show-search
                :filter-option="filterOption"
                style="width: 100%"
              >
                <a-select-option
                  v-for="user in users"
                  :key="user.id"
                  :value="user.id"
                >
                  {{ `${user.first_name || ''} ${user.last_name || ''}`.trim() || 'N/A' }} ({{ user.email }})
                </a-select-option>
              </a-select>
            </a-form-item>
          </a-col>

          <a-col :xs="24" :md="8">
            <a-form-item label="Email" name="email">
              <Input
                v-model="form.email"
                type="email"
                placeholder="team@example.com"
                :maxlength="255"
              />
            </a-form-item>
          </a-col>

          <a-col :xs="24" :md="8">
            <a-form-item label="Phone" name="phone">
              <Input
                v-model="form.phone"
                placeholder="+1234567890"
                :maxlength="20"
              />
            </a-form-item>
          </a-col>
        </a-row>

        <a-row :gutter="12">
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
            <a-form-item label="Logo URL" name="logo">
              <Input
                v-model="form.logo"
                placeholder="https://example.com/logo.png"
                :maxlength="255"
              />
            </a-form-item>
          </a-col>
        </a-row>

        <a-form-item label="Verification Status" name="is_verified">
          <a-select
            v-model:value="form.is_verified"
            placeholder="Select verification status"
            style="width: 100%"
          >
            <a-select-option :value="true">Verified</a-select-option>
            <a-select-option :value="false">Unverified</a-select-option>
          </a-select>
          <template #extra>
            <span class="form-extra-text">Verified teams are publicly visible</span>
          </template>
        </a-form-item>

        <a-form-item>
          <a-space>
            <a-button type="primary" html-type="submit" :loading="saving">
              Update Team
            </a-button>
            <a-button @click="handleCancel">Cancel</a-button>
          </a-space>
        </a-form-item>
      </a-form>
    </a-card>
  </DashboardLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import DashboardLayout from '../../../Layouts/DashboardLayout.vue';
import Breadcrumb from '../../../Components/Breadcrumb.vue';
import Input from '../../../Components/Input.vue';

const page = usePage();
const formRef = ref(null);
const saving = ref(false);
const isSlugManuallyEdited = ref(false);
const initialSlug = ref('');

const team = computed(() => page.props.team || {});
const users = computed(() => page.props.users || []);

const breadcrumbItems = ref([
  { label: 'Dashboard', href: '/dashboard' },
  { label: 'Teams', href: '/dashboard/teams' },
  { label: 'Edit' },
]);

const form = reactive({
  name: '',
  slug: '',
  description: '',
  owner_id: null,
  website: '',
  email: '',
  phone: '',
  logo: '',
  is_verified: false,
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
  if (form.slug && form.slug !== generateSlug(form.name) && form.slug !== initialSlug.value) {
    isSlugManuallyEdited.value = true;
  }
});

const filterOption = (input, option) => {
  return option.children[0].children.toLowerCase().indexOf(input.toLowerCase()) >= 0;
};

const rules = {
  name: [
    { required: true, message: 'Please enter team name', trigger: 'blur' },
    { max: 255, message: 'Name cannot exceed 255 characters', trigger: 'blur' },
  ],
  slug: [
    { max: 255, message: 'Slug cannot exceed 255 characters', trigger: 'blur' },
  ],
  owner_id: [
    { required: true, message: 'Please select team owner', trigger: 'change' },
  ],
  email: [
    { type: 'email', message: 'Please enter a valid email address', trigger: 'blur' },
  ],
  website: [
    { type: 'url', message: 'Please enter a valid URL', trigger: 'blur' },
  ],
};

onMounted(() => {
  if (team.value) {
    Object.assign(form, {
      name: team.value.name || '',
      slug: team.value.slug || '',
      description: team.value.description || '',
      owner_id: team.value.owner_id || null,
      website: team.value.website || '',
      email: team.value.email || '',
      phone: team.value.phone || '',
      logo: team.value.logo || '',
      is_verified: team.value.is_verified !== undefined ? team.value.is_verified : false,
    });
    initialSlug.value = team.value.slug || '';
  }
});

const handleSubmit = async () => {
  try {
    await formRef.value.validate();
    saving.value = true;

    router.put(`/dashboard/teams/${team.value.id}`, form, {
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
  router.visit('/dashboard/teams');
};
</script>

<style scoped>
.team-form-card {
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

