<?php

namespace App\Controllers;

use App\Models\Game;
use App\Models\User;
use App\Libraries\SteamHelper;
use Config\Steam as SteamConfig;

class Games extends BaseController
{
    protected $gameModel;
    protected $steamHelper;
    protected $steamConfig;
    protected $db;

    public function __construct()
    {
        $this->gameModel = new Game();
        $this->steamHelper = new SteamHelper();
        $this->steamConfig = new SteamConfig();
        $this->db = \Config\Database::connect();
    }

    /**
     * Lists games in a paginated card view.
     *
     * @return string
     */
    public function index()
    {
        // Join with media to get background images for cards
        $this->gameModel->select('games.*, game_media.background');
        $this->gameModel->join('game_media', 'game_media.game_id = games.id', 'left');
        
        $filter = $this->request->getGet('filter');
        $activeFilter = '';
        
        if ($filter === 'library') {
            $library = session()->get('library') ?? [];
            if (!empty($library)) {
                $this->gameModel->whereIn('games.id', $library);
            } else {
                $this->gameModel->where('games.id', 0); // Force empty result
            }
            $activeFilter = 'library';
        } elseif ($filter === 'created') {
            $created = session()->get('created_games') ?? [];
            if (!empty($created)) {
                $this->gameModel->whereIn('games.id', $created);
            } else {
                $this->gameModel->where('games.id', 0); // Force empty result
            }
            $activeFilter = 'created';
        }
        
        $search = $this->request->getGet('search');
        if (!empty($search)) {
            $this->gameModel->like('games.name', trim($search));
        }
        
        $games = $this->gameModel->paginate($this->steamConfig->perPage ?? 20);
        
        return view('games/index', [
            'title'        => 'Přehled her | Steam DB',
            'games'        => $games,
            'pager'        => $this->gameModel->pager,
            'steamHelper'  => $this->steamHelper,
            'activeFilter' => $activeFilter,
            'search'       => $search,
        ]);
    }

    /**
     * Shows detail of a specific game.
     *
     * @param int $id The game ID
     * @param string $slug Optional slug for SEO url
     * @return string|\CodeIgniter\HTTP\RedirectResponse
     */
    public function show($id, $slug = '')
    {
        $game = $this->gameModel
            ->select('games.*, game_descriptions.detailed_description, game_descriptions.about_the_game, game_descriptions.short_description, game_media.background, game_media.screenshots, game_requirements.pc_requirements, game_requirements.mac_requirements, game_requirements.linux_requirements')
            ->join('game_descriptions', 'game_descriptions.game_id = games.id', 'left')
            ->join('game_media', 'game_media.game_id = games.id', 'left')
            ->join('game_requirements', 'game_requirements.game_id = games.id', 'left')
            ->where('games.id', $id)
            ->first();

        if (!$game) {
            return redirect()->to('games')->with('error', 'Hra nebyla nalezena.');
        }

        // Get genres using M:N relationship
        $genres = $this->db->table('game_genres')
            ->select('genres.name')
            ->join('genres', 'genres.id = game_genres.genre_id')
            ->where('game_genres.game_id', $id)
            ->get()
            ->getResultArray();

        $genreNames = array_column($genres, 'name');

        return view('games/show', [
            'title'       => $game['name'] . ' | Steam DB',
            'game'        => $game,
            'genres'      => $genreNames,
            'steamHelper' => $this->steamHelper,
        ]);
    }

