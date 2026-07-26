<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\Api\V1\Company\CompanyResource;
use App\Models\Company;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CompanyController extends BaseController
{
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Company::class);

        return $this->successResponse(
            data: CompanyResource::collection(Company::query()->get()),
            message: 'Empresas listadas com sucesso.'
        );
    }

    public function show(Company $company): JsonResponse
    {
        $this->authorize('view', $company);

        return $this->successResponse(
            data: new CompanyResource($company),
            message: 'Empresa encontrada.'
        );
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Company::class);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:companies,slug'],
            'domain' => ['nullable', 'string', 'max:255', 'unique:companies,domain'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'document' => ['nullable', 'string', 'max:30'],
            'status' => ['nullable', 'in:active,inactive,suspended'],
            'settings' => ['nullable', 'array'],
            'trial_ends_at' => ['nullable', 'date'],
        ]);

        $company = Company::create($data);

        return $this->successResponse(
            data: new CompanyResource($company),
            message: 'Empresa criada com sucesso.'
        );
    }

    public function update(Request $request, Company $company): JsonResponse
    {
        $this->authorize('update', $company);

        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => ['sometimes', 'required', 'string', 'max:255', 'alpha_dash', Rule::unique('companies', 'slug')->ignore($company->id)],
            'domain' => ['nullable', 'string', 'max:255', Rule::unique('companies', 'domain')->ignore($company->id)],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'document' => ['nullable', 'string', 'max:30'],
            'status' => ['sometimes', 'in:active,inactive,suspended'],
            'settings' => ['nullable', 'array'],
            'trial_ends_at' => ['nullable', 'date'],
        ]);

        $company->update($data);

        return $this->successResponse(
            data: new CompanyResource($company),
            message: 'Empresa atualizada com sucesso.'
        );
    }

    public function destroy(Company $company): JsonResponse
    {
        $this->authorize('delete', $company);

        $company->delete();

        return $this->successResponse(
            data: null,
            message: 'Empresa removida com sucesso.'
        );
    }
}
