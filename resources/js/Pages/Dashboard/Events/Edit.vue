<template>
  <DashboardLayout>
    <Breadcrumb :items="breadcrumbItems" />

    <a-card class="event-form-card">
      <template #title>
        <h2 class="card-title">Edit Event</h2>
      </template>

      <a-form :model="form" :rules="rules" layout="vertical" ref="formRef" @finish="handleSubmit">
        <a-tabs default-active-key="basic">
          <!-- Basic Information -->
          <a-tab-pane key="basic" tab="Basic Information">
            <a-row :gutter="16">
              <a-col :xs="24" :md="12">
                <a-form-item label="Organizer" name="organizer_id" required>
                  <a-select v-model:value="form.organizer_id" placeholder="Select an organizer" show-search
                    :filter-option="filterOption" style="width: 100%">
                    <a-select-option v-for="organizer in organizers" :key="organizer.id" :value="organizer.id">
                      {{ organizer.first_name }} {{ organizer.last_name }} ({{ organizer.email }})
                    </a-select-option>
                  </a-select>
                </a-form-item>
              </a-col>

              <a-col :xs="24" :md="12">
                <a-form-item label="Category" name="category_id">
                  <a-select v-model:value="form.category_id" placeholder="Select a category (optional)" allow-clear
                    show-search :filter-option="filterOption" style="width: 100%">
                    <a-select-option v-for="category in categories" :key="category.id" :value="category.id">
                      {{ category.name }}
                    </a-select-option>
                  </a-select>
                </a-form-item>
              </a-col>
            </a-row>

            <a-row :gutter="16">
              <a-col :xs="24" :md="16">
                <a-form-item label="Title" name="title" required>
                  <a-input v-model:value="form.title" placeholder="Enter event title" :maxlength="255" />
                </a-form-item>
              </a-col>

              <a-col :xs="24" :md="8">
                <a-form-item label="Slug" name="slug">
                  <a-input v-model:value="form.slug" placeholder="Auto-generated from title" :maxlength="255" />
                  <template #extra>
                    <span class="form-extra-text">Auto-generated if left empty</span>
                  </template>
                </a-form-item>
              </a-col>
            </a-row>

            <a-form-item label="Short Description" name="short_description">
              <a-textarea v-model:value="form.short_description"
                placeholder="Enter a short description (max 500 characters)" :rows="3" :maxlength="500" show-count />
            </a-form-item>

            <a-form-item label="Description" name="description">
              <a-textarea v-model:value="form.description" placeholder="Enter full event description" :rows="6" />
            </a-form-item>

            <a-row :gutter="16">
              <a-col :xs="24" :md="8">
                <a-form-item label="Event Type" name="event_type" required>
                  <a-select v-model:value="form.event_type" placeholder="Select event type" style="width: 100%">
                    <a-select-option value="online">Online</a-select-option>
                    <a-select-option value="offline">Offline</a-select-option>
                    <a-select-option value="hybrid">Hybrid</a-select-option>
                  </a-select>
                </a-form-item>
              </a-col>

              <a-col :xs="24" :md="8">
                <a-form-item label="Status" name="status">
                  <a-select v-model:value="form.status" placeholder="Select status" style="width: 100%">
                    <a-select-option value="draft">Draft</a-select-option>
                    <a-select-option value="published">Published</a-select-option>
                    <a-select-option value="cancelled">Cancelled</a-select-option>
                    <a-select-option value="completed">Completed</a-select-option>
                    <a-select-option value="postponed">Postponed</a-select-option>
                  </a-select>
                </a-form-item>
              </a-col>

              <a-col :xs="24" :md="8">
                <a-form-item label="Visibility" name="visibility">
                  <a-select v-model:value="form.visibility" placeholder="Select visibility" style="width: 100%">
                    <a-select-option value="public">Public</a-select-option>
                    <a-select-option value="private">Private</a-select-option>
                    <a-select-option value="unlisted">Unlisted</a-select-option>
                  </a-select>
                </a-form-item>
              </a-col>
            </a-row>

            <a-form-item label="Tags" name="tags">
              <a-select v-model:value="form.tags" mode="multiple" placeholder="Select tags" show-search
                :filter-option="filterOption" style="width: 100%">
                <a-select-option v-for="tag in tags" :key="tag.id" :value="tag.id">
                  {{ tag.name }}
                </a-select-option>
              </a-select>
            </a-form-item>
          </a-tab-pane>

          <!-- Date & Time -->
          <a-tab-pane key="datetime" tab="Date & Time">
            <a-row :gutter="16">
              <a-col :xs="24" :md="12">
                <a-form-item label="Start Date & Time" name="start_date" required>
                  <a-date-picker v-model:value="form.start_date" show-time format="YYYY-MM-DD HH:mm:ss"
                    style="width: 100%" placeholder="Select start date and time" />
                </a-form-item>
              </a-col>

              <a-col :xs="24" :md="12">
                <a-form-item label="End Date & Time" name="end_date" required>
                  <a-date-picker v-model:value="form.end_date" show-time format="YYYY-MM-DD HH:mm:ss"
                    style="width: 100%" placeholder="Select end date and time" />
                </a-form-item>
              </a-col>
            </a-row>

            <a-row :gutter="16">
              <a-col :xs="24" :md="12">
                <a-form-item label="Timezone" name="timezone" required>
                  <a-input v-model:value="form.timezone" placeholder="e.g., UTC, America/New_York" :maxlength="50" />
                </a-form-item>
              </a-col>
            </a-row>

            <a-row :gutter="16">
              <a-col :xs="24" :md="12">
                <a-form-item label="Registration Start" name="registration_start">
                  <a-date-picker v-model:value="form.registration_start" show-time format="YYYY-MM-DD HH:mm:ss"
                    style="width: 100%" placeholder="Select registration start date" />
                </a-form-item>
              </a-col>

              <a-col :xs="24" :md="12">
                <a-form-item label="Registration End" name="registration_end">
                  <a-date-picker v-model:value="form.registration_end" show-time format="YYYY-MM-DD HH:mm:ss"
                    style="width: 100%" placeholder="Select registration end date" />
                </a-form-item>
              </a-col>
            </a-row>
          </a-tab-pane>

          <!-- Location (for offline/hybrid) -->
          <a-tab-pane key="location" tab="Location">
            <!-- Venue Selection from Database -->
          </a-tab-pane>

          <!-- Online Details (for online/hybrid) -->
          <a-tab-pane key="online" tab="Online Details">
            <a-row :gutter="16">
              <a-col :xs="24">
                <a-form-item label="Meeting URL" name="meeting_url">
                  <a-input v-model:value="form.meeting_url" placeholder="Enter meeting URL" />
                </a-form-item>
              </a-col>
            </a-row>

            <a-row :gutter="16">
              <a-col :xs="24" :md="12">
                <a-form-item label="Meeting Platform" name="meeting_platform">
                  <a-input v-model:value="form.meeting_platform" placeholder="e.g., Zoom, Google Meet, Teams"
                    :maxlength="50" />
                </a-form-item>
              </a-col>

              <a-col :xs="24" :md="12">
                <a-form-item label="Meeting ID" name="meeting_id">
                  <a-input v-model:value="form.meeting_id" placeholder="Enter meeting ID" :maxlength="255" />
                </a-form-item>
              </a-col>
            </a-row>

            <a-row :gutter="16">
              <a-col :xs="24" :md="12">
                <a-form-item label="Meeting Password" name="meeting_password">
                  <a-input-password v-model:value="form.meeting_password" placeholder="Enter meeting password"
                    :maxlength="255" />
                </a-form-item>
              </a-col>
            </a-row>
          </a-tab-pane>

          <!-- Capacity & Settings -->
          <a-tab-pane key="capacity" tab="Capacity & Settings">
            <a-row :gutter="16">
              <a-col :xs="24" :md="8">
                <a-form-item label="Max Attendees" name="max_attendees">
                  <a-input-number v-model:value="form.max_attendees" placeholder="Enter max attendees"
                    style="width: 100%" :min="1" />
                </a-form-item>
              </a-col>

              <a-col :xs="24" :md="8">
                <a-form-item label="Min Attendees" name="min_attendees">
                  <a-input-number v-model:value="form.min_attendees" placeholder="Enter min attendees"
                    style="width: 100%" :min="1" />
                </a-form-item>
              </a-col>

              <a-col :xs="24" :md="8">
                <a-form-item label="Waitlist Enabled" name="waitlist_enabled">
                  <a-switch v-model:checked="form.waitlist_enabled" />
                </a-form-item>
              </a-col>
            </a-row>

            <a-row :gutter="16">
              <a-col :xs="24" :md="6">
                <a-form-item label="Featured Event" name="is_featured">
                  <a-switch v-model:checked="form.is_featured" />
                </a-form-item>
              </a-col>

              <a-col :xs="24" :md="6">
                <a-form-item label="Allow Comments" name="allow_comments">
                  <a-switch v-model:checked="form.allow_comments" />
                </a-form-item>
              </a-col>

              <a-col :xs="24" :md="6">
                <a-form-item label="Allow Reviews" name="allow_reviews">
                  <a-switch v-model:checked="form.allow_reviews" />
                </a-form-item>
              </a-col>

              <a-col :xs="24" :md="6">
                <a-form-item label="Require Approval" name="require_approval">
                  <a-switch v-model:checked="form.require_approval" />
                </a-form-item>
              </a-col>
            </a-row>
          </a-tab-pane>

          <!-- Media & SEO -->
          <a-tab-pane key="media" tab="Media & SEO">
            <a-row :gutter="16">
              <a-col :xs="24" :md="12">
                <a-form-item label="Featured Image URL" name="featured_image">
                  <a-input v-model:value="form.featured_image" placeholder="Enter featured image URL"
                    :maxlength="255" />
                </a-form-item>
              </a-col>

              <a-col :xs="24" :md="12">
                <a-form-item label="Banner Image URL" name="banner_image">
                  <a-input v-model:value="form.banner_image" placeholder="Enter banner image URL" :maxlength="255" />
                </a-form-item>
              </a-col>
            </a-row>

            <a-row :gutter="16">
              <a-col :xs="24">
                <a-form-item label="Video URL" name="video_url">
                  <a-input v-model:value="form.video_url" placeholder="Enter video URL" />
                </a-form-item>
              </a-col>
            </a-row>

            <a-divider>SEO Settings</a-divider>

            <a-row :gutter="16">
              <a-col :xs="24">
                <a-form-item label="Meta Title" name="meta_title">
                  <a-input v-model:value="form.meta_title" placeholder="Enter meta title" :maxlength="255" />
                </a-form-item>
              </a-col>
            </a-row>

            <a-form-item label="Meta Description" name="meta_description">
              <a-textarea v-model:value="form.meta_description" placeholder="Enter meta description" :rows="3" />
            </a-form-item>

            <a-form-item label="Meta Keywords" name="meta_keywords">
              <a-textarea v-model:value="form.meta_keywords" placeholder="Enter meta keywords (comma-separated)"
                :rows="2" />
            </a-form-item>
          </a-tab-pane>
        </a-tabs>

        <a-form-item style="margin-top: 24px">
          <a-space>
            <a-button type="primary" html-type="submit" :loading="saving" size="large">
              Update Event
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

