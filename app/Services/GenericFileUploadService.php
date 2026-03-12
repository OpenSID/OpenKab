<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use RuntimeException;

class GenericFileUploadService
{
    /**
     * Dangerous patterns that indicate malicious content
     * (Subset dari SecureImageUploadService, disesuaikan untuk generic files)
     */
    private array $dangerousPatterns = [
        // PHP tags
        '<\?php',
        '<\?',
        // Script tags
        '<script',
        'javascript:',
        // Dangerous PHP functions
        'eval\s*\(',
        'system\s*\(',
        'exec\s*\(',
        'shell_exec\s*\(',
        'passthru\s*\(',
        'popen\s*\(',
        'proc_open\s*\(',
        // Compiler halt
        '__halt_compiler',
    ];

    /**
     * Maximum file size in bytes
     */
    private int $maxFileSize;

    /**
     * Destination folder
     */
    private string $destinationFolder;

    /**
     * Constructor
     *
     * @param int $maxFileSizeKb Maximum file size in KB
     * @param string $destinationFolder Destination folder relative to storage/app/public
     */
    public function __construct(int $maxFileSizeKb = 5120, string $destinationFolder = 'uploads')
    {
        $this->maxFileSize = $maxFileSizeKb * 1024;
        $this->destinationFolder = $destinationFolder;
    }

    /**
     * Upload and validate generic file securely
     *
     * @param UploadedFile $file The uploaded file
     * @return array ['path' => string, 'filename' => string, 'mime' => string, 'size' => int]
     * @throws RuntimeException If validation or upload fails
     */
    public function processUpload(UploadedFile $file): array
    {
        // Step 1: Validate file exists and is valid
        $this->validateFileExists($file);
        
        // Step 2: Validate file size
        $this->validateFileSize($file);
        
        // Step 3: Scan for dangerous patterns
        $this->scanForDangerousPatterns($file);
        
        // Step 4: Generate random filename and save
        $result = $this->saveFile($file);
        
        return $result;
    }

    /**
     * Validate file exists and is valid
     */
    private function validateFileExists(UploadedFile $file): void
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
    private function validateFileSize(UploadedFile $file): void
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
     * Scan file for dangerous patterns
     */
    private function scanForDangerousPatterns(UploadedFile $file): void
    {
        $content = file_get_contents($file->getRealPath());
        
        if ($content === false) {
            throw new RuntimeException('Cannot read file content for scanning');
        }

        // Check for dangerous patterns (case-insensitive)
        foreach ($this->dangerousPatterns as $pattern) {
            $escapedPattern = $this->escapePattern($pattern);
            
            if (preg_match('/' . $escapedPattern . '/i', $content)) {
                throw new RuntimeException('Dangerous content detected in file. Upload rejected for security reasons.');
            }
        }
    }

    /**
     * Save file with random filename
     */
    private function saveFile(UploadedFile $file): array
    {
        // Generate random filename with original extension
        $extension = strtolower($file->getClientOriginalExtension());
        $filename = bin2hex(random_bytes(16)) . '.' . $extension;
        
        // Determine full path
        $fullPath = storage_path('app/public/' . trim($this->destinationFolder, '/') . '/' . $filename);
        
        // Ensure directory exists
        $directory = dirname($fullPath);
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true, true);
        }

        // Get file content BEFORE moving (to avoid temp file deletion issue)
        $realPath = $file->getRealPath();
        if (!$realPath || !file_exists($realPath)) {
            throw new RuntimeException('Uploaded file not found');
        }
        
        $content = file_get_contents($realPath);
        if ($content === false) {
            throw new RuntimeException('Failed to read uploaded file content');
        }

        // Save content to destination
        $bytesWritten = file_put_contents($fullPath, $content);
        if ($bytesWritten === false) {
            throw new RuntimeException('Failed to save uploaded file');
        }

        return [
            'path' => trim($this->destinationFolder, '/') . '/' . $filename,
            'filename' => $filename,
            'fullPath' => $fullPath,
            'mime' => $this->getMimeType($file),
            'size' => File::size($fullPath),
        ];
    }

    /**
     * Get MIME type from file
     */
    private function getMimeType(UploadedFile $file): string
    {
        $realPath = $file->getRealPath();
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $realPath);
        finfo_close($finfo);
        
        return $mimeType ?: 'application/octet-stream';
    }

    /**
     * Escape pattern for regex
     */
    private function escapePattern(string $pattern): string
    {
        return str_replace(
            ['\\', '/', '.', '^', '$', '*', '+', '?', '[', ']', '(', ')', '{', '}', '|'],
            ['\\\\', '\\/', '\\.', '\\^', '\\$', '\\*', '\\+', '\\?', '\\[', '\\]', '\\(', '\\)', '\\{', '\\}', '\\|'],
            $pattern
        );
    }

    /**
     * Set maximum file size
     */
    public function setMaxFileSize(int $maxFileSizeKb): self
    {
        $this->maxFileSize = $maxFileSizeKb * 1024;
        return $this;
    }

    /**
     * Set destination folder
     */
    public function setDestinationFolder(string $folder): self
    {
        $this->destinationFolder = $folder;
        return $this;
    }
}
