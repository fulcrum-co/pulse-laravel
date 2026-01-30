<?php

namespace App\Livewire\Survey;

use App\Models\Survey;
use App\Models\SurveyTemplate;
use App\Models\SurveyCreationSession;
use App\Models\QuestionBank;
use App\Services\SurveyCreationService;
use Livewire\Component;
use Illuminate\Support\Str;

class SurveyCreator extends Component
{
    // Mode: select, form, chat, voice, template
    public string $mode = 'select';

    // Survey being edited (null for new)
    public ?string $surveyId = null;

    // Basic Info
    public string $title = '';
    public string $description = '';
    public string $surveyType = 'wellness';

    // Questions
    public array $questions = [];

    // Settings
    public array $deliveryChannels = ['web'];
    public bool $isAnonymous = true;
    public ?int $estimatedDuration = 5;
    public bool $allowVoiceResponses = false;
    public bool $aiFollowUpEnabled = false;
    public ?array $targetGrades = null;

    // AI Chat mode state
    public ?int $sessionId = null;
    public array $chatMessages = [];
    public string $chatInput = '';
    public bool $isProcessing = false;

    // Voice mode state
    public bool $isRecording = false;
    public ?string $transcription = null;

    // Template selection
    public ?int $selectedTemplateId = null;

    // UI State
    public bool $showQuestionEditor = false;
    public ?int $editingQuestionIndex = null;
    public bool $showQuestionBank = false;
    public bool $showTemplates = false;
    public bool $showDeliveryConfig = false;
    public bool $showPreview = false;

    // Question form
    public array $questionForm = [
        'id' => '',
        'type' => 'scale',
        'question' => '',
        'options' => [],
        'required' => true,
        'interpretation_rules' => [],
    ];

    protected $rules = [
        'title' => 'required|string|max:255',
        'description' => 'nullable|string|max:1000',
        'surveyType' => 'required|string|in:wellness,academic,behavioral,custom',
        'questions' => 'required|array|min:1',
    ];

    public function mount(?string $surveyId = null): void
    {
        $this->surveyId = $surveyId;

        if ($surveyId) {
            $survey = Survey::forOrganization(auth()->user()->org_id)->findOrFail($surveyId);
            $this->loadSurvey($survey);
            $this->mode = 'form';
        }
    }

    protected function loadSurvey(Survey $survey): void
    {
        $this->title = $survey->title;
        $this->description = $survey->description ?? '';
        $this->surveyType = $survey->survey_type;
        $this->questions = $survey->questions ?? [];
        $this->deliveryChannels = $survey->delivery_channels ?? ['web'];
        $this->isAnonymous = $survey->is_anonymous ?? true;
        $this->estimatedDuration = $survey->estimated_duration_minutes;
        $this->allowVoiceResponses = $survey->allow_voice_responses ?? false;
        $this->aiFollowUpEnabled = $survey->ai_follow_up_enabled ?? false;
        $this->targetGrades = $survey->target_grades;
    }

    // ============================================
    // MODE SELECTION
    // ============================================

    public function selectMode(string $mode): void
    {
        $this->mode = $mode;

        if ($mode === 'chat') {
            $this->startChatSession();
        }
    }

    public function selectTemplate(int $templateId): void
    {
        $this->selectedTemplateId = $templateId;
        $template = SurveyTemplate::findOrFail($templateId);

        $this->title = $template->name;
        $this->description = $template->description;
        $this->surveyType = $template->template_type;
        $this->questions = $template->questions ?? [];
        $this->deliveryChannels = $template->delivery_defaults['channels'] ?? ['web'];

        $this->mode = 'form';
        $this->showTemplates = false;
    }

    // ============================================
    // CHAT MODE
    // ============================================

    protected function startChatSession(): void
    {
        $user = auth()->user();

        $session = SurveyCreationSession::create([
            'org_id' => $user->org_id,
            'user_id' => $user->id,
            'creation_mode' => 'chat',
            'status' => 'active',
            'context' => [
                'purpose' => null,
                'target_audience' => null,
                'survey_type' => $this->surveyType,
            ],
            'started_at' => now(),
        ]);

        $this->sessionId = $session->id;

        // Add initial greeting
        $this->chatMessages[] = [
            'role' => 'assistant',
            'content' => "Hi! I'm here to help you create a survey. What's the purpose of your survey? For example: 'I want to check in on student wellness' or 'I need to assess academic stress levels'.",
        ];
    }

