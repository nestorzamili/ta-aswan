<?php

namespace App\Libraries;

use App\Models\BarangKeluarModel;
use App\Models\BarangMasukModel;
use App\Models\DetailKeluarModel;
use App\Models\DetailMasukModel;

class TransactionService
{
    protected StockService $stock;
    protected NumberingService $numbering;

    public function __construct(?StockService $stock = null, ?NumberingService $numbering = null)
    {
        $this->stock     = $stock ?? new StockService();
        $this->numbering = $numbering ?? new NumberingService();
    }

    /**
     * Process a goods receipt transaction (Barang Masuk)
     */
    public function processBarangMasuk(array $headerData, array $lines): int
    {
        $db = db_connect();
        $db->transException(true);
        $db->transStart();

        $model = new BarangMasukModel();
        $model->insert([
            'no_faktur'      => $this->numbering->nextFakturMasuk(),
            'tanggal_masuk'  => $headerData['tanggal_masuk'],
            'id_supplier'    => $headerData['id_supplier'],
            'total_item'     => $lines['count'],
            'total_quantity' => $lines['qty'],
            'total_harga'    => $lines['total'],
            'id_admin'       => $headerData['id_admin'],
            'created_at'     => date('Y-m-d H:i:s'),
        ]);

        $idMasuk = (int) $model->getInsertID();
        $detail  = new DetailMasukModel();

        foreach ($lines['rows'] as $line) {
            $line['id_masuk'] = $idMasuk;
            $detail->insert($line);
            $this->stock->increase((int) $line['id_barang'], (int) $line['quantity']);
        }

        $db->transComplete();

        return $idMasuk;
    }

    /**
     * Process a goods issue transaction (Barang Keluar)
     */
    public function processBarangKeluar(array $headerData, array $lines): int
    {
        $db = db_connect();
        $db->transException(true);
        $db->transStart();

        // Early UX check; decrease() is the atomic authority.
        foreach ($lines['rows'] as $line) {
            $this->stock->assertAvailable((int) $line['id_barang'], (int) $line['quantity']);
        }

        $model = new BarangKeluarModel();
        $model->insert([
            'no_transaksi'   => $this->numbering->nextTransaksiKeluar(),
            'tanggal_keluar' => $headerData['tanggal_keluar'],
            'tujuan'         => $headerData['tujuan'],
            'total_item'     => $lines['count'],
            'total_quantity' => $lines['qty'],
            'total_harga'    => $lines['total'],
            'id_admin'       => $headerData['id_admin'],
            'created_at'     => date('Y-m-d H:i:s'),
        ]);

        $idKeluar = (int) $model->getInsertID();
        $detail   = new DetailKeluarModel();

        foreach ($lines['rows'] as $line) {
            $line['id_keluar'] = $idKeluar;
            $detail->insert($line);
            $this->stock->decrease((int) $line['id_barang'], (int) $line['quantity']);
        }

        $db->transComplete();

        return $idKeluar;
    }

    /**
     * Delete goods receipt — reverse stock increase (may fail if stock already used).
     */
    public function deleteBarangMasuk(int $id, array $details): void
    {
        $db = db_connect();
        $db->transException(true);
        $db->transStart();

        foreach ($details as $d) {
            $this->stock->decrease((int) $d['id_barang'], (int) $d['quantity']);
        }

        (new DetailMasukModel())->where('id_masuk', $id)->delete();
        (new BarangMasukModel())->delete($id);

        $db->transComplete();
    }

    /**
     * Delete goods issue — reverse stock decrease.
     */
    public function deleteBarangKeluar(int $id, array $details): void
    {
        $db = db_connect();
        $db->transException(true);
        $db->transStart();

        foreach ($details as $d) {
            $this->stock->increase((int) $d['id_barang'], (int) $d['quantity']);
        }

        (new DetailKeluarModel())->where('id_keluar', $id)->delete();
        (new BarangKeluarModel())->delete($id);

        $db->transComplete();
    }

    /**
     * Update goods issue — restore old stock, apply new lines.
     */
    public function updateBarangKeluar(int $id, array $existingDetails, array $headerData, array $lines): void
    {
        $db = db_connect();
        $db->transException(true);
        $db->transStart();

        foreach ($existingDetails as $d) {
            $this->stock->increase((int) $d['id_barang'], (int) $d['quantity']);
        }

        (new DetailKeluarModel())->where('id_keluar', $id)->delete();

        foreach ($lines['rows'] as $line) {
            $this->stock->assertAvailable((int) $line['id_barang'], (int) $line['quantity']);
        }

        $detail = new DetailKeluarModel();

        foreach ($lines['rows'] as $line) {
            $line['id_keluar'] = $id;
            $detail->insert($line);
            $this->stock->decrease((int) $line['id_barang'], (int) $line['quantity']);
        }

        (new BarangKeluarModel())->update($id, [
            'tanggal_keluar' => $headerData['tanggal_keluar'],
            'tujuan'         => $headerData['tujuan'],
            'total_item'     => $lines['count'],
            'total_quantity' => $lines['qty'],
            'total_harga'    => $lines['total'],
        ]);

        $db->transComplete();
    }
}
