<template>
  <DashboardLayout>
    <Breadcrumb :items="breadcrumbItems" />

    <a-card class="team-member-form-card">
      <template #title>
        <h2 class="card-title">Add Team Member</h2>
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
            <a-form-item label="Team" name="team_id" required>
              <a-select
                v-model:value="form.team_id"
                placeholder="Select team"
                show-search
                :filter-option="filterTeamOption"
                style="width: 100%"
              >
                <a-select-option
                  v-for="team in teams"
                  :key="team.id"
                  :value="team.id"
                >
                  {{ team.name }}
                </a-select-option>
              </a-select>
            </a-form-item>
          </a-col>

          <a-col :xs="24" :md="12">
            <a-form-item label="User" name="user_id" required>
              <a-select
                v-model:value="form.user_id"
                placeholder="Select user"
                show-search
                :filter-option="filterUserOption"
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
        </a-row>

        <a-row :gutter="12">
          <a-col :xs="24" :md="12">
            <a-form-item label="Role" name="role" required>
              <a-select
                v-model:value="form.role"
                placeholder="Select role"
                style="width: 100%"
              >
                <a-select-option value="owner">Owner</a-select-option>
                <a-select-option value="admin">Admin</a-select-option>
                <a-select-option value="manager">Manager</a-select-option>
                <a-select-option value="member">Member</a-select-option>
              </a-select>
            </a-form-item>
          </a-col>

          <a-col :xs="24" :md="12">
            <a-form-item label="Invited By" name="invited_by">
              <a-select
                v-model:value="form.invited_by"
                placeholder="Select inviter (optional)"
                show-search
                :filter-option="filterUserOption"
                allow-clear
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
              <template #extra>
                <span class="form-extra-text">Leave empty to use current user</span>
              </template>
            </a-form-item>
          </a-col>
        </a-row>

        <a-form-item label="Joined At" name="joined_at">
          <a-date-picker
            v-model:value="form.joined_at"
            show-time
            format="YYYY-MM-DD HH:mm:ss"
            style="width: 100%"
            placeholder="Select join date"
          />
          <template #extra>
            <span class="form-extra-text">Leave empty to use current date</span>
          </template>
        </a-form-item>

        <a-form-item label="Permissions" name="permissions">
          <a-textarea
            v-model:value="permissionsText"
            placeholder="Enter permissions as JSON array, e.g., [&quot;view_events&quot;, &quot;create_events&quot;]"
            :rows="4"
            @blur="handlePermissionsBlur"
          />
          <template #extra>
            <span class="form-extra-text">Enter permissions as a JSON array</span>
          </template>
        </a-form-item>

        <a-form-item>
          <a-space>
            <a-button type="primary" html-type="submit" :loading="saving">
              Add Member
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
import dayjs from 'dayjs';

const page = usePage();
const formRef = ref(null);
const saving = ref(false);

const teams = computed(() => page.props.teams || []);
const users = computed(() => page.props.users || []);

const breadcrumbItems = ref([
  { label: 'Dashboard', href: '/dashboard' },
  { label: 'Team Members', href: '/dashboard/team-members' },
  { label: 'Add Member' },
]);

const form = reactive({
  team_id: null,
  user_id: null,
  role: null,
  invited_by: null,
  joined_at: null,
  permissions: null,
});

const permissionsText = ref('');

const filterTeamOption = (input, option) => {
  return option.children[0].children.toLowerCase().indexOf(input.toLowerCase()) >= 0;
};

const filterUserOption = (input, option) => {
  return option.children[0].children.toLowerCase().indexOf(input.toLowerCase()) >= 0;
};

const handlePermissionsBlur = () => {
  if (permissionsText.value.trim()) {
    try {
      const parsed = JSON.parse(permissionsText.value);
      if (Array.isArray(parsed)) {
        form.permissions = parsed;
      } else {
        form.permissions = null;
      }
    } catch (e) {
      form.permissions = null;
    }
  } else {
    form.permissions = null;
  }
};

const rules = {
  team_id: [
    { required: true, message: 'Please select a team', trigger: 'change' },
  ],
  user_id: [
    { required: true, message: 'Please select a user', trigger: 'change' },
  ],
  role: [
    { required: true, message: 'Please select a role', trigger: 'change' },
  ],
};

const handleSubmit = async () => {
  try {
    await formRef.value.validate();
    saving.value = true;

    const submitData = {
      ...form,
      joined_at: form.joined_at ? dayjs(form.joined_at).format('YYYY-MM-DD HH:mm:ss') : null,
    };

    router.post('/dashboard/team-members', submitData, {
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
  router.visit('/dashboard/team-members');
};
</script>

<style scoped>
.team-member-form-card {
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

