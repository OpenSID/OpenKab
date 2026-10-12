<?php

namespace App\Traits;

use App\Services\GenericFileUploadService;
use App\Services\SecureImageUploadService;
use Illuminate\Http\Request;
use RuntimeException;

trait UploadedFile
{
    protected $pathFolder = 'uploads';

    /**
     * Upload file dengan validasi keamanan
     *
     * @param Request $request
     * @param string $name
     * @param string|null $outputFormat Format output: jpg, png, gif (null untuk non-image)
     * @param int $maxSizeKb Ukuran maksimal dalam KB
     * @return string Path relatif file yang diupload
     * @throws RuntimeException Jika validasi atau upload gagal
     */
    protected function uploadFile(Request $request, $name, ?string $outputFormat = 'jpg', int $maxSizeKb = 2048): string
    {
        $file = $request->file($name);

        if (empty($file)) {
            throw new RuntimeException('file '.$name.' is required');
        }

        if (! $file->isValid()) {
            throw new RuntimeException($file->getErrorString().'('.$file->getError().')');
        }

        // For image files, use secure image upload service
        if ($outputFormat !== null) {
            $secureService = new SecureImageUploadService($maxSizeKb);

            try {
                $result = $secureService->processSecureUpload(
                    $file,
                    $outputFormat,
                    $this->pathFolder
                );

                return $result['path'];
            } catch (\Exception $e) {
                throw new RuntimeException('Upload gagal: ' . $e->getMessage());
            }
        }

        // For non-image files, use generic file upload service
        $genericService = new GenericFileUploadService($maxSizeKb, $this->pathFolder);
        
        try {
            $result = $genericService->processUpload($file);
            return $result['path'];
        } catch (\Exception $e) {
            throw new RuntimeException('Upload gagal: ' . $e->getMessage());
        }
    }
}