    public function sendChatMessage(): void
    {
        if (empty(trim($this->chatInput))) {
            return;
        }

        $message = $this->chatInput;
        $this->chatInput = '';
        $this->isProcessing = true;

        // Add user message to chat
        $this->chatMessages[] = [
            'role' => 'user',
            'content' => $message,
        ];

        // Process with AI (simplified for now - will be enhanced with actual AI)
        $this->processChatMessage($message);
    }

    protected function processChatMessage(string $message): void
    {
        $session = SurveyCreationSession::find($this->sessionId);

        if ($session) {
            $session->addMessage('user', $message);

            // Simulate AI response based on message content
            $response = $this->generateAIResponse($message, $session);

            $session->addMessage('assistant', $response);
            $this->chatMessages[] = [
                'role' => 'assistant',
                'content' => $response,
            ];

            // Update draft questions if AI suggests any
            $this->questions = $session->fresh()->draft_questions ?? $this->questions;
        }

        $this->isProcessing = false;
    }

    protected function generateAIResponse(string $message, SurveyCreationSession $session): string
    {
        $lowerMessage = strtolower($message);

        // Simple pattern matching for demo - actual implementation uses Claude
        if (empty($session->draft_questions)) {
            // First message - try to understand purpose
            if (str_contains($lowerMessage, 'wellness') || str_contains($lowerMessage, 'wellbeing')) {
                $this->surveyType = 'wellness';
                $suggestedQuestions = $this->suggestQuestionsForType('wellness');
                $session->update(['draft_questions' => $suggestedQuestions]);
                $this->questions = $suggestedQuestions;

                return "Great! A wellness check-in survey. I've drafted " . count($suggestedQuestions) . " questions for you:\n\n" .
                    collect($suggestedQuestions)->map(fn($q, $i) => ($i + 1) . ". " . $q['question'])->join("\n") .
                    "\n\nWould you like to modify any of these, or shall we add more questions?";
            }

            if (str_contains($lowerMessage, 'academic') || str_contains($lowerMessage, 'stress')) {
                $this->surveyType = 'academic';
                $suggestedQuestions = $this->suggestQuestionsForType('academic');
                $session->update(['draft_questions' => $suggestedQuestions]);
                $this->questions = $suggestedQuestions;

                return "I'll help you create an academic stress assessment. Here are " . count($suggestedQuestions) . " suggested questions:\n\n" .
                    collect($suggestedQuestions)->map(fn($q, $i) => ($i + 1) . ". " . $q['question'])->join("\n") .
                    "\n\nFeel free to ask me to modify, remove, or add questions!";
            }

            return "Could you tell me more about what you'd like to survey? For example:\n- Student wellness and emotional well-being\n- Academic stress and workload\n- Classroom engagement\n- Social connections";
        }

        // Follow-up messages
        if (str_contains($lowerMessage, 'add') || str_contains($lowerMessage, 'more question')) {
            return "What topic should the new question cover? You can also say something like 'add a question about sleep quality' and I'll suggest one.";
        }

        if (str_contains($lowerMessage, 'looks good') || str_contains($lowerMessage, 'done') || str_contains($lowerMessage, 'finish')) {
            return "Excellent! Your survey with " . count($this->questions) . " questions is ready. Click 'Finish & Edit' above to review and customize the final details, or continue chatting if you'd like to make more changes.";
        }

        return "I can help you:\n- Add more questions\n- Modify existing questions\n- Change question types\n- Remove questions\n\nJust tell me what you'd like to do!";
    }

