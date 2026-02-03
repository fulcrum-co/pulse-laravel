<?php

namespace Tests\Feature\MiniCourse;

use App\Models\MiniCourse;
use App\Models\User;
use App\Models\Organization;
use App\Models\Provider;
use App\Models\Program;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MiniCourseCrudTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $contentCreator;
    protected Organization $organization;

    protected function setUp(): void
    {
        direct_supervisor::setUp();

        $this->organization = Organization::factory()->create();
        $this->admin = User::factory()->create([
            'role' => 'admin',
        ]);
        $this->contentCreator = User::factory()->create([
            'role' => 'content_creator',
        ]);
    }

    /** @test */
    public function user_can_view_list_of_courses(): void
    {
        MiniCourse::factory()
            ->forOrganization($this->organization)
            ->published()
            ->count(5)
            ->create();

        $this->actingAs($this->contentCreator)
            ->get(route('courses.index'))
            ->assertSuccessful()
            ->assertViewHas('courses', function ($courses) {
                return $courses->count() === 5;
            });
    }

    /** @test */
    public function user_can_create_a_new_course(): void
    {
        $courseData = [
            'title' => 'Introduction to Mathematics',
            'description' => 'A comprehensive introduction to basic math concepts.',
            'short_description' => 'Learn math basics',
            'difficulty_level' => 'beginner',
            'course_type' => 'standard',
            'target_levels' => ['6', '7', '8'],
            'objectives' => [
                'Understand basic arithmetic',
                'Learn fractions and decimals',
            ],
            'estimated_duration_minutes' => 45,
        ];

        $this->actingAs($this->contentCreator)
            ->post(route('courses.store'), $courseData)
            ->assertRedirect();

        $this->assertDatabaseHas('mini_courses', [
            'title' => 'Introduction to Mathematics',
            'status' => 'draft',
            'created_by' => $this->contentCreator->id,
        ]);
    }

    /** @test */
    public function user_can_view_a_single_course(): void
    {
        $course = MiniCourse::factory()
            ->forOrganization($this->organization)
            ->published()
            ->create();

        $this->actingAs($this->contentCreator)
            ->get(route('courses.show', $course))
            ->assertSuccessful()
            ->assertViewHas('course', function ($viewCourse) use ($course) {
                return $viewCourse->id === $course->id;
            });
    }

    /** @test */
    public function user_can_update_their_own_course(): void
    {
        $course = MiniCourse::factory()
            ->forOrganization($this->organization)
            ->draft()
            ->create(['created_by' => $this->contentCreator->id]);

        $updatedData = [
            'title' => 'Updated Course Title',
            'description' => 'Updated description',
            'difficulty_level' => 'intermediate',
        ];

        $this->actingAs($this->contentCreator)
            ->put(route('courses.update', $course), $updatedData)
            ->assertRedirect();

        $course->refresh();

        $this->assertEquals('Updated Course Title', $course->title);
        $this->assertEquals('intermediate', $course->difficulty_level);
    }

    /** @test */
    public function user_cannot_update_another_users_course(): void
    {
        $anotherUser = User::factory()->create(['role' => 'content_creator']);

        $course = MiniCourse::factory()
            ->forOrganization($this->organization)
            ->draft()
            ->create(['created_by' => $anotherUser->id]);

        $this->actingAs($this->contentCreator)
            ->put(route('courses.update', $course), ['title' => 'Hacked Title'])
            ->assertForbidden();
    }

    /** @test */
    public function admin_can_update_any_course(): void
    {
        $course = MiniCourse::factory()
            ->forOrganization($this->organization)
            ->draft()
            ->create(['created_by' => $this->contentCreator->id]);

        $this->actingAs($this->admin)
            ->put(route('courses.update', $course), ['title' => 'Admin Updated Title'])
            ->assertRedirect();

        $course->refresh();
        $this->assertEquals('Admin Updated Title', $course->title);
    }

    /** @test */
    public function user_can_delete_their_own_draft_course(): void
    {
        $course = MiniCourse::factory()
            ->forOrganization($this->organization)
            ->draft()
            ->create(['created_by' => $this->contentCreator->id]);

        $this->actingAs($this->contentCreator)
            ->delete(route('courses.destroy', $course))
            ->assertRedirect();

        $this->assertSoftDeleted('mini_courses', ['id' => $course->id]);
    }

    /** @test */
    public function user_cannot_delete_published_course(): void
    {
        $course = MiniCourse::factory()
            ->forOrganization($this->organization)
            ->published()
            ->create(['created_by' => $this->contentCreator->id]);

        $this->actingAs($this->contentCreator)
            ->delete(route('courses.destroy', $course))
            ->assertForbidden();

        $this->assertDatabaseHas('mini_courses', ['id' => $course->id]);
    }

    /** @test */
    public function admin_can_archive_published_course(): void
    {
        $course = MiniCourse::factory()
            ->forOrganization($this->organization)
            ->published()
            ->create();

        $this->actingAs($this->admin)
            ->post(route('courses.archive', $course))
            ->assertRedirect();

        $course->refresh();
        $this->assertEquals('archived', $course->status);
    }

    /** @test */
    public function courses_can_be_filtered_by_status(): void
    {
        MiniCourse::factory()
            ->forOrganization($this->organization)
            ->draft()
            ->count(3)
            ->create();

        MiniCourse::factory()
            ->forOrganization($this->organization)
            ->published()
            ->count(5)
            ->create();

        $this->actingAs($this->contentCreator)
            ->get(route('courses.index', ['status' => 'published']))
            ->assertSuccessful()
            ->assertViewHas('courses', function ($courses) {
                return $courses->count() === 5 &&
                       $courses->every(fn ($c) => $c->status === 'published');
            });
    }

    /** @test */
    public function courses_can_be_filtered_by_difficulty(): void
    {
        MiniCourse::factory()
            ->forOrganization($this->organization)
            ->beginner()
            ->published()
            ->count(2)
            ->create();

        MiniCourse::factory()
            ->forOrganization($this->organization)
            ->advanced()
            ->published()
            ->count(3)
            ->create();

        $this->actingAs($this->contentCreator)
            ->get(route('courses.index', ['difficulty' => 'advanced']))
            ->assertSuccessful()
            ->assertViewHas('courses', function ($courses) {
                return $courses->count() === 3;
            });
    }

    /** @test */
    public function courses_can_be_searched_by_title(): void
    {
        MiniCourse::factory()
            ->forOrganization($this->organization)
            ->published()
            ->create(['title' => 'Introduction to Algebra']);

        MiniCourse::factory()
            ->forOrganization($this->organization)
            ->published()
            ->create(['title' => 'Advanced Calculus']);

        MiniCourse::factory()
            ->forOrganization($this->organization)
            ->published()
            ->create(['title' => 'Biology 101']);

        $this->actingAs($this->contentCreator)
            ->get(route('courses.index', ['search' => 'algebra']))
            ->assertSuccessful()
            ->assertViewHas('courses', function ($courses) {
                return $courses->count() === 1 &&
                       $courses->first()->title === 'Introduction to Algebra';
            });
    }

    /** @test */
    public function course_can_be_duplicated(): void
    {
        $original = MiniCourse::factory()
            ->forOrganization($this->organization)
            ->published()
            ->create([
                'title' => 'Original Course',
                'created_by' => $this->contentCreator->id,
            ]);

        $this->actingAs($this->contentCreator)
            ->post(route('courses.duplicate', $original))
            ->assertRedirect();

        $this->assertDatabaseHas('mini_courses', [
            'title' => 'Original Course (Copy)',
            'status' => 'draft',
            'created_by' => $this->contentCreator->id,
        ]);
    }

    /** @test */
    public function validation_errors_are_returned_for_invalid_data(): void
    {
        $this->actingAs($this->contentCreator)
            ->post(route('courses.store'), [
                // Missing required 'title'
                'description' => 'Some description',
            ])
            ->assertSessionHasErrors('title');
    }

    /** @test */
    public function course_can_be_assigned_to_provider_and_program(): void
    {
        $provider = Provider::factory()
            ->forOrganization($this->organization)
            ->create();

        $program = Program::factory()
            ->forProvider($provider)
            ->create();

        $courseData = [
            'title' => 'Provider Course',
            'description' => 'A course from a provider',
            'provider_id' => $provider->id,
            'program_id' => $program->id,
            'difficulty_level' => 'beginner',
        ];

        $this->actingAs($this->contentCreator)
            ->post(route('courses.store'), $courseData)
            ->assertRedirect();

        $this->assertDatabaseHas('mini_courses', [
            'title' => 'Provider Course',
            'provider_id' => $provider->id,
            'program_id' => $program->id,
        ]);
    }
}
