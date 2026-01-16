<?php

declare(strict_types=1);

namespace App\Models;

use Framework\Core\Model;

class ContactMessage extends Model
{
    protected static ?string $tableName = 'contact_messages';

    public ?int $id = null;
    public string $name = '';
    public string $phone = '';
    public string $email = '';
    public ?string $subject = null;
    public string $message = '';

    public ?string $ip = null;
    public ?string $user_agent = null;
    public int $is_read = 0;

    public ?string $created_at = null;
}