    protected function suggestQuestionsForType(string $type): array
    {
        return match($type) {
            'wellness' => [
                [
                    'id' => (string) Str::uuid(),
                    'type' => 'scale',
                    'question' => 'How are you feeling overall today?',
                    'options' => ['1' => 'Not good at all', '5' => 'Great'],
                    'required' => true,
                ],
                [
                    'id' => (string) Str::uuid(),
                    'type' => 'scale',
                    'question' => 'How well did you sleep last night?',
                    'options' => ['1' => 'Very poorly', '5' => 'Very well'],
                    'required' => true,
                ],
                [
                    'id' => (string) Str::uuid(),
                    'type' => 'scale',
                    'question' => 'How connected do you feel to your classmates?',
                    'options' => ['1' => 'Not connected', '5' => 'Very connected'],
                    'required' => true,
                ],
                [
                    'id' => (string) Str::uuid(),
                    'type' => 'multiple_choice',
                    'question' => 'Is there anything you need support with today?',
                    'options' => ['Academic help', 'Someone to talk to', 'Physical health', 'Nothing right now'],
                    'required' => false,
                ],
            ],
            'academic' => [
                [
                    'id' => (string) Str::uuid(),
                    'type' => 'scale',
                    'question' => 'How stressed do you feel about your schoolwork?',
                    'options' => ['1' => 'Not stressed', '5' => 'Very stressed'],
                    'required' => true,
                ],
                [
                    'id' => (string) Str::uuid(),
                    'type' => 'scale',
                    'question' => 'How manageable is your current workload?',
                    'options' => ['1' => 'Overwhelming', '5' => 'Very manageable'],
                    'required' => true,
                ],
                [
                    'id' => (string) Str::uuid(),
                    'type' => 'scale',
                    'question' => 'How confident do you feel about upcoming tests or assignments?',
                    'options' => ['1' => 'Not confident', '5' => 'Very confident'],
                    'required' => true,
                ],
                [
                    'id' => (string) Str::uuid(),
                    'type' => 'text',
                    'question' => 'Which subject or class is causing you the most concern right now?',
                    'options' => [],
                    'required' => false,
                ],
            ],
            default => [],
        };
    }

    public function finishChatAndEdit(): void
    {
        if ($this->sessionId) {
            $session = SurveyCreationSession::find($this->sessionId);
            if ($session) {
                $this->questions = $session->draft_questions ?? $this->questions;
            }
        }
        $this->mode = 'form';
    }

    // ============================================
    // VOICE MODE
    // ============================================

    public function processVoiceTranscription(string $transcription): void
    {
        $this->transcription = $transcription;

        // Extract questions from transcription (simplified)
        // Actual implementation would use AI to parse
        $lines = preg_split('/[.?!]\s+/', $transcription);
        $extractedQuestions = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (strlen($line) > 10 && str_contains(strtolower($line), '?') || str_contains(strtolower($line), 'how') || str_contains(strtolower($line), 'what')) {
                $extractedQuestions[] = [
                    'id' => (string) Str::uuid(),
                    'type' => 'scale',
                    'question' => ucfirst($line) . (str_ends_with($line, '?') ? '' : '?'),
                    'options' => ['1' => 'Strongly Disagree', '5' => 'Strongly Agree'],
                    'required' => true,
                ];
            }
        }

        if (!empty($extractedQuestions)) {
            $this->questions = array_merge($this->questions, $extractedQuestions);
        }

