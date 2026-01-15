<?php

namespace App\Models;

use Framework\Core\Model;

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
}
