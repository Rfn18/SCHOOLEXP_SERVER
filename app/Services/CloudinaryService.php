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
                'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                'api_key'    => env('CLOUDINARY_API_KEY'),
                'api_secret' => env('CLOUDINARY_API_SECRET'),
            ],
            'url' => ['secure' => true]
        ]);
        
        $this->cloudinary = new Cloudinary();
    }

    public function upload(UploadedFile $file, string $folder = 'documentations'): string
    {
        $result = $this->cloudinary->uploadApi()->upload($file->getRealPath(), [
            'folder' => $folder,
            'resource_type' => 'auto'
        ]);
        
        return $result['secure_url']; 
    }

    public function delete(string $url): void
    {
        if (!$url || !str_contains($url, 'cloudinary')) return;
        
        try {
            $path = parse_url($url, PHP_URL_PATH);
            preg_match('/\/upload\/(?:v\d+\/)?(.+)\.\w+$/', $path, $matches);
            
            if (isset($matches[1])) {
                $this->cloudinary->uploadApi()->destroy($matches[1]);
            }
        } catch (\Exception $e) {
            \Log::warning('Gagal hapus file di Cloudinary: ' . $e->getMessage());
        }
    }
}