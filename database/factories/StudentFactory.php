<?php

namespace Database\Factories;

use App\Models\Student;
use App\Models\Organization;
use App\Models\Classroom;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Student>
 */
class StudentFactory extends Factory
{
    protected $model = Student::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $grades = ['K', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'];

        return [
            'org_id' => Organization::factory(),
            'student_id' => $this->faker->unique()->numerify('STU-######'),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            'grade_level' => $this->faker->randomElement($grades),
            'date_of_birth' => $this->faker->dateTimeBetween('-18 years', '-5 years'),
            'is_active' => true,
            'metadata' => [],
        ];
    }

    /**
     * Indicate that the student is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Assign to a specific organization.
     */
    public function forOrganization(Organization $organization): static
    {
        return $this->state(fn (array $attributes) => [
            'org_id' => $organization->id,
        ]);
    }

    /**
     * Assign to a specific classroom.
     */
    public function inClassroom(Classroom $classroom): static
    {
        return $this->state(fn (array $attributes) => [
            'classroom_id' => $classroom->id,
            'org_id' => $classroom->org_id,
        ]);
    }

    /**
     * Set a specific grade level.
     */
    public function grade(string $grade): static
    {
        return $this->state(fn (array $attributes) => [
            'grade_level' => $grade,
        ]);
    }

    /**
     * Make the student elementary age (K-5).
     */
    public function elementary(): static
    {
        return $this->state(fn (array $attributes) => [
            'grade_level' => $this->faker->randomElement(['K', '1', '2', '3', '4', '5']),
            'date_of_birth' => $this->faker->dateTimeBetween('-11 years', '-5 years'),
        ]);
    }

    /**
     * Make the student middle school age (6-8).
     */
    public function middleSchool(): static
    {
        return $this->state(fn (array $attributes) => [
            'grade_level' => $this->faker->randomElement(['6', '7', '8']),
            'date_of_birth' => $this->faker->dateTimeBetween('-14 years', '-11 years'),
        ]);
    }

    /**
     * Make the student high school age (9-12).
     */
    public function highSchool(): static
    {
        return $this->state(fn (array $attributes) => [
            'grade_level' => $this->faker->randomElement(['9', '10', '11', '12']),
            'date_of_birth' => $this->faker->dateTimeBetween('-18 years', '-14 years'),
        ]);
    }
}
