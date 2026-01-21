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

    /**
     * Validate business rules and create message. Throws Exception with JSON-encoded errors on validation failure.
     * @param array $data
     * @return int inserted id
     * @throws \Exception
     */
    public static function createFromArray(array $data): int
    {
        $errors = [];
        // honeypot
        if (!empty((string)($data['website'] ?? ''))) {
            $errors['form'] = 'Invalid submission';
        }

        $name = trim((string)($data['name'] ?? ''));
        $email = trim((string)($data['email'] ?? ''));
        $message = trim((string)($data['message'] ?? ''));

        if ($name === '') { $errors['name'] = 'Meno je povinné'; }
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors['email'] = 'Zadajte platný email'; }
        if (strlen($message) < 5) { $errors['message'] = 'Správa musí mať aspoň 5 znakov'; }

        if (!empty($errors)) {
            throw new \Exception(json_encode($errors));
        }

        // safe to persist
        $payload = [
            'name' => $name,
            'email' => $email,
            'phone' => trim((string)($data['phone'] ?? '')),
            'subject' => $data['subject'] ?? null,
            'message' => $message,
            'website' => $data['website'] ?? null,
            'ip' => $data['ip'] ?? null,
            'user_agent' => $data['user_agent'] ?? null,
            'is_read' => 0,
        ];

        return self::create($payload);
    }
}