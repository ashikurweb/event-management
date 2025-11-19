<template>
  <DashboardLayout>
    <Breadcrumb :items="breadcrumbItems" />

    <a-card class="promo-code-form-card">
      <template #title>
        <h2 class="card-title">Edit Promo Code</h2>
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
            <a-form-item label="Code" name="code" required>
              <a-input
                v-model:value="form.code"
                placeholder="Enter promo code (e.g., SAVE20)"
                :maxlength="50"
                style="text-transform: uppercase"
              />
            </a-form-item>
          </a-col>

          <a-col :xs="24" :md="12">
            <a-form-item label="Discount Type" name="discount_type" required>
              <a-select
                v-model:value="form.discount_type"
                placeholder="Select discount type"
                style="width: 100%"
                @change="handleDiscountTypeChange"
              >
                <a-select-option value="percentage">Percentage</a-select-option>
                <a-select-option value="fixed">Fixed Amount</a-select-option>
                <a-select-option value="free_ticket">Free Ticket</a-select-option>
              </a-select>
            </a-form-item>
          </a-col>
        </a-row>

        <a-row :gutter="16">
          <a-col :xs="24" :md="12">
            <a-form-item label="Discount Value" name="discount_value" required>
              <a-input-number
                v-model:value="form.discount_value"
                :placeholder="discountPlaceholder"
                style="width: 100%"
                :min="0"
                :precision="form.discount_type === 'percentage' ? 2 : 2"
                :max="form.discount_type === 'percentage' ? 100 : undefined"
              >
                <template #addonAfter>
                  <span v-if="form.discount_type === 'percentage'">%</span>
                  <span v-else-if="form.discount_type === 'fixed'">$</span>
                  <span v-else>—</span>
                </template>
              </a-input-number>
            </a-form-item>
          </a-col>

          <a-col :xs="24" :md="12">
            <a-form-item label="Minimum Order Amount" name="min_order_amount">
              <a-input-number
                v-model:value="form.min_order_amount"
                placeholder="Enter minimum order amount (optional)"
                style="width: 100%"
                :min="0"
                :precision="2"
              >
                <template #addonBefore>$</template>
              </a-input-number>
            </a-form-item>
          </a-col>
        </a-row>

        <a-form-item label="Description" name="description">
          <a-textarea
            v-model:value="form.description"
            placeholder="Enter promo code description (optional)"
            :rows="3"
          />
        </a-form-item>

        <a-row :gutter="16">
          <a-col :xs="24" :md="12">
            <a-form-item label="Applicable To" name="applicable_to" required>
              <a-select
                v-model:value="form.applicable_to"
                placeholder="Select where this applies"
                style="width: 100%"
                @change="handleApplicableToChange"
              >
                <a-select-option value="all">All Events</a-select-option>
                <a-select-option value="specific_events">Specific Events</a-select-option>
                <a-select-option value="specific_categories">Specific Categories</a-select-option>
              </a-select>
            </a-form-item>
          </a-col>

          <a-col :xs="24" :md="12">
            <a-form-item label="Is Active" name="is_active">
              <a-switch v-model:checked="form.is_active" />
            </a-form-item>
          </a-col>
        </a-row>

        <!-- Events Selection (if applicable_to is specific_events) -->
        <a-form-item
          v-if="form.applicable_to === 'specific_events'"
          label="Select Events"
          name="events"
          required
        >
          <a-select
            v-model:value="form.events"
            mode="multiple"
            placeholder="Select events"
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

        <!-- Categories Selection (if applicable_to is specific_categories) -->
        <a-form-item
          v-if="form.applicable_to === 'specific_categories'"
          label="Select Categories"
          name="categories"
          required
        >
          <a-select
            v-model:value="form.categories"
            mode="multiple"
            placeholder="Select categories"
            show-search
            :filter-option="filterOption"
            style="width: 100%"
          >
            <a-select-option
              v-for="category in categories"
              :key="category.id"
              :value="category.id"
            >
              {{ category.name }}
            </a-select-option>
          </a-select>
        </a-form-item>

        <a-row :gutter="16">
          <a-col :xs="24" :md="12">
            <a-form-item label="Valid From" name="valid_from" required>
              <a-date-picker
                v-model:value="form.valid_from"
                show-time
                format="YYYY-MM-DD HH:mm:ss"
                style="width: 100%"
                placeholder="Select start date and time"
              />
            </a-form-item>
          </a-col>

          <a-col :xs="24" :md="12">
            <a-form-item label="Valid Until" name="valid_until" required>
              <a-date-picker
                v-model:value="form.valid_until"
                show-time
                format="YYYY-MM-DD HH:mm:ss"
                style="width: 100%"
                placeholder="Select end date and time"
              />
            </a-form-item>
          </a-col>
        </a-row>

        <a-row :gutter="16">
          <a-col :xs="24" :md="12">
            <a-form-item label="Max Uses" name="max_uses">
              <a-input-number
                v-model:value="form.max_uses"
                placeholder="Leave empty for unlimited"
                style="width: 100%"
                :min="1"
              />
              <template #extra>
                <span class="form-extra-text">Leave empty for unlimited uses</span>
              </template>
            </a-form-item>
          </a-col>

          <a-col :xs="24" :md="12">
            <a-form-item label="Max Uses Per User" name="max_uses_per_user">
              <a-input-number
                v-model:value="form.max_uses_per_user"
                placeholder="Default: 1"
                style="width: 100%"
                :min="1"
              />
            </a-form-item>
          </a-col>
        </a-row>

        <a-form-item>
          <a-space>
            <a-button type="primary" html-type="submit" :loading="saving" size="large">
              Update Promo Code
            </a-button>
            <a-button @click="handleCancel" size="large">Cancel</a-button>
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

