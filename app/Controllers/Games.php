<?php

namespace App\Controllers;

use App\Models\GameModel;
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
        $this->gameModel = new GameModel();
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
        $this->gameModel->select('steam.*, steam_media.background');
        $this->gameModel->join('steam_media', 'steam_media.steam_appid = steam.appid', 'left');
        
        $games = $this->gameModel->paginate($this->steamConfig->gamesPerPage);
        
        return view('games/index', [
            'title'       => 'Přehled her | Steam DB',
            'games'       => $games,
            'pager'       => $this->gameModel->pager,
            'steamHelper' => $this->steamHelper,
        ]);
    }

    /**
     * Shows detail of a specific game.
     *
     * @param int $id The game AppID
     * @param string $slug Optional slug for SEO url
     * @return string|\CodeIgniter\HTTP\RedirectResponse
     */
    public function show($id, $slug = '')
    {
        $game = $this->gameModel
            ->select('steam.*, steam_description.detailed_description, steam_description.about_the_game, steam_description.short_description, steam_media.background, steam_media.screenshots, steam_requirements.pc_requirements, steam_requirements.mac_requirements, steam_requirements.linux_requirements')
            ->join('steam_description', 'steam_description.steam_appid = steam.appid', 'left')
            ->join('steam_media', 'steam_media.steam_appid = steam.appid', 'left')
            ->join('steam_requirements', 'steam_requirements.steam_appid = steam.appid', 'left')
            ->where('steam.appid', $id)
            ->first();

        if (!$game) {
            return redirect()->to('/')->with('error', 'Hra nebyla nalezena.');
        }

        // Get genres using M:N relationship
        $genres = $this->db->table('steam_genres')
            ->select('genres.name')
            ->join('genres', 'genres.id = steam_genres.genre_id')
            ->where('steam_genres.steam_appid', $id)
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
        $genresStats = $this->db->table('steam_genres')
            ->select('genres.name, COUNT(steam_genres.steam_appid) as game_count')
            ->join('genres', 'genres.id = steam_genres.genre_id')
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
     * Displays form to create a new game.
     *
     * @return string|\CodeIgniter\HTTP\RedirectResponse
     */
    public function create()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login')->with('error', 'Pro přidání hry se musíte přihlásit.');
        }

        // Get unique list of developers and publishers for dropdown selection (DB sourced)
        $developers = $this->db->table('steam')
            ->select('developer')
            ->distinct()
            ->where('developer !=', '')
            ->orderBy('developer', 'ASC')
            ->limit(100)
            ->get()
            ->getResultArray();

        $publishers = $this->db->table('steam')
            ->select('publisher')
            ->distinct()
            ->where('publisher !=', '')
            ->orderBy('publisher', 'ASC')
            ->limit(100)
            ->get()
            ->getResultArray();

        // Get all genres for multiselect checkboxes
        $genres = $this->db->table('genres')->orderBy('name', 'ASC')->get()->getResultArray();

        return view('games/create', [
            'title'       => 'Přidat hru | Steam DB',
            'developers'  => array_column($developers, 'developer'),
            'publishers'  => array_column($publishers, 'publisher'),
            'genres'      => $genres,
            'steamHelper' => $this->steamHelper,
        ]);
    }

    /**
     * Saves a newly created game in DB.
     *
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function store()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login')->with('error', 'Nepovolený přístup.');
        }

        $rules = [
            'appid'        => 'required|integer|is_unique[steam.appid]',
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

        $appid = $this->request->getPost('appid');

        // Insert main steam record
        $this->gameModel->insert([
            'appid'        => $appid,
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
        $this->db->table('steam_description')->insert([
            'steam_appid'          => $appid,
            'detailed_description' => $this->request->getPost('detailed_description') ?? '',
            'about_the_game'       => $this->request->getPost('about_the_game') ?? '',
            'short_description'    => $this->request->getPost('short_description') ?? '',
        ]);

        // Insert media
        $this->db->table('steam_media')->insert([
            'steam_appid' => $appid,
            'screenshots' => '',
            'background'  => $backgroundPath,
        ]);

        // Insert requirements
        $this->db->table('steam_requirements')->insert([
            'steam_appid'        => $appid,
            'pc_requirements'    => $this->request->getPost('pc_requirements') ?? 'Minimum requirements not specified.',
            'mac_requirements'   => '',
            'linux_requirements' => '',
        ]);

        // Insert genres relationships (M:N)
        foreach ($selectedGenres as $genreId) {
            $this->db->table('steam_genres')->insert([
                'steam_appid' => $appid,
                'genre_id'    => $genreId,
            ]);
        }

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Hru se nepodařilo uložit do databáze.');
        }

        // Redirect with alert info
        return redirect()->to('/')->with('success', 'Hra "' . esc($this->request->getPost('name')) . '" byla úspěšně přidána!');
    }

    /**
     * Displays form to edit a game.
     *
     * @param int $id The game AppID
     * @return string|\CodeIgniter\HTTP\RedirectResponse
     */
    public function edit($id)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login')->with('error', 'Pro úpravu hry se musíte přihlásit.');
        }

        $game = $this->gameModel
            ->select('steam.*, steam_description.detailed_description, steam_description.about_the_game, steam_description.short_description, steam_media.background, steam_requirements.pc_requirements')
            ->join('steam_description', 'steam_description.steam_appid = steam.appid', 'left')
            ->join('steam_media', 'steam_media.steam_appid = steam.appid', 'left')
            ->join('steam_requirements', 'steam_requirements.steam_appid = steam.appid', 'left')
            ->where('steam.appid', $id)
            ->first();

        if (!$game) {
            return redirect()->to('/')->with('error', 'Hra nebyla nalezena.');
        }

        // Fetch selected genres ids for checkbox values
        $selectedGenresQuery = $this->db->table('steam_genres')
            ->select('genre_id')
            ->where('steam_appid', $id)
            ->get()
            ->getResultArray();
        $selectedGenres = array_column($selectedGenresQuery, 'genre_id');

        // Sourced from DB for selection
        $developers = $this->db->table('steam')
            ->select('developer')
            ->distinct()
            ->where('developer !=', '')
            ->orderBy('developer', 'ASC')
            ->limit(100)
            ->get()
            ->getResultArray();

        $publishers = $this->db->table('steam')
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
     * @param int $id The game AppID
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

        // Update main steam record
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
        $this->db->table('steam_description')
            ->where('steam_appid', $id)
            ->update([
                'detailed_description' => $this->request->getPost('detailed_description') ?? '',
                'about_the_game'       => $this->request->getPost('about_the_game') ?? '',
                'short_description'    => $this->request->getPost('short_description') ?? '',
            ]);

        // Update media table
        $this->db->table('steam_media')
            ->where('steam_appid', $id)
            ->update([
                'background' => $backgroundPath,
            ]);

        // Update requirements table
        $this->db->table('steam_requirements')
            ->where('steam_appid', $id)
            ->update([
                'pc_requirements' => $this->request->getPost('pc_requirements') ?? '',
            ]);

        // Clear and rewrite genre relations (M:N)
        $this->db->table('steam_genres')->where('steam_appid', $id)->delete();
        foreach ($selectedGenres as $genreId) {
            $this->db->table('steam_genres')->insert([
                'steam_appid' => $id,
                'genre_id'    => $genreId,
            ]);
        }

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Změny se nepodařilo uložit do databáze.');
        }

        return redirect()->to('games/show/' . $id . '/' . url_title($this->request->getPost('name')))->with('success', 'Hra byla úspěšně upravena!');
    }

    /**
     * Soft deletes a game.
     *
     * @param int $id The game AppID
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function delete($id)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login')->with('error', 'Pro smazání hry se musíte přihlásit.');
        }

        $game = $this->gameModel->find($id);
        if (!$game) {
            return redirect()->to('/')->with('error', 'Hra nebyla nalezena.');
        }

        // Soft delete using Model delete method
        if ($this->gameModel->delete($id)) {
            return redirect()->to('/')->with('success', 'Hra "' . esc($game['name']) . '" byla úspěšně smazána.');
        }

        return redirect()->to('/')->with('error', 'Při mazání hry došlo k chybě.');
    }
}
