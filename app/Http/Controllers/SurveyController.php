<?php

namespace App\Http\Controllers;

use App\Models\Survey;
use App\Models\Event;
use App\Models\UserActivityLog;
use App\Http\Requests\Survey\SurveyStoreRequest;
use App\Http\Requests\Survey\SurveyUpdateRequest;
use App\Http\Requests\Survey\SurveySearchRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class SurveyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $surveys = Survey::with('event')
            ->withCount('responses')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Get events for filter dropdown
        $events = Event::orderBy('title')->get(['id', 'title']);

        return Inertia::render('Dashboard/Surveys/Index', [
            'surveys' => $surveys,
            'events' => $events,
            'filters' => [],
        ]);
    }

    /**
     * Search surveys.
     */
    public function search(SurveySearchRequest $request)
    {
        $query = Survey::with('event');

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%')
                    ->orWhereHas('event', function ($eventQuery) use ($request) {
                        $eventQuery->where('title', 'like', '%' . $request->search . '%');
                    });
            });
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by event
        if ($request->filled('event_id')) {
            $query->where('event_id', $request->event_id);
        }

        // Filter by required status
        if ($request->has('is_required') && $request->is_required !== null && $request->is_required !== '') {
            $isRequired = $request->is_required === '1' || $request->is_required === 1 || $request->is_required === true;
            $query->where('is_required', $isRequired);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        if (in_array($sortBy, ['title', 'status', 'created_at', 'updated_at'])) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $surveys = $query->withCount('responses')
            ->paginate($request->get('per_page', 15));

        // Get events for filter dropdown
        $events = Event::orderBy('title')->get(['id', 'title']);

        return Inertia::render('Dashboard/Surveys/Index', [
            'surveys' => $surveys,
            'events' => $events,
            'filters' => $request->only(['search', 'status', 'event_id', 'is_required', 'date_from', 'date_to']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $events = Event::orderBy('title')->get(['id', 'title']);

        return Inertia::render('Dashboard/Surveys/Create', [
            'events' => $events,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SurveyStoreRequest $request)
    {
        $validated = $request->validated();

        $survey = Survey::create($validated);

        // Log activity
        UserActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'survey.created',
            'description' => "Survey '{$survey->title}' was created",
            'metadata' => [
                'survey_id' => $survey->id,
                'survey_title' => $survey->title,
                'event_id' => $survey->event_id,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()
            ->route('surveys.index')
            ->with('success', 'Survey created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Survey $survey, Request $request)
    {
        $survey->load('event', 'questions', 'responses.user', 'deletedBy');

        // Get activity logs for this survey
        $activities = UserActivityLog::where(function ($query) use ($survey) {
                $query->where('action', 'like', 'survey.%')
                    ->whereJsonContains('metadata->survey_id', $survey->id);
            })
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return Inertia::render('Dashboard/Surveys/Show', [
            'survey' => $survey,
            'activities' => $activities,
        ]);
    }

    /**
     * Get activity logs for a survey.
     */
    public function activities(Survey $survey, Request $request)
    {
        $activities = UserActivityLog::where(function ($query) use ($survey) {
                $query->where('action', 'like', 'survey.%')
                    ->whereJsonContains('metadata->survey_id', $survey->id);
            })
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return Inertia::render('Dashboard/Surveys/Show', [
            'survey' => $survey->load('event', 'questions', 'responses.user', 'deletedBy'),
            'activities' => $activities,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Survey $survey)
    {
        $events = Event::orderBy('title')->get(['id', 'title']);

        return Inertia::render('Dashboard/Surveys/Edit', [
            'survey' => $survey->load('event'),
            'events' => $events,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SurveyUpdateRequest $request, Survey $survey)
    {
        $validated = $request->validated();

        $survey->update($validated);

        // Log activity
        UserActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'survey.updated',
            'description' => "Survey '{$survey->title}' was updated",
            'metadata' => [
                'survey_id' => $survey->id,
                'survey_title' => $survey->title,
                'event_id' => $survey->event_id,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()
            ->route('surveys.index')
            ->with('success', 'Survey updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Survey $survey)
    {
        $surveyTitle = $survey->title;
        $surveyId = $survey->id;
        $eventId = $survey->event_id;

        // Set deleted_by before soft delete
        $survey->deleted_by = Auth::id();
        $survey->save();
        $survey->delete();

        // Log activity
        UserActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'survey.deleted',
            'description' => "Survey '{$surveyTitle}' was deleted",
            'metadata' => [
                'survey_id' => $surveyId,
                'survey_title' => $surveyTitle,
                'event_id' => $eventId,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()
            ->route('surveys.index')
            ->with('success', 'Survey deleted successfully.');
    }

    /**
     * Bulk actions
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,close,delete',
            'ids' => 'required|array',
            'ids.*' => 'exists:surveys,id',
        ]);

        $surveys = Survey::whereIn('id', $request->ids);

        switch ($request->action) {
            case 'activate':
                $surveys->update(['status' => 'active']);
                $message = 'Surveys activated successfully.';
                break;

            case 'close':
                $surveys->update(['status' => 'closed']);
                $message = 'Surveys closed successfully.';
                break;

            case 'delete':
                $surveys->update(['deleted_by' => Auth::id()]);
                $surveys->delete();
                $message = 'Surveys deleted successfully.';
                break;
        }

        return back()->with('success', $message);
    }
}

