import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

/* ------------------------------------------------------------------ *
 *  Dialog & toast kustom (pengganti SweetAlert2)
 *
 *  Semua tampilan memakai class `.sipkl-*` yang didefinisikan di
 *  resources/css/app.css, sehingga warna, radius, dan elevation-nya
 *  konsisten dengan komponen admin (kartu putih + border outline).
 * ------------------------------------------------------------------ */

const SIPKL_TONE = {
    success: { icon: 'check_circle' },
    error: { icon: 'error' },
    warning: { icon: 'warning' },
    info: { icon: 'info' },
};

const FOCUSABLE =
    'a[href], button:not([disabled]), input:not([disabled]):not([type="hidden"]), ' +
    'textarea:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])';

function sipklEl(tag, className, html) {
    const node = document.createElement(tag);

    if (className) node.className = className;
    if (html != null) node.innerHTML = html;

    return node;
}

function sipklEscape(value) {
    return String(value ?? '').replace(/[&<>"']/g, (char) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;',
    }[char]));
}

function sipklGlyph(name) {
    return `<span class="material-symbols-outlined sipkl-dialog__glyph" aria-hidden="true">${sipklEscape(name)}</span>`;
}

/**
 * Dialog modal generik.
 *
 * Mengembalikan Promise yang resolve dengan 'confirm', 'cancel', atau null
 * (dismiss lewat ESC / backdrop). `timeout` menutup dialog otomatis dan
 * mengembalikan null, `onMount(dialog, api)` dipakai untuk isi dinamis.
 */
function sipklOpenDialog({
    tone = 'info',
    icon,
    title,
    text = '',
    body = '',
    note = '',
    confirmText = null,
    cancelText = null,
    danger = false,
    wide = false,
    dismissable = true,
    focus = 'cancel',
    timeout = 0,
    onMount,
}) {
    return new Promise((resolve) => {
        const previous = document.activeElement;
        const meta = SIPKL_TONE[tone] ?? SIPKL_TONE.info;

        const overlay = sipklEl('div', 'sipkl-overlay');
        overlay.setAttribute('role', 'dialog');
        overlay.setAttribute('aria-modal', 'true');

        const dialog = sipklEl('div', 'sipkl-dialog' + (wide ? ' sipkl-dialog--wide' : ''));

        const titleId = `sipkl-dialog-title-${Date.now()}`;
        dialog.setAttribute('aria-labelledby', titleId);

        // ---- header ----
        const head = sipklEl('div', 'sipkl-dialog__head');
        head.append(
            sipklEl('div', `sipkl-dialog__badge sipkl-dialog__badge--${tone}`, sipklGlyph(icon ?? meta.icon)),
            sipklEl(
                'div',
                'sipkl-dialog__headtext',
                `<p class="sipkl-dialog__title" id="${titleId}">${sipklEscape(title)}</p>` +
                (text ? `<p class="sipkl-dialog__text">${sipklEscape(text)}</p>` : '')
            )
        );
        dialog.append(head);

        // ---- body ----
        if (body || note) {
            const bodyNode = sipklEl('div', 'sipkl-dialog__body', body);
            if (note) bodyNode.append(sipklEl('p', 'sipkl-dialog__note', note));
            dialog.append(bodyNode);
        }

        // ---- footer ----
        const confirmBtn = confirmText
            ? sipklEl('button', danger ? 'btn-danger btn-md' : 'btn-primary btn-md', sipklEscape(confirmText))
            : null;
        const cancelBtn = cancelText
            ? sipklEl('button', 'btn-secondary btn-md', sipklEscape(cancelText))
            : null;

        if (confirmBtn || cancelBtn) {
            const foot = sipklEl('div', 'sipkl-dialog__foot');
            if (cancelBtn) foot.append(cancelBtn);
            if (confirmBtn) foot.append(confirmBtn);
            dialog.append(foot);
        }

        overlay.append(sipklEl('div', 'sipkl-overlay__backdrop'), dialog);
        document.body.append(overlay);
        document.body.classList.add('overflow-hidden');

        let settled = false;
        let ticker = null;
        let timeoutId = null;

        const api = {
            dialog,
            close(result) {
                if (settled) return;
                settled = true;

                clearInterval(ticker);
                clearTimeout(timeoutId);
                document.removeEventListener('keydown', onKeydown, true);
                overlay.remove();
                document.body.classList.remove('overflow-hidden');

                if (previous instanceof HTMLElement && previous.isConnected) previous.focus();

                resolve(result);
            },
        };

        function onKeydown(event) {
            if (event.key === 'Escape' && dismissable) {
                event.stopPropagation();
                api.close(null);

                return;
            }

            if (event.key !== 'Tab') return;

            // Jebak fokus agar tetap di dalam dialog.
            const items = [...dialog.querySelectorAll(FOCUSABLE)];
            if (!items.length) return;

            const first = items[0];
            const last = items[items.length - 1];

            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        }

        if (cancelBtn) {
            cancelBtn.type = 'button';
            cancelBtn.addEventListener('click', () => api.close('cancel'));
        }

        if (confirmBtn) {
            confirmBtn.type = 'button';
            confirmBtn.addEventListener('click', () => api.close('confirm'));
        }

        overlay.addEventListener('click', (event) => {
            if (event.target.classList.contains('sipkl-overlay__backdrop') && dismissable) api.close(null);
        });

        document.addEventListener('keydown', onKeydown, true);

        const initial = focus === 'confirm' ? confirmBtn : (cancelBtn ?? confirmBtn);
        initial?.focus();

        onMount?.(dialog, { ...api, setTicker: (fn) => { ticker = fn; } });

        if (timeout > 0) {
            timeoutId = setTimeout(() => api.close(null), timeout * 1000);
        }
    });
}

