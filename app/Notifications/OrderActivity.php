<?php

namespace App\Notifications;

use App\Models\Order;

/**
 * In-app notification tied to an order (status change, payment, dispute...).
 */
class OrderActivity extends AppNotification
{
    public function __construct(
        Order $order,
        string $title,
        string $message,
        string $icon = 'package_2',
        string $tone = 'info',
    ) {
        parent::__construct(
            title: $title,
            message: $message,
            url: route('orders.show', $order),
            icon: $icon,
            tone: $tone,
            meta: [
                'order_id' => $order->id,
                'order_code' => $order->code,
            ],
        );
    }
}
