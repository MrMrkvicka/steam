<?php

namespace App\Libraries;

class SteamHelper
{
    /**
     * Generates HTML breadcrumbs navigation using Bootstrap 5 classes.
     *
     * @param array $crumbs Associative array where keys are labels and values are URLs (or null for active page)
     * @return string The generated HTML string for breadcrumbs
     */
    public function generateBreadcrumbs(array $crumbs): string
    {
        $html = '<nav aria-label="breadcrumb">';
        $html .= '<ol class="breadcrumb bg-dark p-2 rounded" style="--bs-breadcrumb-divider: \'>\';">';
        
        // Always add Home first
        $html .= '<li class="breadcrumb-item"><a href="' . base_url() . '" class="text-info text-decoration-none"><i class="fas fa-home me-1"></i>Domů</a></li>';
        
        foreach ($crumbs as $label => $url) {
            if ($url) {
                $html .= '<li class="breadcrumb-item"><a href="' . base_url($url) . '" class="text-info text-decoration-none">' . esc($label) . '</a></li>';
            } else {
                $html .= '<li class="breadcrumb-item active text-light" aria-current="page">' . esc($label) . '</li>';
            }
        }
        
        $html .= '</ol>';
        $html .= '</nav>';
        
        return $html;
    }

    /**
     * Formats a game's price for display.
     * Returns "Zdarma" if price is 0, or formats with currency symbol.
     *
     * @param float $price The numeric price of the game
     * @return string The formatted price string (e.g. "9.99 €" or "Zdarma")
     */
    public function formatPrice(float $price): string
    {
        if ($price <= 0.0) {
            return '<span class="badge bg-success">Zdarma</span>';
        }
        return '<span class="text-warning fw-bold">' . number_format($price, 2, '.', ' ') . ' €</span>';
    }

    /**
     * Formats the platforms string to show icons or nicely styled text.
     *
     * @param string $platforms Semicolon-separated platforms (e.g. "windows;mac;linux")
     * @return string HTML containing platform badges/icons
     */
    public function formatPlatforms(string $platforms): string
    {
        $parts = explode(';', $platforms);
        $html = '';
        foreach ($parts as $p) {
            $p_trimmed = trim(strtolower($p));
            if ($p_trimmed === 'windows') {
                $html .= '<span class="badge bg-secondary me-1"><i class="fab fa-windows me-1"></i>Windows</span>';
            } elseif ($p_trimmed === 'mac') {
                $html .= '<span class="badge bg-secondary me-1"><i class="fab fa-apple me-1"></i>macOS</span>';
            } elseif ($p_trimmed === 'linux') {
                $html .= '<span class="badge bg-secondary me-1"><i class="fab fa-linux me-1"></i>Linux</span>';
            } else {
                $html .= '<span class="badge bg-secondary me-1">' . esc($p) . '</span>';
            }
        }
        return $html;
    }

    /**
     * Returns a valid image URL for a game.
     * If a custom background image is uploaded, it returns it.
     * Otherwise, it falls back to the official Steam header CDN URL using the AppID.
     * If the AppID is invalid, it returns a placeholder.
     *
     * @param array $game The game array containing appid and background
     * @return string The image URL
     */
    public function getGameImage(array $game): string
    {
        $background = $game['background'] ?? '';
        $appid = $game['id'] ?? $game['appid'] ?? null;

        // If background is set and is not a placeholder or empty
        if (!empty($background) && strpos($background, 'placeholder') === false && strpos($background, 'via.placeholder') === false) {
            return $background;
        }

        // Fallback to Steam CDN header
        if (!empty($appid)) {
            return 'https://cdn.akamai.steamstatic.com/steam/apps/' . $appid . '/header.jpg';
        }

        return 'https://via.placeholder.com/460x215.png?text=No+Image'; // fallback
    }

    /**
     * Parses the dictionary/serialized requirements string from the database
     * and returns formatted HTML for display.
     *
     * @param string|null $req Raw requirements string
     * @return string Formatted HTML
     */
    public function parseRequirements(?string $req): string
    {
        if (empty($req)) {
            return 'Minimální požadavky nebyly definovány.';
        }

        $req = trim($req);

        // If it doesn't look like a serialized Python dict or JSON, just return it
        if (strpos($req, 'minimum') === false) {
            return nl2br(esc($req));
        }

        $minimum = '';
        $recommended = '';

        // Try to extract minimum content
        if (preg_match("/['\"]minimum['\"]\s*:\s*['\"]([\s\S]*?)(?:['\"],?\s*['\"]recommended['\"]|['\"]?\s*}?$)/i", $req, $matches)) {
            $minimum = $matches[1];
        }

        // Try to extract recommended content
        if (preg_match("/['\"]recommended['\"]\s*:\s*['\"]([\s\S]*?)(?:['\"]?\s*}?$)/i", $req, $matches)) {
            $recommended = $matches[1];
        }

        // Unescape common characters
        $minimum = stripcslashes($minimum);
        $recommended = stripcslashes($recommended);

        // Clean up any remaining trailing formatting from malformed strings
        $minimum = rtrim($minimum, "\"',} ");
        $recommended = rtrim($recommended, "\"',} ");

        // If both are empty, just display the raw text
        if (empty($minimum) && empty($recommended)) {
            return nl2br(esc($req));
        }

        $html = '';
        if (!empty($minimum)) {
            if (strpos($minimum, '<') === false) {
                $minimum = nl2br(esc($minimum));
            }
            $html .= '<div class="req-minimum mb-3">' . $minimum . '</div>';
        }
        
        if (!empty($recommended)) {
            if (strpos($recommended, '<') === false) {
                $recommended = nl2br(esc($recommended));
            }
            $html .= '<div class="req-recommended">' . $recommended . '</div>';
        }

        return $html;
    }