let toastHost = null;

function sipklToastHost() {
    if (toastHost?.isConnected) return toastHost;

    toastHost = sipklEl('div', 'sipkl-toasts');
    toastHost.setAttribute('role', 'status');
    toastHost.setAttribute('aria-live', 'polite');
    document.body.append(toastHost);

    return toastHost;
}

window.sipklAlert = {
    /** Notifikasi ringkas di pojok layar. */
    toast(type, message, life = 4200) {
        if (!message) return;

        const meta = SIPKL_TONE[type] ?? SIPKL_TONE.info;
        const node = sipklEl('div', 'sipkl-toast');

        node.append(
            sipklEl(
                'div',
                `sipkl-toast__icon sipkl-toast__icon--${type in SIPKL_TONE ? type : 'info'}`,
                `<span class="material-symbols-outlined text-[16px] leading-none" aria-hidden="true">${meta.icon}</span>`
            ),
            sipklEl('div', 'sipkl-toast__body', `<p class="sipkl-toast__title">${sipklEscape(message)}</p>`)
        );

        const close = () => node.remove();

        node.append(
            sipklEl(
                'button',
                'sipkl-toast__close',
                '<span class="material-symbols-outlined text-[16px] leading-none" aria-hidden="true">close</span>'
            )
        );
        node.querySelector('.sipkl-toast__close').addEventListener('click', close);

        const bar = sipklEl('div', 'sipkl-toast__bar');
        bar.style.setProperty('--toast-life', `${life}ms`);
        node.append(bar);

        sipklToastHost().append(node);
        setTimeout(close, life);
    },

    /** Dialog konfirmasi untuk aksi destruktif / irreversibel. */
    async confirm({ title, text = '', confirmText = 'Ya, lanjutkan', cancelText = 'Batal', danger = true }) {
        const result = await sipklOpenDialog({
            tone: danger ? 'warning' : 'info',
            icon: danger ? 'warning' : 'help',
            title,
            text,
            confirmText,
            cancelText,
            danger,
            dismissable: true,
            focus: 'cancel', // aksi destruktif -> fokus ditahan di Batal
        });

        return result === 'confirm';
    },

    /**
     * Notifikasi permintaan login QR yang masuk ke dashboard.
     *
     * Nilai balik: 'approve' | 'reject' | null ( ESC / waktu habis ).
     * Countdown memakai progress bar + label detik yang berkurang tiap detik.
     */
    async qrRequest({ device, browser, platform, lokasi, seconds }) {
        const total = Math.max(1, Number(seconds) || 0);

        const baris = (label, value) => `
            <div class="sipkl-dialog__row">
                <span class="sipkl-dialog__key">${sipklEscape(label)}</span>
                <span class="sipkl-dialog__val">${sipklEscape(value ?? '-')}</span>
            </div>`;

        const role = document.body.dataset.sipklRole ?? 'sesuai akun ini';

        const body = `
            <div class="sipkl-dialog__rows">
                ${baris('Perangkat', device)}
                ${baris('Browser', browser)}
                ${baris('Platform', platform)}
                ${baris('Lokasi', lokasi)}
            </div>
            <div class="mt-3 flex items-center justify-between gap-3">
                <p class="font-body-sm text-[12px] text-on-surface-variant">Berlaku <span
                        class="font-title-sm text-[13px] text-on-surface tabular-nums" data-sipkl-qr-left>${total}</span> detik lagi</p>
                <p class="font-label-sm text-label-sm text-[11px] text-outline shrink-0">satu kali pakai</p>
            </div>
            <div class="sipkl-progress mt-2">
                <div class="sipkl-progress__bar" data-sipkl-qr-bar style="width:100%"></div>
            </div>`;

        const note =
            '<span class="material-symbols-outlined text-[16px] leading-none" aria-hidden="true">shield</span>' +
            `<span>Masuk dengan hak akses <strong class="font-title-sm text-on-surface">${sipklEscape(role)}</strong>. ` +
            'Izinkan hanya bila perangkat ini milik Anda.</span>';

        return sipklOpenDialog({
            tone: 'info',
            icon: 'qr_code_scanner',
            title: 'Permintaan Login QR',
            text: 'Ada perangkat lain yang ingin masuk ke akun Anda.',
            body,
            note,
            confirmText: 'Izinkan',
            cancelText: 'Tolak',
            wide: true,
            focus: 'confirm',
            dismissable: true,
            timeout: total,
            onMount(dialog, api) {
                const label = dialog.querySelector('[data-sipkl-qr-left]');
                const bar = dialog.querySelector('[data-sipkl-qr-bar]');
                let left = total;

                api.setTicker(setInterval(() => {
                    left -= 1;
                    if (label) label.textContent = Math.max(0, left);
                    if (bar) bar.style.width = `${Math.max(0, (left / total) * 100)}%`;
                }, 1000));
            },
        }).then((result) => (result === 'confirm' ? 'approve' : (result === 'cancel' ? 'reject' : null)));
    },

    /**
     * Tampilkan pesan flash Laravel yang ditanam di layout.
     *
     * Payload bisa berupa satu objek {type, message} (layout lama) atau
     * daftar objek (layout baru), sehingga beberapa pesan sekaligus
     * ditumpuk berurutan.
     */
    flashFromDom() {
        const el = document.getElementById('sipkl-flash');
        if (!el) return;

        try {
            const payload = JSON.parse(el.textContent);
            const list = Array.isArray(payload) ? payload : [payload];

            list.forEach(({ type, message }, index) => {
                if (!message) return;

                // beri jeda singkat agar beberapa toast tidak saling tumpang tindih
                setTimeout(() => window.sipklAlert.toast(type, message), index * 260);
            });
        } catch (e) {
            /* payload rusak -> abaikan, tidak boleh halt halaman */
        }
    },
};

