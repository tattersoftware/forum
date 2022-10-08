<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UserHandle extends Migration
{
    public function up()
    {
        $fields = [
            'handle' => ['type' => 'varchar', 'constraint' => 255, 'after' => 'username', 'default' => ''],
        ];

        $this->forge->addColumn('users', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'handle');
    }
}
