<?php

namespace App\Controllers;

class SparepartController extends BarangController
{
    protected string $tipe = 'sparepart';

    protected function cfg(): array
    {
        return [
            'label' => 'Sparepart',
            'route' => 'sparepart',
            'kode'  => 'kode_barang',
            'nama'  => 'nama_barang',
            'pk'    => 'id_barang',
        ];
    }
}