/* ------------------------------------------------------------------ *
 *  Komponen Alpine
 * ------------------------------------------------------------------ */

/**
 * Field tautan yang bisa disalin — dipakai di halaman login dan di
 * "Perangkat Saya" untuk mengirim tautan ke perangkat baru.
 */
Alpine.data('copyable', (text = '', labelSalin = 'Salin', labelTersalin = 'Tersalin') => ({
    text,
    labelSalin,
    labelTersalin,
    copied: false,
    timer: null,

    async copy() {
        if (!this.text) return;

        try {
            await navigator.clipboard.writeText(this.text);
        } catch (e) {
            // Clipboard API butuh konteks aman (https/localhost).
            this.$refs.field?.select();
        }

        this.copied = true;
        clearTimeout(this.timer);
        this.timer = setTimeout(() => (this.copied = false), 2500);
    },

    destroy() {
        clearTimeout(this.timer);
    },
}));

/**
 * Konfirmasi submit form.
 *
 * Dipakai di form yang BERTINDAKAN BERBAHAYA (hapus, batalkan, keluar dari
 * semua perangkat). Submit pertama intercepted → dialog kustom tampil. Kalau
 * pengguna menyetujui, form dikirim ulang lewat `form.submit()` yang tidak
 * memicu event submit lagi, jadi tidak terjadi loop.
 *
 *   <form x-data="confirmForm({...})" x-on:submit="submit($event)">
 */
