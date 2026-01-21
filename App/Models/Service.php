<?php

namespace App\Models;

use Framework\Core\Model;
use Framework\DB\Connection;

/**
 * Service model — trimmed to only include the edit (update) operation as requested.
 */
class Service extends Model
{
    protected static ?string $tableName = 'services';
    protected static ?string $primaryKey = 'id';

    public ?int $id = null;
    public ?string $name = null;
    public ?string $price = null; // keep as string/decimal
    public ?string $created_at = null;
    public ?string $updated_at = null;

    /**
     * Edit a single service by id. $data can contain 'name' and/or 'price'.
     * Validates business rules and updates only provided fields.
     * Uses prepared statements in the model (SQL lives here).
     *
     * @param int $id
     * @param array $data
     * @return void
     * @throws \Exception
     */
    public static function edit(int $id, array $data): void
    {
        if ($id <= 0) throw new \Exception('Invalid id');

        $fields = [];
        $params = ['id' => $id];

        if (array_key_exists('name', $data)) {
            $name = trim((string)$data['name']);
            if ($name === '') throw new \Exception('Name is required');
            $fields[] = '`name` = :name';
            $params['name'] = $name;
        }

        if (array_key_exists('price', $data)) {
            $priceRaw = trim((string)$data['price']);
            $price = str_replace(',', '.', $priceRaw);
            if ($price === '' || !preg_match('/^\d+(?:\.\d{1,2})?$/', $price)) throw new \Exception('Invalid price');
            if ((float)$price < 0.0) throw new \Exception('Price cannot be negative');
            $fields[] = '`price` = :price';
            $params['price'] = number_format((float)$price, 2, '.', '');
        }

        if (empty($fields)) {
            // Nothing to update
            return;
        }

        // always update timestamp
        $fields[] = '`updated_at` = :updated_at';
        $params['updated_at'] = date('Y-m-d H:i:s');

        $sql = 'UPDATE `services` SET ' . implode(', ', $fields) . ' WHERE `id` = :id';
        $conn = Connection::getInstance();
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
    }
}
