<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_demo_seeder_builds_a_usable_dataset(): void
    {
        $this->seed();

        $this->assertDatabaseCount('districts', 31);
        $this->assertDatabaseHas('users', ['email' => 'kabupaten@wadulin.test']);
        $this->assertDatabaseCount('qr_locations', 1);
        $this->assertDatabaseCount('announcements', 1);
        $this->get('/')->assertOk()->assertSee('WADULIN resmi diluncurkan');
    }
}
