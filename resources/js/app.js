import Alpine from 'alpinejs';
import focus from '@alpinejs/focus';

Alpine.plugin(focus);

/**
 * Global toast store.
 * Usage from Blade:  <div x-data x-init="$store.toast.push({type:'success', message:'...'})">
 * or dispatch:       window.dispatchEvent(new CustomEvent('toast', {detail:{type, message}}))
 */
Alpine.store('toast', {
    items: [],
    push({ type = 'info', title = null, message = '', timeout = 4500 }) {
        const id = Date.now() + Math.random();
        this.items.push({ id, type, title, message });
        if (timeout > 0) {
            setTimeout(() => this.remove(id), timeout);
        }
    },
    remove(id) {
        this.items = this.items.filter((item) => item.id !== id);
    },
});

window.addEventListener('toast', (event) => {
    Alpine.store('toast').push(event.detail ?? {});
});

/**
 * OTP input group: auto-advance, backspace, paste of full code.
 */
Alpine.data('otpInput', (length = 6) => ({
    length,
    digits: Array.from({ length }, () => ''),
    get value() {
        return this.digits.join('');
    },
    onInput(index, event) {
        const value = event.target.value.replace(/\D/g, '');
        if (!value) {
            this.digits[index] = '';
            return;
        }
        if (value.length > 1) {
            this.fill(value, index);
            return;
        }
        this.digits[index] = value;
        this.focusAt(index + 1);
    },
    onKeydown(index, event) {
        if (event.key === 'Backspace' && !this.digits[index] && index > 0) {
            this.focusAt(index - 1);
        }
        if (event.key === 'ArrowLeft' && index > 0) {
            this.focusAt(index - 1);
        }
        if (event.key === 'ArrowRight' && index < this.length - 1) {
            this.focusAt(index + 1);
        }
    },
    onPaste(event) {
        const text = (event.clipboardData || window.clipboardData).getData('text');
        const clean = text.replace(/\D/g, '');
        if (clean) {
            event.preventDefault();
            this.fill(clean, 0);
        }
    },
    fill(value, start) {
        for (let i = 0; i < value.length && start + i < this.length; i++) {
            this.digits[start + i] = value[i];
        }
        this.focusAt(Math.min(start + value.length, this.length - 1));
    },
    focusAt(index) {
        if (index < 0 || index >= this.length) return;
        this.$nextTick(() => {
            const el = this.$refs[`digit${index}`];
            if (el) {
                el.focus();
                el.select();
            }
        });
    },
}));

/**
 * Countdown timer (seconds) used for OTP resend cooldown.
 */
Alpine.data('countdown', (seconds = 60) => ({
    remaining: seconds,
    timer: null,
    init() {
        if (this.remaining > 0) {
            this.timer = setInterval(() => {
                this.remaining -= 1;
                if (this.remaining <= 0) {
                    clearInterval(this.timer);
                }
            }, 1000);
        }
    },
    get label() {
        const m = Math.floor(this.remaining / 60).toString().padStart(2, '0');
        const s = (this.remaining % 60).toString().padStart(2, '0');
        return `${m}:${s}`;
    },
}));

/**
 * File input preview + name display.
 */
Alpine.data('filePicker', ({ accept = 'image/*', maxMb = 4 } = {}) => ({
    accept,
    maxMb,
    name: '',
    preview: null,
    error: '',
    onChange(event) {
        const file = event.target.files?.[0];
        this.error = '';
        this.preview = null;
        this.name = '';
        if (!file) return;
        if (file.size > maxMb * 1024 * 1024) {
            this.error = `Ukuran file maksimal ${maxMb} MB.`;
            event.target.value = '';
            return;
        }
        this.name = file.name;
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = (e) => (this.preview = e.target.result);
            reader.readAsDataURL(file);
        }
    },
    clear(input) {
        this.name = '';
        this.preview = null;
        this.error = '';
        if (input) input.value = '';
    },
}));

/**
 * Dynamic repeatable item rows (order items).
 */
Alpine.data('itemRows', (initial = []) => ({
    rows: initial.length ? initial : [{ name: '', quantity: 1, estimated_price: '', note: '' }],
    add() {
        if (this.rows.length >= 15) return;
        this.rows.push({ name: '', quantity: 1, estimated_price: '', note: '' });
    },
    remove(index) {
        if (this.rows.length === 1) {
            this.rows[0] = { name: '', quantity: 1, estimated_price: '', note: '' };
            return;
        }
        this.rows.splice(index, 1);
    },
    get subtotal() {
        return this.rows.reduce((sum, row) => {
            const qty = parseInt(row.quantity || 0, 10);
            const price = parseInt(row.estimated_price || 0, 10);
            return sum + (isNaN(qty) || isNaN(price) ? 0 : qty * price);
        }, 0);
    },
}));

window.rupiah = (value) => {
    const n = Number(value || 0);
    return 'Rp ' + n.toLocaleString('id-ID');
};

window.Alpine = Alpine;
Alpine.start();
