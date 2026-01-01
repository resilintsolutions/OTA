<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class PromoWebAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_promo_index(): void
    {
    Role::findOrCreate('admin', 'web');

        /** @var User $admin */
        $admin = User::factory()->create();
    $admin->assignRole('admin');

        $response = $this->actingAs($admin)->get('/admin/promo');

        if ($response->getStatusCode() === 403) {
            // Helps identify whether this is role middleware vs policy denial.
            fwrite(STDERR, "\n403 body:\n".$response->getContent()."\n");
        }

        $response->assertOk();
    }

    public function test_admin_can_post_recompute(): void
    {
    Role::findOrCreate('admin', 'web');

        /** @var User $admin */
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        // Missing hotel_id should redirect back with validation-like error, but must not be 403.
        $this->actingAs($admin)
            ->post('/admin/promo-engine/recompute', [])
            ->assertSessionHas('error')
            ->assertStatus(302);
    }
}