Alpine.data('confirmForm', (options = {}) => ({
    options,
    busy: false,

    async submit(event) {
        event.preventDefault();

        // cegah klik ganda saat dialog sedang tampil
        if (this.busy) return;
        this.busy = true;

        try {
            const ok = await window.sipklAlert.confirm(this.options);

            if (ok) {
                // submit() bawaan browser tidak memancarkan event submit
                event.target.submit();
            }
        } finally {
            this.busy = false;
        }
    },
}));

/**
 * Pemilih foto profil.
 *
 * Pratinjau dibuat di browser lewat object URL, jadi pengguna tidak perlu
 * menyimpan dulu untuk melihat hasilnya. Jenis berkas dan ukuran dicek
 * sebelum masuk form — validasi yang sama tetap diulang di server.
 *
 * @param {?string} currentUrl  foto yang sudah terpasang (null kalau belum)
 * @param {string} initials     huruf cadangan untuk avatar tanpa foto
 * @param {{maksKb?: number}} options
 */
Alpine.data('photoPicker', (currentUrl = null, initials = '?', options = {}) => ({
    fotoAwal: currentUrl || null,
    preview: currentUrl || null,
    initials: initials || '?',
    loading: false,
    dragAktif: false,
    galat: '',
    namaFile: '',
    ukuranFile: '',
    punyaFoto: Boolean(currentUrl),
    hapusDipilih: false,

    maksKb: options.maksKb || 5120,
    tipe: ['image/jpeg', 'image/png', 'image/webp'],

    /** Buka dialog pilih berkas. */
    buka() {
        this.$refs.input.click();
    },

    /** Dipanggil dari <input type="file">. */
    pilih(event) {
        const file = event.target.files?.[0];

        if (file) this.terima(file);
    },

    /** Dipanggil dari event drop. */
    tarik(event) {
        this.dragAktif = false;

        const file = event.dataTransfer?.files?.[0];

        if (file) this.terima(file);
    },

    /** Validasi ringan + buat pratinjau. */
    terima(file) {
        this.galat = '';

        if (!this.tipe.includes(file.type)) {
            this.gagal('Format foto harus JPG, PNG, atau WEBP.');

            return;
        }

        if (file.size > this.maksKb * 1024) {
            this.gagal(`Ukuran foto maksimal ${Math.round(this.maksKb / 1024)}MB.`);

            return;
        }

        this.hapusDipilih = false;
        this.namaFile = file.name;
        this.ukuranFile = this.formatUkuran(file.size);
        this.loading = true;

        // object URL lebih murah daripada FileReader untuk pratinjau
        if (this.preview?.startsWith?.('blob:')) URL.revokeObjectURL(this.preview);
        this.preview = URL.createObjectURL(file);
        this.punyaFoto = true;

        requestAnimationFrame(() => { this.loading = false; });
    },

    /** Buang pilihan berkas (bukan foto yang sudah tersimpan). */
    bersihkan() {
        if (this.preview?.startsWith?.('blob:')) URL.revokeObjectURL(this.preview);

        // kembali ke foto yang tersimpan di server, kalau ada
        this.preview = this.hapusDipilih ? null : this.fotoAwal;
        this.namaFile = '';
        this.ukuranFile = '';
        this.galat = '';
        this.hapusDipilih = false;
        this.punyaFoto = Boolean(this.fotoAwal);

        if (this.$refs.input) this.$refs.input.value = '';
    },

    /** Minta konfirmasi custom, lalu tandai foto untuk dihapus. */
    async hapusFoto() {
        const ok = await window.sipklAlert.confirm({
            title: 'Hapus foto profil?',
            text: 'Foto akan dihapus dan diganti dengan huruf awal nama setelah kamu tekan Simpan.',
            confirmText: 'Ya, hapus',
            cancelText: 'Batal',
            danger: true,
        });

        if (!ok) return;

        if (this.preview?.startsWith?.('blob:')) URL.revokeObjectURL(this.preview);

        this.preview = null;
        this.namaFile = '';
        this.ukuranFile = '';
        this.punyaFoto = false;
        this.hapusDipilih = true;
    },

    gagal(pesan) {
        this.galat = pesan;
        this.namaFile = '';
        this.ukuranFile = '';

        if (this.preview?.startsWith?.('blob:')) URL.revokeObjectURL(this.preview);
        if (!this.punyaFoto || this.hapusDipilih) this.preview = null;

        if (this.$refs.input) this.$refs.input.value = '';
    },

    formatUkuran(byte) {
        if (byte < 1024) return `${byte} B`;
        if (byte < 1024 * 1024) return `${(byte / 1024).toFixed(0)} KB`;

        return `${(byte / (1024 * 1024)).toFixed(1)} MB`;
    },

    destroy() {
        if (this.preview?.startsWith?.('blob:')) URL.revokeObjectURL(this.preview);
    },
}));

