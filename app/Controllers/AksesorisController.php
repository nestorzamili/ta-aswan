<?php

namespace App\Controllers;

class AksesorisController extends BarangController
{
    protected string $tipe = 'aksesoris';

    protected function cfg(): array
    {
        return [
            'label' => 'Aksesoris',
            'route' => 'aksesoris',
            'kode'  => 'kode_barang',
            'nama'  => 'nama_barang',
            'pk'    => 'id_barang',
        ];
    }
}
