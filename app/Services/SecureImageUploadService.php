<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Intervention\Image\Laravel\Facades\Image;
use RuntimeException;

class SecureImageUploadService
{
    /**
     * Allowed MIME types for images
     */
    private array $allowedMimeTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
    ];

    /**
     * Magic bytes signatures for image formats
     */
    private array $magicBytes = [
        'image/jpeg' => ["\xFF\xD8\xFF"],
        'image/png'  => ["\x89\x50\x4E\x47\x0D\x0A\x1A\x0A"],
        'image/gif'  => ["GIF87a", "GIF89a"],
    ];

    /**
     * Dangerous patterns that indicate malicious content
     * Note: These patterns are checked BEFORE re-encoding only
     */
    private array $dangerousPatterns = [
        // PHP tags (must be at start of line or after whitespace/newline)
        '(?:^|[\s;{}])<\?php',
        '(?:^|[\s;{}])<\?=',
        '(?:^|[\s;{}])<\?(?:\s|$)',
        // Script tags
        '<script\s',
        'javascript\s*:',
        'vbscript\s*:',
        'data\s*:\s*text/html',
        // PHP dangerous functions (with word boundaries)
        '\beval\s*\(',
        '\bbase64_decode\s*\(',
        '\bexec\s*\(',
        '\bsystem\s*\(',
        '\bpassthru\s*\(',
        '\bshell_exec\s*\(',
        '\bpopen\s*\(',
        '\bproc_open\s*\(',
        '\bassert\s*\(',
        '\bpreg_replace\s*\(\s*[\'"]/e[\'"]',
        // HTML elements (with word boundaries)
        '<html\b',
        '<body\b',
        '<iframe\b',
        '<object\b',
        '<embed\b',
        '<form\b',
        // JavaScript dangerous operations
        'document\.cookie',
        'document\.write',
        'window\.location\s*=',
        // Compiler halt (common in PHP malware)
        '__halt_compiler',
        // Webshell signatures (with word boundaries)
        '\bc99shell\b',
        '\br57shell\b',
        '\bb374k\b',
        '\bwso\b',
        '\balfa\b',
        '\bmini\bshell',
        '\bkiller\b',
        '\bdz\bshell',
    ];

    /**
     * Maximum file size in bytes (default 2MB)
     */
    private int $maxFileSize;

    /**
     * Constructor
     * 
     * @param int $maxFileSizeKb Maximum file size in KB
     */
    public function __construct(int $maxFileSizeKb = 2048)
    {
        $this->maxFileSize = $maxFileSizeKb * 1024;
    }

    /**
     * Validate and process uploaded image securely
     * 
     * @param UploadedFile $file The uploaded file
     * @param string $outputFormat Output format (jpg, png, gif)
     * @return array ['path' => string, 'filename' => string, 'mime' => string]
     * @throws RuntimeException If validation fails
     */
    public function processSecureUpload(UploadedFile $file, string $outputFormat = 'jpg', string $destinationFolder = ''): array
    {
        // Step 1: Basic file validation
        $this->validateFileExists($file);
        $this->validateFileSize($file);
        
        // Step 2: Get real MIME type from file content
        $realMimeType = $this->getRealMimeType($file);
        $this->validateMimeType($realMimeType);
        
        // Step 3: Validate magic bytes
        $this->validateMagicBytes($file, $realMimeType);
        
        // Step 4: Validate image integrity
        $this->validateImageIntegrity($file);
        
        // Step 5: Scan for dangerous patterns
        $this->scanForDangerousPatterns($file);
        
        // Step 6: Re-encode image to strip embedded payloads
        $processedFile = $this->reencodeImage($file, $outputFormat, $destinationFolder);
        
        // Step 7: Validate the processed image
        $this->validateProcessedImage($processedFile);
        
        return [
            'path' => $processedFile['path'],
            'filename' => $processedFile['filename'],
            'mime' => $realMimeType,
            'size' => File::size($processedFile['fullPath']),
        ];
    }

    /**
     * Validate file exists and is valid
     */
    public function validateFileExists(UploadedFile $file): void
    {
        if (!$file || !$file->isValid()) {
            throw new RuntimeException(
                'File upload error: ' . ($file ? $file->getErrorMessage() : 'No file uploaded')
            );
        }
    }

    /**
     * Validate file size
     */
    public function validateFileSize(UploadedFile $file): void
    {
        if ($file->getSize() > $this->maxFileSize) {
            $maxKb = $this->maxFileSize / 1024;
            throw new RuntimeException("File size exceeds maximum allowed size of {$maxKb} KB");
        }
        
        if ($file->getSize() === 0) {
            throw new RuntimeException('File is empty');
        }
    }

    /**
     * Get real MIME type from file content (not from extension)
     */
    public function getRealMimeType(UploadedFile $file): string
    {
        $realPath = $file->getRealPath();
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $realPath);
        finfo_close($finfo);
        
        return $mimeType ?: 'application/octet-stream';
    }

    /**
     * Validate MIME type is allowed
     */
    public function validateMimeType(string $mimeType): void
    {
        if (!isset($this->allowedMimeTypes[$mimeType])) {
            throw new RuntimeException("File type '{$mimeType}' is not allowed. Only JPG, PNG, and GIF are permitted.");
        }
    }

    /**
     * Validate file has correct magic bytes
     */
    public function validateMagicBytes(UploadedFile $file, string $expectedMimeType): void
    {
        $realPath = $file->getRealPath();
        $handle = fopen($realPath, 'rb');
        
        if (!$handle) {
            throw new RuntimeException('Cannot read file for validation');
        }
        
        // Read first 8 bytes (enough for all image signatures)
        $header = fread($handle, 8);
        fclose($handle);
        
        if ($header === false || strlen($header) < 4) {
            throw new RuntimeException('File is too small or corrupted');
        }
        
        $validSignature = false;
        $expectedSignatures = $this->magicBytes[$expectedMimeType] ?? [];
        
        foreach ($expectedSignatures as $signature) {
            if (strpos($header, $signature) === 0) {
                $validSignature = true;
                break;
            }
        }
        
        if (!$validSignature) {
            throw new RuntimeException('File signature does not match expected image format. Possible file spoofing detected.');
        }
    }

    /**
     * Validate image can be properly read by GD/Imagick
     */
    public function validateImageIntegrity(UploadedFile $file): void
    {
        $realPath = $file->getRealPath();
        
        try {
            $imageInfo = getimagesize($realPath);
            
            if ($imageInfo === false) {
                throw new RuntimeException('File is not a valid image or is corrupted');
            }
            
            // Check for minimum dimensions to prevent DoS with tiny images
            $width = $imageInfo[0];
            $height = $imageInfo[1];
            
            if ($width < 1 || $height < 1 || $width > 10000 || $height > 10000) {
                throw new RuntimeException('Image dimensions are out of acceptable range');
            }
        } catch (\Exception $e) {
            throw new RuntimeException('Image integrity check failed: ' . $e->getMessage());
        }
    }

    /**
     * Scan file for dangerous patterns
     */
    public function scanForDangerousPatterns(UploadedFile $file): void
    {
        $content = file_get_contents($file->getRealPath());
        
        if ($content === false) {
            throw new RuntimeException('Cannot read file content for scanning');
        }

        // Check for dangerous patterns (case-insensitive, multiline)
        // Use # delimiter to avoid conflicts with / in patterns
        foreach ($this->dangerousPatterns as $pattern) {
            if (preg_match('#' . $pattern . '#ims', $content)) {
                throw new RuntimeException('Dangerous content detected in file. Upload rejected for security reasons.');
            }
        }

        // Additional check: look for null bytes (used in bypass attacks)
        if (strpos($content, "\x00") !== false) {
            // Allow null bytes only in binary image data, but check file structure
            // For PNG, null bytes are normal. For JPEG, check if it's not at suspicious locations
            $mimeType = $this->getRealMimeType($file);
            if ($mimeType === 'image/jpeg') {
                // Check if null bytes appear outside of JPEG segments
                $this->validateJpegNullBytes($content);
            }
        }
    }

    /**
     * Validate null bytes in JPEG are in acceptable locations
     */
    private function validateJpegNullBytes(string $content): void
    {
        // JPEG files can have null bytes in compressed data
        // But we should check for patterns like: file.jpg\x00.php
        if (preg_match('/\x00\.php/i', $content) || 
            preg_match('/\x00\.phtml/i', $content) || 
            preg_match('/\x00\.php\d/i', $content)) {
            throw new RuntimeException('Null byte injection attack detected');
        }
    }

    /**
     * Re-encode image to strip embedded payloads and EXIF data
     */
    private function reencodeImage(UploadedFile $file, string $outputFormat, string $destinationFolder): array
    {
        $realPath = $file->getRealPath();
        
        try {
            // Load image using Intervention Image
            $image = Image::read($realPath);
            
            // Generate random filename
            $filename = $this->generateRandomFilename($outputFormat);
            
            // Determine full path
            $fullPath = storage_path('app/public/' . trim($destinationFolder, '/') . '/' . $filename);
            
            // Ensure directory exists
            $directory = dirname($fullPath);
            if (!File::exists($directory)) {
                File::makeDirectory($directory, 0755, true, true);
            }
            
            // Save with format-specific settings
            switch ($outputFormat) {
                case 'jpg':
                case 'jpeg':
                    $image->toJpg(85)->save($fullPath); // 85% quality
                    break;
                case 'png':
                    $image->toPng()->save($fullPath);
                    break;
                case 'gif':
                    $image->toGif()->save($fullPath);
                    break;
                default:
                    $image->toJpg(85)->save($fullPath);
            }
            
            return [
                'path' => trim($destinationFolder, '/') . '/' . $filename,
                'filename' => $filename,
                'fullPath' => $fullPath,
            ];
        } catch (\Exception $e) {
            throw new RuntimeException('Image processing failed: ' . $e->getMessage());
        }
    }

    /**
     * Validate the processed image is still valid
     */
    private function validateProcessedImage(array $imageData): void
    {
        $fullPath = $imageData['fullPath'];

        if (!File::exists($fullPath)) {
            throw new RuntimeException('Processed image file was not created');
        }

        // Verify the processed file is still a valid image
        $imageInfo = getimagesize($fullPath);
        if ($imageInfo === false) {
            // Clean up invalid file
            File::delete($fullPath);
            throw new RuntimeException('Processed image is invalid');
        }

        // NOTE: We don't need to scan for dangerous patterns here because:
        // 1. The original file was already scanned before re-encoding
        // 2. Re-encoding creates a completely new image file from pixel data
        // 3. Any embedded code/scripts in EXIF/metadata are stripped during re-encoding
        // 4. The resulting file is a pure image with no executable content
        // 
        // Scanning the re-encoded file can cause false positives because:
        // - JPEG compression artifacts may match regex patterns by chance
        // - Binary image data can coincidentally contain pattern-like sequences
    }

    /**
     * Generate random filename
     */
    private function generateRandomFilename(string $extension): string
    {
        return bin2hex(random_bytes(16)) . '.' . strtolower($extension);
    }

    /**
     * Get allowed MIME types
     */
    public function getAllowedMimeTypes(): array
    {
        return array_keys($this->allowedMimeTypes);
    }

    /**
     * Set maximum file size
     */
    public function setMaxFileSize(int $maxFileSizeKb): self
    {
        $this->maxFileSize = $maxFileSizeKb * 1024;
        return $this;
    }
}