const organizers = computed(() => page.props.organizers || []);
const categories = computed(() => page.props.categories || []);
const tags = computed(() => page.props.tags || []);
const venues = computed(() => page.props.venues || []);
const selectedVenueId = ref(null);

const breadcrumbItems = ref([
  { label: 'Dashboard', href: '/dashboard' },
  { label: 'Events', href: '/dashboard/events' },
  { label: 'Edit' },
]);

const event = computed(() => page.props.event);

const form = reactive({
  organizer_id: event.value?.organizer_id || null,
  category_id: event.value?.category_id || null,
  title: event.value?.title || '',
  slug: event.value?.slug || '',
  description: event.value?.description || '',
  short_description: event.value?.short_description || '',
  event_type: event.value?.event_type || 'online',
  status: event.value?.status || 'draft',
  visibility: event.value?.visibility || 'public',
  start_date: event.value?.start_date ? dayjs(event.value.start_date) : null,
  end_date: event.value?.end_date ? dayjs(event.value.end_date) : null,
  timezone: event.value?.timezone || 'UTC',
  registration_start: event.value?.registration_start ? dayjs(event.value.registration_start) : null,
  registration_end: event.value?.registration_end ? dayjs(event.value.registration_end) : null,
  venue_name: event.value?.venue_name || '',
  venue_address: event.value?.venue_address || '',
  venue_city: event.value?.venue_city || '',
  venue_state: event.value?.venue_state || '',
  venue_country: event.value?.venue_country || '',
  venue_postal_code: event.value?.venue_postal_code || '',
  latitude: event.value?.latitude || null,
  longitude: event.value?.longitude || null,
  meeting_url: event.value?.meeting_url || '',
  meeting_platform: event.value?.meeting_platform || '',
  meeting_id: event.value?.meeting_id || '',
  meeting_password: event.value?.meeting_password || '',
  max_attendees: event.value?.max_attendees || null,
  min_attendees: event.value?.min_attendees || null,
  waitlist_enabled: event.value?.waitlist_enabled || false,
  featured_image: event.value?.featured_image || '',
  banner_image: event.value?.banner_image || '',
  video_url: event.value?.video_url || '',
  meta_title: event.value?.meta_title || '',
  meta_description: event.value?.meta_description || '',
  meta_keywords: event.value?.meta_keywords || '',
  is_featured: event.value?.is_featured || false,
  allow_comments: event.value?.allow_comments !== undefined ? event.value.allow_comments : true,
  allow_reviews: event.value?.allow_reviews !== undefined ? event.value.allow_reviews : true,
  require_approval: event.value?.require_approval || false,
  tags: event.value?.tags ? event.value.tags.map(tag => tag.id) : [],
});