/** Panel login: satu form untuk semua peran, tanpa pemilihan peran. */
Alpine.data('authLogin', (oldIdentity = '', startQr = false) => ({
    method: startQr ? 'qr' : 'password', // 'password' | 'qr'
    identifier: oldIdentity,
    password: '',
    remember: false,
    showPassword: false,
    submitting: false,
}));

/**
 * Formulir pendaftaran: toggle sandi + indikator kekuatan. */
Alpine.data('registerForm', () => ({
    password: '',
    passwordConfirmation: '',
    showPassword: false,
    setuju: false,
    submitting: false,
    strength: 0,

    scorePassword(value) {
        const pw = value || '';
        if (!pw) return 0;

        let score = 0;
        if (pw.length >= 8) score++;
        if (pw.length >= 12) score++;
        if (/[a-z]/.test(pw) && /[A-Z]/.test(pw)) score++;
        if (/[0-9]/.test(pw) && /[^A-Za-z0-9]/.test(pw)) score++;

        return Math.min(score, 4);
    },

    get strengthLabel() {
        return ['Sangat lemah', 'Lemah', 'Cukup', 'Kuat', 'Sangat kuat'][this.strength] ?? '';
    },

    get strengthColor() {
        return ['bg-outline', 'bg-error', 'bg-secondary', 'bg-tertiary', 'bg-tertiary'][this.strength] ?? 'bg-outline';
    },

    get strengthTextColor() {
        return ['text-outline', 'text-error', 'text-secondary', 'text-tertiary', 'text-tertiary'][this.strength] ?? 'text-outline';
    },
}));

/**
 * Combobox "creatable" — SATU field yang bisa:
 *   1. memilih nilai yang sudah terdaftar (searchable dropdown), dan
 *   2. mengetik nilai baru yang belum ada di database.
 *
 * Nilai baru tidak pernah dianggap error: baris "+ Gunakan \"...\"" muncul
 * selama isian tidak persis sama dengan salah satu opsi, dan Enter menerima
 * apa pun yang diketik. Yang dikirim ke server tetap isi input itu sendiri,
 * jadi tidak ada input tersembunyi yang nilainya bisa tidak sinkron.
 *
 * @param {string[]} options  nilai yang sudah ada
 * @param {string}   initial  nilai awal (old input)
 */
Alpine.data('combobox', (options = [], initial = '') => ({
    options: options.map((value) => String(value)).filter((value) => value !== ''),
    value: String(initial ?? ''),
    query: String(initial ?? ''),
    open: false,
    highlight: 0,

    /** Opsi yang cocok dengan ketikan (pencocokan sebagian, mengabaikan huruf besar). */
    get filtered() {
        const q = this.query.trim().toLowerCase();
        if (!q) return this.options;
        return this.options.filter((option) => option.toLowerCase().includes(q));
    },

    /** Ketikan yang persis sama dengan salah satu opsi. */
    get hasExactMatch() {
        const q = this.query.trim().toLowerCase();
        return q !== '' && this.options.some((option) => option.toLowerCase() === q);
    },

    get customValue() {
        return this.query.trim();
    },

    /** Tampilkan "+ Gunakan ..." selama ada ketikan yang belum terdaftar. */
    get showCustomRow() {
        return this.customValue !== '' && !this.hasExactMatch;
    },

    /** Jumlah baris yang bisa dinavigasi (opsi + baris nilai baru). */
    get rowCount() {
        return this.filtered.length + (this.showCustomRow ? 1 : 0);
    },

    openPanel() {
        this.open = true;
        const q = this.query.trim().toLowerCase();
        const index = this.filtered.findIndex((option) => option.toLowerCase() === q);
        this.highlight = index >= 0 ? index : 0;
    },

    closePanel() {
        this.open = false;
        this.highlight = 0;
    },

    /** Ketikan = nilai yang akan dikirim; panel langsung terbuka. */
    onInput() {
        this.value = this.query;
        this.open = true;
        this.highlight = 0;
    },

    onBlur() {
        this.query = this.query.trim();
        this.value = this.query;
        this.closePanel();
    },

    pick(option) {
        this.value = option;
        this.query = option;
        this.closePanel();
        this.$nextTick(() => this.$refs.input.focus());
    },

    pickCustom() {
        if (this.customValue !== '') this.pick(this.customValue);
    },

    clear() {
        this.value = '';
        this.query = '';
        this.open = true;
        this.highlight = 0;
        this.$nextTick(() => this.$refs.input.focus());
    },

    /** Arrow Up / Arrow Down memutar antar baris. */
    move(step) {
        if (!this.open) {
            this.openPanel();
            return;
        }

        const total = this.rowCount;
        if (total === 0) return;

        this.highlight = (this.highlight + step + total) % total;
        this.$nextTick(() => {
            this.$refs.list?.children[this.highlight]?.scrollIntoView({ block: 'nearest' });
        });
    },

    /** Enter: pakai baris yang disorot; kalau tidak ada, pakai yang diketik. */
    accept() {
        const rows = this.filtered;

        if (this.highlight < rows.length) {
            this.pick(rows[this.highlight]);
            return;
        }

        this.pickCustom();
    },
}));

