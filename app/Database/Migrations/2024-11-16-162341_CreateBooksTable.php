<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBooksTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'uuid' => [
                'type' => 'UUID',
            ],
            'title' => ['type' => 'VARCHAR', 'constraint' => 255],
            'author' => ['type' => 'VARCHAR', 'constraint' => 255],
            'category_uuid' => ['type' => 'UUID'],
            'isbn' => ['type' => 'VARCHAR', 'constraint' => 20, 'unique' => true],
            'published_date' => ['type' => 'DATE'],
            'status' => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => 'available'],
            'created_at' => ['type' => 'datetime', 'null' => false],
            'updated_at' => ['type' => 'datetime', 'null' => false],
        ]);
        $this->forge->addPrimaryKey('uuid');
        $this->forge->addForeignKey('category_uuid', 'categories', 'uuid');
        $this->forge->createTable('books');

        // Add CHECK constraint for status
        $this->db->query("ALTER TABLE books ADD CONSTRAINT status_check CHECK (status IN ('available', 'issued'))");
    }

    public function down()
    {
        $this->forge->dropTable('books');
    }
}
