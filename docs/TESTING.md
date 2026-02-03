# Testing Guide

This document provides comprehensive guidance on testing the Pulse application, including setup, running tests, writing tests, and best practices.

## Table of Contents

- [Test Environment Setup](#test-environment-setup)
- [Running Tests](#running-tests)
- [Test Structure](#test-structure)
- [Writing Tests](#writing-tests)
- [Factories](#factories)
- [Mocking External Services](#mocking-external-services)
- [Database Testing](#database-testing)
- [Coverage Requirements](#coverage-requirements)
- [CI/CD Integration](#cicd-integration)

## Test Environment Setup

### Database Configuration

Tests use an in-memory SQLite database by default, but PostgreSQL is recommended for full compatibility:

```env
# .env.testing
APP_ENV=testing
APP_KEY=base64:test-key-here

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_DATABASE=pulse_testing
DB_USERNAME=pulse_test
DB_PASSWORD=test_password

CACHE_DRIVER=array
QUEUE_CONNECTION=sync
SESSION_DRIVER=array
```

### Create Test Database

```bash
createdb pulse_testing
psql pulse_testing -c "CREATE EXTENSION IF NOT EXISTS vector;"
```

### PHPUnit Configuration

The `phpunit.xml` file is pre-configured:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true">
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="Feature">
            <directory>tests/Feature</directory>
        </testsuite>
    </testsuites>
    <source>
        <include>
            <directory>app</directory>
        </include>
    </source>
    <php>
        <env name="APP_ENV" value="testing"/>
        <env name="BCRYPT_ROUNDS" value="4"/>
        <env name="CACHE_DRIVER" value="array"/>
        <env name="DB_CONNECTION" value="pgsql"/>
        <env name="DB_DATABASE" value="pulse_testing"/>
        <env name="MAIL_MAILER" value="array"/>
        <env name="QUEUE_CONNECTION" value="sync"/>
        <env name="SESSION_DRIVER" value="array"/>
    </php>
</phpunit>
```

## Running Tests

### Basic Commands

```bash
# Run all tests
php artisan test

# Run with verbose output
php artisan test -v

# Run specific test file
php artisan test tests/Feature/MiniCourse/MiniCourseCrudTest.php

# Run specific test method
php artisan test --filter test_user_can_create_a_course

# Run tests matching a pattern
php artisan test --filter MiniCourse
```

### Advanced Options

```bash
# Run in parallel (faster)
php artisan test --parallel

# Run with coverage report
php artisan test --coverage

# Enforce minimum coverage
php artisan test --coverage --min=80

# Stop on first failure
php artisan test --stop-on-failure

# Run specific test suite
php artisan test --testsuite=Feature
```

### Using PHPUnit Directly

```bash
# Run with PHPUnit
./vendor/bin/phpunit

# Generate HTML coverage report
./vendor/bin/phpunit --coverage-html coverage/

# Generate Clover XML (for CI)
./vendor/bin/phpunit --coverage-clover coverage.xml
```

## Test Structure

### Directory Organization

```
tests/
├── Feature/                    # Feature/integration tests
│   ├── Auth/
│   │   └── AuthenticationTest.php
│   ├── MiniCourse/
│   │   ├── MiniCourseCrudTest.php
│   │   ├── CourseVersioningTest.php
│   │   └── CourseEnrollmentTest.php
│   ├── Moderation/
│   │   ├── ModerationQueueTest.php
│   │   └── ModerationWorkflowTest.php
│   ├── Collection/
│   │   └── CollectionCrudTest.php
│   └── Api/
│       └── CourseApiTest.php
├── Unit/                       # Unit tests
│   ├── Models/
│   │   └── MiniCourseTest.php
│   ├── Services/
│   │   ├── CollectionServiceTest.php
│   │   └── ModerationServiceTest.php
│   └── Helpers/
│       └── DateHelperTest.php
├── TestCase.php               # Base test case
└── CreatesApplication.php     # Application bootstrap
```

### Base Test Case

```php
<?php

namespace Tests;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected Organization $organization;
    protected User $user;

    protected function setUp(): void
    {
        direct_supervisor::setUp();

        // Create default organization and user for tests
        $this->organization = Organization::factory()->create();
        $this->user = User::factory()->create();
    }

    /**
     * Create a user with a specific role.
     */
    protected function createUserWithRole(string $role): User
    {
        return User::factory()->create(['role' => $role]);
    }

    /**
     * Create an admin user.
     */
    protected function createAdmin(): User
    {
        return $this->createUserWithRole('admin');
    }

    /**
     * Create a content creator user.
     */
    protected function createContentCreator(): User
    {
        return $this->createUserWithRole('content_creator');
    }

    /**
     * Create a moderator user.
     */
    protected function createModerator(): User
    {
        return $this->createUserWithRole('moderator');
    }
}
```

## Writing Tests

### Feature Test Example

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
        // Arrange
        $user = $this->createContentCreator();
        $courseData = [
            'title' => 'Introduction to Mathematics',
            'description' => 'A comprehensive introduction.',
            'difficulty_level' => 'beginner',
        ];

        // Act
        $response = $this->actingAs($user)
            ->post(route('courses.store'), $courseData);

        // Assert
        $response->assertRedirect();
        $this->assertDatabaseHas('mini_courses', [
            'title' => 'Introduction to Mathematics',
            'created_by' => $user->id,
        ]);
    }

    /** @test */
    public function guest_cannot_create_course(): void
    {
        $response = $this->post(route('courses.store'), [
            'title' => 'Test Course',
        ]);

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function validation_errors_are_returned_for_invalid_data(): void
    {
        $user = $this->createContentCreator();

        $response = $this->actingAs($user)
            ->post(route('courses.store'), [
                // Missing required 'title'
                'description' => 'Some description',
            ]);

        $response->assertSessionHasErrors('title');
    }
}
```

### Unit Test Example

```php
<?php

namespace Tests\Unit\Services;

use App\Models\Collection;
use App\Models\MiniCourse;
use App\Models\User;
use App\Services\CollectionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CollectionServiceTest extends TestCase
{
    use RefreshDatabase;

    private CollectionService $service;

    protected function setUp(): void
    {
        direct_supervisor::setUp();
        $this->service = app(CollectionService::class);
    }

    /** @test */
    public function it_can_create_a_collection(): void
    {
        $data = [
            'name' => 'My Collection',
            'description' => 'Test description',
        ];

        $collection = $this->service->create(
            $data,
            $this->user,
            $this->organization
        );

        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertEquals('My Collection', $collection->name);
    }

    /** @test */
    public function it_prevents_duplicate_entries(): void
    {
        $collection = Collection::factory()->create();
        $course = MiniCourse::factory()->create();

        // First add should succeed
        $this->service->addEntry($collection, $course);

        // Second add should throw exception
        $this->expectException(\App\Exceptions\DuplicateEntryException::class);
        $this->service->addEntry($collection, $course);
    }
}
```

### API Test Example

```php
<?php

namespace Tests\Feature\Api;

use App\Models\MiniCourse;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CourseApiTest extends TestCase
{
    /** @test */
    public function it_returns_paginated_courses(): void
    {
        Sanctum::actingAs($this->user);

        MiniCourse::factory()
            ->forOrganization($this->organization)
            ->published()
            ->count(25)
            ->create();

        $response = $this->getJson('/api/courses');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'title', 'description', 'status'],
                ],
                'meta' => ['current_page', 'total'],
            ])
            ->assertJsonCount(15, 'data'); // Default pagination
    }

    /** @test */
    public function it_requires_authentication(): void
    {
        $response = $this->getJson('/api/courses');

        $response->assertUnauthorized();
    }
}
```

## Factories

### Using Factories

```php
// Create single model
$course = MiniCourse::factory()->create();

// Create multiple models
$courses = MiniCourse::factory()->count(5)->create();

// Create with specific attributes
$course = MiniCourse::factory()->create([
    'title' => 'Custom Title',
    'status' => 'published',
]);

// Use factory states
$course = MiniCourse::factory()
    ->published()
    ->beginner()
    ->create();

// Create with relationships
$course = MiniCourse::factory()
    ->forOrganization($organization)
    ->has(MiniCourseStep::factory()->count(5), 'steps')
    ->create();
```

### Available Factory States

#### MiniCourseFactory
- `published()` - Published course
- `draft()` - Draft course
- `pendingReview()` - Pending review
- `archived()` - Archived course
- `beginner()`, `intermediate()`, `advanced()` - Difficulty levels
- `template()` - Course template
- `generated()` - AI-generated course

#### ModerationQueueItemFactory
- `pending()` - Pending review
- `inReview($reviewer)` - Currently being reviewed
- `completed()` - Review completed
- `escalated()` - Escalated item
- `urgent()`, `highPriority()` - Priority levels
- `overdue()` - Past SLA deadline

## Mocking External Services

### Mocking Claude AI Service

```php
<?php

use App\Services\ClaudeService;
use Mockery\MockInterface;

/** @test */
public function it_generates_course_content_using_ai(): void
{
    $this->mock(ClaudeService::class, function (MockInterface $mock) {
        $mock->shouldReceive('generateContent')
            ->once()
            ->with(\Mockery::type('string'))
            ->andReturn([
                'title' => 'Generated Title',
                'description' => 'Generated description',
                'steps' => [
                    ['title' => 'Step 1', 'content' => 'Content 1'],
                ],
            ]);
    });

    // Test code that uses ClaudeService...
}
```

### Mocking HTTP Requests

```php
use Illuminate\Support\Facades\Http;

/** @test */
public function it_fetches_external_resource(): void
{
    Http::fake([
        'api.example.com/*' => Http::response([
            'data' => ['key' => 'value'],
        ], 200),
    ]);

    // Test code that makes HTTP requests...

    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.example.com/resource';
    });
}
```

### Mocking Queue Jobs

```php
use Illuminate\Support\Facades\Queue;
use App\Jobs\ModerateContentJob;

/** @test */
public function it_dispatches_moderation_job(): void
{
    Queue::fake();

    // Trigger action that dispatches job...

    Queue::assertPushed(ModerateContentJob::class, function ($job) {
        return $job->content->id === $this->course->id;
    });
}
```

## Database Testing

### RefreshDatabase vs DatabaseTransactions

```php
// RefreshDatabase - Migrates fresh for each test (slower, cleaner)
use Illuminate\Foundation\Testing\RefreshDatabase;

// DatabaseTransactions - Wraps in transaction (faster, some limitations)
use Illuminate\Foundation\Testing\DatabaseTransactions;
```

### Database Assertions

```php
// Assert record exists
$this->assertDatabaseHas('mini_courses', [
    'title' => 'Expected Title',
    'status' => 'published',
]);

// Assert record doesn't exist
$this->assertDatabaseMissing('mini_courses', [
    'id' => $deletedId,
]);

// Assert soft deleted
$this->assertSoftDeleted('mini_courses', [
    'id' => $course->id,
]);

// Assert count
$this->assertDatabaseCount('mini_courses', 5);
```

## Coverage Requirements

### Minimum Coverage Targets

| Area | Minimum Coverage |
|------|-----------------|
| Critical Services | 90% |
| Models | 80% |
| Controllers | 75% |
| Overall | 80% |

### Critical Paths Requiring High Coverage

- Authentication and authorization
- Course creation and publishing
- Moderation workflow
- Payment processing (if applicable)
- Data import/export

### Generating Coverage Reports

```bash
# Console coverage
php artisan test --coverage

# HTML report
./vendor/bin/phpunit --coverage-html coverage/

# View in browser
open coverage/index.html
```

## CI/CD Integration

### GitHub Actions Example

```yaml
name: Tests

on: [push, pull_request]

jobs:
  tests:
    runs-on: ubuntu-latest

    services:
      postgres:
        image: postgres:16
        env:
          POSTGRES_DB: pulse_testing
          POSTGRES_USER: pulse_test
          POSTGRES_PASSWORD: password
        ports:
          - 5432:5432
        options: --health-cmd pg_isready

    steps:
      - uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          coverage: xdebug

      - name: Install dependencies
        run: composer install --prefer-dist --no-progress

      - name: Run tests
        env:
          DB_CONNECTION: pgsql
          DB_HOST: localhost
          DB_DATABASE: pulse_testing
          DB_USERNAME: pulse_test
          DB_PASSWORD: password
        run: php artisan test --coverage --min=80

      - name: Upload coverage
        uses: codecov/codecov-action@v3
```

---

For questions about testing, reach out to the team or check the Laravel testing documentation.
