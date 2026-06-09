<?php

namespace App\Models;

use CodeIgniter\Model;

class GameModel extends Model
{
    protected $table            = 'steam';
    protected $primaryKey       = 'appid';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    
    // Enable soft deletes as required
    protected $useSoftDeletes   = true;
    
    protected $allowedFields    = [
        'appid', 'name', 'release_date', 'english', 'developer', 'publisher', 
        'platforms', 'required_age', 'categories', 'genres', 'achievements', 
        'owners', 'price'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';
}
