<?php

namespace App\Models\Concerns;

use App\Models\Scopes\TenantScope;
use Illuminate\Support\Facades\Auth;

trait BelongsToTenant
{
    protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope);

        static::saving(function (self $model): void {
            $user = Auth::user();

            if ($user && ! $user->is_super_admin) {
                $model->company_id = $user->company_id;
                $model->is_super_admin = false;
            }
        });
    }

    public function tenantScopeIncludesGlobalRecords(): bool
    {
        return false;
    }
}
