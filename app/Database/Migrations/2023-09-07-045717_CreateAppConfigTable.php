<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAppConfigTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 5,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'key' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'value' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('app_config');
    }

    public function down()
    {
        $this->forge->dropTable('app_config');
    }
}
