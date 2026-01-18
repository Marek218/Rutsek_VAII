<?php

namespace App\Models;

use Framework\Core\Model;

/**
 * Order model for persisting booking requests into `orders` table.
 */
class Order extends Model
{
    // Explicit table and PK names to avoid convention guess
    protected static ?string $tableName = 'orders';
    protected static ?string $primaryKey = 'id';

    // Properties must match DB columns for Model base class to map values
    public ?int $id = null;
    public ?string $first_name = null;
    public ?string $last_name = null;
    public ?string $email = null;
    public ?string $phone = null;

    // Legacy text field (kept for backward compatibility)
    public ?string $service = null;

    // New FK to services.id (real 1:N relationship)
    public ?int $service_id = null;

    public ?string $date = null;  // store as DATE (Y-m-d)
    public ?string $time = null;  // store as TIME (HH:MM:SS)
    public ?string $notes = null;
    public ?string $created_at = null; // populated by DB default
}
