<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SteamSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();
        
        // Disable foreign key checks for seeding
        $db->query('SET FOREIGN_KEY_CHECKS=0;');

        // 1. Seed admin user
        $user_data = [
            'username'      => 'admin',
            'password_hash' => password_hash('admin123', PASSWORD_DEFAULT),
            'created_at'    => date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s'),
        ];
        $db->table('users')->insert($user_data);
        echo "Admin user seeded: admin / admin123\n";

        $dumpDir = WRITEPATH . 'db_dump';

        // 2. Load and seed steam table
        $steamFile = $dumpDir . '/steam.json';
        if (file_exists($steamFile)) {
            $games = json_decode(file_get_contents($steamFile), true);
            
            // Collect unique genres
            $unique_genres = [];
            foreach ($games as $game) {
                if (!empty($game['genres'])) {
                    $g_list = explode(',', $game['genres']);
                    foreach ($g_list as $g) {
                        $g_trimmed = trim($g);
                        if ($g_trimmed !== '' && !in_array($g_trimmed, $unique_genres)) {
                            $unique_genres[] = $g_trimmed;
                        }
                    }
                }
            }

            // Insert genres
            $genre_map = []; // name => id
            foreach ($unique_genres as $genre_name) {
                $db->table('genres')->insert(['name' => $genre_name]);
                $genre_map[$genre_name] = $db->insertID();
            }
            echo "Seeded " . count($unique_genres) . " genres.\n";

            // Insert games & their M:N genre relations
            $count = 0;
            foreach ($games as $game) {
                $db->table('steam')->insert([
                    'appid'        => $game['appid'],
                    'name'         => $game['name'],
                    'release_date' => $game['release_date'],
                    'english'      => isset($game['english']) ? (int)$game['english'] : 1,
                    'developer'    => $game['developer'],
                    'publisher'    => $game['publisher'],
                    'platforms'    => $game['platforms'],
                    'required_age' => $game['required_age'],
                    'categories'   => $game['categories'],
                    'genres'       => $game['genres'],
                    'achievements' => $game['achievements'],
                    'owners'       => $game['owners'],
                    'price'        => $game['price'],
                    'created_at'   => date('Y-m-d H:i:s'),
                    'updated_at'   => date('Y-m-d H:i:s'),
                ]);

                // Insert genre relations
                if (!empty($game['genres'])) {
                    $g_list = explode(',', $game['genres']);
                    foreach ($g_list as $g) {
                        $g_trimmed = trim($g);
                        if (isset($genre_map[$g_trimmed])) {
                            $db->table('steam_genres')->insert([
                                'steam_appid' => $game['appid'],
                                'genre_id'    => $genre_map[$g_trimmed],
                            ]);
                        }
                    }
                }
                $count++;
            }
            echo "Seeded $count games into steam table with genre relationships.\n";
        }

        // 3. Seed steam_description table
        $descFile = $dumpDir . '/steam_description.json';
        if (file_exists($descFile)) {
            $descriptions = json_decode(file_get_contents($descFile), true);
            foreach ($descriptions as $desc) {
                $db->table('steam_description')->insert([
                    'steam_appid'          => $desc['steam_appid'],
                    'detailed_description' => $desc['detailed_description'],
                    'about_the_game'       => $desc['about_the_game'],
                    'short_description'    => $desc['short_description'],
                ]);
            }
            echo "Seeded " . count($descriptions) . " descriptions.\n";
        }

        // 4. Seed steam_media table
        $mediaFile = $dumpDir . '/steam_media.json';
        if (file_exists($mediaFile)) {
            $media = json_decode(file_get_contents($mediaFile), true);
            foreach ($media as $med) {
                $db->table('steam_media')->insert([
                    'steam_appid' => $med['steam_appid'],
                    'screenshots' => $med['screenshots'],
                    'background'  => $med['background'],
                ]);
            }
            echo "Seeded " . count($media) . " media rows.\n";
        }

        // 5. Seed steam_requirements table
        $reqFile = $dumpDir . '/steam_requirements.json';
        if (file_exists($reqFile)) {
            $requirements = json_decode(file_get_contents($reqFile), true);
            foreach ($requirements as $req) {
                $db->table('steam_requirements')->insert([
                    'steam_appid'        => $req['steam_appid'],
                    'pc_requirements'    => $req['pc_requirements'],
                    'mac_requirements'   => $req['mac_requirements'],
                    'linux_requirements' => $req['linux_requirements'],
                ]);
            }
            echo "Seeded " . count($requirements) . " requirements.\n";
        }

        // Re-enable foreign key checks
        $db->query('SET FOREIGN_KEY_CHECKS=1;');
    }
}
