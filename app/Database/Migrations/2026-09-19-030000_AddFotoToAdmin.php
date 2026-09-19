<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFotoToAdmin extends Migration
{
    public function up()
    {
        $this->forge->addColumn('admin', [
            'foto' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'after' => 'nomor_telepon'],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('admin', 'foto');
    }
}
