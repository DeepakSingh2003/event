<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_categories_api_returns_lucide_icon_key(): void
    {
        Category::factory()->create([
            'name' => 'Movies',
            'slug' => 'movies',
            'icon' => 'Ticket',
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/categories');

        $response
            ->assertOk()
            ->assertJsonFragment([
                'name' => 'Movies',
                'icon' => 'Ticket',
                'slug' => 'movies',
            ]);
    }
}