    /**
     * Cleans requirements text for form input fields / textareas.
     *
     * @param string|null $req Raw requirements string
     * @return string Plain text
     */
    public function getCleanRequirements(?string $req): string
    {
        if (empty($req)) {
            return '';
        }
        
        $req = trim($req);
        if (strpos($req, 'minimum') === false) {
            return $req;
        }
        
        $minimum = '';
        $recommended = '';

        if (preg_match("/['\"]minimum['\"]\s*:\s*['\"]([\s\S]*?)(?:['\"],?\s*['\"]recommended['\"]|['\"]?\s*}?$)/i", $req, $matches)) {
            $minimum = $matches[1];
        }
        if (preg_match("/['\"]recommended['\"]\s*:\s*['\"]([\s\S]*?)(?:['\"]?\s*}?$)/i", $req, $matches)) {
            $recommended = $matches[1];
        }

        $minimum = stripcslashes($minimum);
        $recommended = stripcslashes($recommended);

        $minimum = rtrim($minimum, "\"',} ");
        $recommended = rtrim($recommended, "\"',} ");

        $text = '';
        if (!empty($minimum)) {
            $text .= "Minimum:\n" . strip_tags($minimum) . "\n\n";
        }
        if (!empty($recommended)) {
            $text .= "Doporučeno:\n" . strip_tags($recommended);
        }
        
        return trim($text) ?: $req;
    }

    /**
     * Converts a string to a safe, ASCII-only SEO friendly URL slug.
     * Transliterates Czech and other accented characters.
     *
     * @param string $title The raw string (e.g. game title)
     * @return string The safe ASCII slug
     */
    public function slugify(string $title): string
    {
        $table = [
            'ä'=>'a', 'á'=>'a', 'à'=>'a', 'â'=>'a', 'ã'=>'a', 'å'=>'a', 'æ'=>'ae', 'ç'=>'c',
            'é'=>'e', 'è'=>'e', 'ê'=>'e', 'ë'=>'e', 'ě'=>'e', 'í'=>'i', 'ì'=>'i', 'î'=>'i',
            'ï'=>'i', 'ň'=>'n', 'ñ'=>'n', 'ó'=>'o', 'ò'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'o',
            'ø'=>'o', 'œ'=>'oe', 'ř'=>'r', 'š'=>'s', 'ß'=>'ss', 'ť'=>'t', 'ú'=>'u', 'ù'=>'u',
            'û'=>'u', 'ü'=>'u', 'ů'=>'u', 'ý'=>'y', 'ÿ'=>'y', 'ž'=>'z',
            'Ä'=>'a', 'Á'=>'a', 'À'=>'a', 'Â'=>'a', 'Ã'=>'a', 'Å'=>'a', 'Æ'=>'ae', 'Ç'=>'c',
            'É'=>'e', 'È'=>'e', 'Ê'=>'e', 'Ë'=>'e', 'Ě'=>'e', 'Í'=>'i', 'Ì'=>'i', 'Î'=>'i',
            'Ï'=>'i', 'Ň'=>'n', 'Ñ'=>'n', 'Ó'=>'o', 'Ò'=>'o', 'Ô'=>'o', 'Ö'=>'o',
            'Ø'=>'o', 'Œ'=>'oe', 'Ř'=>'r', 'Š'=>'s', 'Ť'=>'t', 'Ú'=>'u', 'Ù'=>'u',
            'Û'=>'u', 'Ü'=>'u', 'Ů'=>'u', 'Ý'=>'y', 'Ÿ'=>'y', 'Ž'=>'z', 'ď'=>'d', 'Ď'=>'d',
            'ť'=>'t', 'Ť'=>'t', 'ň'=>'n', 'Ň'=>'n', 'ó'=>'o', 'Ó'=>'o'
        ];

        $title = strtr($title, $table);
        // Remove any non-alphanumeric characters, except spaces and dashes
        $title = preg_replace('/[^\w\s-]/u', '', $title);
        // Lowercase
        $title = mb_strtolower($title);
        // Replace spaces and underscores with dashes
        $title = preg_replace('/[\s_-]+/u', '-', $title);
        // Trim dashes
        $title = trim($title, '-');

        return $title ?: 'game';
    }
}
