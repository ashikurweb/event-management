<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AIAssistantService
{
    private $apiKey;
    private $apiUrl;
    private $provider;
    private $model;

    public function __construct()
    {
        // Check if Groq is enabled (free alternative)
        if (config('services.groq.enabled') && !empty(config('services.groq.api_key'))) {
            $this->provider = 'groq';
            $this->apiKey = config('services.groq.api_key');
            $this->apiUrl = 'https://api.groq.com/openai/v1/chat/completions';
            $this->model = 'llama-3.1-8b-instant'; // Fast and free
        } else {
            // Fallback to OpenAI
            $this->provider = 'openai';
            $this->apiKey = config('services.openai.api_key');
            $this->apiUrl = 'https://api.openai.com/v1/chat/completions';
            $this->model = 'gpt-3.5-turbo';
        }
    }

    /**
     * Process user message and get AI response
     */
    public function processMessage(string $message, array $context = []): array
    {
        try {
            // Check if API key is configured
            if (empty($this->apiKey)) {
                Log::error('AI Assistant Error: API key not configured for ' . $this->provider);
                return [
                    'success' => false,
                    'response' => 'AI Assistant is not configured. Please contact the administrator.',
                ];
            }

            // Get dashboard context
            $dashboardContext = $this->getDashboardContext();

            // Build system prompt
            $systemPrompt = $this->buildSystemPrompt($dashboardContext);

            // Make API call to AI provider
            $response = $this->callAI($systemPrompt, $message, $context);

            // Parse response and extract actions
            $parsedResponse = $this->parseResponse($response, $message);

            return [
                'success' => true,
                'response' => $parsedResponse['message'],
                'action' => $parsedResponse['action'] ?? null,
            ];
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            Log::error('AI Assistant Error: ' . $errorMessage);
            
            // Provide more specific error messages
            if (str_contains($errorMessage, 'insufficient_quota') || str_contains($errorMessage, 'quota')) {
                return [
                    'success' => false,
                    'response' => 'OpenAI API quota has been exceeded. Please check your OpenAI account billing and add credits.',
                ];
            }
            
            if (str_contains($errorMessage, 'invalid_api_key') || str_contains($errorMessage, 'authentication')) {
                return [
                    'success' => false,
                    'response' => 'Invalid API key. Please check the configuration.',
                ];
            }
            
            if (str_contains($errorMessage, 'rate_limit')) {
                return [
                    'success' => false,
                    'response' => 'Rate limit exceeded. Please wait a moment and try again.',
                ];
            }
            
            return [
                'success' => false,
                'response' => 'Sorry, I encountered an error. Please try again later. If the problem persists, contact support.',
            ];
        }
    }

    /**
     * Get dashboard context (events, statistics, etc.)
     */
    private function getDashboardContext(): array
    {
        $user = Auth::user();
        
        if (!$user) {
            return [];
        }

        $events = Event::where('organizer_id', $user->id)
            ->with('category')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $totalEvents = Event::where('organizer_id', $user->id)->count();
        $publishedEvents = Event::where('organizer_id', $user->id)
            ->where('status', 'published')
            ->count();
        $draftEvents = Event::where('organizer_id', $user->id)
            ->where('status', 'draft')
            ->count();

        return [
            'user_name' => $user->name ?? $user->first_name . ' ' . $user->last_name,
            'total_events' => $totalEvents,
            'published_events' => $publishedEvents,
            'draft_events' => $draftEvents,
            'recent_events' => $events->map(function ($event) {
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'status' => $event->status,
                    'start_date' => $event->start_date?->format('Y-m-d H:i'),
                ];
            })->toArray(),
        ];
    }

    /**
     * Build system prompt for AI
     */
    private function buildSystemPrompt(array $context): string
    {
        $prompt = "You are an AI assistant for an event management dashboard called EventHub. ";
        $prompt .= "You help users manage their events, view statistics, and navigate the dashboard.\n\n";
        
        $prompt .= "Current Dashboard Context:\n";
        $prompt .= "- User: {$context['user_name']}\n";
        $prompt .= "- Total Events: {$context['total_events']}\n";
        $prompt .= "- Published Events: {$context['published_events']}\n";
        $prompt .= "- Draft Events: {$context['draft_events']}\n";
        
        if (!empty($context['recent_events'])) {
            $prompt .= "\nRecent Events:\n";
            foreach ($context['recent_events'] as $event) {
                $prompt .= "- {$event['title']} (Status: {$event['status']}, ID: {$event['id']})\n";
            }
        }

        $prompt .= "\n\nAvailable Actions:\n";
        $prompt .= "1. CREATE_EVENT - Navigate to event creation page\n";
        $prompt .= "2. SHOW_EVENTS - Navigate to events list\n";
        $prompt .= "3. SHOW_STATISTICS - Navigate to dashboard\n";
        $prompt .= "4. SHOW_CATEGORIES - Navigate to categories\n";
        $prompt .= "5. SHOW_SPEAKERS - Navigate to speakers\n";
        $prompt .= "6. SHOW_TEAMS - Navigate to teams\n";

        $prompt .= "\n\nWhen user asks to create an event, respond with: ";
        $prompt .= "ACTION:CREATE_EVENT\n";
        $prompt .= "When user asks to view events, respond with: ACTION:SHOW_EVENTS\n";
        $prompt .= "When user asks about statistics or dashboard, respond with: ACTION:SHOW_STATISTICS\n";

        $prompt .= "\n\nYour responses should be helpful, concise, and friendly. ";
        $prompt .= "Always respond in the same language as the user's message. ";
        $prompt .= "If the user asks in Bengali, respond in Bengali. ";
        $prompt .= "If the user asks in English, respond in English.";

        return $prompt;
    }

    /**
     * Call AI API (supports both OpenAI and Groq)
     */
    private function callAI(string $systemPrompt, string $userMessage, array $context): array
    {
        if (empty($this->apiKey)) {
            throw new \Exception('API key not configured for ' . $this->provider);
        }

        $response = Http::timeout(30)->withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post($this->apiUrl, [
            'model' => $this->model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $systemPrompt,
                ],
                [
                    'role' => 'user',
                    'content' => $userMessage,
                ],
            ],
            'temperature' => 0.7,
            'max_tokens' => 500,
        ]);

        if ($response->failed()) {
            $errorBody = $response->json();
            $errorMessage = $errorBody['error']['message'] ?? $response->body();
            throw new \Exception($this->provider . ' API request failed: ' . $errorMessage);
        }

        return $response->json();
    }

    /**
     * Parse AI response and extract actions
     */
    private function parseResponse(array $apiResponse, string $userMessage): array
    {
        $content = $apiResponse['choices'][0]['message']['content'] ?? '';
        
        // Extract action if present
        $action = null;
        if (preg_match('/ACTION:(\w+)/', $content, $matches)) {
            $actionType = $matches[1];
            $content = preg_replace('/ACTION:\w+\s*/', '', $content);
            
            $action = $this->mapActionToRoute($actionType);
        }

        // Auto-detect actions from user message if no explicit action
        if (!$action) {
            $action = $this->detectActionFromMessage($userMessage);
        }

        return [
            'message' => trim($content),
            'action' => $action,
        ];
    }

    /**
     * Map action type to route
     */
    private function mapActionToRoute(string $actionType): ?array
    {
        $routes = [
            'CREATE_EVENT' => [
                'type' => 'create_event',
                'url' => '/dashboard/events/create',
            ],
            'SHOW_EVENTS' => [
                'type' => 'navigate',
                'url' => '/dashboard/events',
            ],
            'SHOW_STATISTICS' => [
                'type' => 'show_statistics',
                'url' => '/dashboard',
            ],
            'SHOW_CATEGORIES' => [
                'type' => 'navigate',
                'url' => '/dashboard/categories',
            ],
            'SHOW_SPEAKERS' => [
                'type' => 'navigate',
                'url' => '/dashboard/speakers',
            ],
            'SHOW_TEAMS' => [
                'type' => 'navigate',
                'url' => '/dashboard/teams',
            ],
        ];

        return $routes[$actionType] ?? null;
    }

    /**
     * Detect action from user message
     */
    private function detectActionFromMessage(string $message): ?array
    {
        $message = strtolower($message);

        // Create event
        if (preg_match('/create|new|add|make.*event/i', $message)) {
            return [
                'type' => 'create_event',
                'url' => '/dashboard/events/create',
            ];
        }

        // Show events
        if (preg_match('/show|view|list|see.*event/i', $message)) {
            return [
                'type' => 'navigate',
                'url' => '/dashboard/events',
            ];
        }

        // Show statistics/dashboard
        if (preg_match('/statistic|dashboard|overview|summary/i', $message)) {
            return [
                'type' => 'show_statistics',
                'url' => '/dashboard',
            ];
        }

        // Show categories
        if (preg_match('/categor/i', $message)) {
            return [
                'type' => 'navigate',
                'url' => '/dashboard/categories',
            ];
        }

        // Show speakers
        if (preg_match('/speaker/i', $message)) {
            return [
                'type' => 'navigate',
                'url' => '/dashboard/speakers',
            ];
        }

        // Show teams
        if (preg_match('/team/i', $message)) {
            return [
                'type' => 'navigate',
                'url' => '/dashboard/teams',
            ];
        }

        return null;
    }
}

