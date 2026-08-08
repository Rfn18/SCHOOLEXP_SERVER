<?php

namespace App\Services;

use Cloudinary\Cloudinary;
use Cloudinary\Configuration\Configuration;
use Illuminate\Http\UploadedFile;

class CloudinaryService
{
    protected Cloudinary $cloudinary;

    public function __construct()
    {
        Configuration::instance([
            'cloud' => [
                'cloud_name' => config('cloudinary.cloud_name'),
                'api_key'    => config('cloudinary.api_key'),
                'api_secret' => config('cloudinary.api_secret'),
            ],
            'url' => ['secure' => true],
        ]);

        $this->cloudinary = new Cloudinary();
    }

    /**
     * Upload file dan kembalikan PATH saja (bukan full URL).
     * Contoh return: "documentation/phbn-2026/lws1eky5x74jdggrh0oa.jpg"
     */
    public function upload(UploadedFile $file, string $folder = 'documentation'): string
    {
        $result = $this->cloudinary->uploadApi()->upload($file->getRealPath(), [
            'folder' => $folder,
            'resource_type' => 'auto',
        ]);

        // public_id = "documentation/phbn-2026/lws1eky5x74jdggrh0oa"
        // format    = "jpg"
        return $result['public_id'] . '.' . $result['format'];
    }

    /**
     * Delete berdasarkan PATH (bukan URL lagi).
     */
    public function delete(?string $path): void
    {
        if (!$path) return;

        try {
            // Buang ekstensi untuk dapat public_id murni
            $publicId = preg_replace('/\.\w+$/', '', $path);

            $this->cloudinary->uploadApi()->destroy($publicId);
        } catch (\Exception $e) {
            \Log::warning('Gagal hapus file di Cloudinary: ' . $e->getMessage());
        }
    }

    /**
     * Bangun full URL dari path tersimpan, dipakai saat display.
     */
    public function url(?string $path): ?string
    {
        if (!$path) return null;
        $cloudName = config('cloudinary.cloud_name');
        return "https://res.cloudinary.com/{$cloudName}/image/upload/{$path}";
    }
}