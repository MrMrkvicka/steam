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
        $appid = $game['appid'] ?? null;

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
}
