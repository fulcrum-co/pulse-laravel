# Pulse - AI-Powered User Support Platform

## Project Overview

Pulse is a Laravel 12 application designed for Enterprise organizational organizations to monitor user well-being, identify at-risk users through data analysis, and deliver personalized interventions through mini-courses and workflows. The platform serves organization administrators, organization leads, advisors, coachs, and users.

**Tech Stack:**
- Laravel 12 with PHP 8.3
- Livewire 4.x for reactive UI components
- Tailwind CSS for styling
- MongoDB (via laravel-mongodb package) for flexible document storage
- MySQL/PostgreSQL for relational data
- Redis for caching, sessions, and queues
- Laravel Cloud for deployment

---

## Core Domain Concepts

### Organizations (Multi-Tenant Hierarchy)
- **Organizations** - Top-level organizations containing multiple organizations
- **Organizations** - Individual organizations within a organization
- **Direct Supervisor-Child Relationships** - Organizations can have hierarchical relationships via `parent_org_id`
- **Downstream Organizations** - Organizations can push content/resources to child organizations

### Users & Roles
- **Organization Admin** - Full access to organization and all child organizations
- **Organization Lead** - Access to their organization
- **Advisor** - User-facing, manages interventions
- **Coach** - Limited view of assigned users
- **Consultant** - External users with access to assigned organizations
- **User** - End users who consume mini-courses

### Users
- Belong to organizations (organizations)
- Have risk signals tracked across multiple factors
- Can be enrolled in mini-courses
- Have demographic data, support plan status, level levels

### Risk Factors
The platform tracks multiple risk dimensions:
- `attendance` - Attendance patterns and absences
- `behavior` - Behavioral incidents
- `academic` - Academic performance
- `social_emotional` - Social-emotional indicators
- `engagement` - Engagement metrics

---

## Database Architecture

### Key Tables

#### Organizations
```
organizations
├── id
├── parent_org_id (nullable, self-referential FK)
├── name
├── type (organization, organization, etc.)
├── settings (JSON)
├── course_generation_settings (JSON) -- AI course generation config
├── active
└── timestamps
```

#### Users
```
users
├── id
├── org_id (FK to organizations)
├── primary_role
├── email
├── user_id (nullable, FK to users)
└── timestamps
```

#### Users
```
users
├── id
├── org_id (FK to organizations)
├── user_number
├── first_name, last_name
├── level
├── demographics (JSON)
├── iep_status
└── timestamps
```

---

## Mini-Courses System (Learning Delivery)

### Overview
Mini-courses are personalized learning experiences delivered to users. They consist of ordered steps with various content types and can be generated manually, from templates, or via AI.

### Database Schema

#### mini_courses
```
mini_courses
├── id
├── org_id (FK)
├── title, slug, description
├── course_type (wellness, academic, social_emotional, behavioral)
├── objectives (JSON array)
├── rationale (TEXT) -- Why this course was created/assigned
├── status (draft, active, archived)
├── current_version_id (FK to mini_course_versions)
├── is_ai_generated (BOOLEAN)
├── template_id (FK to course_templates, nullable)
├── generation_request_id (FK, nullable)
├── assigned_user_ids (JSON)
├── created_by (FK to users)
└── timestamps, soft_deletes
```

#### mini_course_steps
```
mini_course_steps
├── id
├── mini_course_id (FK)
├── sort_order (INT)
├── step_type (content, reflection, action, practice, human_connection, assessment, checkpoint)
├── title, description, instructions
├── content_type (text, video, document, link, embedded, interactive)
├── content_data (JSON) -- Flexible content storage
├── resource_id (FK, nullable)
├── provider_id (FK, nullable)
├── program_id (FK, nullable)
├── estimated_duration_minutes
├── is_required (BOOLEAN)
├── completion_criteria (JSON)
├── branching_logic (JSON)
├── feedback_prompt (TEXT)
└── timestamps, soft_deletes
```

#### mini_course_enrollments
```
mini_course_enrollments
├── id
├── mini_course_id (FK)
├── mini_course_version_id (FK)
├── user_id (FK)
├── enrolled_by (FK to users)
├── enrollment_source (self_enrolled, assigned, workflow, risk_triggered)
├── status (not_started, in_progress, completed, dropped)
├── progress_percentage (DECIMAL)
├── current_step_id (FK)
├── started_at, completed_at
└── timestamps
```

