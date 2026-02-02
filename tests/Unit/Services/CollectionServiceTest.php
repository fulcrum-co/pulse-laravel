<?php

namespace Tests\Unit\Services;

use App\Models\Collection;
use App\Models\CollectionEntry;
use App\Models\MiniCourse;
use App\Models\Resource;
use App\Models\User;
use App\Models\Organization;
use App\Services\CollectionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CollectionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CollectionService $service;
    protected User $user;
    protected Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(CollectionService::class);
        $this->organization = Organization::factory()->create();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_can_create_a_collection(): void
    {
        $data = [
            'name' => 'My Learning Collection',
            'description' => 'A collection of learning resources',
            'type' => 'curated',
            'is_public' => false,
        ];

        $collection = $this->service->create($data, $this->user, $this->organization);

        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertEquals('My Learning Collection', $collection->name);
        $this->assertEquals($this->user->id, $collection->created_by);
        $this->assertEquals($this->organization->id, $collection->org_id);
    }

    /** @test */
    public function it_can_update_a_collection(): void
    {
        $collection = Collection::factory()
            ->forOrganization($this->organization)
            ->createdBy($this->user)
            ->create();

        $data = [
            'name' => 'Updated Collection Name',
            'description' => 'Updated description',
        ];

        $updated = $this->service->update($collection, $data);

        $this->assertEquals('Updated Collection Name', $updated->name);
        $this->assertEquals('Updated description', $updated->description);
    }

    /** @test */
    public function it_can_delete_a_collection(): void
    {
        $collection = Collection::factory()
            ->forOrganization($this->organization)
            ->createdBy($this->user)
            ->create();

        $result = $this->service->delete($collection);

        $this->assertTrue($result);
        $this->assertSoftDeleted('collections', ['id' => $collection->id]);
    }

    /** @test */
    public function it_can_add_course_to_collection(): void
    {
        $collection = Collection::factory()
            ->forOrganization($this->organization)
            ->curated()
            ->create();

        $course = MiniCourse::factory()
            ->forOrganization($this->organization)
            ->published()
            ->create();

        $entry = $this->service->addEntry($collection, $course);

        $this->assertInstanceOf(CollectionEntry::class, $entry);
        $this->assertEquals($collection->id, $entry->collection_id);
        $this->assertEquals(MiniCourse::class, $entry->entryable_type);
        $this->assertEquals($course->id, $entry->entryable_id);
    }

    /** @test */
    public function it_can_add_resource_to_collection(): void
    {
        $collection = Collection::factory()
            ->forOrganization($this->organization)
            ->curated()
            ->create();

        $resource = Resource::factory()
            ->forOrganization($this->organization)
            ->published()
            ->create();

        $entry = $this->service->addEntry($collection, $resource);

        $this->assertEquals(Resource::class, $entry->entryable_type);
        $this->assertEquals($resource->id, $entry->entryable_id);
    }

    /** @test */
    public function it_prevents_duplicate_entries(): void
    {
        $collection = Collection::factory()
            ->forOrganization($this->organization)
            ->curated()
            ->create();

        $course = MiniCourse::factory()
            ->forOrganization($this->organization)
            ->published()
            ->create();

        // Add first time
        $this->service->addEntry($collection, $course);

        // Try to add again
        $this->expectException(\App\Exceptions\DuplicateEntryException::class);
        $this->service->addEntry($collection, $course);
    }

    /** @test */
    public function it_can_remove_entry_from_collection(): void
    {
        $collection = Collection::factory()
            ->forOrganization($this->organization)
            ->curated()
            ->create();

        $course = MiniCourse::factory()
            ->forOrganization($this->organization)
            ->published()
            ->create();

        $entry = $this->service->addEntry($collection, $course);

        $result = $this->service->removeEntry($entry);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('collection_entries', ['id' => $entry->id]);
    }

    /** @test */
    public function it_can_reorder_collection_entries(): void
    {
        $collection = Collection::factory()
            ->forOrganization($this->organization)
            ->curated()
            ->create();

        $courses = MiniCourse::factory()
            ->forOrganization($this->organization)
            ->published()
            ->count(3)
            ->create();

        $entries = $courses->map(function ($course) use ($collection) {
            return $this->service->addEntry($collection, $course);
        });

        // Reorder: move the third item to first position
        $newOrder = [
            $entries[2]->id,
            $entries[0]->id,
            $entries[1]->id,
        ];

        $this->service->reorderEntries($collection, $newOrder);

        $entries[0]->refresh();
        $entries[1]->refresh();
        $entries[2]->refresh();

        $this->assertEquals(1, $entries[2]->sort_order);
        $this->assertEquals(2, $entries[0]->sort_order);
        $this->assertEquals(3, $entries[1]->sort_order);
    }

    /** @test */
    public function it_can_get_collections_for_user(): void
    {
        // User's own collections
        Collection::factory()
            ->forOrganization($this->organization)
            ->createdBy($this->user)
            ->count(3)
            ->create();

        // Another user's private collection
        $otherUser = User::factory()->create();
        Collection::factory()
            ->forOrganization($this->organization)
            ->createdBy($otherUser)
            ->create(['is_public' => false]);

        // Another user's public collection
        Collection::factory()
            ->forOrganization($this->organization)
            ->createdBy($otherUser)
            ->public()
            ->create();

        $collections = $this->service->getForUser($this->user, $this->organization);

        // Should get own collections (3) + public collections (1)
        $this->assertCount(4, $collections);
    }

    /** @test */
    public function smart_collection_auto_populates_based_on_rules(): void
    {
        $collection = Collection::factory()
            ->forOrganization($this->organization)
            ->smart([
                'difficulty_level' => 'beginner',
                'target_grades' => ['6', '7', '8'],
            ])
            ->create();

        // Create matching courses
        $matchingCourses = MiniCourse::factory()
            ->forOrganization($this->organization)
            ->published()
            ->beginner()
            ->count(3)
            ->create(['target_grades' => ['6', '7']]);

        // Create non-matching course
        MiniCourse::factory()
            ->forOrganization($this->organization)
            ->published()
            ->advanced()
            ->create(['target_grades' => ['11', '12']]);

        $this->service->refreshSmartCollection($collection);

        $entries = $collection->entries()->get();
        $this->assertCount(3, $entries);
    }

    /** @test */
    public function it_can_calculate_collection_stats(): void
    {
        $collection = Collection::factory()
            ->forOrganization($this->organization)
            ->curated()
            ->create();

        $courses = MiniCourse::factory()
            ->forOrganization($this->organization)
            ->published()
            ->count(5)
            ->create(['estimated_duration_minutes' => 30]);

        foreach ($courses as $course) {
            $this->service->addEntry($collection, $course);
        }

        $stats = $this->service->getStats($collection);

        $this->assertEquals(5, $stats['total_items']);
        $this->assertEquals(150, $stats['total_duration_minutes']);
        $this->assertEquals(5, $stats['courses_count']);
        $this->assertEquals(0, $stats['resources_count']);
    }

    /** @test */
    public function it_can_duplicate_collection(): void
    {
        $original = Collection::factory()
            ->forOrganization($this->organization)
            ->createdBy($this->user)
            ->curated()
            ->create(['name' => 'Original Collection']);

        $course = MiniCourse::factory()
            ->forOrganization($this->organization)
            ->published()
            ->create();

        $this->service->addEntry($original, $course);

        $duplicate = $this->service->duplicate($original, $this->user);

        $this->assertNotEquals($original->id, $duplicate->id);
        $this->assertEquals('Original Collection (Copy)', $duplicate->name);
        $this->assertEquals(1, $duplicate->entries()->count());
    }
}
