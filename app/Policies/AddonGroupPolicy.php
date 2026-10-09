<?php

namespace App\Policies;

use App\Models\AddonGroup;
use App\Models\User;

class AddonGroupPolicy
{
    public function before(User $user, string $ability): bool|null
    {
        return $user->isAdmin() ? true : null;
    }

    public function viewAny(User $user): bool  { return false; }
    public function view(User $user, AddonGroup $group): bool { return false; }
    public function create(User $user): bool { return false; }
    public function update(User $user, AddonGroup $group): bool { return false; }
    public function delete(User $user, AddonGroup $group): bool { return false; }
    public function restore(User $user, AddonGroup $group): bool { return false; }
    public function forceDelete(User $user, AddonGroup $group): bool { return false; }
}
