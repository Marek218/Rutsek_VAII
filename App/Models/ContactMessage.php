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

    public static function all(): array
    {
        return parent::getAll(null, [], '`created_at` DESC');
    }

    public static function create(array $data): int
    {
        $m = new self();
        $m->name = (string)($data['name'] ?? '');
        $m->phone = (string)($data['phone'] ?? '');
        $m->email = (string)($data['email'] ?? '');
        $m->subject = $data['subject'] ?? null;
        $m->message = (string)($data['message'] ?? '');
        $m->ip = $data['ip'] ?? null;
        $m->user_agent = $data['user_agent'] ?? null;
        $m->is_read = (int)($data['is_read'] ?? 0);
        $now = date('Y-m-d H:i:s');
        $m->created_at = $now;
        $m->save();
        return (int)$m->id;
    }

}