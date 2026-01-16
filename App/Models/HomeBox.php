<?php

declare(strict_types=1);

namespace App\Models;

use Framework\Core\Model;

/**
 * Simple editable text blocks for homepage boxes.
 *
 * Table: home_boxes
 * Fields: id, box_key, title, description, sort_order, updated_at
 */
class HomeBox extends Model
{
    protected static ?string $tableName = 'home_boxes';

    public ?int $id = null;
    public string $box_key = '';
    public string $title = '';
    public string $description = '';
    public int $sort_order = 0;
    public ?string $updated_at = null;

    /**
     * @return array<string, self>
     */
    public static function getAllByKey(): array
    {
        $items = self::getAll(orderBy: '`sort_order` ASC, `id` ASC');
        $map = [];
        foreach ($items as $it) {
            $map[(string)$it->box_key] = $it;
        }
        return $map;
    }
}
