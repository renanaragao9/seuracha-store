<?php

namespace Tests\Unit\Models;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_user(): void
    {
        $role = Role::create(['name' => 'Admin']);

        $user = User::create([
            'name' => 'Administrador',
            'email' => 'admin@seuracha.com',
            'password' => '12345678',
            'status' => 'active',
            'role_id' => $role->id,
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'admin@seuracha.com',
        ]);
        $this->assertTrue(Hash::check('12345678', $user->password));
    }

    public function test_can_read_user(): void
    {
        $user = User::create([
            'name' => 'Administrador',
            'email' => 'admin@seuracha.com',
            'password' => '12345678',
            'status' => 'active',
        ]);

        $found = User::find($user->id);

        $this->assertNotNull($found);
        $this->assertSame('admin@seuracha.com', $found->email);
    }

    public function test_can_update_user(): void
    {
        $user = User::create([
            'name' => 'Administrador',
            'email' => 'admin@seuracha.com',
            'password' => '12345678',
            'status' => 'active',
        ]);

        $user->update(['name' => 'Administrador Geral', 'status' => 'inactive']);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Administrador Geral',
            'status' => 'inactive',
        ]);
    }

    public function test_can_delete_user(): void
    {
        $user = User::create([
            'name' => 'Administrador',
            'email' => 'admin@seuracha.com',
            'password' => '12345678',
            'status' => 'active',
        ]);

        $user->delete();

        $this->assertSoftDeleted('users', [
            'id' => $user->id,
        ]);
        $this->assertNull(User::find($user->id));
    }

    public function test_user_belongs_to_role(): void
    {
        $role = Role::create(['name' => 'Admin']);

        $user = User::create([
            'name' => 'Administrador',
            'email' => 'admin@seuracha.com',
            'password' => '12345678',
            'status' => 'active',
            'role_id' => $role->id,
        ]);

        $this->assertTrue($user->role->is($role));
    }

    public function test_password_is_hidden_from_array(): void
    {
        $user = User::create([
            'name' => 'Administrador',
            'email' => 'admin@seuracha.com',
            'password' => '12345678',
            'status' => 'active',
        ]);

        $this->assertArrayNotHasKey('password', $user->toArray());
    }
}
