<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;

/**
 * Wrapper tipis di atas cloudinary-labs/cloudinary-laravel - dipusatkan di
 * sini (bukan panggil cloudinary() langsung di tiap controller) biar kalau
 * suatu saat ganti provider storage, cukup ubah 1 file.
 *
 * API cloudinary()->upload()->getSecurePath()/getPublicId() dikonfirmasi
 * dari dokumentasi resmi package (bukan tebakan) - TAPI belum pernah
 * dites end-to-end ke akun Cloudinary asli project ini, jadi tetap perlu
 * 1x tes upload manual sebelum dianggap final.
 */
class CloudinaryUploadService
{
    /**
     * @return array{url: string, public_id: string}
     */
    public function upload(UploadedFile $file, string $folder): array
    {
        $result = cloudinary()->upload($file->getRealPath(), [
            'folder' => $folder,
        ]);

        return [
            'url' => $result->getSecurePath(),
            'public_id' => $result->getPublicId(),
        ];
    }

    public function destroy(?string $publicId): void
    {
        if (! $publicId) {
            return;
        }

        // Kebijakan error: hapus Cloudinary gagal JANGAN sampai ngeblok
        // proses utama (mis. update produk) - log doang, gak throw. File
        // nyangkut di Cloudinary itu masalah kecil (bisa dibersihin manual
        // nanti), beda kelas sama gagal simpan data ke database.
        try {
            cloudinary()->destroy($publicId);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
