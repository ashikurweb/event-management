<?php

namespace App\Http\Controllers;

use App\Services\AIAssistantService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AIAssistantController extends Controller
{
    protected $aiService;

    public function __construct(AIAssistantService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Handle chat message
     */
    public function chat(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'context' => 'sometimes|array',
        ]);

        try {
            $result = $this->aiService->processMessage(
                $request->input('message'),
                $request->input('context', [])
            );

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'response' => 'Sorry, I encountered an error. Please try again later.',
            ], 500);
        }
    }

    /**
     * Get dashboard context for AI
     */
    public function getContext(): JsonResponse
    {
        try {
            // This can be used to get context without sending a message
            $user = auth()->user();
            
            return response()->json([
                'success' => true,
                'context' => [
                    'user_name' => $user->name ?? $user->first_name . ' ' . $user->last_name,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get context',
            ], 500);
        }
    }
}

