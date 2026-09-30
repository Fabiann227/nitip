<?php

namespace App\Services;

use App\Enums\DisputeReason;
use App\Enums\DisputeResolution;
use App\Enums\DisputeStatus;
use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Exceptions\NitipException;
use App\Models\Dispute;
use App\Models\Order;
use App\Models\User;
use App\Notifications\AppNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class DisputeService
{
    public function __construct(
        private readonly OrderWorkflowService $workflow,
        private readonly FileStorageService $files,
    ) {}

    public function open(Order $order, User $opener, DisputeReason $reason, string $description, ?UploadedFile $evidence = null): Dispute
    {
        if (! $order->isParticipant($opener)) {
            throw new NitipException('Kamu bukan pihak dalam pesanan ini.');
        }

        if ($order->dispute()->exists()) {
            throw new NitipException('Sengketa untuk pesanan ini sudah pernah dibuka.');
        }

        return DB::transaction(function () use ($order, $opener, $reason, $description, $evidence) {
            $this->workflow->markDisputed($order, $opener, $reason->label());

            $dispute = Dispute::query()->create([
                'order_id' => $order->id,
                'opened_by' => $opener->id,
                'reason' => $reason,
                'description' => $description,
                'evidence_path' => $evidence ? $this->files->storePrivate($evidence, "orders/{$order->id}/dispute") : null,
                'status' => DisputeStatus::Open,
            ]);

            foreach (User::query()->where('role', UserRole::Admin->value)->get() as $admin) {
                $admin->notify(new AppNotification(
                    'Sengketa baru perlu ditinjau',
                    "{$opener->shortName()} membuka sengketa ({$reason->label()}) untuk pesanan {$order->code}.",
                    route('admin.disputes.show', $dispute),
                    'gavel',
                    'danger',
                    ['order_code' => $order->code],
                ));
            }

            return $dispute;
        });
    }

    public function resolve(Dispute $dispute, User $admin, DisputeResolution $resolution, string $note): Dispute
    {
        if (! $admin->isAdmin()) {
            throw new NitipException('Hanya admin yang bisa menyelesaikan sengketa.');
        }

        if (! $dispute->isOpen()) {
            throw new NitipException('Sengketa ini sudah diselesaikan.');
        }

        return DB::transaction(function () use ($dispute, $admin, $resolution, $note) {
            $finalStatus = $resolution === DisputeResolution::CompleteOrder ? OrderStatus::Completed : OrderStatus::Cancelled;

            $this->workflow->resolveDispute(
                $dispute->order,
                $admin,
                $finalStatus,
                $note,
                $resolution === DisputeResolution::CancelWithRefund,
            );

            $dispute->forceFill([
                'status' => DisputeStatus::Resolved,
                'resolution' => $resolution,
                'resolution_note' => $note,
                'resolved_by' => $admin->id,
                'resolved_at' => now(),
            ])->save();

            return $dispute;
        });
    }
}
