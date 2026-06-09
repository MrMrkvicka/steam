<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Steam extends BaseConfig
{
    /**
     * Number of games displayed per page on the paginated cards view.
     */
    public int $gamesPerPage = 12;
}
