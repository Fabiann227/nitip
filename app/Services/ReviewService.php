<?php

namespace App\Services;

use App\Enums\OrderEventType;
use App\Enums\OrderStatus;
use App\Exceptions\NitipException;
use App\Models\Order;
use App\Models\Review;
use App\Models\User;
use App\Notifications\OrderActivity;
use Illuminate\Support\Facades\DB;

class ReviewService
{
    public function __construct(private readonly OrderWorkflowService $workflow) {}

    public function create(Order $order, User $reviewer, int $rating, ?string $comment): Review
    {
        if ($order->status !== OrderStatus::Completed) {
            throw new NitipException('Ulasan hanya bisa diberikan setelah pesanan selesai.');
        }

        $reviewee = $order->counterpartFor($reviewer);

        if (! $reviewee) {
            throw new NitipException('Kamu bukan pihak dalam pesanan ini.');
        }

        if ($order->reviews()->where('reviewer_id', $reviewer->id)->exists()) {
            throw new NitipException('Kamu sudah memberi ulasan untuk pesanan ini.');
        }

        return DB::transaction(function () use ($order, $reviewer, $reviewee, $rating, $comment) {
            $review = Review::query()->create([
                'order_id' => $order->id,
                'reviewer_id' => $reviewer->id,
                'reviewee_id' => $reviewee->id,
                'rating' => $rating,
                'comment' => $comment,
            ]);

            $this->workflow->record($order, $reviewer, OrderEventType::Reviewed, null, null,
                "{$reviewer->shortName()} memberi ulasan {$rating}/5 untuk {$reviewee->shortName()}.");

            $reviewee->notify(new OrderActivity(
                $order,
                'Ulasan baru',
                "{$reviewer->shortName()} memberimu {$rating} bintang untuk pesanan \"{$order->title}\".",
                'star',
                'success',
            ));

            return $review;
        });
    }
}
