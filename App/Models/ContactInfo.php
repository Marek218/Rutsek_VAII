<?php

declare(strict_types=1);

namespace App\Models;

use Framework\Core\Model;
use Framework\DB\Connection;

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

    /**
     * Persist contact info from an array of values. Returns the ContactInfo instance on success.
     * On validation errors, throws Exception with JSON-encoded errors array.
     *
     * @param array $data
     * @return self
     * @throws \Exception
     */
    public static function saveFromArray(array $data): self
    {
        // Ensure single row exists with id=1
        $contact = self::getOne(1);
        if (!$contact) {
            $contact = new self();
            $contact->id = 1;
        }

        $salonName = trim((string)($data['salon_name'] ?? ''));
        $personName = trim((string)($data['person_name'] ?? ''));
        $phone = trim((string)($data['phone'] ?? ''));
        $email = trim((string)($data['email'] ?? ''));
        $address = trim((string)($data['address_line'] ?? ''));
        $opening = trim((string)($data['opening_hours'] ?? ''));
        $mapUrl = trim((string)($data['map_embed_url'] ?? ''));

        $errors = [];
        if ($salonName === '' || mb_strlen($salonName) < 2) {
            $errors['salon_name'] = 'Názov salónu musí mať aspoň 2 znaky.';
        }
        if ($phone === '' || mb_strlen($phone) < 6) {
            $errors['phone'] = 'Telefón je povinný (min. 6 znakov).';
        }
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Zadajte platný email.';
        }
        if ($address === '' || mb_strlen($address) < 5) {
            $errors['address_line'] = 'Adresa je povinná (min. 5 znakov).';
        }

        if (!empty($errors)) {
            throw new \Exception(json_encode($errors));
        }

        // assign values and save
        $contact->salon_name = $salonName;
        $contact->person_name = ($personName === '' ? null : $personName);
        $contact->phone = $phone;
        $contact->email = $email;
        $contact->address_line = $address;
        $contact->opening_hours = ($opening === '' ? null : $opening);
        $contact->map_embed_url = ($mapUrl === '' ? null : $mapUrl);

        $contact->save();
        return $contact;
    }
}
