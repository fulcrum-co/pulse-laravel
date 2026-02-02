# Contributing to Pulse

Thank you for your interest in contributing to Pulse! This document provides guidelines and instructions for contributing to the project.

## Table of Contents

- [Code of Conduct](#code-of-conduct)
- [Development Setup](#development-setup)
- [Branch Naming Convention](#branch-naming-convention)
- [Commit Message Format](#commit-message-format)
- [Pull Request Process](#pull-request-process)
- [Code Style](#code-style)
- [Testing Requirements](#testing-requirements)
- [Code Review Checklist](#code-review-checklist)

## Code of Conduct

Please be respectful and constructive in all interactions. We're all working toward the same goal of building great educational software.

## Development Setup

### Prerequisites

- PHP 8.2+
- Composer 2.x
- Node.js 18+ and npm
- PostgreSQL 15+ with pgvector extension
- Redis 7+
- Docker (optional, for containerized development)

### Local Setup

1. Clone the repository:
   ```bash
   git clone https://github.com/fulcrum-co/pulse-laravel.git
   cd pulse-laravel
   ```

2. Install dependencies:
   ```bash
   composer install
   npm install
   ```

3. Configure environment:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. Set up the database:
   ```bash
   # Update .env with your database credentials
   php artisan migrate
   php artisan db:seed
   ```

5. Build assets:
   ```bash
   npm run build
   ```

6. Start the development server:
   ```bash
   composer dev
   ```

### Using Docker

```bash
docker-compose up -d
docker-compose exec app composer install
docker-compose exec app php artisan migrate --seed
```

## Branch Naming Convention

Use the following prefixes for branch names:

| Prefix | Description | Example |
|--------|-------------|---------|
| `feature/` | New features | `feature/add-course-versioning` |
| `fix/` | Bug fixes | `fix/enrollment-count-error` |
| `refactor/` | Code refactoring | `refactor/collection-service` |
| `docs/` | Documentation updates | `docs/api-endpoints` |
| `test/` | Adding or updating tests | `test/moderation-workflow` |
| `chore/` | Maintenance tasks | `chore/update-dependencies` |

Branch names should be lowercase, use hyphens for spaces, and be descriptive but concise.

## Commit Message Format

We follow the [Conventional Commits](https://www.conventionalcommits.org/) specification:

```
<type>(<scope>): <description>

[optional body]

[optional footer(s)]
```

### Types

- `feat`: A new feature
- `fix`: A bug fix
- `docs`: Documentation changes
- `style`: Code style changes (formatting, semicolons, etc.)
- `refactor`: Code changes that neither fix bugs nor add features
- `test`: Adding or updating tests
- `chore`: Maintenance tasks (dependencies, build, etc.)

### Examples

```bash
feat(courses): add course versioning support

fix(moderation): correct SLA calculation for urgent items

docs(api): document course endpoints

refactor(services): extract collection repository

test(enrollment): add feature tests for enrollment flow
```

### Co-authoring

When pairing or using AI assistance, include co-author footer:

```
feat(courses): add AI course generation

Co-Authored-By: Claude <noreply@anthropic.com>
```

## Pull Request Process

### Before Submitting

1. **Ensure tests pass:**
   ```bash
   php artisan test
   ```

2. **Run code formatting:**
   ```bash
   ./vendor/bin/pint
   ```

3. **Run static analysis:**
   ```bash
   ./vendor/bin/phpstan analyse
   ```

4. **Update documentation** if you've changed APIs or added features.

### PR Template

When creating a PR, include:

```markdown
## Summary
Brief description of changes.

## Changes Made
- Change 1
- Change 2

## Testing
Describe how you tested these changes.

## Screenshots (if applicable)
Add screenshots for UI changes.

## Checklist
- [ ] Tests pass locally
- [ ] Code follows project style guidelines
- [ ] Documentation updated (if applicable)
- [ ] No breaking changes (or documented if unavoidable)
```

### Review Process

1. Create PR against `main` branch
2. Request review from at least one team member
3. Address all review comments
4. Squash and merge once approved

## Code Style

We use [Laravel Pint](https://laravel.com/docs/pint) for code formatting with a custom configuration.

### Run Formatter

```bash
# Check for issues
./vendor/bin/pint --test

# Fix issues
./vendor/bin/pint
```

### Key Style Rules

- Use strict types: `declare(strict_types=1);`
- Use type hints for parameters and return types
- Use named arguments for clarity when calling methods with multiple parameters
- Prefer early returns over nested conditionals
- Keep methods under 20 lines when possible
- Use descriptive variable and method names

### Example

```php
<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\CollectionServiceInterface;
use App\Models\Collection;
use App\Models\User;

final class CollectionService implements CollectionServiceInterface
{
    public function create(array $data, User $user): Collection
    {
        // Validate early
        if (empty($data['name'])) {
            throw new \InvalidArgumentException('Collection name is required');
        }

        // Create and return
        return Collection::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'created_by' => $user->id,
        ]);
    }
}
```

## Testing Requirements

### Minimum Coverage

- All new features must have feature tests
- All new services must have unit tests
- Critical paths must maintain 80%+ coverage

### Running Tests

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/MiniCourse/MiniCourseCrudTest.php

# Run with coverage
php artisan test --coverage --min=80

# Run in parallel
php artisan test --parallel
```

### Test Organization

```
tests/
├── Feature/           # Integration/feature tests
│   ├── MiniCourse/
│   ├── Moderation/
│   └── ...
├── Unit/              # Unit tests
│   ├── Services/
│   ├── Models/
│   └── ...
└── TestCase.php       # Base test case
```

### Writing Tests

```php
<?php

namespace Tests\Feature\MiniCourse;

use App\Models\MiniCourse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MiniCourseCrudTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_create_a_course(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('courses.store'), [
                'title' => 'Test Course',
                'description' => 'Test description',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('mini_courses', [
            'title' => 'Test Course',
        ]);
    }
}
```

## Code Review Checklist

When reviewing PRs, check for:

### Functionality
- [ ] Does the code do what it's supposed to do?
- [ ] Are edge cases handled?
- [ ] Is error handling appropriate?

### Code Quality
- [ ] Is the code readable and well-organized?
- [ ] Are there any code smells (god classes, tight coupling, etc.)?
- [ ] Is duplication minimized?

### Testing
- [ ] Are there sufficient tests?
- [ ] Do tests cover edge cases?
- [ ] Are tests readable and maintainable?

### Security
- [ ] Is user input validated?
- [ ] Are authorization checks in place?
- [ ] Are sensitive data properly handled?

### Performance
- [ ] Are there any N+1 query issues?
- [ ] Are expensive operations cached?
- [ ] Are database queries optimized?

### Documentation
- [ ] Are public methods documented?
- [ ] Are complex algorithms explained?
- [ ] Is the README updated if needed?

---

Questions? Reach out to the team lead or open a discussion on GitHub.