#### mini_course_step_progress
```
mini_course_step_progress
├── id
├── enrollment_id (FK)
├── step_id (FK)
├── status (not_started, in_progress, completed, skipped)
├── response_data (JSON) -- Reflection answers, quiz responses
├── time_spent_seconds
├── completed_at
└── timestamps
```

### Step Types Explained

| Type | Purpose | Example |
|------|---------|---------|
| `content` | Organizational material to consume | Video lesson, reading |
| `reflection` | Prompt for user journaling/thinking | "What triggers your stress?" |
| `action` | Task to complete outside the course | "Talk to a trusted adult" |
| `practice` | Interactive exercise | Breathing technique practice |
| `human_connection` | Connect with advisor/mentor | Schedule appointment |
| `assessment` | Quiz or self-assessment | Resilience self-check |
| `checkpoint` | Progress milestone | Summary and celebration |

### Content Data Structures

**Video Content:**
```json
{
  "video_url": "https://www.youtube.com/embed/VIDEO_ID",
  "duration_seconds": 300,
  "body": "## Welcome\n\nMarkdown description...",
  "key_points": ["Point 1", "Point 2", "Point 3"]
}
```

**Text with Downloads:**
```json
{
  "body": "## Main Content\n\nMarkdown text...",
  "key_points": ["Key takeaway 1", "Key takeaway 2"],
  "downloads": [
    {
      "title": "Worksheet",
      "filename": "worksheet.pdf",
      "type": "pdf",
      "size": "245 KB"
    }
  ]
}
```

**Assessment/Quiz:**
```json
{
  "instructions": "Answer honestly...",
  "questions": [
    {
      "id": 1,
      "question": "When facing a challenge, I typically...",
      "type": "multiple_choice",
      "options": ["Option A", "Option B", "Option C", "Option D"],
      "correct_answer": 1
    }
  ]
}
```

**Reflection:**
```json
{
  "prompts": [
    "Think about a recent stressful situation...",
    "What patterns do you notice?"
  ],
  "depth": "moderate"
}
```

**Interactive Activity:**
```json
{
  "type": "breathing_exercise",
  "title": "4-7-8 Breathing",
  "instructions": "Follow along...",
  "steps": [
    {"action": "Breathe in", "duration": 4},
    {"action": "Hold", "duration": 7},
    {"action": "Breathe out", "duration": 8}
  ]
}
```

---

## AI-Powered Course Generation System

### Overview
The platform can automatically generate personalized mini-courses based on user data using a hybrid approach: curated content blocks + AI generation + external content sources.

### Database Schema

#### content_blocks
Modular, reusable content pieces tagged for discovery and assembly.

```
content_blocks
├── id
├── org_id (FK, nullable for system blocks)
├── title, slug, description
├── block_type (video, document, activity, assessment, text, link, embed)
├── content_data (JSON)
├── source_type (internal, youtube, vimeo, khan_academy, uploaded, custom_url)
├── source_url, source_metadata (JSON)
├── topics (JSON array) -- ['anxiety', 'stress', 'coping']
├── skills (JSON array) -- ['breathing', 'journaling']
├── levels (JSON array) -- ['6', '7', '8']
├── subject_areas (JSON array) -- ['SEL', 'health']
├── target_risk_factors (JSON array)
├── target_demographics (JSON)
├── iep_appropriate (BOOLEAN)
├── language (VARCHAR, default 'en')
├── usage_count, avg_completion_rate, avg_rating
├── status (draft, active, archived)
├── reviewed_at, reviewed_by (FK)
└── timestamps
```

#### content_tags
```
content_tags
├── id
├── org_id (FK)
├── name, slug
├── category (topic, skill, level, subject, risk_factor)
├── description, color
└── timestamps

content_block_tag (pivot)
├── content_block_id (FK)
├── content_tag_id (FK)
```

#### course_templates
Pre-defined course structures with placeholders.

```
course_templates
├── id
├── org_id (FK, nullable for system templates)
├── name, slug, description
├── course_type
├── template_data (JSON) -- Full structure with placeholders
├── target_risk_factors (JSON)
├── target_levels (JSON)
├── estimated_duration_minutes
├── is_system (BOOLEAN)
├── status (draft, active, archived)
├── usage_count
├── created_by (FK)
└── timestamps, soft_deletes
```

**Template Data Structure:**
```json
{
  "objectives_template": [
    "Understand {topic}",
    "Practice {skill}",
    "Apply techniques"
  ],
  "variables": {
    "topic": {"required": true, "description": "Main topic"},
    "skill": {"required": true, "description": "Primary skill"}
  },
  "steps": [
    {
      "order": 1,
      "title_template": "Introduction to {topic}",
      "step_type": "content",
      "content_type": "video",
      "content_block_query": {
        "block_type": "video",
        "topics": ["{primary_topic}"],
        "max_duration": 300
      },
      "fallback_ai_prompt": "Create an introduction to {topic} for {level} users"
    }
  ]
}
```

