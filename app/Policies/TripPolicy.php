<?php

namespace App\Policies;

use App\Enums\TripStatus;
use App\Models\Trip;
use App\Models\User;

class TripPolicy
{
    public function view(User $user, Trip $trip): bool
    {
        return $user->isAdmin() || $user->isStudent();
    }

    public function update(User $user, Trip $trip): bool
    {
        return $trip->isOwnedBy($user) && $trip->status === TripStatus::Open;
    }

    public function close(User $user, Trip $trip): bool
    {
        return $trip->isOwnedBy($user) && $trip->status === TripStatus::Open;
    }

    public function cancel(User $user, Trip $trip): bool
    {
        return ($trip->isOwnedBy($user) || $user->isAdmin()) && $trip->status !== TripStatus::Cancelled;
    }

    public function join(User $user, Trip $trip): bool
    {
        return $user->isStudent()
            && ! $user->isSuspended()
            && ! $trip->isOwnedBy($user)
            && $trip->isJoinable();
    }
}
