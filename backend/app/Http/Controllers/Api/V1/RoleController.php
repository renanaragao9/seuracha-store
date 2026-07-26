<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\Api\V1\Role\RoleResource;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends BaseController
{
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Role::class);

        return $this->successResponse(
            data: RoleResource::collection(Role::with('permissions')->get()),
            message: 'Perfis listados com sucesso.'
        );
    }

    public function show(Role $role): JsonResponse
    {
        $this->authorize('view', $role);

        return $this->successResponse(
            data: new RoleResource($role->load('permissions')),
            message: 'Perfil encontrado.'
        );
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Role::class);

        $user = $request->user();
        $companyId = $user->is_super_admin ? $request->input('company_id') : $user->company_id;

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')->where(fn ($query) => $query->where('company_id', $companyId))],
            'description' => ['nullable', 'string'],
            'company_id' => ['nullable', Rule::exists('companies', 'id')],
            'permission_ids' => ['sometimes', 'array'],
            'permission_ids.*' => ['integer'],
        ]);

        $permissionIds = Permission::whereIn('id', $data['permission_ids'] ?? [])->pluck('id');
        unset($data['permission_ids']);

        $role = Role::create($data);
        $role->permissions()->sync($permissionIds);

        return $this->successResponse(
            data: new RoleResource($role->load('permissions')),
            message: 'Perfil criado com sucesso.'
        );
    }

    public function update(Request $request, Role $role): JsonResponse
    {
        $this->authorize('update', $role);

        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('roles', 'name')->where(fn ($query) => $query->where('company_id', $role->company_id))->ignore($role->id)],
            'description' => ['nullable', 'string'],
            'permission_ids' => ['sometimes', 'array'],
            'permission_ids.*' => ['integer'],
        ]);

        if (array_key_exists('permission_ids', $data)) {
            $permissionIds = Permission::whereIn('id', $data['permission_ids'])->pluck('id');
            $role->permissions()->sync($permissionIds);
            unset($data['permission_ids']);
        }

        $role->update($data);

        return $this->successResponse(
            data: new RoleResource($role->load('permissions')),
            message: 'Perfil atualizado com sucesso.'
        );
    }

    public function destroy(Role $role): JsonResponse
    {
        $this->authorize('delete', $role);

        $role->delete();

        return $this->successResponse(
            data: null,
            message: 'Perfil removido com sucesso.'
        );
    }
}