/* ------------------------------------------------------------------ *
 *  Login QR
 *  Device B: panel QR di halaman login (buat QR, hitung mundur, polling).
 *  Device A: pemindai kamera + notifikasi dashboard.
 *  Pustaka QR dimuat dinamis supaya halaman tanpa QR tidak ikut berat.
 * ------------------------------------------------------------------ */

/** Ambil token CSRF dari meta tag (dipasang di layout). */
const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

/** Permintaan POST JSON ke endpoint login QR. */
async function postQr(url, payload = {}) {
    const response = await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken(),
            Accept: 'application/json',
        },
        body: JSON.stringify(payload),
        credentials: 'same-origin',
    });

    if (!response.ok && response.status !== 422) {
        throw new Error(`HTTP ${response.status}`);
    }

    return response.json();
}

/** Perangkat baru — menunggu persetujuan atas QR yang sudah dibuka. */
Alpine.data('qrLoginPanel', (urls = {}, pollInterval = 2000) => ({
    status: 'idle', // idle | pending | scanned | awaiting_confirmation | approved | rejected | expired | used | error
    message: '',
    seconds: 0,
    pollTimer: null,
    pollInterval,

    start() {
        this.stopTimers();
        this.pollTimer = setInterval(() => this.poll(), this.pollInterval);
        setTimeout(() => this.poll(), 800);
    },

    async poll() {
        if (!this.isLive) return;

        let data;
        try {
            data = await postQr(urls.status);
        } catch (e) {
            return; // kegagalan sesaat: coba lagi pada tick berikutnya
        }

        this.status = data.status;
        this.message = data.message ?? '';
        this.seconds = data.expires_in ?? 0;

        if (data.status === 'approved') {
            this.stopTimers();
            this.$refs.claimForm?.submit();
        } else if (!this.isLive) {
            this.stopTimers();
        }
    },

    get isLive() {
        return ['idle', 'pending', 'scanned', 'awaiting_confirmation'].includes(this.status);
    },

    get countdown() {
        const m = String(Math.floor(this.seconds / 60)).padStart(2, '0');
        const s = String(this.seconds % 60).padStart(2, '0');

        return `${m}:${s}`;
    },

    stopTimers() {
        clearInterval(this.pollTimer);
        this.pollTimer = null;
    },

    destroy() {
        this.stopTimers();
    },
}));

/**
 * Dashboard — membuat QR milik akun ini, lalu menyalin tautannya untuk dikirim
 * ke perangkat baru. Perangkat baru yang membuka tautan itu akan menunggu
 *  persetujuan (notifikasi dialog) sebelum benar-benar masuk.
 */