const filterOption = (input, option) => {
  return option.children[0].children.toLowerCase().indexOf(input.toLowerCase()) >= 0;
};

const rules = {
  organizer_id: [
    { required: true, message: 'Please select an organizer', trigger: 'change' },
  ],
  title: [
    { required: true, message: 'Please enter event title', trigger: 'blur' },
    { max: 255, message: 'Title cannot exceed 255 characters', trigger: 'blur' },
  ],
  event_type: [
    { required: true, message: 'Please select event type', trigger: 'change' },
  ],
  start_date: [
    { required: true, message: 'Please select start date', trigger: 'change' },
  ],
  end_date: [
    { required: true, message: 'Please select end date', trigger: 'change' },
  ],
  timezone: [
    { required: true, message: 'Please enter timezone', trigger: 'blur' },
    { max: 50, message: 'Timezone cannot exceed 50 characters', trigger: 'blur' },
  ],
};

const handleSubmit = async () => {
  try {
    await formRef.value.validate();
    saving.value = true;

    const submitData = {
      ...form,
      start_date: form.start_date ? form.start_date.format('YYYY-MM-DD HH:mm:ss') : null,
      end_date: form.end_date ? form.end_date.format('YYYY-MM-DD HH:mm:ss') : null,
      registration_start: form.registration_start ? form.registration_start.format('YYYY-MM-DD HH:mm:ss') : null,
      registration_end: form.registration_end ? form.registration_end.format('YYYY-MM-DD HH:mm:ss') : null,
    };

    router.put(`/dashboard/events/${event.value.id}`, submitData, {
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
  router.visit('/dashboard/events');
};

const handleVenueChange = (venueId) => {
  if (!venueId) {
    return;
  }

  const venue = venues.value.find(v => v.id === venueId);
  if (venue) {
    form.venue_name = venue.name;
    form.venue_address = venue.address;
    form.venue_city = venue.city;
    form.venue_state = venue.state;
    form.venue_country = venue.country;
    form.venue_postal_code = venue.postal_code;
    form.latitude = venue.latitude ? Number(venue.latitude) : null;
    form.longitude = venue.longitude ? Number(venue.longitude) : null;
  }
};
</script>

<style scoped>
.event-form-card {
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

:deep(.ant-tabs-content-holder) {
  padding-top: 16px;
}
</style>
