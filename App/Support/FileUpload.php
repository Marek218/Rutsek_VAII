<?php

namespace App\Support;

use App\Configuration;

class FileUpload
{
    // Allowed extensions and their MIME types
    public const ALLOWED_EXTENSIONS = [
        'jpg' => ['image/jpeg', 'image/pjpeg', 'image/jpg'],
        'jpeg' => ['image/jpeg', 'image/pjpeg', 'image/jpg'],
        'png' => ['image/png', 'image/x-png'],
        'webp' => ['image/webp']
    ];

    // Max file size in bytes (default 5 MB)
    public const MAX_FILE_SIZE = 5242880;

    /**
     * Validate uploaded file object (implements methods used by Framework\Http\UploadedFile)
     * Returns array [ok => bool, error => string|null]
     */
    public static function validateUploadedFile($file): array
    {
        if (!$file) {
            return ['ok' => false, 'error' => 'No file provided'];
        }
        if (method_exists($file, 'isOk') && !$file->isOk()) {
            $msg = method_exists($file, 'getErrorMessage') ? $file->getErrorMessage() : 'Upload failed or file not completely uploaded';
            return ['ok' => false, 'error' => $msg];
        }

        // size
        $size = null;
        if (method_exists($file, 'getSize')) { $size = (int)$file->getSize(); }
        if ($size === null || $size <= 0) {
            // try reading tmp file
            if (method_exists($file, 'getFileTempPath')) {
                $tmp = $file->getFileTempPath();
                if ($tmp && is_file($tmp)) { $size = filesize($tmp); }
            }
        }
        if ($size === null) { return ['ok' => false, 'error' => 'Unable to determine file size']; }
        if ($size > self::MAX_FILE_SIZE) { return ['ok' => false, 'error' => 'File is too large']; }

        // original name and extension
        $orig = method_exists($file, 'getName') ? (string)$file->getName() : '';
        $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
        if ($ext === '') { return ['ok' => false, 'error' => 'Missing file extension']; }
        if (!array_key_exists($ext, self::ALLOWED_EXTENSIONS)) { return ['ok' => false, 'error' => 'Invalid file extension']; }

        // tmp name and mime detection
        $tmpName = null;
        if (method_exists($file, 'getFileTempPath')) { $tmpName = $file->getFileTempPath(); }
        if (!$tmpName || !is_file($tmpName)) {
            return ['ok' => false, 'error' => 'Temporary file not found'];
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $tmpName);
        finfo_close($finfo);
        if (!in_array($mime, self::ALLOWED_EXTENSIONS[$ext], true)) {
            return ['ok' => false, 'error' => 'Invalid MIME type: ' . $mime];
        }

        return ['ok' => true, 'error' => null, 'ext' => $ext, 'size' => $size, 'orig' => $orig, 'mime' => $mime, 'tmp' => $tmpName];
    }

    /**
     * Stores the validated uploaded file into the target directory (inside public/uploads/gallery)
     * Returns array [ok => bool, path => relative_path_on_success, error => string|null]
     */
    public static function storeGalleryFile($file, string $subdir = 'gallery') : array
    {
        $valid = self::validateUploadedFile($file);
        if (!$valid['ok']) { return ['ok' => false, 'error' => $valid['error'] ?? 'Validation failed']; }

        $orig = $valid['orig'];
        $ext = $valid['ext'];

        $publicDir = realpath(__DIR__ . '/../../public');
        if ($publicDir === false) { return ['ok' => false, 'error' => 'Public dir not found']; }

        $uploadDir = rtrim($publicDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . Configuration::UPLOAD_DIR . trim($subdir, DIRECTORY_SEPARATOR);
        if (!is_dir($uploadDir)) {
            if (!@mkdir($uploadDir, 0777, true)) {
                return ['ok' => false, 'error' => 'Unable to create upload directory'];
            }
        }

        // create safe base name
        $baseName = pathinfo($orig, PATHINFO_FILENAME);
        $baseName = preg_replace('~[^a-z0-9_-]+~i', '-', $baseName);
        $baseName = trim($baseName, '-');
        if ($baseName === '') { $baseName = 'image'; }

        // unique filename
        $random = bin2hex(random_bytes(8));
        $fileName = $baseName . '-' . date('Ymd-His') . '-' . $random . '.' . $ext;

        $targetPath = $uploadDir . DIRECTORY_SEPARATOR . $fileName;

        // move / store depending on uploaded file API
        if (method_exists($file, 'store')) {
            $ok = $file->store($targetPath);
            if (!$ok) { return ['ok' => false, 'error' => 'Failed to store uploaded file']; }
        } else {
            // fallback: move_uploaded_file
            $tmp = $valid['tmp'] ?? null;
            if (!$tmp || !move_uploaded_file($tmp, $targetPath)) {
                return ['ok' => false, 'error' => 'Failed to move uploaded file'];
            }
        }

        $rel = rtrim(Configuration::UPLOAD_DIR, '/\\') . '/' . trim($subdir, '/\\') . '/' . $fileName;
        $rel = str_replace('\\', '/', $rel);
        return ['ok' => true, 'path' => $rel, 'filename' => $fileName];
    }
}
