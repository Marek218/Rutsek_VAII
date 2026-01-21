<?php

namespace App\Models;

use Framework\Core\Model;
use App\Configuration;

/**
 * Gallery image model representing the `gallery` table.
 */
class Gallery extends Model
{
    protected static ?string $tableName = 'gallery';
    protected static ?string $primaryKey = 'id';

    // If your DB uses different column names, you can map them here.
    // We keep `path_url` as a real property (see below), so it does not need mapping.
    protected static array $columnsMap = [
        // 'url_path' => 'path_url',
    ];

    public ?int $id = null;
    public ?string $title = null;
    public ?string $category = null;

    /**
     * Local path relative to public/ (e.g. "uploads/gallery/1.jpg").
     * This is the only image source stored in DB.
     */
    public ?string $path_url = null;

    public ?int $is_public = 1;
    public ?int $sort_order = 0;
    public ?string $created_at = null;
    public ?string $updated_at = null;

    /**
     * Normalize DB value (path_url) into a path relative to public/.
     * Accepts values like: 'images/Gallery/panske1.png', '/images/...', 'public/images/..', or missing extension.
     * Returns normalized relative path (forward slashes) or null.
     * This mirrors previous logic from the view but belongs into model.
     */
    public static function normalizePathUrl(?string $raw): ?string
    {
        $path = trim((string)$raw);
        if ($path === '') {
            return null;
        }

        // Decode HTML entities just in case
        $path = html_entity_decode($path, ENT_QUOTES | ENT_HTML5);

        // Make it relative (strip leading slashes)
        $path = ltrim($path, "/\\");

        // If someone stored filesystem-ish prefix
        if (stripos($path, 'public/') === 0) {
            $path = substr($path, strlen('public/'));
            $path = ltrim($path, "/\\");
        }

        // Normalize slashes for URL
        $path = str_replace('\\', '/', $path);

        // If no extension, try to find existing file under public/ with common image extensions
        if (!preg_match('~\.(png|jpe?g|webp|gif)$~i', $path)) {
            $publicDir = realpath(__DIR__ . '/../../public');
            if ($publicDir !== false) {
                foreach (['.jpg', '.png', '.jpeg', '.webp'] as $ext) {
                    $candidate = rtrim($publicDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path . $ext);
                    if (is_file($candidate)) {
                        return str_replace('\\', '/', $path . $ext);
                    }
                }
            }
            // fallback: return with .jpg so browser can still try to load something
            return $path . '.jpg';
        }

        return $path;
    }

    /**
     * Return last N lines of upload log if present, for debug display in admin views.
     * Returns array of lines or empty array if unavailable.
     */
    public static function getUploadLogLines(int $maxLines = 40): array
    {
        $logFile = realpath(__DIR__ . '/../../var/log/upload_errors.log');
        if (!$logFile || !is_file($logFile) || !is_readable($logFile)) {
            return [];
        }
        $lines = @file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
        return array_slice($lines, -$maxLines);
    }
}
