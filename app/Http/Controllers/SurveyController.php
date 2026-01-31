<?php

namespace App\Http\Controllers;

use App\Models\Survey;
use App\Models\SurveyAttempt;
use App\Models\SurveyTemplate;
use App\Models\SurveyCreationSession;
use App\Models\SurveyDelivery;
use App\Models\QuestionBank;
use App\Services\SurveyCreationService;
use App\Services\SurveyDeliveryService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class SurveyController extends Controller
{
    public function __construct(
        protected ?SurveyCreationService $creationService = null,
        protected ?SurveyDeliveryService $deliveryService = null
    ) {}

    /**
     * Display a listing of surveys.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $effectiveOrgId = $user->effective_org_id;

        // Build query for surveys
        $query = Survey::query();

        // If user is a consultant/admin at district level, they can see surveys from child orgs
        if ($user->isAdmin() && $user->organization) {
            $accessibleOrgIds = $user->getAccessibleOrganizations()->pluck('id')->toArray();
            $query->whereIn('org_id', $accessibleOrgIds);

            // Filter by specific org if requested
            if ($request->has('org_filter') && in_array($request->org_filter, $accessibleOrgIds)) {
                $query->where('org_id', $request->org_filter);
            }
        } else {
            $query->where('org_id', $effectiveOrgId);
        }

        $surveys = $query->with('organization')
            ->withCount('attempts', 'completedAttempts')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Get accessible orgs for filter dropdown (if admin)
        $accessibleOrgs = $user->isAdmin() ? $user->getAccessibleOrganizations() : collect();

        return view('surveys.index', compact('surveys', 'accessibleOrgs'));
    }

    /**
     * Show the form for creating a new survey.
     */
    public function create(Request $request): View
    {
        $user = $request->user();
        $templates = SurveyTemplate::availableTo($user->org_id)
            ->orderBy('is_featured', 'desc')
            ->orderBy('usage_count', 'desc')
            ->get();

        $questionBank = QuestionBank::availableTo($user->org_id)
            ->orderBy('category')
            ->orderBy('usage_count', 'desc')
            ->get()
            ->groupBy('category');

        return view('surveys.create', compact('templates', 'questionBank'));
    }

    /**
     * Store a newly created survey.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'survey_type' => 'required|string|in:wellness,academic,behavioral,custom',
            'questions' => 'required|array|min:1',
            'questions.*.id' => 'required|string',
            'questions.*.type' => 'required|string|in:scale,multiple_choice,text,voice,matrix',
            'questions.*.question' => 'required|string',
            'template_id' => 'nullable|exists:survey_templates,id',
            'creation_mode' => 'nullable|string|in:static,chat,voice,ai_assisted',
            'interpretation_config' => 'nullable|array',
            'delivery_channels' => 'nullable|array',
            'target_grades' => 'nullable|array',
            'target_classrooms' => 'nullable|array',
            'is_anonymous' => 'nullable|boolean',
            'estimated_duration_minutes' => 'nullable|integer|min:1',
            'allow_voice_responses' => 'nullable|boolean',
            'ai_follow_up_enabled' => 'nullable|boolean',
        ]);

        $survey = Survey::create([
            'org_id' => $user->org_id,
            'created_by' => $user->id,
            'status' => 'draft',
            ...$validated,
        ]);

        // Increment template usage if created from template
        if ($survey->template_id) {
            $survey->template->incrementUsage();
        }

        return response()->json([
            'success' => true,
            'survey' => $survey,
            'redirect' => route('surveys.edit', $survey),
        ], 201);
    }

    /**
     * Display the specified survey.
     */
    public function show(Request $request, Survey $survey): View|JsonResponse
    {
        $this->authorize('view', $survey);

        $survey->load(['attempts' => function ($query) {
            $query->latest()->limit(10);
        }, 'template', 'creator']);

        if ($request->wantsJson()) {
            return response()->json($survey);
        }

        return view('surveys.show', compact('survey'));
    }

    /**
     * Show the form for editing a survey.
     */
    public function edit(Request $request, Survey $survey): View
    {
        $this->authorize('update', $survey);

        $user = $request->user();
        $templates = SurveyTemplate::availableTo($user->org_id)->get();
        $questionBank = QuestionBank::availableTo($user->org_id)
            ->get()
            ->groupBy('category');

        return view('surveys.edit', compact('survey', 'templates', 'questionBank'));
    }

    /**
     * Update the specified survey.
     */
    public function update(Request $request, Survey $survey): JsonResponse
    {
        $this->authorize('update', $survey);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:1000',
            'survey_type' => 'sometimes|string|in:wellness,academic,behavioral,custom',
            'questions' => 'sometimes|array|min:1',
            'questions.*.id' => 'required|string',
            'questions.*.type' => 'required|string|in:scale,multiple_choice,text,voice,matrix',
            'questions.*.question' => 'required|string',
            'status' => 'sometimes|string|in:draft,active,paused,completed,archived',
            'interpretation_config' => 'nullable|array',
            'delivery_channels' => 'nullable|array',
            'target_grades' => 'nullable|array',
            'target_classrooms' => 'nullable|array',
            'is_anonymous' => 'nullable|boolean',
            'estimated_duration_minutes' => 'nullable|integer|min:1',
            'voice_config' => 'nullable|array',
            'allow_voice_responses' => 'nullable|boolean',
            'ai_follow_up_enabled' => 'nullable|boolean',
            'llm_system_prompt' => 'nullable|string',
            'scoring_config' => 'nullable|array',
        ]);

        $survey->update($validated);

        return response()->json([
            'success' => true,
            'survey' => $survey->fresh(),
        ]);
    }

    /**
     * Remove the specified survey.
     */
    public function destroy(Request $request, Survey $survey): JsonResponse
    {
        $this->authorize('delete', $survey);

        $survey->delete();

        return response()->json([
            'success' => true,
            'message' => 'Survey deleted successfully.',
        ]);
    }

    /**
     * Toggle survey status (activate/pause).
     */
    public function toggle(Request $request, Survey $survey): JsonResponse
    {
        $this->authorize('update', $survey);

        $newStatus = $survey->status === 'active' ? 'paused' : 'active';
        $survey->update(['status' => $newStatus]);

        return response()->json([
            'success' => true,
            'status' => $newStatus,
            'survey' => $survey,
        ]);
    }

    /**
     * Duplicate a survey.
     */
    public function duplicate(Request $request, Survey $survey): JsonResponse
    {
        $this->authorize('view', $survey);

        $user = $request->user();

        $newSurvey = $survey->replicate();
        $newSurvey->title = $survey->title . ' (Copy)';
        $newSurvey->status = 'draft';
        $newSurvey->created_by = $user->id;
        $newSurvey->save();

        return response()->json([
            'success' => true,
            'survey' => $newSurvey,
            'redirect' => route('surveys.edit', $newSurvey),
        ], 201);
    }

    /**
     * Push a survey to one or more child organizations.
     */
    public function push(Request $request, Survey $survey): JsonResponse
    {
        $this->authorize('update', $survey);

        $user = $request->user();

        $validated = $request->validate([
            'target_org_ids' => 'required|array|min:1',
            'target_org_ids.*' => 'required|integer|exists:organizations,id',
        ]);

        $sourceOrg = $survey->organization;
        $pushed = [];
        $errors = [];

        foreach ($validated['target_org_ids'] as $targetOrgId) {
            $targetOrg = \App\Models\Organization::find($targetOrgId);

            // Verify the source org can push to target org
            if (!$sourceOrg->canPushContentTo($targetOrg)) {
                $errors[] = "Cannot push to {$targetOrg->org_name} - not a child organization.";
                continue;
            }

            $newSurvey = $survey->pushToOrganization($targetOrg, $user->id);
            $pushed[] = [
                'org_id' => $targetOrg->id,
                'org_name' => $targetOrg->org_name,
                'survey_id' => $newSurvey->id,
            ];
        }

        return response()->json([
            'success' => count($pushed) > 0,
            'pushed' => $pushed,
            'errors' => $errors,
            'message' => count($pushed) . ' survey(s) pushed successfully.',
        ]);
    }

    // ============================================
    // AI-ASSISTED CREATION ENDPOINTS
    // ============================================

    /**
     * Start an AI-assisted survey creation session.
     */
    public function startCreationSession(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'creation_mode' => 'required|string|in:chat,voice,static,hybrid',
            'context' => 'nullable|array',
            'context.purpose' => 'nullable|string',
            'context.target_audience' => 'nullable|string',
            'context.survey_type' => 'nullable|string',
        ]);

        $session = SurveyCreationSession::create([
            'org_id' => $user->org_id,
            'user_id' => $user->id,
            'creation_mode' => $validated['creation_mode'],
            'status' => 'active',
            'context' => $validated['context'] ?? [],
            'started_at' => now(),
        ]);

        // Generate initial AI greeting/suggestions if in chat mode
        $initialMessage = null;
        if ($validated['creation_mode'] === 'chat' && $this->creationService) {
            $initialMessage = $this->creationService->generateInitialGreeting($session);
            $session->addMessage('assistant', $initialMessage);
        }

        return response()->json([
            'success' => true,
            'session' => $session,
            'initial_message' => $initialMessage,
        ], 201);
    }

    /**
     * Process a chat message in an AI creation session.
     */
    public function processCreationChat(Request $request, SurveyCreationSession $session): JsonResponse
    {
        $this->authorize('update', $session);

        if (!$session->isActive()) {
            return response()->json([
                'success' => false,
                'error' => 'Session is no longer active.',
            ], 422);
        }

        $validated = $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $session->addMessage('user', $validated['message']);

        // Process with AI service
        if ($this->creationService) {
            $result = $this->creationService->processChatMessage($session, $validated['message']);

            return response()->json([
                'success' => true,
                'response' => $result['response'],
                'draft_questions' => $session->fresh()->draft_questions,
                'suggestions' => $result['suggestions'] ?? null,
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => 'AI service not available.',
        ], 503);
    }

    /**
     * Process voice input for survey creation.
     */
    public function processCreationVoice(Request $request, SurveyCreationSession $session): JsonResponse
    {
        $this->authorize('update', $session);

        if (!$session->isActive()) {
            return response()->json([
                'success' => false,
                'error' => 'Session is no longer active.',
            ], 422);
        }

        $validated = $request->validate([
            'audio' => 'required|file|mimes:mp3,wav,m4a,ogg,webm|max:51200',
        ]);

        if ($this->creationService) {
            $result = $this->creationService->processVoiceInput($session, $validated['audio']);

            return response()->json([
                'success' => true,
                'transcription' => $result['transcription'],
                'extracted_questions' => $result['extracted_questions'],
                'draft_questions' => $session->fresh()->draft_questions,
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => 'Voice processing service not available.',
        ], 503);
    }

    /**
     * Finalize creation session and create the survey.
     */
    public function finalizeCreationSession(Request $request, SurveyCreationSession $session): JsonResponse
    {
        $this->authorize('update', $session);

        if (!$session->isActive()) {
            return response()->json([
                'success' => false,
                'error' => 'Session is no longer active.',
            ], 422);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'survey_type' => 'required|string|in:wellness,academic,behavioral,custom',
            'questions' => 'sometimes|array', // Override draft questions if provided
            'delivery_channels' => 'nullable|array',
            'target_grades' => 'nullable|array',
        ]);

        $questions = $validated['questions'] ?? $session->draft_questions;

        if (empty($questions)) {
            return response()->json([
                'success' => false,
                'error' => 'No questions defined for the survey.',
            ], 422);
        }

        $user = $request->user();

        $survey = Survey::create([
            'org_id' => $user->org_id,
            'created_by' => $user->id,
            'creation_mode' => $session->creation_mode,
            'creation_session_id' => $session->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'survey_type' => $validated['survey_type'],
            'questions' => $questions,
            'delivery_channels' => $validated['delivery_channels'] ?? ['web'],
            'target_grades' => $validated['target_grades'] ?? null,
            'status' => 'draft',
        ]);

        $session->markCompleted($survey->id);

        return response()->json([
            'success' => true,
            'survey' => $survey,
            'redirect' => route('surveys.edit', $survey),
        ], 201);
    }

    /**
     * Get AI suggestions for questions based on context.
     */
    public function suggestQuestions(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'purpose' => 'required|string|max:500',
            'survey_type' => 'nullable|string|in:wellness,academic,behavioral,custom',
            'existing_questions' => 'nullable|array',
            'count' => 'nullable|integer|min:1|max:10',
        ]);

        if ($this->creationService) {
            $suggestions = $this->creationService->generateQuestionSuggestions($validated);

            return response()->json([
                'success' => true,
                'suggestions' => $suggestions,
            ]);
        }

        // Fallback: return questions from question bank
        $user = $request->user();
        $questions = QuestionBank::availableTo($user->org_id)
            ->when($validated['survey_type'] ?? null, fn($q, $type) => $q->category($type))
            ->orderBy('usage_count', 'desc')
            ->limit($validated['count'] ?? 5)
            ->get()
            ->map(fn($q) => $q->toSurveyQuestion());

        return response()->json([
            'success' => true,
            'suggestions' => $questions,
            'source' => 'question_bank',
        ]);
    }

    /**
     * Refine a question using AI.
     */
    public function refineQuestion(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'question' => 'required|string|max:500',
            'feedback' => 'required|string|max:500',
            'question_type' => 'nullable|string|in:scale,multiple_choice,text,voice',
        ]);

        if ($this->creationService) {
            $refined = $this->creationService->refineQuestion(
                $validated['question'],
                $validated['feedback'],
                $validated['question_type'] ?? null
            );

            return response()->json([
                'success' => true,
                'refined_question' => $refined,
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => 'AI service not available.',
        ], 503);
    }

    /**
     * Generate interpretation rules for questions.
     */
    public function generateInterpretation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'questions' => 'required|array|min:1',
            'survey_type' => 'required|string|in:wellness,academic,behavioral,custom',
        ]);

        if ($this->creationService) {
            $interpretation = $this->creationService->generateInterpretationRules($validated);

            return response()->json([
                'success' => true,
                'interpretation_config' => $interpretation,
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => 'AI service not available.',
        ], 503);
    }

    // ============================================
    // QUESTION BANK ENDPOINTS
    // ============================================

    /**
     * List questions from the question bank.
     */
    public function questionBankIndex(Request $request): JsonResponse
    {
        $user = $request->user();

        $questions = QuestionBank::availableTo($user->org_id)
            ->when($request->category, fn($q, $cat) => $q->category($cat))
            ->when($request->type, fn($q, $type) => $q->ofType($type))
            ->when($request->search, fn($q, $search) => $q->search($search))
            ->when($request->tags, fn($q, $tags) => $q->withTags(explode(',', $tags)))
            ->orderBy('usage_count', 'desc')
            ->paginate($request->per_page ?? 20);

        return response()->json($questions);
    }

    /**
     * Store a new question in the question bank.
     */
    public function questionBankStore(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'category' => 'required|string|in:wellness,academic,behavioral,sel,custom',
            'subcategory' => 'nullable|string|max:100',
            'question_text' => 'required|string|max:500',
            'question_type' => 'required|string|in:scale,multiple_choice,text,voice,matrix',
            'options' => 'nullable|array',
            'interpretation_rules' => 'nullable|array',
            'tags' => 'nullable|array',
            'is_public' => 'nullable|boolean',
        ]);

        $question = QuestionBank::create([
            'org_id' => $user->org_id,
            'created_by' => $user->id,
            ...$validated,
        ]);

        return response()->json([
            'success' => true,
            'question' => $question,
        ], 201);
    }

    // ============================================
    // TEMPLATE ENDPOINTS
    // ============================================

    /**
     * List available survey templates.
     */
    public function templatesIndex(Request $request): JsonResponse
    {
        $user = $request->user();

        $templates = SurveyTemplate::availableTo($user->org_id)
            ->when($request->type, fn($q, $type) => $q->ofType($type))
            ->when($request->featured, fn($q) => $q->featured())
            ->orderBy('is_featured', 'desc')
            ->orderBy('usage_count', 'desc')
            ->get();

        return response()->json($templates);
    }

    /**
     * Create a survey from a template.
     */
    public function createFromTemplate(Request $request, SurveyTemplate $template): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'target_grades' => 'nullable|array',
            'target_classrooms' => 'nullable|array',
        ]);

        $survey = $template->createSurvey($user->org_id, $user->id, $validated);

        return response()->json([
            'success' => true,
            'survey' => $survey,
            'redirect' => route('surveys.edit', $survey),
        ], 201);
    }

    // ============================================
    // DELIVERY ENDPOINTS
    // ============================================

    /**
     * Show the delivery form for a survey.
     */
    public function deliverForm(Request $request, Survey $survey): View
    {
        $this->authorize('update', $survey);

        return view('surveys.deliver', compact('survey'));
    }

    /**
     * Deliver survey via specified channel.
     */
    public function deliver(Request $request, Survey $survey): JsonResponse
    {
        $this->authorize('update', $survey);

        $validated = $request->validate([
            'channel' => 'required|string|in:web,sms,voice_call,whatsapp,chat',
            'recipients' => 'required|array|min:1',
            'recipients.*.type' => 'required|string|in:student,user',
            'recipients.*.id' => 'required|integer',
            'recipients.*.phone_number' => 'required_unless:channel,web|string',
            'scheduled_for' => 'nullable|date|after:now',
        ]);

        if (!$this->deliveryService) {
            return response()->json([
                'success' => false,
                'error' => 'Delivery service not available.',
            ], 503);
        }

        $deliveries = [];
        foreach ($validated['recipients'] as $recipient) {
            $delivery = $this->deliveryService->deliver(
                $survey,
                $validated['channel'],
                $recipient['type'],
                $recipient['id'],
                $recipient['phone_number'] ?? null,
                $validated['scheduled_for'] ?? null
            );
            $deliveries[] = $delivery;
        }

        return response()->json([
            'success' => true,
            'deliveries' => $deliveries,
            'message' => count($deliveries) . ' delivery(ies) initiated.',
        ]);
    }

    /**
     * Get delivery status for a survey.
     */
    public function deliveryStatus(Request $request, Survey $survey): JsonResponse
    {
        $this->authorize('view', $survey);

        $deliveries = $survey->deliveries()
            ->with('recipient')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($deliveries);
    }
}
