<?php

namespace App\Models;

use CodeIgniter\Model;

class Game extends Model
{
    protected $table            = 'games';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    
    // Enable soft deletes
    protected $useSoftDeletes   = true;
    
    protected $allowedFields    = [
        'id', 'name', 'release_date', 'english', 'developer', 'publisher', 
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
