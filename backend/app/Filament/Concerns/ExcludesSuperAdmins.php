<?php

namespace App\Filament\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait ExcludesSuperAdmins
{
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('is_super_admin', false);
    }
}
