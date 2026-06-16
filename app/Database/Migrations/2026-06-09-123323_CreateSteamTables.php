<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSteamTables extends Migration
{
    public function up()
    {
        // 1. users Table
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'username' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'unique'     => true,
            ],
            'password_hash' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('users', true);

        // 2. games Table
        $this->forge->addField([
            'id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'release_date' => [
                'type' => 'DATE',
            ],
            'english' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'developer' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'publisher' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'platforms' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'required_age' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'categories' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
            ],
            'genres' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
            ],
            'achievements' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'owners' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'price' => [
                'type'       => 'DECIMAL',
                'constraint' => '6,2',
                'default'    => '0.00',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('games', true);

        // 3. game_descriptions Table
        $this->forge->addField([
            'game_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'detailed_description' => [
                'type' => 'MEDIUMTEXT',
            ],
            'about_the_game' => [
                'type' => 'MEDIUMTEXT',
            ],
            'short_description' => [
                'type'       => 'VARCHAR',
                'constraint' => 1000,
            ],
        ]);
        $this->forge->addKey('game_id', true);
        $this->forge->createTable('game_descriptions', true);

        // 4. game_media Table
        $this->forge->addField([
            'game_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'screenshots' => [
                'type' => 'MEDIUMTEXT',
            ],
            'background' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
        ]);
        $this->forge->addKey('game_id', true);
        $this->forge->createTable('game_media', true);

        // 5. game_requirements Table
        $this->forge->addField([
            'game_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'pc_requirements' => [
                'type'       => 'VARCHAR',
                'constraint' => 5000,
            ],
            'mac_requirements' => [
                'type'       => 'VARCHAR',
                'constraint' => 3000,
            ],
            'linux_requirements' => [
                'type'       => 'VARCHAR',
                'constraint' => 3000,
            ],
        ]);
        $this->forge->addKey('game_id', true);
        $this->forge->createTable('game_requirements', true);

        // 6. genres Table (For M:N relation)
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'unique'     => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('genres', true);

        // 7. game_genres Table (M:N Pivot Table)
        $this->forge->addField([
            'game_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'genre_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
        ]);
        $this->forge->addKey(['game_id', 'genre_id'], true);
        $this->forge->createTable('game_genres', true);
    }

    public function down()
    {
        $this->forge->dropTable('game_genres', true);
        $this->forge->dropTable('genres', true);
        $this->forge->dropTable('game_requirements', true);
        $this->forge->dropTable('game_media', true);
        $this->forge->dropTable('game_descriptions', true);
        $this->forge->dropTable('games', true);
        $this->forge->dropTable('users', true);
    }
}
