<?php

namespace App\Models;

use Framework\Core\Model;
use App\Models\Service;

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

    /**
     * Return true if requested slot [start, start+duration) overlaps any existing reservation on the same date.
     * @param string $date Y-m-d
     * @param string $startTime HH:MM or HH:MM:SS
     * @param int $durationMinutes
     * @return bool
     */
    public static function hasOverlap(string $date, string $startTime, int $durationMinutes = 60): bool
    {
        $startTime = preg_match('/^\d{2}:\d{2}:\d{2}$/', $startTime) ? $startTime : ($startTime . ':00');
        $startTs = strtotime($date . ' ' . $startTime);
        if ($startTs === false) return true;
        $endTs = $startTs + ($durationMinutes * 60);

        try {
            $orders = self::getAll('`date` = ?', [$date]);
        } catch (\Throwable $e) {
            // On DB error, treat as overlapping to be safe
            return true;
        }

        foreach ($orders as $o) {
            $t = (string)($o->time ?? '');
            if ($t === '') continue;
            $existingStart = strtotime($date . ' ' . $t);
            if ($existingStart === false) continue;
            $existingEnd = $existingStart + ($durationMinutes * 60);
            if ($startTs < $existingEnd && $endTs > $existingStart) {
                return true;
            }
        }
        return false;
    }

    /**
     * Create an order from a data array (business validation + persist).
     * Expects keys: first_name,last_name,email,phone,service_id,date,time,notes
     * Throws Exception on validation or DB error.
     *
     * @param array $data
     * @return self
     * @throws \Exception
     */
    public static function createFromArray(array $data): self
    {
        // Extract and normalize
        $first = trim((string)($data['first_name'] ?? ''));
        $last = trim((string)($data['last_name'] ?? ''));
        $email = trim((string)($data['email'] ?? ''));
        $phone = trim((string)($data['phone'] ?? ''));
        $serviceId = (int)($data['service_id'] ?? 0);
        $date = trim((string)($data['date'] ?? ''));
        $time = trim((string)($data['time'] ?? ''));
        $notes = trim((string)($data['notes'] ?? ''));

        $errors = [];
        if ($first === '') { $errors['first_name'] = 'Meno je povinné.'; }
        if ($last === '') { $errors['last_name'] = 'Priezvisko je povinné.'; }
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors['email'] = 'Neplatný email.'; }
        if ($phone === '' || !preg_match('/^[+0-9 ()-]{6,20}$/', $phone)) { $errors['phone'] = 'Neplatný telefón.'; }
        if ($serviceId <= 0) { $errors['service_id'] = 'Vyberte službu.'; }
        if ($date === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) { $errors['date'] = 'Neplatný dátum.'; }
        if ($time === '' || !preg_match('/^\d{2}:\d{2}$/', $time)) { $errors['time'] = 'Neplatný čas.'; }

        // Past date guard
        $today = date('Y-m-d');
        if ($date !== '' && $date < $today) {
            $errors['date'] = 'Dátum nemôže byť v minulosti.';
        }

        // Verify service exists
        $service = null;
        if ($serviceId > 0) {
            $service = Service::getOne($serviceId);
            if (!$service) { $errors['service_id'] = 'Vyberte platnú službu.'; }
        }

        if (!empty($errors)) {
            throw new \Exception(json_encode($errors));
        }

        // normalize time
        $timeDb = preg_match('/^\d{2}:\d{2}:\d{2}$/', $time) ? $time : ($time . ':00');

        // Check overlap
        if (self::hasOverlap($date, $timeDb, 60)) {
            throw new \Exception(json_encode(['time' => 'Tento termín je už obsadený.']));
        }

        // Create and save
        $order = new self();
        $order->first_name = $first;
        $order->last_name = $last;
        $order->email = $email;
        $order->phone = $phone;
        $order->service_id = $serviceId;
        $order->service = (string)($service->name ?? '');
        $order->date = $date;
        $order->time = $timeDb;
        $order->notes = $notes ?: null;

        $order->save();
        return $order;
    }
}