    /**
     * Displays database statistics using SQL aggregation functions.
     *
     * @return string
     */
    public function stats()
    {
        // 1. Total games count (excluding soft deleted)
        $totalGames = $this->gameModel->countAllResults();

        // 2. Average price of games
        $avgPriceQuery = $this->gameModel->select('AVG(price) as avg_price')->first();
        $avgPrice = $avgPriceQuery['avg_price'] ?? 0;

        // 3. Game with maximum achievements
        $maxAchievementsQuery = $this->gameModel->select('name, achievements')->orderBy('achievements', 'DESC')->first();

        // 4. Group by genres with COUNT (aggregation with JOIN)
        $genresStats = $this->db->table('game_genres')
            ->select('genres.name, COUNT(game_genres.game_id) as game_count')
            ->join('genres', 'genres.id = game_genres.genre_id')
            ->groupBy('genres.id')
            ->orderBy('game_count', 'DESC')
            ->limit(10)
            ->get()
            ->getResultArray();

        return view('games/stats', [
            'title'                => 'Statistiky databáze | Steam DB',
            'totalGames'           => $totalGames,
            'avgPrice'             => $avgPrice,
            'maxAchievementsGame'  => $maxAchievementsQuery,
            'genresStats'          => $genresStats,
            'steamHelper'          => $this->steamHelper,
        ]);
    }