const events = computed(() => page.props.events || []);
const categories = computed(() => page.props.categories || []);

const breadcrumbItems = ref([
  { label: 'Dashboard', href: '/dashboard' },
  { label: 'Promo Codes', href: '/dashboard/promo-codes' },
  { label: 'Edit' },
]);

const promoCode = computed(() => page.props.promoCode);

const form = reactive({
  code: promoCode.value?.code || '',
  description: promoCode.value?.description || '',
  discount_type: promoCode.value?.discount_type || 'percentage',
  discount_value: promoCode.value?.discount_value || null,
  applicable_to: promoCode.value?.applicable_to || 'all',
  max_uses: promoCode.value?.max_uses || null,
  max_uses_per_user: promoCode.value?.max_uses_per_user || 1,
  min_order_amount: promoCode.value?.min_order_amount || null,
  valid_from: promoCode.value?.valid_from ? dayjs(promoCode.value.valid_from) : null,
  valid_until: promoCode.value?.valid_until ? dayjs(promoCode.value.valid_until) : null,
  is_active: promoCode.value?.is_active !== undefined ? promoCode.value.is_active : true,
  events: promoCode.value?.events ? promoCode.value.events.map(e => e.id) : [],
  categories: [],
});

const discountPlaceholder = computed(() => {
  if (form.discount_type === 'percentage') {
    return 'Enter percentage (0-100)';
  } else if (form.discount_type === 'fixed') {
    return 'Enter fixed amount';
  }
  return 'N/A';
});

const filterOption = (input, option) => {
  return option.children[0].children.toLowerCase().indexOf(input.toLowerCase()) >= 0;
};

const rules = {
  code: [
    { required: true, message: 'Please enter promo code', trigger: 'blur' },
    { max: 50, message: 'Code cannot exceed 50 characters', trigger: 'blur' },
  ],
  discount_type: [
    { required: true, message: 'Please select discount type', trigger: 'change' },
  ],
  discount_value: [
    { required: true, message: 'Please enter discount value', trigger: 'blur' },
    { type: 'number', min: 0, message: 'Discount value must be at least 0', trigger: 'blur' },
  ],
  applicable_to: [
    { required: true, message: 'Please select where this applies', trigger: 'change' },
  ],
  valid_from: [
    { required: true, message: 'Please select valid from date', trigger: 'change' },
  ],
  valid_until: [
    { required: true, message: 'Please select valid until date', trigger: 'change' },
  ],
  events: [
    { required: true, message: 'Please select at least one event', trigger: 'change' },
  ],
  categories: [
    { required: true, message: 'Please select at least one category', trigger: 'change' },
  ],
};

const handleDiscountTypeChange = () => {
  // Reset discount value when type changes
  if (form.discount_type === 'free_ticket') {
    form.discount_value = 0;
  }
};

const handleApplicableToChange = () => {
  // Clear selections when applicable_to changes
  form.events = [];
  form.categories = [];
};

const handleSubmit = async () => {
  try {
    await formRef.value.validate();
    saving.value = true;

    const submitData = {
      ...form,
      code: form.code.toUpperCase(),
      valid_from: form.valid_from ? form.valid_from.format('YYYY-MM-DD HH:mm:ss') : null,
      valid_until: form.valid_until ? form.valid_until.format('YYYY-MM-DD HH:mm:ss') : null,
    };

    router.put(`/dashboard/promo-codes/${promoCode.value.id}`, submitData, {
      preserveScroll: true,
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
  router.visit('/dashboard/promo-codes');
};
</script>

<style scoped>
.promo-code-form-card {
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
  margin-bottom: 16px;
}

:deep(.ant-form-item-label > label) {
  color: var(--text-primary, #262626);
  font-weight: 500;
}

[data-theme="dark"] :deep(.ant-form-item-label > label) {
  color: rgba(255, 255, 255, 0.85);
}
</style>