#### course_generation_requests
Tracks all generation requests for auditing and approval.

```
course_generation_requests
├── id
├── org_id (FK)
├── trigger_type (risk_threshold, workflow, manual)
├── triggered_by_user_id (FK)
├── workflow_execution_id (FK, nullable)
├── assignment_type (individual, group)
├── target_user_ids (JSON)
├── target_group_id (FK, nullable)
├── user_context (JSON) -- Risk signals, demographics, history
├── template_id (FK, nullable)
├── generation_strategy (template_fill, ai_full, hybrid)
├── generation_params (JSON)
├── generated_course_id (FK to mini_courses)
├── generation_log (JSON)
├── status (pending, generating, pending_approval, approved, rejected, failed)
├── requires_approval (BOOLEAN)
├── approved_by, approved_at, rejection_reason
└── timestamps
```

### Organization Course Generation Settings
```json
{
  "enabled": true,
  "approval_required": true,
  "approval_roles": ["admin", "advisor"],
  "auto_approve_templates": false,
  "allowed_triggers": ["risk_threshold", "workflow", "manual"],
  "risk_threshold_config": {
    "enabled": true,
    "min_risk_score": 0.7,
    "risk_factors": ["attendance", "behavior", "academic"]
  },
  "default_generation_strategy": "hybrid",
  "external_sources": {
    "youtube_enabled": true,
    "khan_academy_enabled": true,
    "custom_uploads_enabled": true
  },
  "ai_config": {
    "model": "claude-sonnet",
    "creativity_level": "balanced"
  }
}
```

### Generation Architecture

```
TRIGGER SOURCES
├── Risk Threshold (auto-detect high-risk users)
├── Alert Workflow (node action in workflow builder)
└── Manual Staff Request (UI button)
           │
           ▼
COURSE GENERATION SERVICE
├── 1. Aggregate User Context
├── 2. Select Generation Strategy
├── 3. Query Content Blocks + External Sources
├── 4. AI Assembly/Generation
├── 5. Build Course Structure
└── 6. Route to Approval (if required)
           │
     ┌─────┼─────┐
     ▼     ▼     ▼
CONTENT  TEMPLATES  EXTERNAL
BLOCKS              SOURCES
```

### Planned Services

| Service | Purpose |
|---------|---------|
| `CourseGenerationService` | Main orchestrator |
| `UserContextAggregator` | Collects user data for personalization |
| `AIGenerationService` | Claude API integration for content generation |
| `ExternalContentService` | YouTube, Khan Academy, uploads integration |
| `CourseAssemblyService` | Builds final course structure |

---

## Alert Workflows System

### Overview
Visual workflow builder for creating automated alert rules that trigger actions based on user data changes.

### Current State

#### workflows
```
workflows
├── id
├── org_id (FK)
├── name, description
├── trigger_type (data_change, schedule, manual, risk_threshold)
├── trigger_config (JSON)
├── canvas_data (JSON) -- Visual node positions
├── nodes (JSON) -- Workflow logic nodes
├── status (draft, active, paused, archived)
├── last_triggered_at
└── timestamps
```

#### workflow_executions
```
workflow_executions
├── id
├── workflow_id (FK)
├── trigger_data (JSON) -- What triggered this execution
├── node_results (JSON) -- Results from each node
├── status (pending, running, waiting, completed, failed, cancelled)
├── started_at, completed_at
├── error_message
└── timestamps
```

### Node Types

**Triggers:**
- `data_change` - React to data updates (attendance, levels, behavior)
- `schedule` - Run on schedule (daily, weekly)
- `risk_threshold` - When risk score exceeds threshold
- `manual` - Manually triggered

**Conditions:**
- `if_then` - Branch based on conditions
- `filter` - Filter user population
- `wait` - Delay execution

**Actions:**
- `send_email` - Send email notification
- `send_sms` - Send SMS message
- `create_task` - Create task for staff
- `send_notification` - In-app notification
- `call_webhook` - External API call
- `generate_course` - Trigger AI course generation (planned)

### Planned Alerts Section Redesign

The Alerts section will be redesigned with a **tabbed interface**:

1. **"Notifications" Tab** - Shows ALL workflow executions with action details
   - Displays what actions were taken (emails sent, SMS sent, tasks created)
   - Status badges (completed, failed, running, waiting)
   - Retry failed executions
   - View execution details

