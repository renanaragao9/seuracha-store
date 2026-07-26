<?php

namespace Tests\Unit\Models;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_role(): void
    {
        $role = Role::create([
            'name' => 'Admin',
            'description' => 'Acesso total ao sistema',
        ]);

        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
            'name' => 'Admin',
        ]);
    }

    public function test_can_read_role(): void
    {
        $role = Role::create([
            'name' => 'Admin',
            'description' => 'Acesso total ao sistema',
        ]);

        $found = Role::find($role->id);

        $this->assertNotNull($found);
        $this->assertSame('Admin', $found->name);
    }

    public function test_can_update_role(): void
    {
        $role = Role::create([
            'name' => 'Admin',
            'description' => 'Acesso total ao sistema',
        ]);

        $role->update(['description' => 'Acesso administrativo completo']);

        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
            'description' => 'Acesso administrativo completo',
        ]);
    }

    public function test_can_delete_role(): void
    {
        $role = Role::create([
            'name' => 'Admin',
            'description' => 'Acesso total ao sistema',
        ]);

        $role->delete();

        $this->assertSoftDeleted('roles', [
            'id' => $role->id,
        ]);
        $this->assertNull(Role::find($role->id));
    }

    public function test_can_sync_permissions_to_role(): void
    {
        $role = Role::create(['name' => 'Admin']);

        $permission = Permission::create([
            'name' => 'Ver Usuários',
            'code' => 'user.view',
            'group' => 'Usuários',
        ]);

        $role->permissions()->sync([$permission->id]);

        $this->assertTrue($role->permissions()->where('permissions.id', $permission->id)->exists());
    }
}
