<?php

namespace App\Models;

use Framework\Core\Model;

/**
 * Simple Service model representing the `services` table.
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
}

