<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCompanyUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_company_users(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->for(Tenant::factory())->create(['name' => 'مستخدم شركة']);

        $this->actingAs($admin)
            ->get(route('admin.company-users'))
            ->assertOk()
            ->assertSee('مستخدم شركة');
    }

    public function test_company_user_cannot_access_company_users(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('admin.company-users'))->assertForbidden();
    }

    public function test_admin_can_update_a_company_user(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->for(Tenant::factory())->create(['name' => 'قديم', 'is_owner' => false]);

        $this->actingAs($admin)->put(route('admin.company-users.update', $user), [
            'name' => 'محدّث',
            'email' => $user->email,
            'is_owner' => '1',
        ])->assertRedirect(route('admin.company-users'));

        $user->refresh();
        $this->assertSame('محدّث', $user->name);
        $this->assertTrue($user->is_owner);
    }

    public function test_admin_cannot_update_with_a_duplicate_email(): void
    {
        $admin = User::factory()->admin()->create();
        $other = User::factory()->for(Tenant::factory())->create();
        $user = User::factory()->for(Tenant::factory())->create();

        $this->actingAs($admin)->put(route('admin.company-users.update', $user), [
            'name' => 'اسم',
            'email' => $other->email,
        ])->assertSessionHasErrors('email');
    }

    public function test_admin_cannot_edit_a_platform_admin(): void
    {
        $admin = User::factory()->admin()->create();
        $target = User::factory()->admin()->create();

        $this->actingAs($admin)->put(route('admin.company-users.update', $target), [
            'name' => 'اختراق',
            'email' => $target->email,
        ])->assertNotFound();
    }

    public function test_admin_can_delete_a_company_user(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->for(Tenant::factory())->create();

        $this->actingAs($admin)
            ->delete(route('admin.company-users.destroy', $user))
            ->assertRedirect(route('admin.company-users'));

        $this->assertModelMissing($user);
    }

    public function test_admin_cannot_delete_a_platform_admin(): void
    {
        $admin = User::factory()->admin()->create();
        $target = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->delete(route('admin.company-users.destroy', $target))
            ->assertNotFound();

        $this->assertModelExists($target);
    }
}
