<?php

declare(strict_types=1);

namespace App\Models;

use Framework\Core\Model;
use Framework\DB\Connection;

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

    /**
     * Update multiple home box records in a single transaction.
     * Expects $items in format: [ id => ['title' => ..., 'description' => ...], ... ]
     * Validates business rules (title/description length) and uses prepared statements.
     *
     * @param array $items
     * @throws \Exception
     */
    public static function updateMany(array $items): void
    {
        if (empty($items)) return;

        // basic validation pass: collect valid rows
        $toUpdate = [];
        foreach ($items as $id => $row) {
            $id = (int)$id;
            if ($id <= 0) continue;
            $title = isset($row['title']) ? trim((string)$row['title']) : '';
            $desc = isset($row['description']) ? trim((string)$row['description']) : '';
            if ($title === '' || mb_strlen($title) < 2) continue;
            if ($desc === '' || mb_strlen($desc) < 2) continue;
            $toUpdate[$id] = ['title' => $title, 'description' => $desc];
        }

        if (empty($toUpdate)) return;

        $conn = Connection::getInstance();
        $pdo = $conn->getPdo();
        $pdo->beginTransaction();
        try {
            $stmt = $conn->prepare('UPDATE `home_boxes` SET `title` = :title, `description` = :description, `updated_at` = :updated_at WHERE `id` = :id');
            $now = date('Y-m-d H:i:s');
            foreach ($toUpdate as $id => $data) {
                $stmt->execute([
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'updated_at' => $now,
                    'id' => $id,
                ]);
            }
            $pdo->commit();
        } catch (\Throwable $e) {
            try { $pdo->rollBack(); } catch (\Throwable $_) {}
            throw $e;
        }
    }
}
