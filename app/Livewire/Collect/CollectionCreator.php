<?php

namespace App\Livewire\Collect;

use App\Models\Collection;
use App\Models\Survey;
use App\Models\Student;
use App\Models\Classroom;
use App\Services\CollectionService;
use Livewire\Component;
use Illuminate\Support\Str;

class CollectionCreator extends Component
{
    protected CollectionService $collectionService;

    public function boot(CollectionService $collectionService): void
    {
        $this->collectionService = $collectionService;
    }

    // Current step (1-7)
    public int $currentStep = 1;

    // Step 1: Basic Info
    public string $title = '';
    public string $description = '';
    public string $collectionType = 'recurring';

    // Step 2: Data Source
    public string $dataSource = 'inline'; // survey, inline, hybrid
    public ?int $surveyId = null;
    public array $inlineQuestions = [];

    // Step 3: Format Mode
    public string $formatMode = 'form'; // conversational, form, grid

    // Step 4: Schedule Configuration
    public string $scheduleType = 'interval'; // interval, custom, event
    public string $intervalType = 'weekly'; // daily, weekly, monthly
    public int $intervalValue = 1;
    public array $customDays = []; // monday, tuesday, etc.
    public array $customTimes = ['09:00'];
    public ?string $eventTrigger = null;
    public string $timezone = 'America/New_York';
    public ?string $startDate = null;
    public ?string $endDate = null;

    // Step 5: Contact Scope
    public string $targetType = 'students'; // students, users
    public array $selectedGrades = [];
    public array $selectedClassrooms = [];
    public array $selectedTags = [];
    public array $selectedRoles = [];

    // Step 6: Reminder Settings
    public bool $enableReminders = true;
    public array $reminderChannels = ['email'];
    public int $reminderLeadTime = 60; // minutes before
    public bool $enableFollowUp = true;
    public int $followUpDelay = 24; // hours after

    // Step 7: Review (no additional fields)

    // UI State
    public bool $showQuestionEditor = false;
    public ?int $editingQuestionIndex = null;
    public array $questionForm = [];

    // Available options
    public array $availableSurveys = [];
    public array $availableGrades = [];
    public array $availableClassrooms = [];

    protected $rules = [
        'title' => 'required|string|max:255',
        'description' => 'nullable|string|max:1000',
        'collectionType' => 'required|in:recurring,one_time,event_triggered',
        'dataSource' => 'required|in:survey,inline,hybrid',
        'formatMode' => 'required|in:conversational,form,grid',
    ];

    public function mount(): void
    {
        $user = auth()->user();
        $this->startDate = now()->toDateString();
        $this->timezone = config('app.timezone', 'America/New_York');

        // Load available surveys
        $this->availableSurveys = Survey::forOrganization($user->org_id)
            ->where('status', 'active')
            ->orderBy('title')
            ->get()
            ->toArray();

        // Load available grades
        $this->availableGrades = Student::where('org_id', $user->org_id)
            ->whereNotNull('grade_level')
            ->distinct()
            ->pluck('grade_level')
            ->sort()
            ->values()
            ->toArray();

        // Load available classrooms
        $this->availableClassrooms = Classroom::where('org_id', $user->org_id)
            ->orderBy('name')
            ->get()
            ->toArray();
    }

    // ============================================
    // STEP NAVIGATION
    // ============================================

    public function nextStep(): void
    {
        if ($this->validateCurrentStep()) {
            $this->currentStep = min($this->currentStep + 1, 7);
        }
    }

    public function previousStep(): void
    {
        $this->currentStep = max($this->currentStep - 1, 1);
    }

    public function goToStep(int $step): void
    {
        // Only allow going back, or forward if validated
        if ($step < $this->currentStep) {
            $this->currentStep = $step;
        } elseif ($step > $this->currentStep && $this->validateCurrentStep()) {
            $this->currentStep = $step;
        }
    }

    protected function validateCurrentStep(): bool
    {
        return match ($this->currentStep) {
            1 => $this->validateStep1(),
            2 => $this->validateStep2(),
            3 => $this->validateStep3(),
            4 => $this->validateStep4(),
            5 => $this->validateStep5(),
            6 => $this->validateStep6(),
            7 => true,
            default => false,
        };
    }