Alpine.data('qrIssuer', (urls = {}, ttl = 60) => ({
    urls,
    ttl,
    status: 'idle', // idle | loading | pending | scanned | awaiting_confirmation | expired | error
    message: '',
    seconds: 0,
    svg: '',
    url: '',
    loading: false,
    tickTimer: null,

    get isLive() {
        return ['pending', 'scanned', 'awaiting_confirmation'].includes(this.status);
    },

    async create() {
        this.stopTimer();
        this.loading = true;
        this.status = 'loading';
        this.message = '';

        try {
            const data = await postQr(urls.issue);
            this.url = data.url;
            this.svg = await this.render(data.url);
            this.seconds = data.expires_in;
            this.status = data.status;
            this.startTimer();
        } catch (e) {
            this.status = 'error';
            // Bedakan "server menolak" dari "gambar QR gagal dirender" supaya
            // pesan di layar benar-benar accuse penyebabnya.
            this.message = e?.message === 'Pustaka QR tidak termuat.'
                ? 'Pustaka QR gagal dimuat. Muat ulang halaman (Ctrl+F5) lalu coba lagi.'
                : 'Gagal membuat QR. Muat ulang halaman lalu coba lagi.';
            console.error('[qr] gagal membuat QR:', e);
        } finally {
            this.loading = false;
        }
    },

    /** Gambar QR dibuat di browser; token hanya hidup di memori halaman ini. */
    async render(text) {
        const QRCode = await import('qrcode');

        // Vite membungkus modul yang di-bundle menjadi objek namespace, jadi
        // `toString` berada di `default`. Cek keduanya supaya tetap jalan
        // baik pada bundler lain.
        const generator = typeof QRCode?.toString === 'function'
            ? QRCode
            : QRCode?.default;

        if (typeof generator?.toString !== 'function') {
            throw new Error('Pustaka QR tidak termuat.');
        }

        // toString() mengembalikan Promise, jadi harus di-await.
        return await generator.toString(text, {
            type: 'svg',
            margin: 1,
            errorCorrectionLevel: 'M',
            color: { dark: '#0F172A', light: '#FFFFFF' },
        });
    },

    /** Salin tautan QR (dengan fallback select teks bila clipboard tidak ada). */
    async copyableCopy() {
        if (!this.url) return;

        try {
            await navigator.clipboard.writeText(this.url);
            window.sipklAlert.toast('success', 'Tautan QR tersalin. Kirim ke perangkat baru.');
        } catch (e) {
            this.$refs.link?.select();
            window.sipklAlert.toast('info', 'Teks disorot — tekan Ctrl+C untuk menyalin.');
        }
    },

    /** Hitung mundur lokal; sisi pengirim tidak perlu polling. */
    startTimer() {
        this.stopTimer();
        this.tickTimer = setInterval(() => {
            this.seconds = Math.max(0, this.seconds - 1);

            if (this.seconds === 0 && this.isLive) {
                this.status = 'expired';
                this.message = 'QR Code telah kedaluwarsa. Buat QR baru untuk melanjutkan.';
                this.stopTimer();
            }
        }, 1000);
    },

    reset() {
        this.stopTimer();
        this.status = 'idle';
        this.svg = '';
        this.url = '';
        this.seconds = 0;
        this.message = '';
    },

    stopTimer() {
        clearInterval(this.tickTimer);
        this.tickTimer = null;
    },

    get countdown() {
        const m = String(Math.floor(this.seconds / 60)).padStart(2, '0');
        const s = String(this.seconds % 60).padStart(2, '0');

        return `${m}:${s}`;
    },

    destroy() {
        this.stopTimer();
    },
}));

/**
 * Pemindai kamera — dipakai di halaman login (perangkat baru) untuk membaca
 * QR milik dashboard.
 */
