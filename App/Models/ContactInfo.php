<?php

declare(strict_types=1);

namespace App\Models;

use Framework\Core\Model;

class ContactInfo extends Model
{
    protected static ?string $tableName = 'contact_info';

    public ?int $id = null;
    public string $salon_name = '';
    public ?string $person_name = null;
    public string $phone = '';
    public string $email = '';
    public string $address_line = '';
    public ?string $opening_hours = null;
    public ?string $map_embed_url = null;

    // Kept for backward compatibility: some DBs still contain this column.
    // Not used in UI anymore (logo is now fixed asset), but Model requires property to exist.
    public ?string $logo_path = null;

    public ?string $updated_at = null;

    public static function getSingleton(): ?self
    {
        return self::getOne(1);
    }
}
