<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\Api\V1\Permission\PermissionResource;
use App\Models\Permission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PermissionController extends BaseController
{
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Permission::class);

        return $this->successResponse(
            data: PermissionResource::collection(Permission::query()->get()),
            message: 'Permissões listadas com sucesso.'
        );
    }

    public function show(Permission $permission): JsonResponse
    {
        $this->authorize('view', $permission);

        return $this->successResponse(
            data: new PermissionResource($permission),
            message: 'Permissão encontrada.'
        );
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Permission::class);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255', 'unique:permissions,code'],
            'group' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'company_id' => ['nullable', Rule::exists('companies', 'id')],
        ]);

        $permission = Permission::create($data);

        return $this->successResponse(
            data: new PermissionResource($permission),
            message: 'Permissão criada com sucesso.'
        );
    }

    public function update(Request $request, Permission $permission): JsonResponse
    {
        $this->authorize('update', $permission);

        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'code' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('permissions', 'code')->ignore($permission->id)],
            'group' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $permission->update($data);

        return $this->successResponse(
            data: new PermissionResource($permission),
            message: 'Permissão atualizada com sucesso.'
        );
    }

    public function destroy(Permission $permission): JsonResponse
    {
        $this->authorize('delete', $permission);

        $permission->delete();

        return $this->successResponse(
            data: null,
            message: 'Permissão removida com sucesso.'
        );
    }
}