        $this->mode = 'form';
    }

    // ============================================
    // QUESTION MANAGEMENT
    // ============================================

    public function openQuestionEditor(?int $index = null): void
    {
        $this->editingQuestionIndex = $index;

        if ($index !== null && isset($this->questions[$index])) {
            $this->questionForm = $this->questions[$index];
        } else {
            $this->questionForm = [
                'id' => (string) Str::uuid(),
                'type' => 'scale',
                'question' => '',
                'options' => ['1' => 'Strongly Disagree', '5' => 'Strongly Agree'],
                'required' => true,
                'interpretation_rules' => [],
            ];
        }

        $this->showQuestionEditor = true;
    }

    public function closeQuestionEditor(): void
    {
        $this->showQuestionEditor = false;
        $this->editingQuestionIndex = null;
        $this->questionForm = [
            'id' => '',
            'type' => 'scale',
            'question' => '',
            'options' => [],
            'required' => true,
            'interpretation_rules' => [],
        ];
    }

    public function saveQuestion(): void
    {
        $this->validate([
            'questionForm.question' => 'required|string|max:500',
            'questionForm.type' => 'required|string|in:scale,multiple_choice,text,voice,matrix',
        ]);

        if ($this->editingQuestionIndex !== null) {
            $this->questions[$this->editingQuestionIndex] = $this->questionForm;
        } else {
            $this->questions[] = $this->questionForm;
        }

        $this->closeQuestionEditor();
    }

    public function removeQuestion(int $index): void
    {
        unset($this->questions[$index]);
        $this->questions = array_values($this->questions);
    }

    public function reorderQuestions(array $order): void
    {
        $reordered = [];
        foreach ($order as $index) {
            if (isset($this->questions[$index])) {
                $reordered[] = $this->questions[$index];
            }
        }
        $this->questions = $reordered;
    }

    public function addQuestionFromBank(array $question): void
    {
        $this->questions[] = [
            'id' => (string) Str::uuid(),
            'type' => $question['question_type'],
            'question' => $question['question_text'],
            'options' => $question['options'] ?? [],
            'required' => true,
            'interpretation_rules' => $question['interpretation_rules'] ?? [],
            'bank_question_id' => $question['id'],
        ];

        $this->showQuestionBank = false;
    }

    // ============================================
    // SAVE SURVEY
    // ============================================

    public function save(bool $activate = false)
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'questions' => 'required|array|min:1',
        ]);

        $user = auth()->user();

        $data = [
            'org_id' => $user->org_id,
            'title' => $this->title,
            'description' => $this->description,
            'survey_type' => $this->surveyType,
            'questions' => $this->questions,
            'status' => $activate ? 'active' : 'draft',
            'creation_mode' => $this->mode === 'chat' ? 'ai_assisted' : ($this->mode === 'voice' ? 'voice' : 'static'),
            'delivery_channels' => $this->deliveryChannels,
            'is_anonymous' => $this->isAnonymous,
            'estimated_duration_minutes' => $this->estimatedDuration,
            'allow_voice_responses' => $this->allowVoiceResponses,
            'ai_follow_up_enabled' => $this->aiFollowUpEnabled,
            'target_grades' => $this->targetGrades,
            'template_id' => $this->selectedTemplateId,
        ];

        if ($this->surveyId) {
            $survey = Survey::forOrganization($user->org_id)->findOrFail($this->surveyId);
            $survey->update($data);
        } else {
            $data['created_by'] = $user->id;
            $survey = Survey::create($data);
            $this->surveyId = $survey->id;
        }

        // Mark creation session as completed if exists
        if ($this->sessionId) {
            SurveyCreationSession::find($this->sessionId)?->markCompleted($survey->id);
        }

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => $activate
                ? 'Survey created and activated!'
                : 'Survey saved as draft.',
        ]);

        return redirect()->route('surveys.show', $survey);
    }

    // ============================================
    // COMPUTED PROPERTIES
    // ============================================

    public function getQuestionTypesProperty(): array
    {
        return [
            'scale' => ['label' => 'Scale (1-5)', 'icon' => 'chart-bar', 'description' => 'Rate on a numeric scale'],
            'multiple_choice' => ['label' => 'Multiple Choice', 'icon' => 'list-bullet', 'description' => 'Select one option'],
            'text' => ['label' => 'Free Text', 'icon' => 'document-text', 'description' => 'Open-ended response'],
            'voice' => ['label' => 'Voice Response', 'icon' => 'microphone', 'description' => 'Audio recording'],
            'matrix' => ['label' => 'Matrix', 'icon' => 'table-cells', 'description' => 'Multiple items on same scale'],
        ];
    }

    public function getSurveyTypesProperty(): array
    {
        return [
            'wellness' => ['label' => 'Wellness', 'color' => 'green'],
            'academic' => ['label' => 'Academic', 'color' => 'blue'],
            'behavioral' => ['label' => 'Behavioral', 'color' => 'orange'],
            'custom' => ['label' => 'Custom', 'color' => 'purple'],
        ];
    }

    public function getTemplatesProperty(): \Illuminate\Support\Collection
    {
        return SurveyTemplate::availableTo(auth()->user()->org_id)
            ->orderBy('is_featured', 'desc')
            ->orderBy('usage_count', 'desc')
            ->get();
    }

    public function getQuestionBankProperty(): \Illuminate\Support\Collection
    {
        return QuestionBank::availableTo(auth()->user()->org_id)
            ->orderBy('category')
            ->orderBy('usage_count', 'desc')
            ->get()
            ->groupBy('category');
    }

    public function render()
    {
        return view('livewire.survey.survey-creator');
    }
}