    /**
     * Displays form to add a new game.
     *
     * @return string|\CodeIgniter\HTTP\RedirectResponse
     */
    public function add()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login')->with('error', 'Pro přidání hry se musíte přihlásit.');
        }

        // Get unique list of developers and publishers for dropdown selection (DB sourced)
        $developers = $this->db->table('games')
            ->select('developer')
            ->distinct()
            ->where('developer !=', '')
            ->orderBy('developer', 'ASC')
            ->limit(100)
            ->get()
            ->getResultArray();

        $publishers = $this->db->table('games')
            ->select('publisher')
            ->distinct()
            ->where('publisher !=', '')
            ->orderBy('publisher', 'ASC')
            ->limit(100)
            ->get()
            ->getResultArray();

        // Get all genres for multiselect checkboxes
        $genres = $this->db->table('genres')->orderBy('name', 'ASC')->get()->getResultArray();

        return view('games/add', [
            'title'       => 'Přidat hru | Steam DB',
            'developers'  => array_column($developers, 'developer'),
            'publishers'  => array_column($publishers, 'publisher'),
            'genres'      => $genres,
            'steamHelper' => $this->steamHelper,
        ]);
    }

    /**
     * Saves a newly created game in DB (POST handler).
     *
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function create()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login')->with('error', 'Nepovolený přístup.');
        }

        $rules = [
            'id'           => 'required|integer|is_unique[games.id]',
            'name'         => 'required|min_length[3]|max_length[255]',
            'release_date' => 'required|valid_date',
            'developer'    => 'required',
            'platforms'    => 'required',
            'price'        => 'required|numeric',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Chyba validace: ' . implode(' ', $this->validator->getErrors()));
        }

        // Handle Image Upload
        $backgroundPath = '';
        $img = $this->request->getFile('background_image');
        if ($img && $img->isValid() && !$img->hasMoved()) {
            $newName = $img->getRandomName();
            // Create uploads directory in FCPATH if it doesn't exist
            $uploadDir = FCPATH . 'uploads';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $img->move($uploadDir, $newName);
            $backgroundPath = base_url('uploads/' . $newName);
        } else {
            $backgroundPath = 'https://via.placeholder.com/460x215.png?text=No+Image';
        }

        // Prepare game genres list as string for backwards compatibility
        $selectedGenres = $this->request->getPost('genres') ?? [];
        $genreNames = [];
        if (!empty($selectedGenres)) {
            $genreRows = $this->db->table('genres')->whereIn('id', $selectedGenres)->get()->getResultArray();
            $genreNames = array_column($genreRows, 'name');
        }
        $genresString = implode(';', $genreNames);

        // Begin transaction
        $this->db->transStart();

        $gameId = $this->request->getPost('id');

        // Insert main games record
        $this->gameModel->insert([
            'id'           => $gameId,
            'name'         => $this->request->getPost('name'),
            'release_date' => $this->request->getPost('release_date'),
            'english'      => (int)$this->request->getPost('english'),
            'developer'    => $this->request->getPost('developer'),
            'publisher'    => $this->request->getPost('publisher'),
            'platforms'    => $this->request->getPost('platforms'),
            'required_age' => (int)$this->request->getPost('required_age'),
            'categories'   => $this->request->getPost('categories') ?? 'Single-player',
            'genres'       => $genresString,
            'achievements' => (int)$this->request->getPost('achievements'),
            'owners'       => '0-20000',
            'price'        => $this->request->getPost('price'),
        ]);

        // Insert descriptions
        $this->db->table('game_descriptions')->insert([
            'game_id'              => $gameId,
            'detailed_description' => $this->request->getPost('detailed_description') ?? '',
            'about_the_game'       => $this->request->getPost('about_the_game') ?? '',
            'short_description'    => $this->request->getPost('short_description') ?? '',
        ]);

        // Insert media
        $this->db->table('game_media')->insert([
            'game_id'     => $gameId,
            'screenshots' => '',
            'background'  => $backgroundPath,
        ]);

        // Insert requirements
        $this->db->table('game_requirements')->insert([
            'game_id'            => $gameId,
            'pc_requirements'    => $this->request->getPost('pc_requirements') ?? 'Minimum requirements not specified.',
            'mac_requirements'   => '',
            'linux_requirements' => '',
        ]);

        // Insert genres relationships (M:N)
        foreach ($selectedGenres as $genreId) {
            $this->db->table('game_genres')->insert([
                'game_id'  => $gameId,
                'genre_id' => $genreId,
            ]);
        }

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Hru se nepodařilo uložit do databáze.');
        }

        // Save to created games list in session
        $createdGames = session()->get('created_games') ?? [];
        $createdGames[] = (int)$gameId;
        session()->set('created_games', $createdGames);

        // Redirect with alert info
        return redirect()->to('games')->with('success', 'Hra "' . esc($this->request->getPost('name')) . '" byla úspěšně přidána!');
    }

    /**
     * Displays form to edit a game.
     *
     * @param int $id The game ID
     * @return string|\CodeIgniter\HTTP\RedirectResponse
     */
    public function edit($id)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login')->with('error', 'Pro úpravu hry se musíte přihlásit.');
        }

        $game = $this->gameModel
            ->select('games.*, game_descriptions.detailed_description, game_descriptions.about_the_game, game_descriptions.short_description, game_media.background, game_requirements.pc_requirements')
            ->join('game_descriptions', 'game_descriptions.game_id = games.id', 'left')
            ->join('game_media', 'game_media.game_id = games.id', 'left')
            ->join('game_requirements', 'game_requirements.game_id = games.id', 'left')
            ->where('games.id', $id)
            ->first();

        if (!$game) {
            return redirect()->to('games')->with('error', 'Hra nebyla nalezena.');
        }

        // Fetch selected genres ids for checkbox values
        $selectedGenresQuery = $this->db->table('game_genres')
            ->select('genre_id')
            ->where('game_id', $id)
            ->get()
            ->getResultArray();
        $selectedGenres = array_column($selectedGenresQuery, 'genre_id');

        // Sourced from DB for selection
        $developers = $this->db->table('games')
            ->select('developer')
            ->distinct()
            ->where('developer !=', '')
            ->orderBy('developer', 'ASC')
            ->limit(100)
            ->get()
            ->getResultArray();

        $publishers = $this->db->table('games')
            ->select('publisher')
            ->distinct()
            ->where('publisher !=', '')
            ->orderBy('publisher', 'ASC')
            ->limit(100)
            ->get()
            ->getResultArray();

        $genres = $this->db->table('genres')->orderBy('name', 'ASC')->get()->getResultArray();

        return view('games/edit', [
            'title'          => 'Upravit ' . $game['name'] . ' | Steam DB',
            'game'           => $game,
            'developers'     => array_column($developers, 'developer'),
            'publishers'     => array_column($publishers, 'publisher'),
            'genres'         => $genres,
            'selectedGenres' => $selectedGenres,
            'steamHelper'    => $this->steamHelper,
        ]);
    }

    /**
     * Updates an existing game in DB.
     *
     * @param int $id The game ID
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function update($id)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login')->with('error', 'Nepovolený přístup.');
        }

        $rules = [
            'name'         => 'required|min_length[3]|max_length[255]',
            'release_date' => 'required|valid_date',
            'developer'    => 'required',
            'platforms'    => 'required',
            'price'        => 'required|numeric',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Chyba validace: ' . implode(' ', $this->validator->getErrors()));
        }

        // Handle Image Upload
        $backgroundPath = $this->request->getPost('current_background');
        $img = $this->request->getFile('background_image');
        if ($img && $img->isValid() && !$img->hasMoved()) {
            $newName = $img->getRandomName();
            $uploadDir = FCPATH . 'uploads';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $img->move($uploadDir, $newName);
            $backgroundPath = base_url('uploads/' . $newName);
        }

        // Prepare game genres list as string for backwards compatibility
        $selectedGenres = $this->request->getPost('genres') ?? [];
        $genreNames = [];
        if (!empty($selectedGenres)) {
            $genreRows = $this->db->table('genres')->whereIn('id', $selectedGenres)->get()->getResultArray();
            $genreNames = array_column($genreRows, 'name');
        }
        $genresString = implode(';', $genreNames);

        // Begin transaction
        $this->db->transStart();

        // Update main games record
        $this->gameModel->update($id, [
            'name'         => $this->request->getPost('name'),
            'release_date' => $this->request->getPost('release_date'),
            'english'      => (int)$this->request->getPost('english'),
            'developer'    => $this->request->getPost('developer'),
            'publisher'    => $this->request->getPost('publisher'),
            'platforms'    => $this->request->getPost('platforms'),
            'required_age' => (int)$this->request->getPost('required_age'),
            'categories'   => $this->request->getPost('categories') ?? 'Single-player',
            'genres'       => $genresString,
            'achievements' => (int)$this->request->getPost('achievements'),
            'price'        => $this->request->getPost('price'),
        ]);

        // Update description table
        $this->db->table('game_descriptions')
            ->where('game_id', $id)
            ->update([
                'detailed_description' => $this->request->getPost('detailed_description') ?? '',
                'about_the_game'       => $this->request->getPost('about_the_game') ?? '',
                'short_description'    => $this->request->getPost('short_description') ?? '',
            ]);

        // Update media table
        $this->db->table('game_media')
            ->where('game_id', $id)
            ->update([
                'background' => $backgroundPath,
            ]);

        // Update requirements table
        $this->db->table('game_requirements')
            ->where('game_id', $id)
            ->update([
                'pc_requirements' => $this->request->getPost('pc_requirements') ?? '',
            ]);

        // Clear and rewrite genre relations (M:N)
        $this->db->table('game_genres')->where('game_id', $id)->delete();
        foreach ($selectedGenres as $genreId) {
            $this->db->table('game_genres')->insert([
                'game_id'  => $id,
                'genre_id' => $genreId,
            ]);
        }

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Změny se nepodařilo uložit do databáze.');
        }

        return redirect()->to('games/show/' . $id . '/' . $this->steamHelper->slugify($this->request->getPost('name')))->with('success', 'Hra byla úspěšně upravena!');
    }

    /**
     * Soft deletes a game.
     *
     * @param int $id The game ID
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function delete($id)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login')->with('error', 'Pro smazání hry se musíte přihlásit.');
        }

        $game = $this->gameModel->find($id);
        if (!$game) {
            return redirect()->to('games')->with('error', 'Hra nebyla nalezena.');
        }

        // Soft delete using Model delete method
        if ($this->gameModel->delete($id)) {
            return redirect()->to('games')->with('success', 'Hra "' . esc($game['name']) . '" byla úspěšně smazána.');
        }

        return redirect()->to('games')->with('error', 'Při mazání hry došlo k chybě.');
    }
}
