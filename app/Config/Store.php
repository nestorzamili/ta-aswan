<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Store identity used on PDF headers/footers (override via .env store.*).
 */
class Store extends BaseConfig
{
    public string $name    = 'Toko Android Service';
    public string $tagline = 'Inventory sparepart & aksesoris';
    public string $address = 'Teluk Dalam';
    public string $phone   = '';
    public string $email   = '';
    public string $city    = 'Teluk Dalam';

    /**
     * Short line under the store name on PDF kop.
     */
    public function contactLine(): string
    {
        $parts = array_filter([
            trim($this->address) !== '' ? trim($this->address) : null,
            trim($this->phone) !== '' ? 'Tel. ' . trim($this->phone) : null,
            trim($this->email) !== '' ? trim($this->email) : null,
        ]);

        return implode(' · ', $parts);
    }
}