2. **"Alert Workflows" Tab** - Existing workflow management
   - Create, edit, list workflows
   - Visual canvas builder
   - Test and activate workflows

---

## Key Models & Relationships

### CourseTemplate Model
```php
class CourseTemplate extends Model
{
    use SoftDeletes;

    // Note: Custom fill() was renamed to fillTemplate() to avoid
    // conflict with Laravel's built-in Model::fill() method

    public function fillTemplate(array $values): array
    {
        // Recursively replaces {placeholders} with values
    }

    public function extractPlaceholders(): array
    {
        // Returns all {placeholder} names from template_data
    }

    // Scopes
    public function scopeActive($query);
    public function scopeSystem($query);
    public function scopeForOrganization($query, int $orgId);
    public function scopeAvailableFor($query, int $orgId); // org + system
    public function scopeForRiskFactors($query, array $factors);
}
```

### MiniCourseStep Model
```php
class MiniCourseStep extends Model
{
    use SoftDeletes;

    // Step types
    const TYPE_CONTENT = 'content';
    const TYPE_REFLECTION = 'reflection';
    const TYPE_ACTION = 'action';
    const TYPE_PRACTICE = 'practice';
    const TYPE_HUMAN_CONNECTION = 'human_connection';
    const TYPE_ASSESSMENT = 'assessment';
    const TYPE_CHECKPOINT = 'checkpoint';

    // Content types
    const CONTENT_TEXT = 'text';
    const CONTENT_VIDEO = 'video';
    const CONTENT_DOCUMENT = 'document';
    const CONTENT_LINK = 'link';
    const CONTENT_EMBEDDED = 'embedded';
    const CONTENT_INTERACTIVE = 'interactive';

    // Relationships
    public function miniCourse(): BelongsTo;
    public function resource(): BelongsTo;
    public function provider(): BelongsTo;
    public function program(): BelongsTo;
    public function progress(): HasMany;

    // Navigation
    public function getPreviousStepAttribute(): ?self;
    public function getNextStepAttribute(): ?self;
    public function isFirstStep(): bool;
    public function isLastStep(): bool;
}
```

---

## Livewire Components

### Key Components

| Component | Purpose |
|-----------|---------|
| `MiniCourseViewer` | User-facing course viewer |
| `AlertsIndex` | Alerts management with search, filters, views |
| `WorkflowCanvas` | Visual workflow builder |
| `UserProfile` | User detail view with risk data |
| `GenerateCourseModal` | Manual course generation trigger |

### Common Patterns

**View Modes:** Grid, List, Table (shared across listing components)

**Search & Filters:** URL query string synced via `$queryString`

**Notifications:** `$this->dispatch('notify', ['type' => 'success', 'message' => '...'])`

---

## File Structure

```
app/
├── Http/Controllers/
├── Livewire/
│   ├── Alerts/
│   │   ├── AlertsIndex.php
│   │   ├── WorkflowCanvas.php
│   │   └── WorkflowHistory.php
│   ├── Courses/
│   │   ├── MiniCourseViewer.php
│   │   └── GenerateCourseModal.php (planned)
│   ├── Admin/
│   │   ├── ContentBlocksIndex.php (planned)
│   │   └── CourseTemplatesIndex.php (planned)
│   └── Settings/
├── Models/
│   ├── Organization.php
│   ├── User.php
│   ├── User.php
│   ├── MiniCourse.php
│   ├── MiniCourseStep.php
│   ├── MiniCourseEnrollment.php
│   ├── MiniCourseStepProgress.php
│   ├── ContentBlock.php
│   ├── ContentTag.php
│   ├── CourseTemplate.php
│   ├── CourseGenerationRequest.php
│   ├── Workflow.php
│   └── WorkflowExecution.php
├── Services/
│   ├── CourseGeneration/
│   │   ├── CourseGenerationService.php (planned)
│   │   ├── UserContextAggregator.php (planned)
│   │   ├── AIGenerationService.php (planned)
│   │   ├── ExternalContentService.php (planned)
│   │   └── CourseAssemblyService.php (planned)
│   └── Workflow/
│       ├── WorkflowEngine.php
│       └── Actions/
├── Jobs/
│   ├── ProcessCourseGeneration.php (planned)
│   └── ProcessWorkflow.php
└── Observers/
    └── UserRiskObserver.php (planned)

database/
├── migrations/
│   ├── 2026_01_31_700000_create_content_blocks_table.php ✓
│   ├── 2026_01_31_700001_create_content_tags_table.php ✓
│   ├── 2026_01_31_700002_create_course_templates_table.php ✓
│   ├── 2026_01_31_700003_create_course_generation_requests_table.php ✓
│   ├── 2026_01_31_700004_add_course_generation_settings.php ✓
│   ├── 2026_01_31_700005_extend_mini_courses_for_ai.php ✓
│   └── 2026_01_31_700006_seed_content_blocks_templates_demo_course.php ✓
└── seeders/
    └── UserSeeder.php

resources/views/
├── layouts/
│   └── dashboard.blade.php
├── livewire/
│   ├── alerts/
│   │   ├── alerts-index.blade.php
│   │   └── partials/
│   ├── mini-course-viewer.blade.php
│   └── components/
└── components/
```

