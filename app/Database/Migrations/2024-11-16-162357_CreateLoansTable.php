<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLoansTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'uuid' => [
                'type' => 'UUID',
                // 'default' => 'uuid_generate_v4()',
            ],
            'user_uuid' => [
                'type' => 'UUID',
            ],
            'book_uuid' => [
                'type' => 'UUID',
            ],
            'loan_date' => ['type' => 'DATE', 'null' => false],
            'return_date' => ['type' => 'DATE', 'null' => true],
            'status' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'default' => 'on_loan',
            ],
            'created_at' => ['type' => 'datetime', 'null' => false],
            'updated_at' => ['type' => 'datetime', 'null' => false],
        ]);
        $this->forge->addPrimaryKey('uuid');
        $this->forge->addForeignKey('user_uuid', 'users', 'uuid');
        $this->forge->addForeignKey('book_uuid', 'books', 'uuid');
        $this->forge->createTable('loans');

        // Add CHECK constraint for status
        $this->db->query("ALTER TABLE loans ADD CONSTRAINT status_check CHECK (status IN ('on_loan', 'returned'))");
    }

    public function down()
    {
        $this->forge->dropTable('loans');
    }
}
