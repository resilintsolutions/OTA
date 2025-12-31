<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

final class PromoEnginePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin')
            || $user->hasRole('admin', 'sanctum')
            || $user->hasRole('admin', 'web');
    }

    public function update(User $user): bool
    {
        return $user->hasRole('admin')
            || $user->hasRole('admin', 'sanctum')
            || $user->hasRole('admin', 'web');
    }
}