---

## Demo Data

### Demo Users (Password: `password`)

| Role | Email | Organization |
|------|-------|--------------|
| Organization Admin | mchen@lincolnorganizations.edu | Lincoln USD |
| Organization Lead | mtorres@lincolnhigh.edu | Lincoln High |
| Advisor | erodriguez@lincolnhigh.edu | Lincoln High |

### Demo Course: "Building Emotional Resilience"

A comprehensive 9-step course demonstrating all content types:
1. Welcome & Introduction (video)
2. Understanding Emotional Resilience (text + download)
3. Self-Assessment (quiz)
4. Deep Breathing Practice (interactive)
5. Grounding Technique (practice)
6. Reflection on Stress Triggers (reflection)
7. Create Your Resilience Plan (action + worksheet)
8. Connect with Support (human_connection)
9. Course Completion (checkpoint)

---

## Deployment

### Laravel Cloud Configuration (cloud.yaml)

```yaml
name: pulse

environments:
  production:
    php: 8.3
    node: 20

    build:
      - composer install --no-dev --optimize-autoloader
      - npm ci
      - npm run build

    deploy:
      - php artisan migrate --force
      - php artisan config:cache
      - php artisan route:cache
      - php artisan view:cache

    scheduler: true
    queue:
      workers: 1

    databases:
      - pulse-db

    caches:
      - pulse-cache
```

---

## Implementation Status

### Phase 1: Database & Models ✅ COMPLETE
- [x] Content Blocks schema & model
- [x] Content Tags schema & model
- [x] Course Templates schema & model
- [x] Course Generation Requests schema & model
- [x] Organization settings extension
- [x] MiniCourse model extensions
- [x] Rich demo course seeded

### Phase 2: Core Services (Planned)
- [ ] UserContextAggregator
- [ ] CourseAssemblyService
- [ ] CourseGenerationService
- [ ] ProcessCourseGeneration job

### Phase 3: AI Integration (Planned)
- [ ] AIGenerationService with Claude API
- [ ] Template filling logic
- [ ] Full course generation

### Phase 4: External Integrations (Planned)
- [ ] YouTube API integration
- [ ] Khan Academy API integration
- [ ] Document upload processing

### Phase 5: Trigger Points (Planned)
- [ ] Risk threshold observer
- [ ] Workflow action node
- [ ] Manual request component

### Phase 6: Admin UI (Planned)
- [ ] Content blocks management
- [ ] Template builder
- [ ] Generation dashboard
- [ ] Organization settings

---

## Coding Conventions

### Laravel Best Practices
- Use Eloquent relationships and scopes
- JSON columns for flexible data storage
- Soft deletes on important models
- Form requests for validation
- Jobs for async processing
- Observers for event handling

### Livewire Patterns
- Public properties synced to URL via `$queryString`
- Computed properties via `get{Name}Property()`
- Dispatch events for cross-component communication
- Mount() for initialization with route parameters

### Naming Conventions
- Models: Singular PascalCase (`CourseTemplate`)
- Tables: Plural snake_case (`course_templates`)
- Pivot tables: Alphabetical order (`content_block_tag`)
- JSON fields: snake_case keys
- Constants: SCREAMING_SNAKE_CASE

---

## Common Gotchas

1. **Model::fill() Conflict** - Never name a method `fill()` on Eloquent models; it conflicts with Laravel's mass assignment method.

2. **JSON Column Casts** - Always add JSON columns to `$casts` array as `'array'`.

3. **Soft Deletes** - Remember to use `withTrashed()` when you need to include deleted records.

4. **Organization Scoping** - Always scope queries by `org_id` for multi-tenant data isolation.

5. **Migration Order** - Foreign keys require the referenced table to exist first; order migrations carefully.
