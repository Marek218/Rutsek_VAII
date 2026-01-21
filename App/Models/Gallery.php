<?php

namespace App\Models;

use Framework\Core\Model;
use App\Configuration;
use App\Support\FileUpload;
use Framework\DB\Connection;
use PDO;

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

    /**
     * Handle upload: store file via FileUpload and insert DB record using prepared statements.
     * Returns ['ok'=>bool,'id'=>int|null,'error'=>string|null,'path'=>string|null]
     */
    public static function handleUpload($uploadedFile): array
    {
        $result = FileUpload::storeGalleryFile($uploadedFile, 'gallery');
        if (!$result['ok']) {
            return ['ok' => false, 'error' => ($result['error'] ?? 'uploadfailed')];
        }

        $path = $result['path'];

        // Determine next sort order (use aggregate query)
        $conn = Connection::getInstance();
        try {
            $stmt = $conn->prepare('SELECT COALESCE(MAX(`sort_order`), 0) AS mx FROM `gallery`');
            $stmt->execute([]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $max = isset($row['mx']) ? (int)$row['mx'] : 0;

            $ins = $conn->prepare('INSERT INTO `gallery` (`path_url`, `title`, `is_public`, `sort_order`, `created_at`) VALUES (:path, :title, :is_public, :sort_order, :created_at)');
            $now = date('Y-m-d H:i:s');
            $ins->execute([
                'path' => $path,
                'title' => null,
                'is_public' => 1,
                'sort_order' => $max + 1,
                'created_at' => $now
            ]);

            $lastId = $conn->lastInsertId();
            return ['ok' => true, 'id' => (int)$lastId, 'path' => $path];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Check if gallery item can be deleted (business rule). Placeholder for checks.
     */
    public static function canBeDeleted(int $id): bool
    {
        // No complex business rules for gallery items now; return true.
        return true;
    }

    /**
     * Delete gallery item by id: performs DB delete via prepared statement and unlinks file if inside public dir.
     */
    public static function deleteById(int $id): void
    {
        if ($id <= 0) return;
        if (!self::canBeDeleted($id)) {
            throw new \Exception('Gallery item cannot be deleted due to business rules.');
        }

        $conn = Connection::getInstance();
        try {
            // fetch path first
            $sel = $conn->prepare('SELECT `path_url` FROM `gallery` WHERE `id` = :id');
            $sel->execute(['id' => $id]);
            $row = $sel->fetch(PDO::FETCH_ASSOC);
            $path = $row['path_url'] ?? null;

            // delete DB row
            $del = $conn->prepare('DELETE FROM `gallery` WHERE `id` = :id');
            $del->execute(['id' => $id]);

            // unlink file if inside public
            if ($path) {
                $norm = self::normalizePathUrl($path);
                $publicDir = realpath(__DIR__ . '/../../public');
                if ($norm && $publicDir) {
                    $full = rtrim($publicDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $norm);
                    $fullReal = realpath($full);
                    if ($fullReal && strpos($fullReal, $publicDir) === 0 && is_file($fullReal)) {
                        @unlink($fullReal);
                    }
                }
            }
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    /**
     * Reorder gallery items by provided array of ids (first receives sort_order 1). Runs in transaction.
     */
    public static function reorder(array $ids): void
    {
        if (empty($ids)) return;
        $conn = Connection::getInstance();
        $pdo = $conn->getPdo();
        $pdo->beginTransaction();
        try {
            $stmt = $conn->prepare('UPDATE `gallery` SET `sort_order` = :pos WHERE `id` = :id');
            $pos = 1;
            foreach ($ids as $id) {
                $id = (int)$id;
                if ($id <= 0) continue;
                $stmt->execute(['pos' => $pos, 'id' => $id]);
                $pos++;
            }
            $pdo->commit();
        } catch (\Throwable $e) {
            try { $pdo->rollBack(); } catch (\Throwable $_) {}
            throw $e;
        }
    }
}
