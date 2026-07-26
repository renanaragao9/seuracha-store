<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\Api\V1\User\UserResource;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends BaseController
{
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', User::class);

        return $this->successResponse(
            data: UserResource::collection(User::with('role')->get()),
            message: 'Usuários listados com sucesso.'
        );
    }

    public function show(User $user): JsonResponse
    {
        $this->authorize('view', $user);

        return $this->successResponse(
            data: new UserResource($user->load('role')),
            message: 'Usuário encontrado.'
        );
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', User::class);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8'],
            'status' => ['nullable', 'in:active,inactive'],
            'role_id' => ['nullable', 'integer'],
            'company_id' => ['nullable', Rule::exists('companies', 'id')],
        ]);

        if (! empty($data['role_id']) && ! Role::find($data['role_id'])) {
            return $this->errorResponse(
                errors: ['role_id' => 'Perfil inválido.'],
                message: 'Perfil inválido.',
                statusCode: 422
            );
        }

        $user = User::create($data);

        return $this->successResponse(
            data: new UserResource($user->load('role')),
            message: 'Usuário criado com sucesso.'
        );
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $this->authorize('update', $user);

        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['sometimes', 'nullable', 'string', 'min:8'],
            'status' => ['sometimes', 'in:active,inactive'],
            'role_id' => ['nullable', 'integer'],
            'company_id' => ['nullable', Rule::exists('companies', 'id')],
        ]);

        if (! empty($data['role_id']) && ! Role::find($data['role_id'])) {
            return $this->errorResponse(
                errors: ['role_id' => 'Perfil inválido.'],
                message: 'Perfil inválido.',
                statusCode: 422
            );
        }

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);

        return $this->successResponse(
            data: new UserResource($user->load('role')),
            message: 'Usuário atualizado com sucesso.'
        );
    }

    public function destroy(User $user): JsonResponse
    {
        $this->authorize('delete', $user);

        $user->delete();

        return $this->successResponse(
            data: null,
            message: 'Usuário removido com sucesso.'
        );
    }
}
