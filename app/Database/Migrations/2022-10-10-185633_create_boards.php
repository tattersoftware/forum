<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBoards extends Migration
{
    public function up(): void
    {
        $fields = [
            'ulid'       => ['type' => 'varchar', 'constraint' => 31],
            'name'       => ['type' => 'varchar', 'constraint' => 255],
            'created_at' => ['type' => 'datetime', 'null' => true],
            'updated_at' => ['type' => 'datetime', 'null' => true],
            'deleted_at' => ['type' => 'datetime', 'null' => true],
        ];

        $this->forge->addField('id');
        $this->forge->addField($fields);

        $this->forge->addKey('ulid');
        $this->forge->addKey('created_at');
        $this->forge->addKey(['deleted_at', 'name']);
        $this->forge->addKey(['deleted_at', 'ulid']);

        $this->forge->createTable('boards');

        $fields = [
            'ulid'       => ['type' => 'varchar', 'constraint' => 31],
            'board_ulid' => ['type' => 'varchar', 'constraint' => 31],
            'title'      => ['type' => 'varchar', 'constraint' => 255],
            'summary'    => ['type' => 'varchar', 'constraint' => 255],
            'created_at' => ['type' => 'datetime', 'null' => true],
            'updated_at' => ['type' => 'datetime', 'null' => true],
            'deleted_at' => ['type' => 'datetime', 'null' => true],
        ];

        $this->forge->addField('id');
        $this->forge->addField($fields);

        $this->forge->addKey('ulid');
        $this->forge->addKey('created_at');
        $this->forge->addKey(['board_ulid', 'title']);
        $this->forge->addKey(['deleted_at', 'ulid']);
        $this->forge->addKey(['deleted_at', 'title']);
        $this->forge->addKey(['deleted_at', 'board_ulid']);

        $this->forge->createTable('topics');
    }

    public function down(): void
    {
        $this->forge->dropTable('topics');
        $this->forge->dropTable('boards');
    }
}
