@props(['user', 'size' => 'sm', 'link' => true, 'subtitle' => null, 'verified' => true])
@php $subtitle ??= $user->campus; @endphp
<div {{ $attributes->merge(['class' => 'flex items-center gap-2 min-w-0']) }}>
    <x-ui.avatar :user="$user" :size="$size" />
    <div class="min-w-0 leading-tight">
        <div class="flex items-center gap-1">
            @if ($link && $user->isStudent())
                <a href="{{ route('users.show', $user) }}" class="text-sm font-semibold text-on-surface hover:text-primary truncate">{{ $user->shortName() }}</a>
            @else
                <span class="text-sm font-semibold text-on-surface truncate">{{ $user->shortName() }}</span>
            @endif
            @if ($verified && $user->hasVerifiedEmail())<span class="material-symbols-outlined text-[14px] text-secondary" title="Terverifikasi">verified</span>@endif
        </div>
        @if ($subtitle)<p class="text-xs text-on-surface-variant truncate">{{ $subtitle }}</p>@endif
    </div>
</div>
