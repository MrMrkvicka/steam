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

        // 2. steam Table
        $this->forge->addField([
            'appid' => [
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
        $this->forge->addKey('appid', true);
        $this->forge->createTable('steam', true);

        // 3. steam_description Table
        $this->forge->addField([
            'steam_appid' => [
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
        $this->forge->addKey('steam_appid', true);
        $this->forge->createTable('steam_description', true);

        // 4. steam_media Table
        $this->forge->addField([
            'steam_appid' => [
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
        $this->forge->addKey('steam_appid', true);
        $this->forge->createTable('steam_media', true);

        // 5. steam_requirements Table
        $this->forge->addField([
            'steam_appid' => [
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
        $this->forge->addKey('steam_appid', true);
        $this->forge->createTable('steam_requirements', true);

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

        // 7. steam_genres Table (M:N Pivot Table)
        $this->forge->addField([
            'steam_appid' => [
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
        $this->forge->addKey(['steam_appid', 'genre_id'], true);
        $this->forge->createTable('steam_genres', true);
    }

    public function down()
    {
        $this->forge->dropTable('steam_genres', true);
        $this->forge->dropTable('genres', true);
        $this->forge->dropTable('steam_requirements', true);
        $this->forge->dropTable('steam_media', true);
        $this->forge->dropTable('steam_description', true);
        $this->forge->dropTable('steam', true);
        $this->forge->dropTable('users', true);
    }
}
