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
        $userData = [
            'username'      => 'admin',
            'password_hash' => password_hash('admin123', PASSWORD_DEFAULT),
            'created_at'    => date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s'),
        ];
        $db->table('users')->insert($userData);
        echo "Admin user seeded: admin / admin123\n";

        $dumpDir = WRITEPATH . 'db_dump';

        // 2. Load and seed games table
        $steamFile = $dumpDir . '/steam.json';
        if (file_exists($steamFile)) {
            $games = json_decode(file_get_contents($steamFile), true);
            
            // Collect unique genres
            $uniqueGenres = [];
            foreach ($games as $game) {
                if (!empty($game['genres'])) {
                    $gList = explode(',', $game['genres']);
                    foreach ($gList as $g) {
                        $gTrimmed = trim($g);
                        if ($gTrimmed !== '' && !in_array($gTrimmed, $uniqueGenres)) {
                            $uniqueGenres[] = $gTrimmed;
                        }
                    }
                }
            }

            // Insert genres
            $genreMap = []; // name => id
            foreach ($uniqueGenres as $genreName) {
                $db->table('genres')->insert(['name' => $genreName]);
                $genreMap[$genreName] = $db->insertID();
            }
            echo "Seeded " . count($uniqueGenres) . " genres.\n";

            // Insert games & their M:N genre relations
            $count = 0;
            foreach ($games as $game) {
                $db->table('games')->insert([
                    'id'           => $game['appid'],
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
                    $gList = explode(',', $game['genres']);
                    foreach ($gList as $g) {
                        $gTrimmed = trim($g);
                        if (isset($genreMap[$gTrimmed])) {
                            $db->table('game_genres')->insert([
                                'game_id'  => $game['appid'],
                                'genre_id' => $genreMap[$gTrimmed],
                            ]);
                        }
                    }
                }
                $count++;
            }
            echo "Seeded $count games into games table with genre relationships.\n";
        }

        // 3. Seed game_descriptions table
        $descFile = $dumpDir . '/steam_description.json';
        if (file_exists($descFile)) {
            $descriptions = json_decode(file_get_contents($descFile), true);
            foreach ($descriptions as $desc) {
                $db->table('game_descriptions')->insert([
                    'game_id'              => $desc['steam_appid'],
                    'detailed_description' => $desc['detailed_description'],
                    'about_the_game'       => $desc['about_the_game'],
                    'short_description'    => $desc['short_description'],
                ]);
            }
            echo "Seeded " . count($descriptions) . " descriptions.\n";
        }

        // 4. Seed game_media table
        $mediaFile = $dumpDir . '/steam_media.json';
        if (file_exists($mediaFile)) {
            $media = json_decode(file_get_contents($mediaFile), true);
            foreach ($media as $med) {
                $db->table('game_media')->insert([
                    'game_id'     => $med['steam_appid'],
                    'screenshots' => $med['screenshots'],
                    'background'  => $med['background'],
                ]);
            }
            echo "Seeded " . count($media) . " media rows.\n";
        }

        // 5. Seed game_requirements table
        $reqFile = $dumpDir . '/steam_requirements.json';
        if (file_exists($reqFile)) {
            $requirements = json_decode(file_get_contents($reqFile), true);
            foreach ($requirements as $req) {
                $db->table('game_requirements')->insert([
                    'game_id'            => $req['steam_appid'],
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
