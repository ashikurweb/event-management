<template>
  <DashboardLayout>
    <Breadcrumb :items="breadcrumbItems" />

    <a-card class="venue-form-card">
      <template #title>
        <h2 class="card-title">Edit Venue</h2>
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
                placeholder="Enter venue name"
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
            placeholder="Enter venue description"
            :rows="4"
            show-count
          />
        </a-form-item>

        <a-form-item label="Address" name="address" required>
          <a-textarea
            v-model:value="form.address"
            placeholder="Enter full address"
            :rows="2"
          />
        </a-form-item>

        <a-row :gutter="16">
          <a-col :xs="24" :md="8">
            <a-form-item label="City" name="city" required>
              <Input
                v-model="form.city"
                placeholder="Enter city"
                :maxlength="100"
              />
            </a-form-item>
          </a-col>

          <a-col :xs="24" :md="8">
            <a-form-item label="State" name="state">
              <Input
                v-model="form.state"
                placeholder="Enter state/province"
                :maxlength="100"
              />
            </a-form-item>
          </a-col>

          <a-col :xs="24" :md="8">
            <a-form-item label="Country" name="country" required>
              <Input
                v-model="form.country"
                placeholder="Enter country"
                :maxlength="100"
              />
            </a-form-item>
          </a-col>
        </a-row>

        <a-row :gutter="16">
          <a-col :xs="24" :md="12">
            <a-form-item label="Postal Code" name="postal_code">
              <Input
                v-model="form.postal_code"
                placeholder="Enter postal code"
                :maxlength="20"
              />
            </a-form-item>
          </a-col>

          <a-col :xs="24" :md="12">
            <a-form-item label="Capacity" name="capacity">
              <a-input-number
                v-model:value="form.capacity"
                :min="1"
                placeholder="Enter capacity"
                style="width: 100%"
              />
            </a-form-item>
          </a-col>
        </a-row>

        <a-row :gutter="16">
          <a-col :xs="24" :md="12">
            <a-form-item label="Latitude" name="latitude">
              <a-input-number
                v-model:value="form.latitude"
                :min="-90"
                :max="90"
                :step="0.00000001"
                :precision="8"
                placeholder="e.g., 40.7128"
                style="width: 100%"
              />
            </a-form-item>
          </a-col>

          <a-col :xs="24" :md="12">
            <a-form-item label="Longitude" name="longitude">
              <a-input-number
                v-model:value="form.longitude"
                :min="-180"
                :max="180"
                :step="0.00000001"
                :precision="8"
                placeholder="e.g., -74.0060"
                style="width: 100%"
              />
            </a-form-item>
          </a-col>
        </a-row>

        <a-form-item label="Facilities" name="facilities">
          <a-select
            v-model:value="form.facilities"
            mode="tags"
            placeholder="Add facilities (press Enter to add)"
            :max-tag-count="20"
            style="width: 100%"
          >
          </a-select>
          <template #extra>
            <span class="form-extra-text">Press Enter after each facility (e.g., WiFi, Parking, AC, Catering)</span>
          </template>
        </a-form-item>

        <a-form-item label="Images" name="images">
          <div v-if="existingImages.length > 0" class="existing-images">
            <div class="image-grid">
              <div v-for="(image, index) in existingImages" :key="index" class="image-item">
                <a-image
                  :src="image"
                  :width="120"
                  :preview="true"
                />
                <a-button
                  type="link"
                  danger
                  size="small"
                  @click="removeImage(index)"
                  style="margin-top: 4px; display: block;"
                >
                  Remove
                </a-button>
              </div>
            </div>
          </div>
          <FileUploader
            v-model="imageFiles"
            :multiple="true"
            :accept="['image/*']"
            :max-size="5 * 1024 * 1024"
            :max-files="10"
            drag-text="Drag & drop images here"
            subtitle="or click to browse (multiple images allowed)"
            @error="handleFileError"
          />
          <template #extra>
            <span class="form-extra-text">Max 10 images, 5MB each. Supported formats: JPG, PNG, GIF, WebP</span>
          </template>
        </a-form-item>

        <a-divider>Contact Information</a-divider>

        <a-row :gutter="16">
          <a-col :xs="24" :md="12">
            <a-form-item label="Contact Name" name="contact_name">
              <Input
                v-model="form.contact_name"
                placeholder="Enter contact name"
                :maxlength="255"
              />
            </a-form-item>
          </a-col>

          <a-col :xs="24" :md="12">
            <a-form-item label="Contact Email" name="contact_email">
              <Input
                v-model="form.contact_email"
                type="email"
                placeholder="Enter contact email"
                :maxlength="255"
              />
            </a-form-item>
          </a-col>
        </a-row>

        <a-row :gutter="16">
          <a-col :xs="24" :md="12">
            <a-form-item label="Contact Phone" name="contact_phone">
              <Input
                v-model="form.contact_phone"
                placeholder="Enter contact phone"
                :maxlength="20"
              />
            </a-form-item>
          </a-col>

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
                <span class="form-extra-text">Mark venue as verified</span>
              </template>
            </a-form-item>
          </a-col>
        </a-row>

        <a-form-item>
          <a-space>
            <a-button type="primary" html-type="submit" :loading="saving">
              Update Venue
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
import FileUploader from '../../../Components/FileUploader.vue';
import { message } from 'ant-design-vue';

