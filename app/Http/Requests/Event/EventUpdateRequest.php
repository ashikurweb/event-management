<?php

namespace App\Http\Requests\Event;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EventUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $eventId = $this->route('event')->id ?? null;

        return [
            'organizer_id' => 'required|exists:users,id',
            'category_id' => 'nullable|exists:categories,id',
            'title' => 'required|string|max:255',
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('events', 'slug')->ignore($eventId),
            ],
            'description' => 'nullable|string',
            'short_description' => 'nullable|string|max:500',
            'event_type' => 'required|in:online,offline,hybrid',
            'status' => 'nullable|in:draft,published,cancelled,completed,postponed',
            'visibility' => 'nullable|in:public,private,unlisted',
            
            // Date & Time
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'timezone' => 'required|string|max:50',
            'registration_start' => 'nullable|date',
            'registration_end' => 'nullable|date|after:registration_start',
            
            // Location (for offline/hybrid)
            'venue_name' => 'nullable|string|max:255',
            'venue_address' => 'nullable|string',
            'venue_city' => 'nullable|string|max:100',
            'venue_state' => 'nullable|string|max:100',
            'venue_country' => 'nullable|string|max:100',
            'venue_postal_code' => 'nullable|string|max:20',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            
            // Online Details (for online/hybrid)
            'meeting_url' => 'nullable|url',
            'meeting_platform' => 'nullable|string|max:50',
            'meeting_id' => 'nullable|string|max:255',
            'meeting_password' => 'nullable|string|max:255',
            
            // Capacity & Limits
            'max_attendees' => 'nullable|integer|min:1',
            'min_attendees' => 'nullable|integer|min:1',
            'waitlist_enabled' => 'nullable|boolean',
            
            // Media
            'featured_image' => 'nullable|string|max:255',
            'banner_image' => 'nullable|string|max:255',
            'video_url' => 'nullable|url',
            
            // SEO & Marketing
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
            
            // Settings
            'is_featured' => 'nullable|boolean',
            'allow_comments' => 'nullable|boolean',
            'allow_reviews' => 'nullable|boolean',
            'require_approval' => 'nullable|boolean',
            
            // Tags
            'tags' => 'nullable|array',
            'tags.*' => 'exists:event_tags,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'organizer_id.required' => 'Please select an organizer.',
            'organizer_id.exists' => 'Selected organizer does not exist.',
            'category_id.exists' => 'Selected category does not exist.',
            'title.required' => 'Event title is required.',
            'title.max' => 'Event title cannot exceed 255 characters.',
            'slug.unique' => 'This slug is already taken.',
            'short_description.max' => 'Short description cannot exceed 500 characters.',
            'event_type.required' => 'Please select an event type.',
            'event_type.in' => 'Invalid event type selected.',
            'status.in' => 'Invalid status selected.',
            'visibility.in' => 'Invalid visibility option selected.',
            'start_date.required' => 'Start date is required.',
            'end_date.required' => 'End date is required.',
            'end_date.after' => 'End date must be after start date.',
            'timezone.required' => 'Timezone is required.',
            'registration_end.after' => 'Registration end date must be after registration start date.',
            'venue_name.max' => 'Venue name cannot exceed 255 characters.',
            'latitude.between' => 'Latitude must be between -90 and 90.',
            'longitude.between' => 'Longitude must be between -180 and 180.',
            'meeting_url.url' => 'Please enter a valid meeting URL.',
            'max_attendees.min' => 'Maximum attendees must be at least 1.',
            'min_attendees.min' => 'Minimum attendees must be at least 1.',
            'video_url.url' => 'Please enter a valid video URL.',
            'tags.array' => 'Tags must be an array.',
            'tags.*.exists' => 'One or more selected tags do not exist.',
        ];
    }
}

