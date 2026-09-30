<?php

namespace App\Policies;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function view(User $user, Order $order): bool
    {
        if ($user->isAdmin() || $order->isParticipant($user)) {
            return true;
        }

        // Open requests are public inside the campus so fulfillers can inspect them before claiming.
        return $order->isOpen() && $user->isStudent();
    }

    public function claim(User $user, Order $order): bool
    {
        return $user->isStudent()
            && ! $user->isSuspended()
            && $order->isOpen()
            && ! $order->isRequester($user)
            && ! $order->isExpired();
    }

    public function release(User $user, Order $order): bool
    {
        return $order->isFulfiller($user)
            && $order->isRequest()
            && in_array($order->status, [OrderStatus::AwaitingPayment, OrderStatus::PaymentSubmitted], true);
    }

    public function pay(User $user, Order $order): bool
    {
        return $order->isRequester($user) && $order->status === OrderStatus::AwaitingPayment;
    }

    public function verifyPayment(User $user, Order $order): bool
    {
        return $order->isFulfiller($user) && $order->status === OrderStatus::PaymentSubmitted;
    }

    public function start(User $user, Order $order): bool
    {
        return $order->isFulfiller($user) && $order->status === OrderStatus::Paid;
    }

    public function deliver(User $user, Order $order): bool
    {
        return $order->isFulfiller($user) && $order->status === OrderStatus::InProgress;
    }

    public function markDelivered(User $user, Order $order): bool
    {
        return $order->isFulfiller($user) && $order->status === OrderStatus::Delivering;
    }

    public function complete(User $user, Order $order): bool
    {
        if ($order->isRequester($user)) {
            return $order->status === OrderStatus::Delivered;
        }

        if ($order->isFulfiller($user)) {
            return in_array($order->status, [OrderStatus::Delivering, OrderStatus::Delivered], true);
        }

        return false;
    }

    public function cancel(User $user, Order $order): bool
    {
        if ($order->status->isTerminal()) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        if ($order->isRequester($user)) {
            return in_array($order->status, [OrderStatus::Open, OrderStatus::AwaitingPayment, OrderStatus::PaymentSubmitted], true);
        }

        if ($order->isFulfiller($user)) {
            return in_array($order->status, [OrderStatus::AwaitingPayment, OrderStatus::PaymentSubmitted, OrderStatus::Paid, OrderStatus::InProgress], true);
        }

        return false;
    }

    public function review(User $user, Order $order): bool
    {
        return $order->isParticipant($user)
            && $order->status === OrderStatus::Completed
            && ! $order->hasReviewFrom($user);
    }

    public function dispute(User $user, Order $order): bool
    {
        return $order->isParticipant($user)
            && in_array($order->status, [OrderStatus::Paid, OrderStatus::InProgress, OrderStatus::Delivering, OrderStatus::Delivered], true)
            && $order->dispute()->doesntExist();
    }

    public function viewPin(User $user, Order $order): bool
    {
        return $order->isRequester($user);
    }

    /**
     * Private files: print document, payment proof, cashier receipt.
     */
    public function viewFiles(User $user, Order $order): bool
    {
        return $user->isAdmin() || $order->isParticipant($user);
    }
}
