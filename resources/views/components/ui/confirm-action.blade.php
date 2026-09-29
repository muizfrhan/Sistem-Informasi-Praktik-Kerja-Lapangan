@props([
    'form',              // id form yang akan disubmit setelah konfirmasi
    'title' => 'Yakin melanjutkan?',
    'message' => null,
    'confirmText' => 'Ya, lanjutkan',
    'cancelText' => 'Batal',
    'danger' => true,
])

{{-- Dialog konfirmasi kustom (window.sipklAlert.confirm) sebelum submit form. --}}
<button type="button"
    x-data
    x-on:click="window.sipklAlert.confirm({
        title: @js($title),
        text: @js($message),
        confirmText: @js($confirmText),
        cancelText: @js($cancelText),
        danger: @js((bool) $danger),
    }).then((ok) => { if (ok) document.getElementById(@js($form)).submit() })"
    {{ $attributes->merge(['class' => 'inline-flex items-center']) }}>
    {{ $slot }}
</button>
