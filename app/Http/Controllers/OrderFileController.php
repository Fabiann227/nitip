<?php

namespace App\Http\Controllers;

use App\Models\Dispute;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Serves private files (print document, payment proof, cashier receipt, dispute evidence, QRIS)
 * only to authorized users.
 */
class OrderFileController extends Controller
{
    public function show(Order $order, string $kind): StreamedResponse
    {
        $this->authorize('viewFiles', $order);

        [$path, $name] = match ($kind) {
            'document' => [$order->document_path, $order->document_name ?? 'dokumen'],
            'proof' => [$order->payment_proof_path, 'bukti-transfer'],
            'receipt' => [$order->receipt_path, 'struk'],
            default => abort(404),
        };

        return $this->stream($path, $name, $kind === 'document');
    }

    public function evidence(Dispute $dispute): StreamedResponse
    {
        $this->authorize('view', $dispute);

        return $this->stream($dispute->evidence_path, 'bukti-sengketa');
    }

    public function qris(User $user): StreamedResponse
    {
        abort_unless(request()->user()?->hasVerifiedEmail() || request()->user()?->isAdmin(), 403);

        return $this->stream($user->payment_qris_path, 'qris');
    }

    private function stream(?string $path, string $name, bool $allowDownload = false): StreamedResponse
    {
        $disk = Storage::disk(config('nitip.uploads.private_disk'));

        abort_unless($path && $disk->exists($path), 404);

        $mime = (string) $disk->mimeType($path);
        $inline = str_starts_with($mime, 'image/') || $mime === 'application/pdf';

        return $inline || ! $allowDownload
            ? $disk->response($path, $name)
            : $disk->download($path, $name);
    }
}
