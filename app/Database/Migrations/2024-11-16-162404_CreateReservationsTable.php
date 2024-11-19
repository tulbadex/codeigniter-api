<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateReservationsTable extends Migration
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
            'reservation_date' => ['type' => 'DATE', 'null' => false],
            'status' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'default' => 'reserved',
            ],
            'created_at' => ['type' => 'datetime', 'null' => false],
            'updated_at' => ['type' => 'datetime', 'null' => false],
        ]);
        $this->forge->addPrimaryKey('uuid');
        $this->forge->addForeignKey('user_uuid', 'users', 'uuid');
        $this->forge->addForeignKey('book_uuid', 'books', 'uuid');
        $this->forge->createTable('reservations');

        // Add CHECK constraint for status
        $this->db->query("ALTER TABLE reservations ADD CONSTRAINT status_check CHECK (status IN ('reserved', 'cancelled', 'completed'))");
    }

    public function down()
    {
        $this->forge->dropTable('reservations');
    }
}