Alpine.data('qrScanner', () => ({
    status: 'idle', // idle | starting | scanning | denied | error | found
    error: '',
    paste: '',
    pasteError: '',
    stream: null,
    frame: null,
    decoder: null,
    raf: 0,

    /**
     * Buka kode QR yang ditempel (tautan `/qr-login/{token}` atau token polos).
     * Belum login tetap aman: halaman menunggu akan menampilkan statusnya
     * sendiri, jadi tidak perlu lewat form masuk dulu.
     */
    pasteLink() {
        const nilai = this.paste.trim();
        const qr = nilai.match(/qr-login\/([A-Za-z0-9]{32,128})/) ?? (/^[A-Za-z0-9]{32,128}$/.test(nilai) ? [null, nilai] : null);

        if (!qr) {
            this.pasteError = 'Kode belum dikenali. Tempel tautan QR (/qr-login/…) atau token QR-nya.';

            return;
        }

        this.pasteError = '';
        window.location.href = `${window.location.origin}/qr-login/${qr[1]}`;
    },

    get supported() {
        return Boolean(navigator.mediaDevices?.getUserMedia);
    },

    async start() {
        this.reset();
        this.status = 'starting';

        try {
            this.stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'environment' },
                audio: false,
            });
        } catch (e) {
            this.status = e.name === 'NotAllowedError' ? 'denied' : 'error';
            this.error =
                e.name === 'NotAllowedError'
                    ? 'Izin kamera ditolak. Aktifkan izin kamera pada pengaturan browser, atau salin tautan QR lalu tempel di halaman masuk.'
                    : 'Kamera tidak dapat dibuka. Pastikan tidak ada aplikasi lain yang memakai kamera.';

            return;
        }

        const { default: jsQR } = await import('jsqr');
        this.decoder = jsQR;

        const video = this.$refs.video;
        video.srcObject = this.stream;
        video.setAttribute('playsinline', 'true');
        await video.play();

        this.status = 'scanning';
        this.tick();
    },

    tick() {
        const video = this.$refs.video;

        if (!this.stream || !video || video.readyState !== video.HAVE_ENOUGH_DATA) {
            this.raf = requestAnimationFrame(() => this.tick());
            return;
        }

        const canvas = this.$refs.canvas;
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        const ctx = canvas.getContext('2d', { willReadFrequently: true });
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

        const image = ctx.getImageData(0, 0, canvas.width, canvas.height);
        const found = this.decoder(image.data, image.width, image.height, {
            inversionAttempts: 'dontInvert',
        });

        if (found?.data) {
            this.handleCode(found.data);
            return;
        }

        this.raf = requestAnimationFrame(() => this.tick());
    },

    /** Ambil token dari QR dan buka halaman konfirmasi di origin kita. */
    handleCode(text) {
        const token = (text.match(/qr-login\/([A-Za-z0-9]{32,128})/) ?? [])[1];

        if (!token) {
            this.status = 'error';
            this.error = 'QR ini bukan QR login SIPKL.';

            return;
        }

        this.status = 'found';
        this.stop();
        window.location.href = `${window.location.origin}/qr-login/${token}`;
    },

    stop() {
        cancelAnimationFrame(this.raf);

        if (this.stream) {
            this.stream.getTracks().forEach((track) => track.stop());
            this.stream = null;
        }

        const video = this.$refs.video;
        if (video) video.srcObject = null;
    },

    reset() {
        this.stop();
        this.status = 'idle';
        this.error = '';
    },

    destroy() {
        this.stop();
    },
}));

/**
 * Device A — notifikasi permintaan login QR yang masuk ke dashboard.
 *
 * Dipasang di layout aplikasi (semua peran), jadi permintaan yang_READ
 * di perangkat ini atau dibuka lewat tautan langsung memunculkan notifikasi
 *  dialog persetujuan di atas halaman, lalu user memilih Izinkan / Tolak.
 */
Alpine.data('qrApprover', (urls = {}, interval = 5000) => ({
    urls,
    interval,
    timer: null,
    shown: null,

    start() {
        this.stop();
        this.timer = setInterval(() => this.check(), this.interval);
        // Jangan langsungspam notifikasi saat halaman baru dibuka.
        setTimeout(() => this.check(), 1500);
    },

    stop() {
        clearInterval(this.timer);
        this.timer = null;
    },

    async check() {
        if (this.shown !== null || document.hidden) return;

        let data;
        try {
            const response = await fetch(this.urls.pending, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
                credentials: 'same-origin',
            });
            if (!response.ok) return;
            data = await response.json();
        } catch (e) {
            return;
        }

        if (!data || data.status === 'none') return;

        this.shown = data.id;
        window.sipklAlert.toast('info', 'Ada permintaan login QR dari perangkat lain.');

        const aksi = await window.sipklAlert.qrRequest({
            device: data.device_name,
            browser: data.browser,
            platform: data.platform,
            lokasi: data.lokasi,
            seconds: data.expires_in,
        });

        this.shown = null;

        if (!aksi) return;

        try {
            const response = await fetch(`${this.urls.decideBase}/${data.id}/${aksi}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                    Accept: 'application/json',
                },
                credentials: 'same-origin',
                body: '{}',
            });
            const hasil = await response.json();

            window.sipklAlert.toast(
                hasil.ok ? (aksi === 'approve' ? 'success' : 'warning') : 'error',
                hasil.message ?? 'Permintaan diproses.'
            );
        } catch (e) {
            window.sipklAlert.toast('error', 'Gagal memproses permintaan.');
        }
    },

    destroy() {
        this.stop();
    },
}));

Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
    window.sipklAlert.flashFromDom();
});
