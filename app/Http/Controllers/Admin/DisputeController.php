<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DisputeResolution;
use App\Enums\DisputeStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ResolveDisputeRequest;
use App\Models\Dispute;
use App\Services\DisputeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DisputeController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status', 'open');

        return view('admin.disputes.index', [
            'disputes' => Dispute::query()
                ->with(['order.requester', 'order.fulfiller', 'openedBy', 'resolvedBy'])
                ->when(DisputeStatus::tryFrom($status), fn ($query) => $query->where('status', $status))
                ->latest()
                ->paginate(15)
                ->withQueryString(),
            'filter' => $status,
        ]);
    }

    public function show(Dispute $dispute): View
    {
        $dispute->load(['openedBy', 'resolvedBy', 'order.requester', 'order.fulfiller', 'order.category', 'order.events.actor']);

        return view('admin.disputes.show', [
            'dispute' => $dispute,
            'order' => $dispute->order,
            'resolutions' => DisputeResolution::cases(),
        ]);
    }

    public function resolve(ResolveDisputeRequest $request, Dispute $dispute, DisputeService $disputes): RedirectResponse
    {
        $disputes->resolve(
            $dispute,
            $request->user(),
            DisputeResolution::from($request->validated('resolution')),
            $request->validated('note'),
        );

        return redirect()->route('admin.disputes.show', $dispute)->with('success', 'Sengketa diselesaikan dan kedua pihak diberi tahu.');
    }
}
