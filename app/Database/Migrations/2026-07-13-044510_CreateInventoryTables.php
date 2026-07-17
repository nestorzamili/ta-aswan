<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateInventoryTables extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_admin'      => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'username'      => ['type' => 'VARCHAR', 'constraint' => 50],
            'password'      => ['type' => 'VARCHAR', 'constraint' => 255],
            'nama'          => ['type' => 'VARCHAR', 'constraint' => 100],
            'email'         => ['type' => 'VARCHAR', 'constraint' => 100],
            'nomor_telepon' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'level'         => ['type' => 'ENUM', 'constraint' => ['admin', 'karyawan'], 'default' => 'karyawan'],
            'status'        => ['type' => 'ENUM', 'constraint' => ['aktif', 'nonaktif'], 'default' => 'aktif'],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_admin', true);
        $this->forge->addUniqueKey('username');
        $this->forge->addUniqueKey('email');
        $this->forge->createTable('admin');

        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'id_admin'   => ['type' => 'INT', 'unsigned' => true],
            'token'      => ['type' => 'VARCHAR', 'constraint' => 64],
            'expires_at' => ['type' => 'DATETIME'],
            'used_at'    => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('token');
        $this->forge->addForeignKey('id_admin', 'admin', 'id_admin', 'CASCADE', 'CASCADE');
        $this->forge->createTable('password_reset_tokens');

        $this->forge->addField([
            'id_supplier'   => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'nama_supplier' => ['type' => 'VARCHAR', 'constraint' => 100],
            'alamat'        => ['type' => 'TEXT', 'null' => true],
            'telepon'       => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'email'         => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_supplier', true);
        $this->forge->createTable('supplier');

        $this->forge->addField([
            'id_barang'   => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'tipe_barang' => ['type' => 'ENUM', 'constraint' => ['sparepart', 'aksesoris']],
            'kode_barang' => ['type' => 'VARCHAR', 'constraint' => 30],
            'kode_manual' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'nama_barang' => ['type' => 'VARCHAR', 'constraint' => 150],
            'kategori'    => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'merk'        => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'satuan'      => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'pcs'],
            'harga_beli'  => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'harga_jual'  => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'stok'        => ['type' => 'INT', 'default' => 0],
            'status_stok' => ['type' => 'ENUM', 'constraint' => ['aman', 'rendah', 'habis'], 'default' => 'habis'],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_barang', true);
        $this->forge->addUniqueKey('kode_barang');
        $this->forge->addKey('kategori');
        $this->forge->addKey('status_stok');
        $this->forge->createTable('barang');

        // Indexes for performance on Barang
        $table  = $this->db->prefixTable('barang');
        $prefix = $this->db->getPrefix();
        $this->db->query("CREATE INDEX `{$prefix}idx_barang_tipe` ON `{$table}` (tipe_barang)");
        $this->db->query("CREATE INDEX `{$prefix}idx_barang_tipe_status` ON `{$table}` (tipe_barang, status_stok)");

        $this->forge->addField([
            'id_masuk'       => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'no_faktur'      => ['type' => 'VARCHAR', 'constraint' => 30],
            'tanggal_masuk'  => ['type' => 'DATE'],
            'id_supplier'    => ['type' => 'INT', 'unsigned' => true],
            'total_item'     => ['type' => 'INT', 'default' => 0],
            'total_quantity' => ['type' => 'INT', 'default' => 0],
            'total_harga'    => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
            'id_admin'       => ['type' => 'INT', 'unsigned' => true],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_masuk', true);
        $this->forge->addUniqueKey('no_faktur');
        $this->forge->addKey('tanggal_masuk');
        $this->forge->addForeignKey('id_supplier', 'supplier', 'id_supplier', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('id_admin', 'admin', 'id_admin', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('barang_masuk');

        $this->forge->addField([
            'id_detail_masuk' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'id_masuk'        => ['type' => 'INT', 'unsigned' => true],
            'id_barang'       => ['type' => 'INT', 'unsigned' => true],
            'quantity'        => ['type' => 'INT'],
            'harga_satuan'    => ['type' => 'DECIMAL', 'constraint' => '12,2'],
            'subtotal'        => ['type' => 'DECIMAL', 'constraint' => '14,2'],
        ]);
        $this->forge->addKey('id_detail_masuk', true);
        $this->forge->addKey('id_barang');
        $this->forge->addForeignKey('id_masuk', 'barang_masuk', 'id_masuk', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_barang', 'barang', 'id_barang', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('detail_masuk');

        $this->forge->addField([
            'id_keluar'      => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'no_transaksi'   => ['type' => 'VARCHAR', 'constraint' => 30],
            'tanggal_keluar' => ['type' => 'DATE'],
            'tujuan'         => ['type' => 'VARCHAR', 'constraint' => 100],
            'total_item'     => ['type' => 'INT', 'default' => 0],
            'total_quantity' => ['type' => 'INT', 'default' => 0],
            'total_harga'    => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
            'id_admin'       => ['type' => 'INT', 'unsigned' => true],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_keluar', true);
        $this->forge->addUniqueKey('no_transaksi');
        $this->forge->addKey('tanggal_keluar');
        $this->forge->addForeignKey('id_admin', 'admin', 'id_admin', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('barang_keluar');

        $this->forge->addField([
            'id_detail_keluar' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'id_keluar'        => ['type' => 'INT', 'unsigned' => true],
            'id_barang'        => ['type' => 'INT', 'unsigned' => true],
            'quantity'         => ['type' => 'INT'],
            'harga_satuan'     => ['type' => 'DECIMAL', 'constraint' => '12,2'],
            'subtotal'         => ['type' => 'DECIMAL', 'constraint' => '14,2'],
        ]);
        $this->forge->addKey('id_detail_keluar', true);
        $this->forge->addKey('id_barang');
        $this->forge->addForeignKey('id_keluar', 'barang_keluar', 'id_keluar', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_barang', 'barang', 'id_barang', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('detail_keluar');

        // ci_sessions
        $this->forge->addField([
            'id' => ['type' => 'VARCHAR', 'constraint' => 128, 'null' => false],
            'ip_address' => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => false],
            '`timestamp` timestamp DEFAULT CURRENT_TIMESTAMP NOT NULL',
            'data' => ['type' => 'BLOB', 'null' => false],
         ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('timestamp');
        $this->forge->createTable('ci_sessions', true);
    }

    public function down()
    {
        $this->forge->dropTable('ci_sessions', true);
        $this->forge->dropTable('detail_keluar', true);
        $this->forge->dropTable('barang_keluar', true);
        $this->forge->dropTable('detail_masuk', true);
        $this->forge->dropTable('barang_masuk', true);
        $this->forge->dropTable('barang', true);
        $this->forge->dropTable('supplier', true);
        $this->forge->dropTable('password_reset_tokens', true);
        $this->forge->dropTable('admin', true);
    }
}
