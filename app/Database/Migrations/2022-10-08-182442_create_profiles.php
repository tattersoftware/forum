<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProfiles extends Migration
{
    public function up(): void
    {
        $fields = [
            'user_id'    => ['type' => 'int', 'unsigned' => true],
            'avatar'     => ['type' => 'varchar', 'constraint' => 511],
            'created_at' => ['type' => 'datetime', 'null' => true],
            'updated_at' => ['type' => 'datetime', 'null' => true],
        ];

        $this->forge->addField('id');
        $this->forge->addField($fields);

        $this->forge->addKey('user_id');
        $this->forge->addKey('created_at');

        $this->forge->createTable('profiles');
    }

    public function down(): void
    {
        $this->forge->dropTable('profiles');
    }
}
