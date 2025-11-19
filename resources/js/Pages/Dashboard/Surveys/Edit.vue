<template>
  <DashboardLayout>
    <Breadcrumb :items="breadcrumbItems" />

    <a-card class="survey-form-card">
      <template #title>
        <h2 class="card-title">Edit Survey</h2>
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
            <a-form-item label="Event" name="event_id" required>
              <a-select
                v-model:value="form.event_id"
                placeholder="Select event"
                show-search
                :filter-option="filterEventOption"
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
            <a-form-item label="Status" name="status" required>
              <a-select
                v-model:value="form.status"
                placeholder="Select status"
                style="width: 100%"
              >
                <a-select-option value="draft">Draft</a-select-option>
                <a-select-option value="active">Active</a-select-option>
                <a-select-option value="closed">Closed</a-select-option>
              </a-select>
            </a-form-item>
          </a-col>
        </a-row>

        <a-form-item label="Title" name="title" required>
          <Input
            v-model="form.title"
            placeholder="Enter survey title"
            :maxlength="255"
          />
        </a-form-item>

        <a-form-item label="Description" name="description">
          <a-textarea
            v-model:value="form.description"
            placeholder="Enter survey description"
            :rows="4"
            show-count
          />
        </a-form-item>

        <a-row :gutter="16">
          <a-col :xs="24" :md="12">
            <a-form-item label="Opens At" name="opens_at">
              <a-date-picker
                v-model:value="opensAt"
                show-time
                format="YYYY-MM-DD HH:mm:ss"
                placeholder="Select opening date and time"
                style="width: 100%"
              />
            </a-form-item>
          </a-col>

          <a-col :xs="24" :md="12">
            <a-form-item label="Closes At" name="closes_at">
              <a-date-picker
                v-model:value="closesAt"
                show-time
                format="YYYY-MM-DD HH:mm:ss"
                placeholder="Select closing date and time"
                style="width: 100%"
              />
            </a-form-item>
          </a-col>
        </a-row>

        <a-form-item label="Required" name="is_required">
          <a-switch v-model:checked="form.is_required" />
          <template #extra>
            <span class="form-extra-text">Mark survey as required for event attendees</span>
          </template>
        </a-form-item>

        <a-form-item>
          <a-space>
            <a-button type="primary" html-type="submit" :loading="saving">
              Update Survey
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
const opensAt = ref(null);
const closesAt = ref(null);

const survey = computed(() => page.props.survey || {});
const events = computed(() => page.props.events || []);

const breadcrumbItems = ref([
  { label: 'Dashboard', href: '/dashboard' },
  { label: 'Surveys', href: '/dashboard/surveys' },
  { label: 'Edit' },
]);

const form = reactive({
  event_id: null,
  title: '',
  description: '',
  is_required: false,
  status: 'draft',
});

const rules = {
  event_id: [
    { required: true, message: 'Please select an event', trigger: 'change' },
  ],
  title: [
    { required: true, message: 'Please enter survey title', trigger: 'blur' },
    { max: 255, message: 'Title cannot exceed 255 characters', trigger: 'blur' },
  ],
  status: [
    { required: true, message: 'Please select status', trigger: 'change' },
  ],
};

onMounted(() => {
  if (survey.value) {
    form.event_id = survey.value.event_id || null;
    form.title = survey.value.title || '';
    form.description = survey.value.description || '';
    form.is_required = survey.value.is_required || false;
    form.status = survey.value.status || 'draft';
    
    if (survey.value.opens_at) {
      opensAt.value = dayjs(survey.value.opens_at);
    }
    if (survey.value.closes_at) {
      closesAt.value = dayjs(survey.value.closes_at);
    }
  }
});

const filterEventOption = (input, option) => {
  const searchText = input.toLowerCase();
  const eventTitle = option.children[0].children.toLowerCase();
  return eventTitle.includes(searchText);
};

const handleSubmit = async () => {
  try {
    await formRef.value.validate();
    
    saving.value = true;

    const formData = {
      ...form,
      opens_at: opensAt.value ? opensAt.value.format('YYYY-MM-DD HH:mm:ss') : null,
      closes_at: closesAt.value ? closesAt.value.format('YYYY-MM-DD HH:mm:ss') : null,
    };

    router.put(`/dashboard/surveys/${survey.value.id}`, formData, {
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
  router.visit('/dashboard/surveys');
};
</script>

<style scoped>
.survey-form-card {
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

