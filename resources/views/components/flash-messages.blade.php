{{--
    Flash message + ringkasan validasi, dirender ke <body> lalu
    ditampilkan sebagai toast oleh window.sipklAlert (resources/js/app.js).

    Dipakai di layout app (dashboard) maupun layout guest (login/register),
    supaya hasil setiap aksi create / update / delete selalu terlihat.

    Kunci flash yang dikenali:
      success, status, message, info, warning, error, swal_error
--}}
@php
    $petunjuk = [
        'success' => 'success',
        'status' => 'success',
        'message' => 'info',
        'info' => 'info',
        'warning' => 'warning',
        'error' => 'error',
        'swal_error' => 'error',
    ];

    $toast = [];

    foreach ($petunjuk as $kunci => $tipe) {
        $nilai = session($kunci);

        if (is_string($nilai) && trim($nilai) !== '') {
            $toast[] = ['type' => $tipe, 'message' => $nilai];
        }

        session()->forget($kunci);
    }

    // Ringkasan validasi: pesan per-field tetap tampil inline di form,
    // toast ini hanya penanda agar pengguna tahu ada yang perlu diperbaiki.
    if ($errors->any()) {
        $pertama = collect($errors->all())->first();

        $toast[] = [
            'type' => 'warning',
            'message' => $pertama ?: 'Periksa kembali isian yang ditandai.',
        ];
    }
@endphp

@if (count($toast) > 0)
    <script type="application/json" id="sipkl-flash">
        {!! json_encode($toast, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}
    </script>
@endif
