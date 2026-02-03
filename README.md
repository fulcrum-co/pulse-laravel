# Pulse - Enterprise Organizational Content Platform

Pulse is a comprehensive organizational content management platform designed for Enterprise wellness and mental health organization. It provides AI-powered content generation, moderation workflows, and resource management tools.

## Table of Contents

- [Features](#features)
- [Architecture](#architecture)
- [Installation](#installation)
- [AI Content Moderation](#ai-content-moderation)
- [Moderation Workflow System](#moderation-workflow-system)
- [Search Infrastructure](#search-infrastructure)
- [Resource Library](#resource-library)
- [Artisan Commands](#artisan-commands)
- [API Documentation](#api-documentation)

---

## Features

### Core Platform
- **Multi-tenant Architecture**: Organization-based data isolation
- **Role-based Access Control**: Granular permissions for admins, moderators, educators, and users
- **MiniCourses**: AI-generated microlearning courses with structured modules
- **Content Blocks**: Reusable organizational content components
- **Resource Hub**: Curated external resources and materials

### AI-Powered Features
- **Course Generation**: Claude AI-powered course creation with customizable parameters
- **Content Moderation**: Automated safety scoring across 4 dimensions
- **Semantic Search**: Vector-based similarity search using pgvector
- **Full-text Search**: Meilisearch integration for fast content discovery

### Moderation Workflow
- **Task Flow Interface**: HubSpot-style guided review experience
- **SLA Tracking**: Configurable deadlines with warning and breach notifications
- **Team Analytics**: Performance dashboards for moderation teams
- **Keyboard Shortcuts**: Productivity-focused review workflow

---

## Architecture

### Technology Stack

| Component | Technology |
|-----------|------------|
| Backend | Laravel 12.x |
| Frontend | Livewire 4.x, Alpine.js, Tailwind CSS |
| Database | PostgreSQL with pgvector extension |
| Search | Meilisearch (full-text), pgvector (semantic) |
| AI | Anthropic Claude API |
| Queue | Laravel Horizon / Redis |
| Hosting | Laravel Cloud |

### Key Directories

```
app/
├── Console/Commands/      # Artisan commands
├── Jobs/                  # Background jobs (embedding, moderation)
├── Livewire/Admin/        # Admin panel components
├── Models/                # Eloquent models
├── Policies/              # Authorization policies
├── Services/
│   ├── Embeddings/        # Vector embedding services
│   ├── Moderation/        # Content moderation services
│   └── Search/            # Search services
└── Traits/                # Reusable model traits

database/
├── migrations/            # Database schema
└── seeders/               # Demo data seeders

resources/views/livewire/admin/
├── moderation-queue.blade.php       # Main moderation queue
├── moderation-task-flow.blade.php   # Guided review interface
├── moderation-dashboard.blade.php   # Analytics dashboard
└── moderation-edit.blade.php        # Content editing view
```

---

## Installation

### Prerequisites
- PHP 8.2+
- PostgreSQL 15+ with pgvector extension
- Redis
- Node.js 20+
- Composer

### Setup

```bash
# Clone repository
git clone https://github.com/fulcrum-co/pulse-laravel.git
cd pulse-laravel

# Install dependencies
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database setup
php artisan migrate
php artisan db:seed

# Build assets
npm run build

# Start development server
php artisan serve
```

### Docker Development

```bash
docker compose up -d
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed
```

---

## AI Content Moderation

Pulse includes a comprehensive AI-powered content moderation system designed specifically for Enterprise organizational content.

### Moderation Dimensions

Content is scored across four key dimensions:

| Dimension | Description | Weight |
|-----------|-------------|--------|
| **Age Appropriateness** | Vocabulary, concepts, and complexity suitable for target level level | 25% |
| **Clinical Safety** | Health/mental health advice safety, crisis resource inclusion | 30% |
| **Cultural Sensitivity** | Inclusive language, diverse representation, bias detection | 20% |
| **Accuracy** | Factual correctness, source credibility, claim verification | 25% |

### Score Thresholds

- **85%+**: Auto-approved (configurable)
- **70-84%**: Flagged for human review
- **Below 70%**: Requires human review before publishing

### Models

#### ContentModerationResult

Stores AI moderation results with:
- Overall and dimension-specific scores (0-1 scale)
- Flags array identifying specific concerns
- Recommendations for improvements
- Dimension details with explanations
- Processing metadata (model version, tokens, timing)

```php
// Example usage
$result = $course->moderate();

// Check scores
$result->overall_score;           // 0.82
$result->age_appropriateness_score; // 0.90
$result->clinical_safety_score;   // 0.75

// Get flags
$result->flags; // ['Clinical safety: Add professional consultation disclaimer']
```

### Trait: HasContentModeration

Add moderation capabilities to any model:

```php
use App\Traits\HasContentModeration;

class MiniCourse extends Model
{
    use HasContentModeration;

    public function getModerationContent(): string
    {
        return $this->title . "\n\n" . $this->description;
    }

    public function getModerationMetadata(): array
    {
        return [
            'target_levels' => $this->target_levels,
            'content_type' => 'mini_course',
        ];
    }
}
```

---

## Moderation Workflow System

The workflow system provides a structured process for human review of AI-moderated content.

### Components

#### 1. Moderation Queue (`/admin/moderation`)

The main queue interface with:
- **List/Grid/Table views**: Toggle between display modes
- **Filtering**: By status (pending, flagged, passed, rejected), content type, assignment
- **Bulk actions**: Assign multiple items to moderators
- **Quick actions**: Approve/reject directly from queue

#### 2. Task Flow (`/admin/moderation/task-flow`)

HubSpot-style guided review experience:
- **Progress tracking**: Daily review count and goals
- **Sequential review**: Auto-load next item after decision
- **Keyboard shortcuts**: A (approve), R (reject), C (changes), E (escalate), S (skip)
- **Inline editing**: Modify content before approving

#### 3. Dashboard (`/admin/moderation/dashboard`)

Analytics and team performance:
- **Queue depth**: Total items, overdue, urgent
- **SLA compliance**: Percentage completed on time
- **Average review time**: Minutes per review
- **Team performance table**: Per-moderator statistics

### Database Schema

```sql
-- Moderation queue items (work assignments)
moderation_queue_items
├── id
├── org_id
├── moderation_result_id (FK)
├── workflow_id (FK, nullable)
├── status: pending | in_progress | completed | escalated | expired
├── assigned_to (FK users)
├── assigned_by (FK users)
├── assigned_at
├── due_at
├── priority: low | normal | high | urgent
└── timestamps

-- Decision audit trail
moderation_decisions
├── id
├── queue_item_id (FK)
├── user_id (FK)
├── decision: approve | reject | request_changes | escalate | skip
├── notes
├── field_changes (JSON)
├── time_spent_seconds
└── timestamps

-- SLA configuration
moderation_sla_configs
├── id
├── org_id
├── priority
├── target_hours
├── warning_hours
├── is_active
└── timestamps
```

### Services

#### ModerationQueueService

Handles queue operations:

```php
$service = app(ModerationQueueService::class);

// Get queue for user
$items = $service->getQueueForUser($user);

// Assignment strategies
$service->assignRoundRobin($item);
$service->assignLeastLoaded($item);
$service->assignBySkill($item, ['wellness', 'clinical']);

// SLA management
$status = $service->checkSlaStatus($item); // 'ok', 'warning', 'breached'
$dueSoon = $service->getItemsDueSoon($org, hoursAhead: 24);

// Statistics
$stats = $service->getQueueStats($orgId);
// Returns: total, by_priority, overdue, avg_review_time, etc.
```

#### ModerationWorkflowService

Executes workflow logic for routing and automation.

---

## Search Infrastructure

Pulse supports two complementary search approaches:

### 1. Full-Text Search (Meilisearch)

Fast, typo-tolerant text search for:
- Course titles and descriptions
- Resource names and content
- Content block text

```php
// Configure indexes
php artisan search:configure

// Reindex content
php artisan search:reindex --model=MiniCourse

// Search
$results = MiniCourse::search('anxiety management techniques')->get();
```

### 2. Semantic Search (pgvector)

AI-powered similarity search using embeddings:

```php
// Generate embeddings
php artisan embeddings:backfill --model=MiniCourse

// Similar content search
$similar = $course->findSimilar(limit: 5);

// Semantic search
$results = MiniCourse::semanticSearch('coping with stress', limit: 10);
```

### Trait: HasEmbedding

Add embedding capabilities to models:

```php
use App\Traits\HasEmbedding;

class MiniCourse extends Model
{
    use HasEmbedding;

    public function getEmbeddingContent(): string
    {
        return $this->title . "\n" . $this->description;
    }
}
```

### Embedding Service

Supports multiple providers (OpenAI, Anthropic):

```php
$service = app(EmbeddingService::class);

// Generate embedding
$embedding = $service->generate($text);

// Batch generation
$embeddings = $service->generateBatch($texts);
```

---

## Resource Library

The Resource Hub provides curated external resources for educators.

### Features

- **Categorization**: Topics, level levels, resource types
- **Search**: Full-text and semantic search
- **Filtering**: By category, level, type, favorites
- **Bookmarking**: Personal resource collections
- **AI Recommendations**: "Similar resources" based on embeddings

### Views

- **Grid View**: Visual cards with thumbnails
- **List View**: Compact list with details
- **Sidebar Navigation**: Category tree and filters

---

## Artisan Commands

### Moderation Commands

```bash
# Generate demo moderation data
php artisan moderation:demo --count=15

# Check SLA status and send notifications
php artisan moderation:check-sla

# Send daily moderation summary
php artisan moderation:daily-summary
```

### Search Commands

```bash
# Configure Meilisearch indexes
php artisan search:configure

# Reindex all searchable content
php artisan search:reindex

# Reindex specific model
php artisan search:reindex --model=MiniCourse
```

### Embedding Commands

```bash
# Backfill embeddings for existing content
php artisan embeddings:backfill --model=MiniCourse

# Show embedding statistics
php artisan embeddings:stats
```

---

## API Documentation

### Moderation Endpoints

```
GET    /admin/moderation                    # Queue view
GET    /admin/moderation/task-flow          # Task flow interface
GET    /admin/moderation/dashboard          # Analytics dashboard
GET    /admin/moderation/{result}/edit      # Edit moderation result
```

### Livewire Components

| Component | Description |
|-----------|-------------|
| `ModerationQueue` | Main queue with filtering and bulk actions |
| `ModerationTaskFlow` | Guided review workflow |
| `ModerationDashboard` | Team analytics |
| `ModerationEdit` | Content editing interface |

---

## Environment Variables

### AI & Moderation

```env
ANTHROPIC_API_KEY=sk-ant-...
MODERATION_AUTO_APPROVE_THRESHOLD=0.85
MODERATION_DEFAULT_SLA_HOURS=48
```

### Search

```env
MEILISEARCH_HOST=http://localhost:7700
MEILISEARCH_KEY=your-master-key
OPENAI_API_KEY=sk-...  # For embeddings
```

### Queue

```env
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
```

---

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

---

## License

Proprietary - Fulcrum Organization Inc.
