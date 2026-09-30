<?php

namespace App\Policies;

use App\Models\Dispute;
use App\Models\User;

class DisputePolicy
{
    public function view(User $user, Dispute $dispute): bool
    {
        return $user->isAdmin() || $dispute->order->isParticipant($user);
    }

    public function resolve(User $user, Dispute $dispute): bool
    {
        return $user->isAdmin() && $dispute->isOpen();
    }
}
