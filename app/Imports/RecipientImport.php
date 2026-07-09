<?php

namespace App\Imports;

use App\Models\Recipient;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class RecipientImport implements ToModel, WithHeadingRow
{
    public function __construct(protected int $pageId) {}

    public function model(array $row)
    {
        // 1. Coba heading row format (Laravel Excel slugified)
        $name = $row['nama_penerima'] ?? null;
        $address = $row['alamat'] ?? null;
        $phone = $row['nomor_telepon'] ?? null;

        // 2. Coba raw headers case-sensitive/insensitive
        if (empty($name)) {
            $name = $row['Nama Penerima'] ?? $row['name'] ?? $row['Name'] ?? null;
        }
        if (empty($address)) {
            $address = $row['Alamat'] ?? $row['address'] ?? $row['Address'] ?? null;
        }
        if (empty($phone)) {
            $phone = $row['Nomor Telepon'] ?? $row['phone_number'] ?? $row['phone'] ?? null;
        }

        // 3. Coba fallback ke index numerik jika header gagal diparsing (No = 0, Nama = 1, Alamat = 2, Telpon = 3)
        $values = array_values($row);
        if (empty($name) && isset($values[1])) {
            $name = $values[1];
            $address = $values[2] ?? null;
            $phone = $values[3] ?? null;
        }

        // Jika baris kosong atau merupakan header itu sendiri, lewati
        if (empty($name) || $name === 'Nama Penerima') {
            return null;
        }

        return new Recipient([
            'page_id' => $this->pageId,
            'name' => $name,
            'address' => $address,
            'phone_number' => $phone,
        ]);
    }
}
