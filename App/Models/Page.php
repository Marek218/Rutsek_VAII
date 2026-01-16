<?php

declare(strict_types=1);

namespace App\Models;

use Framework\Core\Model;

class Page extends Model
{
    protected static ?string $tableName = 'pages';

    public ?int $id = null;
    public string $slug = '';
    public string $title = '';
    public ?string $lead = null;
    public ?string $body = null;
    public int $is_published = 1;
    public ?string $created_at = null;
    public ?string $updated_at = null;

    public static function findPublishedBySlug(string $slug): ?self
    {
        $slug = trim($slug);
        if ($slug === '') {
            return null;
        }

        $items = self::getAll(whereClause: '`slug` = ? AND `is_published` = 1', whereParams: [$slug], limit: 1);
        return $items[0] ?? null;
    }
}
