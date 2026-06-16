<?php

namespace App\Controllers;

use App\Models\GameModel;
use App\Libraries\SteamHelper;

class Home extends BaseController
{
    protected $gameModel;
    protected $steamHelper;
    protected $db;

    public function __construct()
    {
        $this->gameModel = new GameModel();
        $this->steamHelper = new SteamHelper();
        $this->db = \Config\Database::connect();
    }

    /**
     * Homepage dashboard view.
     */
    public function index(): string
    {
        $session = session();
        $library = $session->get('library') ?? [];

        $myGames = [];
        if (!empty($library)) {
            // Fetch games that are in the user's library
            $myGames = $this->gameModel
                ->select('steam.*, steam_media.background')
                ->join('steam_media', 'steam_media.steam_appid = steam.appid', 'left')
                ->whereIn('steam.appid', $library)
                ->findAll();
        }

        // Fetch featured/recent games (e.g. cheapest 4 games or 4 recently added)
        $featuredGames = $this->gameModel
            ->select('steam.*, steam_media.background')
            ->join('steam_media', 'steam_media.steam_appid = steam.appid', 'left')
            ->orderBy('price', 'ASC')
            ->where('price >', 0)
            ->limit(4)
            ->findAll();

        // Get simple database statistics for quick info cards
        $totalGames = $this->gameModel->countAllResults();
        
        $avgPriceQuery = $this->gameModel->select('AVG(price) as avg_price')->first();
        $avgPrice = $avgPriceQuery['avg_price'] ?? 0;

        return view('home', [
            'title'         => 'Nástěnka | Steam DB',
            'myGames'       => $myGames,
            'featuredGames' => $featuredGames,
            'totalGames'    => $totalGames,
            'avgPrice'      => $avgPrice,
            'steamHelper'   => $this->steamHelper,
        ]);
    }

    /**
     * Toggles a game's presence in the user's session-based library.
     */
    public function toggleLibrary($appid)
    {
        $session = session();
        $library = $session->get('library') ?? [];

        // Fetch game to confirm it exists
        $game = $this->gameModel->find($appid);
        if (!$game) {
            return redirect()->back()->with('error', 'Hra nebyla nalezena.');
        }

        if (in_array($appid, $library)) {
            // Remove from library
            $library = array_diff($library, [$appid]);
            $session->set('library', array_values($library));
            return redirect()->back()->with('success', 'Hra "' . esc($game['name']) . '" byla odebrána z vaší knihovny.');
        } else {
            // Add to library
            $library[] = (int)$appid;
            $session->set('library', $library);
            return redirect()->back()->with('success', 'Hra "' . esc($game['name']) . '" byla přidána do vaší knihovny.');
        }
    }
}
