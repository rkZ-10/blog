<?php

namespace Database\Factories;

use App\Enums\ProjectStatus;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = Str::title(fake()->unique()->words(2, true));

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'tagline' => fake()->sentence(),
            'summary' => fake()->paragraphs(2, true),
            'stack' => fake()->randomElements(['Laravel', 'Go', 'Python', 'Redis', 'React', 'PostgreSQL'], 3),
            'repo_url' => 'https://github.com/rkZ-10/'.Str::slug($name),
            'live_url' => null,
            'status' => ProjectStatus::Active,
            'featured' => false,
            'sort_order' => 0,
        ];
    }

    /**
     * Indicate that the project is shown on the home page.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'featured' => true,
        ]);
    }

    /**
     * Indicate that the project is no longer maintained.
     */
    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProjectStatus::Archived,
        ]);
    }
}
