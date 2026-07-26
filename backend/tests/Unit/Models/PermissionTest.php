<?php

namespace Tests\Unit\Models;

use App\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_permission(): void
    {
        $permission = Permission::create([
            'name' => 'Ver Usuários',
            'code' => 'user.view',
            'group' => 'Usuários',
        ]);

        $this->assertDatabaseHas('permissions', [
            'id' => $permission->id,
            'code' => 'user.view',
        ]);
    }

    public function test_can_read_permission(): void
    {
        $permission = Permission::create([
            'name' => 'Ver Usuários',
            'code' => 'user.view',
            'group' => 'Usuários',
        ]);

        $found = Permission::find($permission->id);

        $this->assertNotNull($found);
        $this->assertSame('user.view', $found->code);
        $this->assertSame('Usuários', $found->group);
    }

    public function test_can_update_permission(): void
    {
        $permission = Permission::create([
            'name' => 'Ver Usuários',
            'code' => 'user.view',
            'group' => 'Usuários',
        ]);

        $permission->update(['name' => 'Visualizar Usuários']);

        $this->assertDatabaseHas('permissions', [
            'id' => $permission->id,
            'name' => 'Visualizar Usuários',
        ]);
    }

    public function test_can_delete_permission(): void
    {
        $permission = Permission::create([
            'name' => 'Ver Usuários',
            'code' => 'user.view',
            'group' => 'Usuários',
        ]);

        $permission->delete();

        $this->assertSoftDeleted('permissions', [
            'id' => $permission->id,
        ]);
        $this->assertNull(Permission::find($permission->id));
    }
}
