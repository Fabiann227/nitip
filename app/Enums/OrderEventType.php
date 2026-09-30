<?php

namespace App\Enums;

enum OrderEventType: string
{
    case Created = 'created';
    case Claimed = 'claimed';
    case Released = 'released';
    case JoinedTrip = 'joined_trip';
    case PaymentSubmitted = 'payment_submitted';
    case PaymentVerified = 'payment_verified';
    case PaymentRejected = 'payment_rejected';
    case Started = 'started';
    case Delivering = 'delivering';
    case Delivered = 'delivered';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case DisputeOpened = 'dispute_opened';
    case DisputeResolved = 'dispute_resolved';
    case Reviewed = 'reviewed';
    case AdminNote = 'admin_note';

    public function icon(): string
    {
        return match ($this) {
            self::Created => 'post_add',
            self::Claimed, self::JoinedTrip => 'handshake',
            self::Released => 'undo',
            self::PaymentSubmitted => 'upload_file',
            self::PaymentVerified => 'verified',
            self::PaymentRejected => 'error',
            self::Started => 'shopping_cart_checkout',
            self::Delivering => 'directions_walk',
            self::Delivered => 'inventory',
            self::Completed => 'task_alt',
            self::Cancelled => 'cancel',
            self::DisputeOpened => 'gavel',
            self::DisputeResolved => 'balance',
            self::Reviewed => 'star',
            self::AdminNote => 'admin_panel_settings',
        };
    }
}
