<?php

namespace App\Http\Controllers;

use App\Models\ReviewReply;
use App\Models\Review;
use App\Models\UserActivityLog;
use App\Http\Requests\Review\ReviewReplyStoreRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewReplyController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(ReviewReplyStoreRequest $request)
    {
        $validated = $request->validated();
        $validated['user_id'] = Auth::id();

        $reply = ReviewReply::create($validated);

        // Log activity
        UserActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'review_reply.created',
            'description' => "Reply to review #{$reply->review_id} was created",
            'metadata' => [
                'reply_id' => $reply->id,
                'review_id' => $reply->review_id,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()
            ->route('reviews.show', $reply->review_id)
            ->with('success', 'Reply added successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ReviewReply $reviewReply)
    {
        $request->validate([
            'comment' => 'required|string|min:1',
        ]);

        $reviewReply->update([
            'comment' => $request->comment,
        ]);

        // Log activity
        UserActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'review_reply.updated',
            'description' => "Reply #{$reviewReply->id} was updated",
            'metadata' => [
                'reply_id' => $reviewReply->id,
                'review_id' => $reviewReply->review_id,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()
            ->route('reviews.show', $reviewReply->review_id)
            ->with('success', 'Reply updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ReviewReply $reviewReply)
    {
        $replyId = $reviewReply->id;
        $reviewId = $reviewReply->review_id;

        // Set deleted_by before soft delete
        $reviewReply->deleted_by = Auth::id();
        $reviewReply->save();
        $reviewReply->delete();

        // Log activity
        UserActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'review_reply.deleted',
            'description' => "Reply #{$replyId} was deleted",
            'metadata' => [
                'reply_id' => $replyId,
                'review_id' => $reviewId,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()
            ->route('reviews.show', $reviewId)
            ->with('success', 'Reply deleted successfully.');
    }
}

