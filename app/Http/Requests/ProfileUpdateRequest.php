<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validasi pembaruan profil: nama, email, dan foto profil.
 *
 * Foto profil divalidasi berlapis:
 *   1. harus file gambar yang benar-benar terunggah,
 *   2. ekstensi di whitelist (jpg, jpeg, png, webp),
 *   3. MIME asli hasil pemeriksaan isi file — bukan header browser,
 *   4. ukuran maksimum 5MB.
 *
 * `hapus_foto` sengaja terpisah dari `foto` supaya "hapus foto" dan
 * "ganti foto" tidak saling tumpang tindih dalam satu request.
 */
class ProfileUpdateRequest extends FormRequest
{
    /** MIME asli yang diterima per ekstensi. */
    private const MIME_FOTO = [
        'jpg' => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png' => ['image/png'],
        'webp' => ['image/webp'],
    ];

    /** Batas keras ukuran foto (byte). */
    private const MAKS_BYTES = 5 * 1024 * 1024;

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'foto' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:' . (self::MAKS_BYTES / 1024)],
            'hapus_foto' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'foto.mimes' => 'Format foto harus JPG, PNG, atau WEBP.',
            'foto.max' => 'Ukuran foto maksimal 5MB.',
            'foto.file' => 'Foto yang dipilih tidak valid.',
            'hapus_foto.boolean' => 'Permintaan hapus foto tidak valid.',
        ];
    }

    /**
     * Aturan `mimes` hanya membaca nama file yang dikirim browser, jadi
     * MIME asli isi file dicek ulang di sini.
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                $file = $this->file('foto');

                if (! $file || ! $file->isValid()) {
                    return;
                }

                $ext = strtolower($file->getClientOriginalExtension());

                if (! isset(self::MIME_FOTO[$ext])) {
                    return;
                }

                if (! in_array($file->getMimeType(), self::MIME_FOTO[$ext], true)) {
                    $validator->errors()->add(
                        'foto',
                        "Isi file tidak sesuai dengan ekstensi .{$ext} (terbaca: {$file->getMimeType()})."
                    );
                }
            },
        ];
    }

    /** Batas ukuran dalam KB, untuk ditampilkan di view. */
    public static function maksKb(): int
    {
        return (int) (self::MAKS_BYTES / 1024);
    }
}