const page = usePage();
const formRef = ref(null);
const saving = ref(false);
const imageFiles = ref([]);
const imagesToRemove = ref([]);
const isSlugManuallyEdited = ref(false);
const initialSlug = ref('');

const venue = computed(() => page.props.venue || {});

const existingImages = computed(() => {
  return venue.value.image_urls || [];
});

const breadcrumbItems = ref([
  { label: 'Dashboard', href: '/dashboard' },
  { label: 'Venues', href: '/dashboard/venues' },
  { label: 'Edit' },
]);

const form = reactive({
  name: '',
  slug: '',
  description: '',
  address: '',
  city: '',
  state: '',
  country: '',
  postal_code: '',
  latitude: null,
  longitude: null,
  capacity: null,
  facilities: [],
  contact_name: '',
  contact_email: '',
  contact_phone: '',
  website: '',
  rating: 0.00,
  is_verified: false,
});

// Generate slug from name
const generateSlug = (text) => {
  return text
    .toLowerCase()
    .trim()
    .replace(/[^\w\s-]/g, '')
    .replace(/[\s_-]+/g, '-')
    .replace(/^-+|-+$/g, '');
};

onMounted(() => {
  if (venue.value) {
    form.name = venue.value.name || '';
    form.slug = venue.value.slug || '';
    initialSlug.value = venue.value.slug || '';
    form.description = venue.value.description || '';
    form.address = venue.value.address || '';
    form.city = venue.value.city || '';
    form.state = venue.value.state || '';
    form.country = venue.value.country || '';
    form.postal_code = venue.value.postal_code || '';
    form.latitude = venue.value.latitude || null;
    form.longitude = venue.value.longitude || null;
    form.capacity = venue.value.capacity || null;
    form.facilities = venue.value.facilities || [];
    form.contact_name = venue.value.contact_name || '';
    form.contact_email = venue.value.contact_email || '';
    form.contact_phone = venue.value.contact_phone || '';
    form.website = venue.value.website || '';
    form.rating = venue.value.rating || 0.00;
    form.is_verified = venue.value.is_verified || false;
  }
});

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

const rules = {
  name: [
    { required: true, message: 'Please enter venue name', trigger: 'blur' },
    { max: 255, message: 'Name cannot exceed 255 characters', trigger: 'blur' },
  ],
  address: [
    { required: true, message: 'Please enter address', trigger: 'blur' },
  ],
  city: [
    { required: true, message: 'Please enter city', trigger: 'blur' },
    { max: 100, message: 'City cannot exceed 100 characters', trigger: 'blur' },
  ],
  country: [
    { required: true, message: 'Please enter country', trigger: 'blur' },
    { max: 100, message: 'Country cannot exceed 100 characters', trigger: 'blur' },
  ],
  contact_email: [
    { type: 'email', message: 'Please enter a valid email address', trigger: 'blur' },
  ],
  website: [
    { type: 'url', message: 'Please enter a valid URL', trigger: 'blur' },
  ],
  rating: [
    { type: 'number', min: 0, max: 5, message: 'Rating must be between 0 and 5', trigger: 'blur' },
  ],
};

const removeImage = (index) => {
  const imageUrl = existingImages.value[index];
  imagesToRemove.value.push(imageUrl);
  existingImages.value.splice(index, 1);
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
      if (key === 'facilities') {
        // Append facilities as array items
        if (form.facilities && form.facilities.length > 0) {
          form.facilities.forEach((facility, index) => {
            if (facility && facility.trim()) {
              formData.append(`facilities[${index}]`, facility);
            }
          });
        }
      } else if (form[key] !== null && form[key] !== '' && form[key] !== false) {
        formData.append(key, form[key]);
      } else if (form[key] === false) {
        formData.append(key, '0');
      } else if (form[key] === true) {
        formData.append(key, '1');
      }
    });

    // Handle images removal
    if (imagesToRemove.value.length > 0) {
      imagesToRemove.value.forEach((url) => {
        formData.append('remove_images[]', url);
      });
    }

    // Add new image files
    if (imageFiles.value.length > 0) {
      imageFiles.value.forEach((fileObj) => {
        if (fileObj.file) {
          formData.append('images[]', fileObj.file);
        }
      });
    }

    router.post(`/dashboard/venues/${venue.value.id}`, formData, {
      preserveScroll: true,
      forceFormData: true,
      _method: 'PUT',
      onSuccess: () => {
        message.success('Venue updated successfully');
      },
      onError: (errors) => {
        saving.value = false;
        if (errors.images) {
          message.error(Array.isArray(errors.images) ? errors.images.join(', ') : errors.images);
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
  router.visit('/dashboard/venues');
};
</script>

<style scoped>
.venue-form-card {
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

.existing-images {
  margin-bottom: 16px;
}

.image-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
  gap: 12px;
  margin-bottom: 16px;
}

.image-item {
  display: flex;
  flex-direction: column;
  align-items: center;
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

