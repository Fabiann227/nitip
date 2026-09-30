@props(['length' => 6, 'name' => 'digits', 'autofocus' => true, 'boxClass' => 'w-11 h-12 text-xl', 'error' => null])
<div x-data="otpInput({{ (int) $length }})" class="flex flex-col items-center gap-2" x-on:paste="onPaste($event)">
    <div class="flex items-center justify-center gap-2 w-full">
        @foreach (range(0, $length - 1) as $i)
            <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="{{ $length }}" autocomplete="one-time-code"
                   name="{{ $name }}[]" x-ref="digit{{ $i }}" x-model="digits[{{ $i }}]"
                   x-on:input="onInput({{ $i }}, $event)" x-on:keydown="onKeydown({{ $i }}, $event)" x-on:focus="$event.target.select()"
                   @if($autofocus && $i === 0) autofocus @endif
                   aria-label="Digit {{ $i + 1 }}"
                   class="pin-box {{ $boxClass }} {{ $error ? 'border-error' : '' }}">
        @endforeach
    </div>
    @if ($error)
        <p class="error-text" role="alert"><span class="material-symbols-outlined text-[14px]">error</span><span>{{ $error }}</span></p>
    @endif
</div>
