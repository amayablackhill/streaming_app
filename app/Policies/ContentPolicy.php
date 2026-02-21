<?php

namespace App\Policies;

use App\Models\Content;
use App\Models\User;

class ContentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function view(User $user, Content $content): bool
    {
        return $user->hasRole('admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function update(User $user, Content $content): bool
    {
        return $user->hasRole('admin');
    }

    public function delete(User $user, Content $content): bool
    {
        return $user->hasRole('admin');
    }
}