    protected function validateStep1(): bool
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'collectionType' => 'required|in:recurring,one_time,event_triggered',
        ]);
        return true;
    }

    protected function validateStep2(): bool
    {
        $this->validate([
            'dataSource' => 'required|in:survey,inline,hybrid',
        ]);

        if ($this->dataSource === 'survey' && !$this->surveyId) {
            $this->addError('surveyId', 'Please select a survey.');
            return false;
        }

        if (in_array($this->dataSource, ['inline', 'hybrid']) && empty($this->inlineQuestions)) {
            $this->addError('inlineQuestions', 'Please add at least one question.');
            return false;
        }

        return true;
    }

    protected function validateStep3(): bool
    {
        $this->validate([
            'formatMode' => 'required|in:conversational,form,grid',
        ]);
        return true;
    }

    protected function validateStep4(): bool
    {
        if ($this->collectionType === 'one_time') {
            return true; // No schedule needed
        }

        $this->validate([
            'scheduleType' => 'required|in:interval,custom,event',
            'startDate' => 'required|date',
        ]);

        if ($this->scheduleType === 'custom' && empty($this->customDays)) {
            $this->addError('customDays', 'Please select at least one day.');
            return false;
        }

        return true;
    }

    protected function validateStep5(): bool
    {
        if ($this->targetType === 'students') {
            // At least one filter should be set, or all students
            return true;
        }
        return true;
    }

    protected function validateStep6(): bool
    {
        if ($this->enableReminders && empty($this->reminderChannels)) {
            $this->addError('reminderChannels', 'Please select at least one reminder channel.');
            return false;
        }
        return true;
    }

    // ============================================
    // QUESTION MANAGEMENT
    // ============================================

    public function openQuestionEditor(?int $index = null): void
    {
        $this->editingQuestionIndex = $index;

        if ($index !== null && isset($this->inlineQuestions[$index])) {
            $this->questionForm = $this->inlineQuestions[$index];
        } else {
            $this->questionForm = [
                'id' => (string) Str::uuid(),
                'type' => 'scale',
                'question' => '',
                'options' => ['1' => 'Strongly Disagree', '5' => 'Strongly Agree'],
                'required' => true,
            ];
        }

        $this->showQuestionEditor = true;
    }

    public function closeQuestionEditor(): void
    {
        $this->showQuestionEditor = false;
        $this->editingQuestionIndex = null;
        $this->questionForm = [];
    }

    public function saveQuestion(): void
    {
        $this->validate([
            'questionForm.question' => 'required|string|max:500',
            'questionForm.type' => 'required|string|in:scale,multiple_choice,text,voice',
        ]);

        if ($this->editingQuestionIndex !== null) {
            $this->inlineQuestions[$this->editingQuestionIndex] = $this->questionForm;
        } else {
            $this->inlineQuestions[] = $this->questionForm;
        }

        $this->closeQuestionEditor();
    }

    public function removeQuestion(int $index): void
    {
        unset($this->inlineQuestions[$index]);
        $this->inlineQuestions = array_values($this->inlineQuestions);
    }

    public function addOption(): void
    {
        $options = $this->questionForm['options'] ?? [];
        $options[] = '';
        $this->questionForm['options'] = $options;
    }

    public function removeOption(int $index): void
    {
        unset($this->questionForm['options'][$index]);
        $this->questionForm['options'] = array_values($this->questionForm['options']);
    }

    // ============================================
    // SCHEDULE HELPERS
    // ============================================

    public function toggleDay(string $day): void
    {
        if (in_array($day, $this->customDays)) {
            $this->customDays = array_values(array_filter($this->customDays, fn($d) => $d !== $day));
        } else {
            $this->customDays[] = $day;
        }
    }

    public function addTime(): void
    {
        $this->customTimes[] = '09:00';
    }

    public function removeTime(int $index): void
    {
        unset($this->customTimes[$index]);
        $this->customTimes = array_values($this->customTimes);
    }

    // ============================================
    // CONTACT SCOPE HELPERS
    // ============================================

    public function toggleGrade(string $grade): void
    {
        if (in_array($grade, $this->selectedGrades)) {
            $this->selectedGrades = array_values(array_filter($this->selectedGrades, fn($g) => $g !== $grade));
        } else {
            $this->selectedGrades[] = $grade;
        }
    }

    public function toggleClassroom(int $classroomId): void
    {
        if (in_array($classroomId, $this->selectedClassrooms)) {
            $this->selectedClassrooms = array_values(array_filter($this->selectedClassrooms, fn($c) => $c !== $classroomId));
        } else {
            $this->selectedClassrooms[] = $classroomId;
        }
    }

    public function toggleReminderChannel(string $channel): void
    {
        if (in_array($channel, $this->reminderChannels)) {
            $this->reminderChannels = array_values(array_filter($this->reminderChannels, fn($c) => $c !== $channel));
        } else {
            $this->reminderChannels[] = $channel;
        }
    }

    // ============================================
    // SAVE COLLECTION
    // ============================================

    public function save(bool $activate = false)
    {
        $user = auth()->user();

        // Build contact scope
        $contactScope = [
            'target_type' => $this->targetType,
        ];

        if ($this->targetType === 'students') {
            if (!empty($this->selectedGrades)) {
                $contactScope['grades'] = $this->selectedGrades;
            }
            if (!empty($this->selectedClassrooms)) {
                $contactScope['classrooms'] = $this->selectedClassrooms;
            }
            if (!empty($this->selectedTags)) {
                $contactScope['tags'] = $this->selectedTags;
            }
        } else {
            if (!empty($this->selectedRoles)) {
                $contactScope['roles'] = $this->selectedRoles;
            }
        }

        // Build reminder config
        $reminderConfig = null;
        if ($this->enableReminders) {
            $reminderConfig = [
                'enabled' => true,
                'channels' => $this->reminderChannels,
                'lead_time_minutes' => $this->reminderLeadTime,
                'follow_up_enabled' => $this->enableFollowUp,
                'follow_up_delay_hours' => $this->followUpDelay,
            ];
        }

        // Create collection
        $collection = $this->collectionService->create([
            'title' => $this->title,
            'description' => $this->description,
            'collection_type' => $this->collectionType,
            'data_source' => $this->dataSource,
            'survey_id' => $this->dataSource === 'inline' ? null : $this->surveyId,
            'inline_questions' => in_array($this->dataSource, ['inline', 'hybrid']) ? $this->inlineQuestions : null,
            'format_mode' => $this->formatMode,
            'status' => $activate ? Collection::STATUS_ACTIVE : Collection::STATUS_DRAFT,
            'contact_scope' => $contactScope,
            'reminder_config' => $reminderConfig,
        ], $user);

        // Create schedule if not one-time
        if ($this->collectionType !== 'one_time') {
            $this->collectionService->createSchedule($collection, [
                'schedule_type' => $this->scheduleType,
                'interval_type' => $this->scheduleType === 'interval' ? $this->intervalType : null,
                'interval_value' => $this->intervalValue,
                'custom_days' => $this->scheduleType === 'custom' ? $this->customDays : null,
                'custom_times' => $this->customTimes,
                'event_trigger' => $this->scheduleType === 'event' ? $this->eventTrigger : null,
                'timezone' => $this->timezone,
                'start_date' => $this->startDate,
                'end_date' => $this->endDate,
                'is_active' => $activate,
            ]);
        }

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => $activate
                ? 'Collection created and activated!'
                : 'Collection saved as draft.',
        ]);

        return redirect()->route('collect.show', $collection);
    }

    // ============================================
    // COMPUTED PROPERTIES
    // ============================================

    public function getStepsProperty(): array
    {
        return [
            1 => ['label' => 'Basic Info', 'icon' => 'information-circle'],
            2 => ['label' => 'Data Source', 'icon' => 'document-text'],
            3 => ['label' => 'Format', 'icon' => 'adjustments-horizontal'],
            4 => ['label' => 'Schedule', 'icon' => 'clock'],
            5 => ['label' => 'Contacts', 'icon' => 'users'],
            6 => ['label' => 'Reminders', 'icon' => 'bell'],
            7 => ['label' => 'Review', 'icon' => 'check-circle'],
        ];
    }

    public function getQuestionTypesProperty(): array
    {
        return [
            'scale' => ['label' => 'Scale (1-5)', 'icon' => 'chart-bar'],
            'multiple_choice' => ['label' => 'Multiple Choice', 'icon' => 'list-bullet'],
            'text' => ['label' => 'Free Text', 'icon' => 'document-text'],
            'voice' => ['label' => 'Voice Response', 'icon' => 'microphone'],
        ];
    }

    public function getCollectionTypesProperty(): array
    {
        return [
            'recurring' => ['label' => 'Recurring', 'description' => 'Collect data on a regular schedule', 'icon' => 'arrow-path'],
            'one_time' => ['label' => 'One-Time', 'description' => 'Single collection session', 'icon' => 'document-check'],
            'event_triggered' => ['label' => 'Event-Triggered', 'description' => 'Triggered by specific events', 'icon' => 'bolt'],
        ];
    }

    public function getDataSourcesProperty(): array
    {
        return [
            'survey' => ['label' => 'Link to Survey', 'description' => 'Use questions from an existing survey', 'icon' => 'link'],
            'inline' => ['label' => 'Define Inline', 'description' => 'Create questions specifically for this collection', 'icon' => 'pencil-square'],
            'hybrid' => ['label' => 'Hybrid', 'description' => 'Survey questions + additional inline questions', 'icon' => 'squares-plus'],
        ];
    }

    public function getFormatModesProperty(): array
    {
        return [
            'form' => ['label' => 'Form', 'description' => 'Traditional question-by-question form', 'icon' => 'clipboard-document-list'],
            'conversational' => ['label' => 'Conversational', 'description' => 'AI-guided conversational interface', 'icon' => 'chat-bubble-left-right'],
            'grid' => ['label' => 'Grid', 'description' => 'Spreadsheet-style bulk entry', 'icon' => 'table-cells'],
        ];
    }

    public function getScheduleTypesProperty(): array
    {
        return [
            'interval' => ['label' => 'Interval', 'description' => 'Every N days/weeks/months'],
            'custom' => ['label' => 'Custom Days', 'description' => 'Specific days of the week'],
            'event' => ['label' => 'Event-Based', 'description' => 'Triggered by specific events'],
        ];
    }

    public function getDaysOfWeekProperty(): array
    {
        return ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
    }

    public function getEstimatedContactCountProperty(): int
    {
        $user = auth()->user();
        $query = Student::where('org_id', $user->org_id)->whereNull('deleted_at');

        if (!empty($this->selectedGrades)) {
            $query->whereIn('grade', $this->selectedGrades);
        }

        if (!empty($this->selectedClassrooms)) {
            $query->whereIn('classroom_id', $this->selectedClassrooms);
        }

        return $query->count();
    }

    public function render()
    {
        return view('livewire.collect.collection-creator')
            ->layout('components.layouts.dashboard', ['title' => 'Create Collection']);
    }
}
