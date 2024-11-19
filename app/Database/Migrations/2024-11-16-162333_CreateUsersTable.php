<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUsersTable extends Migration
{
    public function up()
    {
        // Enable the uuid-ossp extension
        $this->db->query('CREATE EXTENSION IF NOT EXISTS "uuid-ossp";');

        $this->forge->addField([
            'uuid' => [
                'type' => 'UUID'
            ],
            'username' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'unique' => true,
            ],
            'password' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'email' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'unique' => true,
            ],
            'role' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'default' => 'user',
            ],
            'created_at' => ['type' => 'datetime', 'null' => false],
            'updated_at' => ['type' => 'datetime', 'null' => false],
        ]);
        $this->forge->addPrimaryKey('uuid');
        $this->forge->createTable('users');

        // Add CHECK constraint for role
        $this->db->query("ALTER TABLE users ADD CONSTRAINT role_check CHECK (role IN ('admin', 'user'))");
    }

    public function down()
    {
        $this->forge->dropTable('users');
    }
}